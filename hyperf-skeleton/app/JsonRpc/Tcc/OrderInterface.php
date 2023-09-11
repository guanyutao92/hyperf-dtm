<?php

namespace App\JsonRpc\Tcc;

interface OrderInterface
{
    public function orderList();

    public function getOrderById(int $id);

    public function createOrder(Array $data);


    public function deleteOrder(int $id);

    public function updateOrder(int $id,Array $data);
}