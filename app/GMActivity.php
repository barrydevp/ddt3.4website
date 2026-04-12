<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GMActivity extends Model
{
    protected $connection = 'sqlsrv_tank41';
    protected $table = 'GM_Activity';

    protected $primaryKey = 'activityId';
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'Id',
        'activityId',
        'activityName',
        'activityType',
        'activityChildType',
        'getWay',
        'rewardDesc',
        'desc',
        'beginTime',
        'beginShowTime',
        'endTime',
        'endShowTime',
        'icon',
        'isContinue',
        'status',
        'remain1',
        'remain2',
        'SectionId',
        'CanReset',
    ];

    public $incrementing = false;

    protected $attributes = [
        'activityType' => 0,
        'activityChildType' => 0,
        'getWay' => 0,
        'icon' => 1,
        'isContinue' => 0,
        'status' => 1,
        'remain1' => 1,
        'SectionId' => 0,
        'CanReset' => 0,
    ];

    protected $casts = [
        'Id' => 'integer',
        'activityType' => 'integer',
        'activityChildType' => 'integer',
        'getWay' => 'integer',
        'beginTime' => 'datetime',
        'beginShowTime' => 'datetime',
        'endTime' => 'datetime',
        'endShowTime' => 'datetime',
        'icon' => 'integer',
        'isContinue' => 'integer',
        'status' => 'integer',
        'remain1' => 'integer',
        'SectionId' => 'integer',
        'CanReset' => 'boolean',
    ];
}
