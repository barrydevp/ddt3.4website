<?php

namespace App\Admin\Controllers\EventManagement;

use App\Admin\Actions\EventManagement\DeleteGMActivityAction;
use App\GMActivity;
use App\ServerList;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GMActivityController extends AdminController
{
    protected $title = 'Sự kiện';

    protected function grid()
    {
        $request = Request::capture();
        if ($request->has('server')) {
            $request->input('server') == null ? $serverId = '1001' : $serverId = $request->input('server');
        } else {
            $serverId = '1001';
        }

        $server = ServerList::findOrFail($serverId);

        $activity = new GMActivity();
        $activity->setConnection($server->Connection);

        $grid = new Grid($activity);
        $grid->setTitle('[' . $server->ServerName . '] Danh sách sự kiện');

        $grid->column('activityId', 'Mã sự kiện')->sortable();
        $grid->column('activityName', 'Tên sự kiện');
        $grid->column('activityType', 'Loại')->sortable();
        $grid->column('activityChildType', 'Loại con')->sortable();
        $grid->column('beginTime', 'Bắt đầu')->display(function ($value) {
            return $value ? date('d-m-Y H:i', strtotime($value)) : null;
        })->sortable();
        $grid->column('endTime', 'Kết thúc')->display(function ($value) {
            return $value ? date('d-m-Y H:i', strtotime($value)) : null;
        })->sortable();
        $grid->column('status', 'Trạng thái')->display(function ($value) {
            return (int) $value === 1
                ? '<span class="badge btn-success">Hoạt động</span>'
                : '<span class="badge btn-danger">Tắt</span>';
        })->filter([1 => 'Hoạt động', 0 => 'Tắt']);
        $grid->column('isContinue', 'Lặp lại')->display(function ($value) {
            return (int) $value === 1 ? 'Có' : 'Không';
        })->filter([1 => 'Có', 0 => 'Không']);

        $grid->quickSearch('activityId', 'activityName');
        $grid->expandFilter();
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('activityId', 'Mã sự kiện');
            $filter->like('activityName', 'Tên sự kiện');
            $filter->equal('activityType', 'Loại');
            $filter->equal('status', 'Trạng thái')->select([1 => 'Hoạt động', 0 => 'Tắt']);
            $filter->between('beginTime', 'Bắt đầu')->datetime();
            $filter->between('endTime', 'Kết thúc')->datetime();
        });

        $grid->disableBatchActions();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->add(new DeleteGMActivityAction());
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
        $show = new Show(GMActivity::on($server->Connection)->findOrFail($id));

        $show->field('activityId', 'Mã sự kiện');
        $show->field('activityName', 'Tên sự kiện');
        $show->field('activityType', 'Loại');
        $show->field('activityChildType', 'Loại con');
        $show->field('getWay', 'Cách nhận');
        $show->field('rewardDesc', 'Mô tả phần thưởng');
        $show->field('desc', 'Mô tả sự kiện');
        $show->field('beginTime', 'Bắt đầu');
        $show->field('beginShowTime', 'Hiển thị từ');
        $show->field('endTime', 'Kết thúc');
        $show->field('endShowTime', 'Hiển thị đến');
        $show->field('icon', 'Icon');
        $show->field('isContinue', 'Lặp lại');
        $show->field('status', 'Trạng thái');
        $show->field('SectionId', 'SectionId');
        $show->field('CanReset', 'CanReset');

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

        $activity = new GMActivity();
        $activity->setConnection($server->Connection);

        $form = new Form($activity);
        $form->setTitle('Sự kiện');
        $form->hidden('server')->default($server->ServerID);

        if ($form->isCreating()) {
            $form->text('activityId', 'Mã sự kiện')
                ->required()
                ->rules('required|unique:' . $server->Connection . '.GM_Activity,activityId')
                ->help('Phải là duy nhất trong máy chủ hiện tại.');
        }

        if ($form->isEditing()) {
            $form->display('activityId', 'Mã sự kiện');
        }

        $form->text('activityName', 'Tên sự kiện')->required();
        $form->number('activityType', 'Loại')->default(0)->required();
        $form->number('activityChildType', 'Loại con')->default(0);
        $form->number('getWay', 'Cách nhận')->default(0);
        $form->textarea('rewardDesc', 'Mô tả phần thưởng');
        $form->textarea('desc', 'Mô tả sự kiện');

        $form->datetime('beginTime', 'Bắt đầu')->default(date('Y-m-d H:i:s'))->required();
        $form->datetime('beginShowTime', 'Hiển thị từ')->default(date('Y-m-d H:i:s'))->required();
        $form->datetime('endTime', 'Kết thúc')->default(date('Y-m-d H:i:s'))->required();
        $form->datetime('endShowTime', 'Hiển thị đến')->default(date('Y-m-d H:i:s'))->required();

        $form->number('icon', 'Icon')->default(1);
        $form->switch('isContinue', 'Lặp lại')->default(0);
        $form->switch('status', 'Trạng thái')->default(1);
        $form->number('remain1', 'remain1')->default(1);
        $form->text('remain2', 'remain2');
        $form->number('SectionId', 'SectionId')->default(0);
        $form->switch('CanReset', 'CanReset')->default(0);

        return $form;
    }
}
