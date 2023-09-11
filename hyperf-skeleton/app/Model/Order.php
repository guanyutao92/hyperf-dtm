<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id 
 * @property int $pid 
 * @property string $price 
 * @property int $status 
 * @property int $total 
 */
class Order extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'orders';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'uid' => 'integer', 'pid' => 'integer', 'status' => 'integer', 'total' => 'integer','uid' => 'integer'];

    public bool $timestamps=false;
}
