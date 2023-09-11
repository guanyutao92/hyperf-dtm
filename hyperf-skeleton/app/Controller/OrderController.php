<?php

namespace App\Controller;


use App\JsonRpc\Tcc\OrderService;
use DtmClient\Annotation\Barrier;
use DtmClient\Api\ApiInterface;
use DtmClient\Middleware\DtmMiddleware;
use DtmClient\Msg;
use DtmClient\Saga;
use DtmClient\TCC;
use DtmClient\TransContext;
use GuzzleHttp\Client;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;


#[Controller(prefix: "order")]
class OrderController extends AbstractController
{
    #[Inject]
    public OrderService $order;

    #[Inject]
    public TCC $tcc;

    #[Inject]
    protected ApiInterface $api;

    public string $order_url='http://192.168.0.106:9501';
    public string $product_url='http://192.168.0.106:9502';
    public string $user_url='http://192.168.0.106:9503';



// -------------------------tcc------------------------------------
    #[GetMapping(path: 'createOrder')]
    public function createOrder()
    {
        try{
            // 获取全局id
            $gid=$this->tcc->generateGid();
            $uid=(int)$this->request->input('uid',0);
            $pid=(int)$this->request->input('pid',0);
            $num=(int)$this->request->input('num',0);
            $client=new Client(['base_uri'=>$this->product_url,'timeout'=>2]);
            $money=0;
            $response=$client->get('/product/getPrice?id='.$pid);
            if($response->getStatusCode()==200){
                $body=$response->getBody()->getContents();
                $product= json_decode($body,true);
                $money=$num*($product['price']??0);
            }
            // 开启全局事务
            $this->tcc->globalTransaction(function (TCC $tcc) use ($pid,$uid,$num,$money){

                echo "扣减库存".PHP_EOL;
                $tcc->callBranch(
                    ['trans_name'=>'deductStock','id'=>$pid,'num'=>$num],
                    $this->product_url.'/product/tccTry',
                    $this->product_url.'/product/tccConfirm',
                    $this->product_url.'/product/tccCancel'
                );
                echo "扣减余额".PHP_EOL;
                $tcc->callBranch(
                    ['trans_name'=>'deductMoney','id'=>$uid,'money'=>$money],
                    $this->user_url.'/user/tccTry',
                    $this->user_url.'/user/tccConfirm',
                    $this->user_url.'/user/tccCancel'
                );
                echo "创建订单".PHP_EOL;
                $msg1=$tcc->callBranch(
                    ['trans_name'=>'creatOrder','uid'=>$uid,'pid'=>$pid,'total'=>$num,'price'=>$money],
                       $this->order_url.'/order/tccTry',
                    // 测试回滚
                   // $this->order_url.'/order/tccTryFail',
                    $this->order_url.'/order/tccConfirm',
                    $this->order_url.'/order/tccCancel'
                );
                var_dump($msg1->getBody()->getContents());
            },$gid);
        }catch (\Throwable $e){
            return $this->response->json(['error'=>$e->getMessage()]);
        }
        var_dump(TransContext::getCustomData());
        var_dump(TransContext::getSubBranchId());
        return $this->response->json(['gid'=>TransContext::getGid()]);
    }

    // try阶段
    #[PostMapping(path: 'tccTry')]
    #[Barrier]
    public function tccTry()
    {
        echo "执行try阶段".PHP_EOL;
        $data=$this->request->inputs(['pid','uid','total','price'],[0,0,0,0]);

        if((int)$data['pid']===0){
            return   $this->response->withStatus(409);
        }

        // 订单状态构建中
        $data['status']=0;
        $data['gid']=$this->request->input('gid');
        $rs=$this->order->createOrder($data);
        if($rs){
            TransContext::set('oid',$rs);
            var_dump($rs);
            return ['dtm_result'=>'SUCCESS'];
        }else{
            return $this->response->json(['dtm_result'=>'FAILURE'])->withStatus(409);
        }
    }

    // try阶段报错实现回滚
    #[PostMapping(path: 'tccTryFail')]
    #[Barrier]
    public function tccTryFail()
    {
        echo "执行try阶段".PHP_EOL;
        return $this->response->json(['dtm_result'=>'FAILURE'])->withStatus(409);


    }

    // confirm阶段
    #[PostMapping(path: 'tccConfirm')]
    #[Barrier]
    public function tccConfirm()
    {
       // return $this->response->withStatus(425);
        echo "执行confirm阶段".PHP_EOL;
       $oid=TransContext::get('oid');
       print_r($oid);
       print_r($this->request->all());
        $id=$this->request->input('id');
        $id=25;
        $gid=$this->request->input('gid');
        $rs=$this->order->updateOrder($gid,['status'=>1]);
        if($rs){
            return ['dtm_result'=>'SUCCESS'];
        }else{
            return $this->response->json(['dtm_result'=>'ONGOING'])->withStatus(425);
        }


    }

    // cancel阶段
    #[PostMapping(path: 'tccCancel')]
    #[Barrier]
    public function tccCancel()
    {
        echo "执行cancel阶段".PHP_EOL;
        $id=$this->request->input('id');
        $gid=$this->request->input('gid');
        $rs=$this->order->deleteOrder($gid);
        return ['dtm_result'=>'SUCCESS'];
        if($rs){
            return ['dtm_result'=>'SUCCESS'];
        }else{
            return $this->response->json(['dtm_result'=>'ONGOING'])->withStatus(425);
        }
    }

    /**
     * 根据gid查询全局事务信息
     * @return array
     */
    #[GetMapping(path:'queryBYGid' )]
    public function queryBYGid()
    {
       if($this->request->has('gid')){
           $gid=$this->request->input('gid');
       }else{
           return ['err'=>'no gid'];
       }
        $rs=$this->api->query(['gid' =>$gid]);

        $rs=$rs->getBody()->getContents();
        $rs=json_decode($rs,true);

        return ['rs'=>$rs];
    }



}