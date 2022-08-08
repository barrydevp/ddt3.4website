<?php

namespace App;

use Illuminate\Notifications\Notifiable;
//use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Member extends Authenticatable
{
    use Notifiable;

    protected $connection = 'sqlsrv_mem';

    protected $guard = 'member';

    protected $table = 'Mem_Account';

    protected $primaryKey = 'UserID';

    protected $fillable = ['Email', 'Password', 'Fullname', 'Phone', 'Money', 'MoneyLock', 'TotalMoney', 'MoneyEvent', 'Point', 'CountLucky', 'VIPLevel', 'VIPExp', 'IsBan', 'IPCreate', 'AllowSocialLogin', 'TimeCreate', 'Pass2', 'TwoFactorStatus', 'VerifiedEmail', 'TwoFactorCode', 'TwoFactorCodeExpiresAt'];

    protected $hidden = [
        'Password', 'Pass2',
    ];

    //This below function play an fucking important role in Model.
    //Which Password field it not like 'password'.
    //This function override custom field
    public function getAuthPassword()
    {
        return $this->Password;
    }

//    public function setEmailAttribute()
//    {
//        return $this->attributes('Fullname');
//    }
//
//    public function getEmailAttribute()
//    {
//        return $this->Fullname;
//    }
//

    public function routeNotificationForMail()
    {
        return $this->Fullname;
    }

    public $timestamps = false;


    public function generateTwoFactorCode()
    {
//        $this->timestamps = false;
        $this->TwoFactorCode = rand(100000, 999999);
        $this->TwoFactorCodeExpiresAt = now()->addMinutes(10);
        $this->save();
    }

    public function resetTwoFactorCode()
    {
//        $this->timestamps = false;
        $this->TwoFactorCode = null;
        $this->TwoFactorCodeExpiresAt = null;
        $this->save();
    }

    public function chargeMoney($coin)
    {
        $this->Money -= $coin;
        return $this->save();
    }

    public function getVipLevel()
    {
        $vipExpRequires = [
            0,
            Setting::get('vip1-exp'),
            Setting::get('vip2-exp'),
            Setting::get('vip3-exp'),
            Setting::get('vip4-exp'),
            Setting::get('vip5-exp'),
            Setting::get('vip6-exp'),
            Setting::get('vip7-exp'),
            Setting::get('vip8-exp'),
        ];
        $vipExp = $this->VIPExp;
        foreach ($vipExpRequires as $i => $expRequire) {
            if ($vipExp < $expRequire) {
                return $i - 1;
            }
        }
        return $i;
    }

    public function getVipBonus()
    {
        $vipBonuses = [
            0,
            Setting::get('vip1-bonus'),
            Setting::get('vip2-bonus'),
            Setting::get('vip3-bonus'),
            Setting::get('vip4-bonus'),
            Setting::get('vip5-bonus'),
            Setting::get('vip6-bonus'),
            Setting::get('vip7-bonus'),
            Setting::get('vip8-bonus'),
        ];
        $vipLevel = $this->getVipLevel();
        return $vipBonuses[$vipLevel];
    }

//    public function coinAdminLog()
//    {
//        return $this->setConnection('sqlsrv')->belongsTo('App\CoinLog');
//    }

}
