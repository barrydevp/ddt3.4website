<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GMActiveReward extends Model
{
    protected $connection = 'sqlsrv_tank41';
    protected $table = 'GM_Active_Reward';

    protected $primaryKey = 'Id';
    public $incrementing = false;
    protected $keyType = 'integer';
    public $timestamps = false;

    protected $fillable = [
        'Id',
        'giftId',
        'templateId',
        'count',
        'isBind',
        'occupationOrSex',
        'rewardType',
        'validDate',
        'property',
        'remain1',
    ];

    protected $attributes = [
        'templateId' => 0,
        'count' => 1,
        'isBind' => 1,
        'occupationOrSex' => 0,
        'rewardType' => 0,
        'validDate' => 0,
        'property' => '0,0,0,0,0,0,0,0,0',
        'remain1' => '',
    ];

    protected $casts = [
        'Id' => 'integer',
        'templateId' => 'integer',
        'count' => 'integer',
        'isBind' => 'boolean',
        'occupationOrSex' => 'integer',
        'rewardType' => 'integer',
        'validDate' => 'integer',
    ];

    protected function setKeysForSaveQuery($query)
    {
        return $query
            ->where('giftId', '=', $this->getAttribute('giftId'))
            ->where('templateId', '=', $this->getAttribute('templateId'));
    }
}
