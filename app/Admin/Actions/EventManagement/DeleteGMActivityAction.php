<?php

namespace App\Admin\Actions\EventManagement;

use App\GMActiveCondition;
use App\GMActiveReward;
use App\GMActivity;
use App\GMGiftBag;
use App\ServerList;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeleteGMActivityAction extends RowAction
{
    public $name = 'Xoá';

    public function form()
    {
        $server = $this->resolveServer();

        $giftBagIds = GMGiftBag::on($server->Connection)
            ->where('activityId', '=', $this->row->activityId)
            ->pluck('giftbagId')
            ->toArray();

        $giftBagCount = count($giftBagIds);
        $conditionCount = 0;
        $rewardCount = 0;

        if ($giftBagCount > 0) {
            $conditionCount = GMActiveCondition::on($server->Connection)
                ->whereIn('giftbagId', $giftBagIds)
                ->count();
            $rewardCount = GMActiveReward::on($server->Connection)
                ->whereIn('giftId', $giftBagIds)
                ->count();
        }

        $summary = 'Dữ liệu liên quan: ' .
            'Mốc=' . $giftBagCount .
            ', Điều kiện=' . $conditionCount .
            ', Phần thưởng=' . $rewardCount;

        $this->text('impact_summary', 'Thống kê liên quan')
            ->default($summary)
            ->disable();

        $this->radio('delete_mode', 'Kiểu xoá')
            ->options([
                'normal' => 'Chỉ xoá sự kiện',
                'cascade' => 'Xoá sự kiện và toàn bộ dữ liệu liên quan',
            ])
            ->default('normal')
            ->required();
    }

    public function handle(Model $model, Request $request)
    {
        $server = $this->resolveServer($request);
        $deleteMode = $request->input('delete_mode', 'normal');

        try {
            if ($deleteMode === 'cascade') {
                DB::connection($server->Connection)->transaction(function () use ($server, $model) {
                    $giftBagIds = GMGiftBag::on($server->Connection)
                        ->where('activityId', '=', $model->activityId)
                        ->pluck('giftbagId')
                        ->toArray();

                    if (!empty($giftBagIds)) {
                        GMActiveCondition::on($server->Connection)
                            ->whereIn('giftbagId', $giftBagIds)
                            ->delete();

                        GMActiveReward::on($server->Connection)
                            ->whereIn('giftId', $giftBagIds)
                            ->delete();

                        GMGiftBag::on($server->Connection)
                            ->where('activityId', '=', $model->activityId)
                            ->delete();
                    }

                    GMActivity::on($server->Connection)
                        ->where('activityId', '=', $model->activityId)
                        ->delete();
                });

                return $this->response()->success('Đã xoá sự kiện và dữ liệu liên quan.')->refresh();
            }

            $deleted = GMActivity::on($server->Connection)
                ->where('activityId', '=', $model->activityId)
                ->delete();

            if ($deleted) {
                return $this->response()->success('Đã xoá sự kiện.')->refresh();
            }

            return $this->response()->error('Xoá sự kiện thất bại.')->refresh();
        } catch (\Throwable $e) {
            return $this->response()->error('Xoá sự kiện thất bại: ' . $e->getMessage())->refresh();
        }
    }

    public function retrieveModel(Request $request)
    {
        if (!$key = $request->get('_key')) {
            return false;
        }

        $server = $this->resolveServer($request);

        return GMActivity::on($server->Connection)
            ->where('activityId', '=', $key)
            ->firstOrFail();
    }

    private function resolveServer(Request $request = null)
    {
        $request = $request ?: Request::capture();

        if ($request->has('server')) {
            $request->input('server') == null ? $serverId = '1001' : $serverId = $request->input('server');
        } else {
            $serverId = '1001';
        }

        return ServerList::findOrFail($serverId);
    }
}
