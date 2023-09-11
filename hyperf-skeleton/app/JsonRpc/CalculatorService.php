<?php

namespace App\JsonRpc;

use Hyperf\RpcServer\Annotation\RpcService;

#[RpcService(name:"CalculatorService",protocol: "jsonrpc-http",server: "jsonrpc-http",publishTo: "nacos")]
class CalculatorService implements CalculatorServiceInterface
{

    public function add(int $a, int $b): int
    {
        // TODO: Implement add() method.
        return $a+$b;
    }

    public function minus(int $a, int $b): int
    {
        // TODO: Implement minus() method.
        return $a-$b;
    }
}