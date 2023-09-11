<?php

namespace App\JsonRpc;

use App\Model\User;
use Hyperf\DbConnection\Db;
use PhpParser\Node\Expr\Cast\Double;


class UsersService implements UsersInterface
{

    public function userList()
    {
        return Users::select()->get();
    }

    public function createUser(array $data): bool
    {
        $user=new User();
        return $user->save($data);
    }

    public function updateUser(int $id, array $data): bool
    {
        $user=User::query()->find($id);
        return $user->save($data);
    }

    public function deleteUser(int $id): bool
    {
        $user=User::query()->find($id);
        return $user->delete();
    }

    public function getUserById(int $id)
    {
        return User::query()->find($id);
    }

    /**
     * 冻结余额
     * @param int $id
     * @param $money
     * @return int
     */
    public function freeMoney(int $id ,$money)
    {
        return User::query()
            ->where('id',$id)
            ->whereRaw("money-?>=0",[$money])
            ->update(['money'=>Db::raw("money-$money"),'free_money'=>Db::raw("free_money+$money")]);
    }

    /**
     * 扣减冻结资金
     * @param int $id
     * @param $money
     * @return int
     */
    public function deductFreeMoney(int $id,$money)
    {

        return User::query()
            ->where('id',$id)
            ->whereRaw("free_money-?>=0",[$money])
            ->update(['free_money'=>Db::raw("free_money-$money")]);
    }

    /**
     * 回滚余额
     * @param int $id
     * @param $money
     * @return int
     */
    public function incMoney(int $id,$money)
    {
        return User::query()
            ->where('id',$id)
            ->whereRaw("free_money-? >=0",[$money])
            ->update(['money'=>Db::raw("money+$money"),'free_money'=>Db::raw("free_money-$money")]);

    }

    /**
     * saga
     * 扣减余额
     * @param int $id
     * @param double $money
     * @return int
     */
    public function deduct(int $id, $money)
    {
        return User::query()
            ->where('id',$id)
            ->whereRaw('money-?>=0',[$money])
            ->update(['money'=>Db::raw("money-$money")]);
    }

    /**
     * saga
     * 补偿余额
     * @param int $id
     * @param double $money
     * @return int
     */
    public function rollbackMoney(int $id, $money)
    {
        return User::query()
            ->where('id',$id)
            ->update(['money'=>Db::raw("money+$money")]);
    }

}