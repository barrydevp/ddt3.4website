<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GMGiftBag extends Model
{
    protected $connection = 'sqlsrv_tank41';
    protected $table = 'GM_Gift_Bag';

    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'giftbagId',
        'activityId',
        'rewardMark',
        'giftbagOrder',
    ];

    protected $attributes = [
        'rewardMark' => 0,
        'giftbagOrder' => 0,
    ];

    protected $casts = [
        'Id' => 'integer',
        'rewardMark' => 'integer',
        'giftbagOrder' => 'integer',
    ];
}
