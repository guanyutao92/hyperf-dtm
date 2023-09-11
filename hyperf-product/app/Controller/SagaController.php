<?php

namespace App\Controller;

use App\JsonRpc\ProductService;
use DtmClient\Annotation\Barrier;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller(prefix:'saga')]
class SagaController extends AbstractController
{
    #[Inject]
    protected ProductService $product;

    #[PostMapping(path:'deduct')]
    #[Barrier]
    public function deduct()
    {
        $id=(int)$this->request->input('id',0);
        $num=(int)$this->request->input('num',0);
        if($id<=0||$num<=0){
            return $this->response->withStatus(409,'params error');
        }
        echo '扣减库存'.PHP_EOL;
        $rs=$this->product->deductSagaStock($id,$num);
        if($rs){
            return $this->response->withStatus(200);
        }
        return $this->response->withStatus(409,'no more stock to deduct');
    }

    #[PostMapping(path: 'deductCompensate')]
    #[Barrier]
    public function deductCompensate()
    {
        var_dump($this->request->all());
        $id=(int)$this->request->input('id',0);
        $num=(int)$this->request->input('num',0);
        if($id<=0||$num<=0){
           // return $this->response->withStatus(409,'params error');
            return ;
        }
        echo '补偿库存'.PHP_EOL;
        $rs=$this->product->rollbackSagaStock($id,$num);
        if($rs){
            echo '库存回滚成功'.PHP_EOL;
            return $this->response->withStatus(200);
        }
        var_dump($rs);
        return [];

        //return $this->response->withStatus(409);

    }

}