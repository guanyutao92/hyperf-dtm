<?php

namespace App\JsonRpc;

use App\Model\Product;
use Hyperf\DbConnection\Db;


class ProductService implements ProductInterface
{


    public function productList()
    {
        return Product::select()->get();
    }

    public function createProduct(array $data)
    {
        $product = new Product();
       return $product->save($data);
    }

    public function updateProduct(int $id, array $data)
    {
        $product=Product::find($id);
        return $product->save($data);
    }

    public function deleteProduct(int $id)
    {
       $product=Product::find($id);
       return $product->delete();
    }

    public function getProductById(int $id)
    {
        return   Product::find($id);
    }

    /**
     * 冻结库存
     * @param int $id
     * @param int $num
     * @return int
     */
    public function freeStock(int $id,int $num): int
    {
        return Product::query()
            ->where('id',$id)
            ->whereRaw("total-? >=0",[$num])
            ->update(['total'=>Db::raw("total-$num"),'free_total'=>Db::raw("free_total+$num")]);
    }

    /**
     * 扣减冻结库存
     * @param int $id
     * @param int $num
     * @return int
     */
    public function deductFreeStock(int $id,int $num)
    {
        return Product::query()
            ->where('id',$id)
            ->whereRaw("free_total-? >=0",[$num])
            ->update(['free_total'=>Db::raw("free_total-$num")]);
    }

    /**
     * 还原库存
     * @param int $id
     * @param int $num
     * @return int
     */
    public function rollbackStock(int $id,int $num)
    {
        return Product::query()
            ->where('id',$id)
            ->whereRaw("free_total-? >=0",[$num])
            ->update(['total'=>Db::raw("total+$num"),'free_total'=>Db::raw("free_total-$num")]);
    }

    /**
     * saga方式
     * 扣减库存
     * @param int $id
     * @param int $num
     * @return int
     */
    public function deductSagaStock(int $id,int $num)
    {
        return Product::query()
            ->where('id',$id)
            ->whereRaw("total-?>=0",[$num])
            ->update(['total'=>Db::raw("total-$num")]);
    }

    /**
     * saga方式
     * saga补偿库存
     * @param int $id
     * @param int $num
     * @return int
     */
    public function rollbackSagaStock(int $id,int $num)
    {
        return Product::query()
            ->where('id',$id)
            ->update(['total'=>Db::raw("total+$num")]);
    }
}