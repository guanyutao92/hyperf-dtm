<?php

namespace App\Controller;

use App\JsonRpc\UsersService;
use DtmClient\Annotation\Barrier;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller(prefix: 'saga')]
class SagaController extends AbstractController
{
    #[Inject]
    protected UsersService $user;

    #[PostMapping(path: 'deduct')]
    #[Barrier]
    public function deduct()
    {
        $id=(int)$this->request->input('id',0);
        $money=(double)$this->request->input('money',0);
        if($id<=0||$money<=0){
            $this->response->withStatus(409,'params error');
        }
        echo '扣减余额'.PHP_EOL;
        $rs=$this->user->deduct($id,$money);
        if($rs){
            return $this->response->withStatus(200);
        }
        return $this->response->json(['dtm_result'=>'FAILURE','msg'=>'no more money'])->withStatus(409);
    }

    #[PostMapping(path: 'deductCompensate')]
    #[Barrier]
    public function deductCompensate()
    {
        $id=(int)$this->request->input('id',0);
        $money=(double)$this->request->input('money',0);
        if($id<=0||$money<=0){
            return $this->response->withStatus(409,'params error');
        }
        echo '补偿余额'.PHP_EOL;
        $rs=$this->user->rollbackMoney($id,$money);
        if($rs){
            echo '余额回滚成功'.PHP_EOL;
            return $this->response->withStatus(200);
        }
        echo '余额回滚失败'.PHP_EOL;
        return $this->response->withStatus(409);
    }

}