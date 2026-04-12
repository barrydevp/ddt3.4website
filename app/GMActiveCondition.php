<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GMActiveCondition extends Model
{
    protected $connection = 'sqlsrv_tank41';
    protected $table = 'GM_Active_Condition';

    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'giftbagId',
        'conditionIndex',
        'conditionValue',
        'remain1',
        'remain2',
    ];

    protected $attributes = [
        'conditionIndex' => 0,
        'conditionValue' => 0,
        'remain1' => 0,
    ];

    protected $casts = [
        'Id' => 'integer',
        'conditionIndex' => 'integer',
        'conditionValue' => 'integer',
        'remain1' => 'integer',
    ];
}
