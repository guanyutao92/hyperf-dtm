<?php

namespace App\Controller;

use App\JsonRpc\Tcc\OrderService;
use DtmClient\Annotation\Barrier;
use DtmClient\Saga;
use DtmClient\TransContext;
use GuzzleHttp\Client;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller(prefix: 'saga')]
class SagaController extends AbstractController
{
// -------------------------saga------------------------------------
    #[Inject]
    protected Saga $saga;
    #[Inject]
    protected OrderService $order;
    public string $order_url='http://192.168.0.106:9501';
    public string $product_url='http://192.168.0.106:9502';
    public string $user_url='http://192.168.0.106:9503';

    #[GetMapping(path: 'sageSuccess')]
    public function sageSuccess()
    {
        $uid=(int)$this->request->input('uid',0);
        $pid=(int)$this->request->input('pid',0);
        $num=(int)$this->request->input('num',0);
        echo '开始saga事务'.PHP_EOL;
        try{
            $client=new Client(['base_uri'=>$this->product_url,'timeout'=>2]);
            $response=$client->get('/product/getPrice?id='.$pid);
            $money=0;
            if($response->getStatusCode()==200){
                $body=$response->getBody()->getContents();
                $product= json_decode($body,true);
                $money=$num*($product['price']??0);
            }


            // 初始化saga事务
            $this->saga->init();
            // 增加库存扣减子事务
            $this->saga->add(
                $this->product_url.'/saga/deduct',
                $this->product_url.'/saga/deductCompensate',
                ['id'=>$pid,'num'=>$num]
            );
            $this->saga->add(
                $this->user_url.'/saga/deduct',
                $this->user_url.'/saga/deductCompensate',
                ['id'=>$uid,'money'=>$money]
            );
            // 设置并发请求，默认是顺序请求的，不管是否开启都会安照正向分支的相反方向（定义的顺序）执行回滚。
           // $this->saga->enableConcurrent();
            // 设置固定间隔重试为3秒一次，默认是指数退避。测试阶段推荐固定，不然等待太久
            TransContext::setRetryInterval(3);
            TransContext::setWaitResult(true);
            $this->saga->add(
                $this->order_url.'/saga/createOrder',
                $this->order_url.'/saga/createOrderCompensate',
                ['pid'=>$pid,'num'=>$num,'money'=>$money,'uid'=>$uid]
            );
            // 对于不可回滚的业务，可以通过一下方法，必须依赖指定的分支都成功才会执行该分支的业务
            // 以下表示分支2依赖于分支0,1都执行成功才会执行。
            $this->saga->addBranchOrder(2,[0,1]);
            // 提交saga事务,返回GuzzleHttp客户端对象。
            $rs=$this->saga->submit();
            var_dump($rs->getBody()->getContents());
        }catch (\Throwable $e)
        {
            var_dump($e->getMessage());
            return $this->response->json(['error'=>$e->getMessage()]);
        }
        return ['gid'=>TransContext::getGid()];


    }

    #[PostMapping(path: 'createOrder')]
    #[Barrier]
    public function createOrder()
    {
        $uid=(int)$this->request->input('uid',0);
        $pid=(int)$this->request->input('pid',0);
        $num=(int)$this->request->input('num',0);
        $money=(double)$this->request->input('money',0);
        if($uid<=0||$pid<=0||$num<=0||$money<=0){
            return $this->response->withStatus(409,'params error');
        }
        $data['pid']=$pid;
        $data['uid']=$uid;
        $data['total']=$num;
        $data['status']=1;
        $data['price']=$money;
        $data['gid']=$this->request->input('gid');
        echo '创建订单'.PHP_EOL;
        $rs=$this->order->createOrder($data);
        if($rs)
        {
           return $this->response->withStatus(200);
        }
        return $this->response->withStatus(409);
    }

    #[PostMapping(path: 'createOrderCompensate')]
    #[Barrier]
    public function createOrderCompensate()
    {
        echo '回滚订单'.PHP_EOL;
        var_dump($this->request->all());
        $gid=$this->request->input('gid');
        $rs=$this->order->deleteOrder($gid);
        if($rs){
            echo '订单回滚成功'.PHP_EOL;
            return $this->response->withStatus(200);
        }
        var_dump($rs);
        return [];
    }
}