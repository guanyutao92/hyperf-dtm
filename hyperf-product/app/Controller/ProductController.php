<?php

namespace App\Controller;

use App\JsonRpc\ProductService;

use DtmClient\Annotation\Barrier;
use DtmClient\Middleware\DtmMiddleware;
use Hyperf\Di\Annotation\Inject;


use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller('product')]
class ProductController extends AbstractController
{
    #[Inject]
    public ProductService $product;

    // try阶段
    #[PostMapping(path: 'tccTry')]
    #[Barrier]
    public function tccTry()
    {
        echo "执行try阶段".PHP_EOL;
        $data=(int)$this->request->input('num');
        $id=(int)$this->request->input('id');

        $rs=$this->product->freeStock($id,$data);
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
        $data=(int)$this->request->input('num');
        $rs=$this->product->deductFreeStock($id,$data);
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
        $data=(int)$this->request->input('num');
        $rs=$this->product->rollbackStock($id,$data);
        if($rs){
            return ['dtm_result'=>'SUCCESS'];
        }else{
            return $this->response->json(['dtm_result'=>'ONGOING'])->withStatus(425);
        }
    }
    #[GetMapping(path: 'getPrice')]
    public function getPrice()
    {
        $id=(int)$this->request->input('id');
        $product=$this->product->getProductById($id);
        if(!empty($product->toArray())){
            return ['price'=>$product->sprice];
        }else{
            return ['error'=>'yes'];
        }
    }
}