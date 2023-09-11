<?php
namespace App\JsonRpc;

interface ProductInterface
{
    public function productList();

    public function createProduct(Array $data);

    public function updateProduct(int $id,Array $data);
    public function deleteProduct(int $id);
    public function getProductById(int $id);
}