<?php

namespace App\Controller;

use DtmClient\Annotation\Barrier;
use DtmClient\DbTransaction\DBTransactionInterface;
use DtmClient\Exception\FailureException;
use DtmClient\TransContext;
use DtmClient\XA;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller(prefix: 'xa')]
class XaController extends AbstractController
{
    #[Inject]
    protected XA $xa;

    #[Inject]
    protected ClientFactory $clientFactory;

    public string $order_url='http://192.168.0.106:9501';
    public string $product_url='http://192.168.0.106:9502';
    public string $user_url='http://192.168.0.106:9503';

    #[GetMapping(path: 'successCase')]
    public function successCase()
    {

        $uid=(int)$this->request->input('uid',0);
        $pid=(int)$this->request->input('pid',0);
        $num=(int)$this->request->input('num',0);
        $money=(double)$this->request->input('money',0);
        if($uid<=0||$pid<=0||$num<=0){
            return $this->response->withStatus(409,'params error');
        }
        $client=$this->clientFactory->create(['base_uri'=>$this->product_url,'timeout'=>2]);
        $response=$client->get('/product/getPrice?id='.$pid);
        $money=0;
        if($response->getStatusCode()===200){
            $body=$response->getBody()->getContents();
            $product= json_decode($body,true);
            $money=$num*($product['price']??0);
        }
        echo "start".PHP_EOL;
        try{
            $gid=$this->xa->generateGid();
            $this->xa->globalTransaction($gid,function()use($pid,$uid,$money,$num){
                // xa事务任何分支都不需要屏障
                echo "扣减库存".PHP_EOL;
                $respone=$this->xa->callBranch($this->product_url.'/xa/deduct',['id'=>$pid,'num'=>$num]);
                echo "扣减库存结果：".PHP_EOL;
                var_dump($respone->getBody()->getContents());

                echo "扣减余额".PHP_EOL;
                $respone=$this->xa->callBranch($this->user_url.'/xa/deduct',['id'=>$uid,'money'=>$money]);
                echo "扣减余额结果：".PHP_EOL;
                var_dump($respone->getBody()->getContents());

                echo '订单创建'.PHP_EOL;
                $respone=$this->xa->callBranch($this->order_url.'/xa/createOrder',
                    ['uid'=>$uid,'num'=>$num,'price'=>$money,'pid'=>$pid]);
                echo "创建结果：".PHP_EOL;
                var_dump($respone->getBody()->getContents());
            });
        }catch (\Throwable $e){
            var_dump($e->getMessage());
            return $this->response->json(['error'=>$e->getMessage()])->withStatus(409);
        }

       return ['gid'=>TransContext::getGid()];

    }

    #[PostMapping(path: 'createOrder')]
    public function createOrder()
    {
        $uid=(int)$this->request->input('uid',0);
        $pid=(int)$this->request->input('pid',0);
        $num=(int)$this->request->input('num',0);
        $price=(double)$this->request->input('price',0);

        $data['pid']=$pid;
        $data['uid']=$uid;
        $data['total']=$num;
        $data['status']=1;
        $data['price']=$price;
        $data['gid']=$this->request->input('gid');
        echo '请求参数'.PHP_EOL;
        var_dump($this->request->all());
        try{
            $this->xa->localTransaction(function (DBTransactionInterface $dbTransaction)use($data){
                // 条件判断只能在localTransaction中处理，因为dtm服务器第二次发送过来的请求不会带第一次请求的参数
                extract($data);
                if($uid<=0||$pid<=0||$total<=0||$price<=0){
                    return $this->response->withStatus(409,'params error');
                }
                echo '执行本地事务'.PHP_EOL;
                $rs=$dbTransaction->xaExecute('insert into `orders` (`pid`,`gid`,`total`,`uid`,`price`,`status`) values(?,?,?,?,?,?)',
                    [$pid,$gid,$total,$uid,$price,1]);
                if($rs<1){
                    throw new FailureException('创建失败');
                }
            });
        }catch (\Throwable $e){
            var_dump($e->getMessage());
            return $this->response->withStatus(409);
        }
       return ['status'=>0,'msg'=>'ok'];
    }
}