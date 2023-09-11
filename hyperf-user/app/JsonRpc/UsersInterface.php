<?php

namespace App\JsonRpc;

interface UsersInterface
{
    public function userList();


    public function createUser(array $data): bool;


    public function updateUser(int $id, array $data): bool;


    public function deleteUser(int $id): bool;

    public function getUserById(int $id);

}