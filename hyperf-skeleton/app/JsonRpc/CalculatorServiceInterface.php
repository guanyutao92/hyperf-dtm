<?php
namespace App\JsonRpc;

interface CalculatorServiceInterface
{
    public function add(int $a,int $b):int;

    public function minus(int $a ,int $b):int;

}