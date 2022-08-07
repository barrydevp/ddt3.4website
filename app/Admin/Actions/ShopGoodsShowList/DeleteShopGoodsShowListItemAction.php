<?php

namespace App\Admin\Actions\ShopGoodsShowList;

use App\ShopGoodsShowList;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DeleteShopGoodsShowListItemAction extends RowAction
{
    public $name = 'Xoá';

    public function dialog()
    {
        $this->confirm('Bạn có chắc xoá vật phẩm này ra khỏi mục không?');
    }

    public function handle(Model $model)
    {
        $currentTank = Auth::guard('admin')->user()->current_tank;

        $type = $this->row->Type;
        $shopId = (int) $this->row->ShopId;
        $shopGoodsShowList = ShopGoodsShowList::on($currentTank)->where('Type',$type)->where('ShopId', $shopId)->first();
        if($shopGoodsShowList->delete()){
            return $this->response()->success('Xoá vật phẩm ra khỏi mục thành công.')->refresh();
        }
        return $this->response()->error('Xoá vật phẩm ra khỏi mục thất bại')->refresh();

    }

}
