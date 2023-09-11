<?php

namespace App\JsonRpc\Tcc;

use App\Model\Order;
use Hyperf\Di\Annotation\Inject;
use Hyperf\RpcServer\Annotation\RpcService;


class OrderService implements OrderInterface
{

    public function orderList()
    {
        // TODO: Implement orderList() method.
        return Order::select()->get();
    }

    public function createOrder(Array $data): bool|int
    {
        $order=new Order();
        $order->pid=$data['pid'];
        $order->uid=$data['uid'];
        $order->total=$data['total'];
        $order->status=$data['status'];
        $order->price=$data['price'];
        $order->gid=$data['gid'];
        $rs=$order->save();
        if($rs){
            return $order->id;
        }
        return false;
    }


    public function deleteOrder($gid)
    {
        // TODO: Implement deleteOrder() method.
        $order=Order::query()->where('gid',$gid)->find();
        return $order->delete();
    }

    public function updateOrder( $gid,Array $data)
    {
        // TODO: Implement updateOrder() method.

        return Order::query()->where('gid',$gid)->update($data);
    }

    public function getOrderById(int $id)
    {
        // TODO: Implement getOrderById() method.
        return Order::query()->find($id);
    }
}