<?php

namespace App\Controller;

use DtmClient\Annotation\Barrier;
use DtmClient\DbTransaction\DBTransactionInterface;
use DtmClient\Exception\FailureException;
use DtmClient\XA;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller(prefix: 'xa')]
class XaController extends AbstractController
{
    #[Inject]
    protected XA $xa;

    #[PostMapping(path: 'deduct')]
    public function deduct()
    {
        $id=(int)$this->request->input('id',0);
        $num=(int)$this->request->input('num',0);

        echo '请求参数'.PHP_EOL;
        var_dump($this->request->all());
        try{
            $this->xa->localTransaction(function (DBTransactionInterface $dbTransaction)use($id,$num){
                if($id<=0||$num<=0){
                 return $this->response->json(['error'=>'params error'])->withStatus(409);
                }
                echo '执行本地事务'.PHP_EOL;
                $rs=$dbTransaction->xaExecute('update `product` set `total`=`total`-? where `id`=? and `total`-? >=0',
                    [$num,$id,$num]);
                if($rs<1){
                    throw new FailureException('库存不足');
                }
                var_dump('扣减库存');
            });
        }catch (\Throwable $e){
            return $this->response->withStatus(409);
        }
        return ['status'=>0,'msg'=>'ok'];

    }
}