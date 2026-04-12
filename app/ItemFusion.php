<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ItemFusion extends Model
{
    /*
     * Dung luyện
     */
    protected $connection = 'sqlsrv_tank';
    protected $table = 'Item_Fusion';
    protected $primaryKey = 'FusionID';
    protected $fillable = ['FusionID', 'Item1', 'Item2', 'Item3', 'Item4', 'Formula', 'Reward'];

    public $timestamps = false;
    public $incrementing = true;

    public function __construct(array $attributes = [])
    {
        $currentTank = Auth::guard('admin')->user()->current_tank;
        $this->connection = $currentTank;
        parent::__construct($attributes);
    }

    public function FirstItem()
    {
        return $this->hasOne('App\ShopGoods','TemplateID','Item1')->withDefault();
    }

    public function SecondItem()
    {
        return $this->hasOne('App\ShopGoods','TemplateID','Item2')->withDefault();
    }

    public function ThirdItem()
    {
        return $this->hasOne('App\ShopGoods','TemplateID','Item3')->withDefault();
    }

    public function FourthItem()
    {
        return $this->hasOne('App\ShopGoods','TemplateID','Item4')->withDefault();
    }

    public function RewardItem()
    {
        return $this->hasOne('App\ShopGoods','TemplateID','Reward')->withDefault();
    }

}
