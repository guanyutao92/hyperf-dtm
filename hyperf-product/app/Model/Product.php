<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id 
 * @property string $pname 
 * @property int $total 
 * @property string $sprice 
 * @property int $free_total 
 */
class Product extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'product';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'total' => 'integer', 'free_total' => 'integer'];
    public bool $timestamps=false;
}
