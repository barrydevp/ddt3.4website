<?php

namespace App\Admin\Controllers\EventManagement;

use App\Admin\Actions\EventManagement\DeleteGMGiftBagAction;
use App\GMActivity;
use App\GMGiftBag;
use App\ServerList;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GMGiftBagController extends AdminController
{
    protected $title = 'Mốc sự kiện';

    protected function grid()
    {
        $request = Request::capture();
        if ($request->has('server')) {
            $request->input('server') == null ? $serverId = '1001' : $serverId = $request->input('server');
        } else {
            $serverId = '1001';
        }

        $server = ServerList::findOrFail($serverId);

        $giftBag = new GMGiftBag();
        $giftBag->setConnection($server->Connection);

        $activities = GMActivity::on($server->Connection)
            ->select('activityId', 'activityName')
            ->orderBy('activityId')
            ->get();

        $activityOptions = [];
        foreach ($activities as $activity) {
            $activityOptions[$activity->activityId] = $activity->activityName . ' - (ID = ' . $activity->activityId . ')';
        }

        $grid = new Grid($giftBag);
        $grid->setTitle('[' . $server->ServerName . '] Danh sách mốc sự kiện');

        $grid->column('Id', 'ID')->sortable();
        $grid->column('giftbagId', 'Giftbag ID')->sortable();
        $grid->column('activityId', 'Mã sự kiện')->sortable();
        $grid->column('rewardMark', 'Đánh dấu thưởng')->sortable();
        $grid->column('giftbagOrder', 'Thứ tự mốc')->sortable();

        $grid->quickSearch('giftbagId', 'activityId');
        $grid->expandFilter();
        $grid->filter(function ($filter) use ($activityOptions) {
            $filter->disableIdFilter();
            $filter->equal('giftbagId', 'Giftbag ID');
            $filter->equal('activityId', 'Mã sự kiện')->select($activityOptions);
            $filter->equal('rewardMark', 'Đánh dấu thưởng');
        });

        $grid->disableBatchActions();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->add(new DeleteGMGiftBagAction());
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
        $show = new Show(GMGiftBag::on($server->Connection)->findOrFail($id));

        $show->field('Id', 'ID');
        $show->field('giftbagId', 'Giftbag ID');
        $show->field('activityId', 'Mã sự kiện');
        $show->field('rewardMark', 'Đánh dấu thưởng');
        $show->field('giftbagOrder', 'Thứ tự mốc');

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

        $server = ServerList::findOrFail($serverId);

        $giftBag = new GMGiftBag();
        $giftBag->setConnection($server->Connection);

        $activities = GMActivity::on($server->Connection)
            ->select('activityId', 'activityName')
            ->orderBy('activityId')
            ->get();

        $activityOptions = [];
        foreach ($activities as $activity) {
            $activityOptions[$activity->activityId] = $activity->activityName . ' - (ID = ' . $activity->activityId . ')';
        }

        $form = new Form($giftBag);
        $form->hidden('server')->default($server->ServerID);

        $form->number('giftbagId', 'Giftbag ID')->required();
        $form->select('activityId', 'Mã sự kiện')->options($activityOptions)->required();
        $form->number('rewardMark', 'Đánh dấu thưởng')->default(0);
        $form->number('giftbagOrder', 'Thứ tự mốc')->default(0);

        return $form;
    }
}
