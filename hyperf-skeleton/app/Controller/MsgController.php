<?php

namespace App\Controller;

use App\JsonRpc\Tcc\OrderService;
use DtmClient\Api\ApiInterface;
use DtmClient\Api\RequestBranch;
use DtmClient\Barrier;
use DtmClient\BranchIdGeneratorInterface;
use DtmClient\Constants\Result;
use DtmClient\Exception\FailureException;
use DtmClient\Msg;
use DtmClient\TransContext;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;


#[Controller(prefix: 'msg')]
class MsgController extends AbstractController
{
    #[Inject]
    protected Msg $msg;
    #[Inject]
    protected OrderService $order;
    #[Inject]
    protected Barrier $barrier;
    #[Inject]
    protected ApiInterface $api;
    #[Inject]
    protected BranchIdGeneratorInterface $branchIdGenerator;
    #[Inject]
    protected ClientFactory $clientFactory;

    public string $order_url='http://192.168.0.106:9501';
    public string $product_url='http://192.168.0.106:9502';
    public string $user_url='http://192.168.0.106:9503';

    #[GetMapping(path: 'msg')]
    public function msg()
    {
        $uid=(int)$this->request->input('uid',0);
        $pid=(int)$this->request->input('pid',0);
        $num=(int)$this->request->input('num',0);

        try{
            $client=$this->clientFactory->create(['base_uri'=>$this->product_url,'timeout'=>2]);
            $response=$client->get('/product/getPrice?id='.$pid);
            $money=0;
          //  if($response->getStatusCode()===200){
                $body=$response->getBody()->getContents();
                $product= json_decode($body,true);
                $money=$num*($product['price']??0);
          //  }


            $gid=$this->msg->generateGid();
            $data['pid']=$pid;
            $data['uid']=$uid;
            $data['total']=$num;
            $data['status']=1;
            $data['price']=$money;
            $data['gid']=$gid;

            TransContext::setGid($gid);
            $this->msg->add($this->product_url.'/saga/deduct',['id'=>$pid,'num'=>$num]);
            $this->msg->add($this->user_url.'/saga/deduct',['id'=>$uid,'money'=>$money]);

            $this->msg->doAndSubmit($this->order_url.'/msg/queryPreparedB',function()use($data){
                echo "执行submit本地事务".PHP_EOL;

                var_dump($data);
                // 这里调用的函数都不需要子事务屏障，不管是走网络还是本地,只有其他分支才需要设置屏障，本地事务不需要。
                // 回调函数，可以走网络也可以走本地方法调用。
                // 程序会先调用这里的业务（本地事务），如果这里页面成功，才会去调用外部add的方法。
                //1.网络
                /*
                // 方式1：
                $branchId = $this->branchIdGenerator->generateSubBranchId();

                $branchRequest = new RequestBranch();
                $branchRequest->method = 'POST';
                $branchRequest->url = $this->order_url . '/msg/createOrder';
                $branchRequest->branchId = TransContext::getBranchId();
                $branchRequest->op = TransContext::getOp();
                $branchRequest->body = $data;
                return $this->api->transRequestBranch($branchRequest);
               */
                /*
                // 方式2：
                $client=$this->clientFactory->create(['base_uri'=>$this->order_url,'timeout'=>2]);
                $response=$client->post('/msg/createOrder',['form_params'=>$data]);
                var_dump($response);
                */
                //2.本地
               /* $rs=$this->localCreateOrder($data);

                if($rs===false) {
                    throw new FailureException('订单创建失败');
                }*/
            });


        }catch (\Throwable $e){
            var_dump($e);
            return $this->response->withStatus(409);
        }
        return ['gid'=>$gid];

    }

    #[PostMapping(path: 'createOrder')]
    public function createOrder()
    {
        echo "网络订单：".PHP_EOL;
        $uid=(int)$this->request->input('uid',0);
        $pid=(int)$this->request->input('pid',0);
        $num=(int)$this->request->input('num',0);
        $money=(double)$this->request->input('money',0);
        $data['pid']=$pid;
        $data['uid']=$uid;
        $data['total']=$num;
        $data['status']=0;
        $data['price']=$money;
        $data['gid']=$this->request->input('gid');
        $rs=$this->localCreateOrder($data);
        if($rs){
            return $this->response->withStatus(200);
        }
        return $this->response->withStatus(409);
    }


    public function localCreateOrder($data)
    {

        echo '执行订单创建'.PHP_EOL;
        var_dump($data);
        $rs=$this->order->createOrder($data);
        echo '创建结果：'.PHP_EOL;
        var_dump($rs);
        if($rs){

            return true;
        }
        return false;
    }


    #[GetMapping(path: 'queryPreparedB')]
    public function queryPreparedB()
    {
        echo '回查'.PHP_EOL;
        $params=$this->request->all();
        try{
            $rs=$this->barrier->queryPrepared($params['trans_type'],$params['gid']);

            return $rs;
        }catch (FailureException $e){
            echo "异常结果：".PHP_EOL;
            var_dump($e->getMessage());
            return Result::FAILURE;
        }
    }


}