<?php

namespace App\Admin\Controllers\FusionManagement;

use App\ItemFusion;
use App\ShopGoods;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class ItemFusionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Quản lý dung luyện';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $currentTank = Auth::guard('admin')->user()->current_tank;

        $itemFusion = new ItemFusion();
        $itemFusion->setConnection($currentTank);

        $grid = new Grid($itemFusion);
        $grid->model()->with(['FirstItem', 'SecondItem', 'ThirdItem', 'FourthItem', 'RewardItem'])->orderByDesc('FusionID');

        $grid->column('FusionID', __('ID'))->width(45);
        $grid->column('_ImageFirstItem_', 'Ảnh 1')->display(function () {
            return $this->FirstItem->ResourceImageColumn();
        });
        $grid->column('FirstItem.Name', __('VP 1'));

        $grid->column('_Image2Item_', __('Ảnh 2'))->display(function () {
            return $this->SecondItem->ResourceImageColumn();
        });
        $grid->column('SecondItem.Name', __('VP 2'));

        $grid->column('_Image3Item_', __('Ảnh 3'))->display(function () {
            return $this->ThirdItem->ResourceImageColumn();
        });
        $grid->column('ThirdItem.Name', __('VP 3'));

        $grid->column('_Image4Item_', __('Ảnh 4'))->display(function () {
            return $this->FourthItem->ResourceImageColumn();
        });
        $grid->column('FourthItem.Name', __('VP 4'));

        $grid->column('_ImageRewardItem_', __('Ảnh PT'))->display(function () {
            return $this->RewardItem->ResourceImageColumn();
        });
        $grid->column('RewardItem.Name', __('Phần thưởng'));

        $grid->column('Formula', __('Formula'));

        $grid->expandFilter();
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->equal('Item1', 'Tìm kiếm vật phẩm thành phần (VP1)')->select()->ajax('/admin/api/load-item');
            $filter->equal('Reward', 'Tìm kiếm vật phẩm dung luyện ra')->select()->ajax('/admin/api/load-item');
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(ItemFusion::findOrFail($id));

        $show->field('FusionID', __('FusionID'));
        $show->field('Item1', __('Item1'));
        $show->field('Item2', __('Item2'));
        $show->field('Item3', __('Item3'));
        $show->field('Item4', __('Item4'));
        $show->field('Formula', __('Formula'));
        $show->field('Reward', __('Reward'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ItemFusion());

        $form->select('Item1', 'Chọn vật phẩm 1')
            ->options(function ($templateID){
                $shopGoods = ShopGoods::find($templateID);
                if($shopGoods){
                    return [$shopGoods->TemplateID => $shopGoods->Name];
                }
            })->ajax('/admin/api/load-item')->required();
        $form->select('Item2', 'Chọn vật phẩm 2')
            ->options(function ($templateID){
                $shopGoods = ShopGoods::find($templateID);
                if($shopGoods){
                    return [$shopGoods->TemplateID => $shopGoods->Name];
                }
            })->ajax('/admin/api/load-item')->required();
        $form->select('Item3', 'Chọn vật phẩm 3')
            ->options(function ($templateID){
                $shopGoods = ShopGoods::find($templateID);
                if($shopGoods){
                    return [$shopGoods->TemplateID => $shopGoods->Name];
                }
            })->ajax('/admin/api/load-item')->required();
        $form->select('Item4', 'Chọn vật phẩm 4')
            ->options(function ($templateID){
                $shopGoods = ShopGoods::find($templateID);
                if($shopGoods){
                    return [$shopGoods->TemplateID => $shopGoods->Name];
                }
            })->ajax('/admin/api/load-item')->required();

        $form->divider('Phần thưởng');

        $form->select('Reward', 'Chọn phần thưởng')
            ->options(function ($templateID){
                $shopGoods = ShopGoods::find($templateID);
                if($shopGoods){
                    return [$shopGoods->TemplateID => $shopGoods->Name];
                }
            })->ajax('/admin/api/load-item')->required();

        $form->disableEditingCheck();
        return $form;
    }
}
