<?php

namespace App\Admin\Controllers\EventManagement;

use App\GMActivity;
use App\GMActiveCondition;
use App\GMGiftBag;
use App\ServerList;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class GMActiveConditionController extends AdminController
{
    protected $title = 'Điều kiện sự kiện';

    protected function grid()
    {
        $request = Request::capture();
        if ($request->has('server')) {
            $request->input('server') == null ? $serverId = '1001' : $serverId = $request->input('server');
        } else {
            $serverId = '1001';
        }

        $server = ServerList::findOrFail($serverId);

        $condition = new GMActiveCondition();
        $condition->setConnection($server->Connection);

        $activities = GMActivity::on($server->Connection)
            ->select('activityId', 'activityName')
            ->orderBy('activityId')
            ->get();

        $activityOptions = [];
        foreach ($activities as $activity) {
            $activityOptions[$activity->activityId] = $activity->activityName . ' - (ID = ' . $activity->activityId . ')';
        }

        $selectedActivityId = $request->input('activityId');

        $giftBags = GMGiftBag::on($server->Connection)
            ->select('giftbagId', 'activityId')
            ->when($selectedActivityId, function ($query) use ($selectedActivityId) {
                $query->where('activityId', '=', $selectedActivityId);
            })
            ->orderBy('giftbagId')
            ->get();

        $giftbagOptions = [];
        foreach ($giftBags as $giftBag) {
            $giftbagOptions[$giftBag->giftbagId] = 'GiftBag ' . $giftBag->giftbagId . ' - Activity ' . $giftBag->activityId;
        }

        $grid = new Grid($condition);
        $grid->setTitle('[' . $server->ServerName . '] Danh sách điều kiện sự kiện');

        $grid->column('Id', 'ID')->sortable();
        $grid->column('giftbagId', 'Giftbag ID')->sortable();
        $grid->column('conditionIndex', 'Loại điều kiện')->sortable();
        $grid->column('conditionValue', 'Giá trị điều kiện')->sortable();
        $grid->column('remain1', 'remain1')->sortable();
        $grid->column('remain2', 'remain2');

        $grid->quickSearch('giftbagId');
        $grid->expandFilter();
        $grid->filter(function ($filter) use ($giftbagOptions, $activityOptions, $server) {
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

                $query->whereIn('giftbagId', $giftbagIds);
            }, 'Mã sự kiện', 'activityId')->select($activityOptions);
            $filter->equal('giftbagId', 'Giftbag ID')->select($giftbagOptions);
            $filter->equal('conditionIndex', 'Loại điều kiện');
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
        $show = new Show(GMActiveCondition::on($server->Connection)->findOrFail($id));

        $show->field('Id', 'ID');
        $show->field('giftbagId', 'Giftbag ID');
        $show->field('conditionIndex', 'Loại điều kiện');
        $show->field('conditionValue', 'Giá trị điều kiện');
        $show->field('remain1', 'remain1');
        $show->field('remain2', 'remain2');

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

        $condition = new GMActiveCondition();
        $condition->setConnection($server->Connection);

        $giftBags = GMGiftBag::on($server->Connection)
            ->select('giftbagId', 'activityId')
            ->orderBy('giftbagId')
            ->get();

        $giftbagOptions = [];
        foreach ($giftBags as $giftBag) {
            $giftbagOptions[$giftBag->giftbagId] = 'GiftBag ' . $giftBag->giftbagId . ' - Activity ' . $giftBag->activityId;
        }

        $form = new Form($condition);
        $form->hidden('server')->default($server->ServerID);

        $form->select('giftbagId', 'Giftbag ID')->options($giftbagOptions)->required();
        $form->number('conditionIndex', 'Loại điều kiện')->default(0);
        $form->number('conditionValue', 'Giá trị điều kiện')->default(0);
        $form->number('remain1', 'remain1')->default(0);
        $form->text('remain2', 'remain2');

        return $form;
    }
}
