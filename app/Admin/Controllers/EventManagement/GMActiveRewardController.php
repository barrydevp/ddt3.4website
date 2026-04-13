<?php

namespace App\Admin\Controllers\EventManagement;

use App\Admin\Actions\EventManagement\DeleteGMActiveRewardAction;
use App\GMActivity;
use App\GMActiveReward;
use App\GMGiftBag;
use App\ShopGoods;
use App\ServerList;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GMActiveRewardController extends AdminController
{
    protected $title = 'Phần thưởng sự kiện';

    protected function grid()
    {
        $request = Request::capture();
        if ($request->has('server')) {
            $request->input('server') == null ? $serverId = '1001' : $serverId = $request->input('server');
        } else {
            $serverId = '1001';
        }

        $currentTank = Auth::guard('admin')->user()->current_tank;

        $server = ServerList::findOrFail($serverId);

        $reward = new GMActiveReward();
        $reward->setConnection($server->Connection);

        $activities = GMActivity::on($server->Connection)
            ->select('activityId', 'activityName')
            ->orderBy('activityId')
            ->get();

        $activityOptions = [];
        foreach ($activities as $activity) {
            $activityOptions[$activity->activityId] = $activity->activityName . ' - (ID = ' . $activity->activityId . ')';
        }

        $selectedActivityId = $request->input('activityId');
        $selectedGiftId = $request->input('giftId');

        $giftBags = GMGiftBag::on($server->Connection)
            ->select('giftbagId', 'activityId')
            ->when($selectedActivityId, function ($query) use ($selectedActivityId) {
                $query->where('activityId', '=', $selectedActivityId);
            })
            ->orderBy('giftbagId')
            ->get();

        $giftIdOptions = [];
        foreach ($giftBags as $giftBag) {
            $giftIdOptions[$giftBag->giftbagId] = 'GiftBag ' . $giftBag->giftbagId . ' - Activity ' . $giftBag->activityId;
        }

        $grid = new Grid($reward);
        $grid->setTitle('[' . $server->ServerName . '] Danh sách phần thưởng sự kiện');

        $grid->column('Id', 'ID')->sortable();
        $grid->column('giftId', 'Gift ID')->sortable();
        $grid->column('templateId', 'Template ID')->sortable();
        $grid->column('image', __('Hình ảnh'))->display(function () use ($currentTank) {
            $shopGoods = ShopGoods::on($currentTank)->find($this->templateId);
            return $shopGoods ? $shopGoods->ResourceImageColumn() : '';
        });
        $grid->column('item_name', 'Vật phẩm')->display(function () use ($currentTank) {
            $shopGoods = ShopGoods::on($currentTank)->find($this->templateId);
            return $shopGoods ? $shopGoods->Name : 'Không xác định';
        });
        $grid->column('count', 'Số lượng')->editable();
        $grid->column('isBind', 'Khoá')->display(function ($value) {
            return (int) $value === 1
                ? '<span class="badge btn-danger">Khoá</span>'
                : '<span class="badge btn-success">Không khoá</span>';
        })->editable('select', [0 => 'Không khoá', 1 => 'Khoá']);
        $grid->column('occupationOrSex', 'Giới tính/Phái')->editable();
        $grid->column('rewardType', 'Loại thưởng')->editable();
        $grid->column('validDate', 'Hạn dùng')->editable();
        $grid->column('property', 'Property')->limit(40);

        $grid->quickSearch('giftId', 'templateId');
        $grid->expandFilter();
        $grid->filter(function ($filter) use ($giftIdOptions, $activityOptions, $server) {
            $filter->disableIdFilter();
            $filter->where(function ($query) use ($server) {
                $giftbagIds = GMGiftBag::on($server->Connection)
                    ->where('activityId', '=', $this->input)
                    ->pluck('giftbagId')
                    ->toArray();

                if (empty($giftbagIds)) {
                    $query->whereRaw('1 = 0');
                    return;
                }

                $query->whereIn('giftId', $giftbagIds);
            }, 'Mã sự kiện', 'activityId')->select($activityOptions);
            $filter->equal('giftId', 'Gift ID')->select($giftIdOptions);
            $filter->equal('templateId', 'Template ID');
            $filter->equal('rewardType', 'Loại thưởng');
        });

        // $grid->disableCreateButton();
        $grid->quickCreate(function (Grid\Tools\QuickCreate $create) use ($giftIdOptions, $selectedGiftId, $currentTank) {
            $create->select('giftId', 'Gift ID')
                ->default($selectedGiftId != null || $selectedGiftId != '' ? $selectedGiftId : null)
                ->options($giftIdOptions)
                ->required();

            $create->select('templateId', 'Chọn vật phẩm')
                ->options(function ($templateID) use ($currentTank) {
                    $shopGoods = ShopGoods::on($currentTank)->find($templateID);
                    if ($shopGoods) {
                        return [$shopGoods->TemplateID => $shopGoods->Name];
                    }
                })->ajax('/admin/api/load-item')
                ->required();

            $create->integer('count', 'Số lượng')->default(1)->required();
            $create->select('isBind', 'Khoá')->options([0 => 'Không khoá', 1 => 'Khoá'])->default(1);
            $create->integer('occupationOrSex', 'Giới tính/Phái')->default(0);
            $create->integer('rewardType', 'Loại thưởng')->default(0);
            $create->integer('validDate', 'Hạn dùng')->default(0);
            $create->text('property', 'Property')->default('0,0,0,0,0,0,0,0,0');
        });

        $grid->disableBatchActions();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->add(new DeleteGMActiveRewardAction());
        });

        return $grid;
    }

    protected function detail($id)
    {
        $request = Request::capture();
        if ($request->has('server')) {
            $request->input('server') == null ? $serverId = '1001' : $serverId = $request->input('server');
        } else {
            $serverId = '1001';
        }

        $server = ServerList::findOrFail($serverId);
        $show = new Show(GMActiveReward::on($server->Connection)->findOrFail($id));

        $show->field('Id', 'ID');
        $show->field('giftId', 'Gift ID');
        $show->field('templateId', 'Template ID');
        $show->field('count', 'Số lượng');
        $show->field('isBind', 'Khoá');
        $show->field('occupationOrSex', 'Giới tính/Phái');
        $show->field('rewardType', 'Loại thưởng');
        $show->field('validDate', 'Hạn dùng');
        $show->field('property', 'Property');
        $show->field('remain1', 'remain1');

        return $show;
    }

    protected function form()
    {
        $request = Request::capture();
        if ($request->has('server')) {
            $request->input('server') == null ? $serverId = '1001' : $serverId = $request->input('server');
        } else {
            $serverId = '1001';
        }

        $currentTank = Auth::guard('admin')->user()->current_tank;
        $server = ServerList::findOrFail($serverId);

        $reward = new GMActiveReward();
        $reward->setConnection($server->Connection);

        $giftBags = GMGiftBag::on($server->Connection)
            ->select('giftbagId', 'activityId')
            ->orderBy('giftbagId')
            ->get();

        $giftIdOptions = [];
        foreach ($giftBags as $giftBag) {
            $giftIdOptions[$giftBag->giftbagId] = 'GiftBag ' . $giftBag->giftbagId . ' - Activity ' . $giftBag->activityId;
        }

        $form = new Form($reward);
        $form->hidden('server')->default($server->ServerID);
        $form->ignore('server');

        $form->select('templateId', 'Vật phẩm')
            ->options(function ($templateID) use ($currentTank) {
                $shopGoods = ShopGoods::on($currentTank)->find($templateID);
                if ($shopGoods) {
                    return [$shopGoods->TemplateID => $shopGoods->Name];
                }
            })->ajax('/admin/api/load-item')
            ->required();

        if ($form->isEditing()) {
            $form->select('giftId', 'Gift ID')->options($giftIdOptions)->required();
        }

        if ($form->isCreating()) {
            $form->select('giftId', 'Gift ID')->options($giftIdOptions)->required();
        }

        $form->number('count', 'Số lượng')->default(1)->required();
        $form->switch('isBind', 'Khoá')->default(1);
        $form->number('occupationOrSex', 'Giới tính/Phái')->default(0);
        $form->number('rewardType', 'Loại thưởng')->default(0);
        $form->number('validDate', 'Hạn dùng')->default(0);
        $form->text('property', 'Property')->default('0,0,0,0,0,0,0,0,0');
        $form->text('remain1', 'remain1');

        return $form;
    }
}
