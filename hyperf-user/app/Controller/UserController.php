<?php

namespace App\Controller;

use App\JsonRpc\UsersService;
use DtmClient\Annotation\Barrier;
use DtmClient\Middleware\DtmMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller(prefix: 'user')]
class UserController extends AbstractController
{
    #[Inject]
    public UsersService $user;

    // try阶段
    #[PostMapping(path: 'tccTry')]
    #[Barrier]
    public function tccTry()
    {
        echo "执行try阶段".PHP_EOL;
        $data=(int)$this->request->input('money');
        $id=(int)$this->request->input('id');
      //  print_r($this->request->all());
        $rs=$this->user->freeMoney($id,$data);
        if($rs){
            return ['dtm_result'=>'SUCCESS'];
        }else{
            return $this->response->json(['dtm_result'=>'FAILURE'])->withStatus(409);
        }
    }

    // confirm阶段
    #[PostMapping(path: 'tccConfirm')]
    #[Barrier]
    public function tccConfirm()
    {
        echo "执行confirm阶段".PHP_EOL;
        $id=(int)$this->request->input('id');
        $data=(int)$this->request->input('money');
        $rs=$this->user->deductFreeMoney($id,$data);
        if($rs)
        {
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
        $id=(int)$this->request->input('id');
        $data=(int)$this->request->input('money');
        $rs=$this->user->incMoney($id,$data);
        if($rs){
            return ['dtm_result'=>'SUCCESS'];
        }else{
            return $this->response->json(['dtm_result'=>'ONGOING'])->withStatus(425);
        }
    }
}