<?php

namespace App\Admin\Actions\EventManagement;

use App\GMActiveCondition;
use App\GMActiveReward;
use App\GMGiftBag;
use App\ServerList;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeleteGMGiftBagAction extends RowAction
{
    public $name = 'Xoá';

    public function form()
    {
        $server = $this->resolveServer();

        $conditionCount = GMActiveCondition::on($server->Connection)
            ->where('giftbagId', '=', $this->row->giftbagId)
            ->count();

        $rewardCount = GMActiveReward::on($server->Connection)
            ->where('giftId', '=', $this->row->giftbagId)
            ->count();

        $summary = 'Dữ liệu liên quan: ' .
            'Điều kiện=' . $conditionCount .
            ', Phần thưởng=' . $rewardCount;

        $this->text('impact_summary', 'Thống kê liên quan')
            ->default($summary)
            ->disable();

        $this->radio('delete_mode', 'Kiểu xoá')
            ->options([
                'normal' => 'Chỉ xoá mốc sự kiện',
                'cascade' => 'Xoá mốc sự kiện và dữ liệu liên quan',
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
                    GMActiveCondition::on($server->Connection)
                        ->where('giftbagId', '=', $model->giftbagId)
                        ->delete();

                    GMActiveReward::on($server->Connection)
                        ->where('giftId', '=', $model->giftbagId)
                        ->delete();

                    GMGiftBag::on($server->Connection)
                        ->where('Id', '=', $model->Id)
                        ->delete();
                });

                return $this->response()->success('Đã xoá mốc sự kiện và dữ liệu liên quan.')->refresh();
            }

            $deleted = GMGiftBag::on($server->Connection)
                ->where('Id', '=', $model->Id)
                ->delete();

            if ($deleted) {
                return $this->response()->success('Đã xoá mốc sự kiện.')->refresh();
            }

            return $this->response()->error('Xoá mốc sự kiện thất bại.')->refresh();
        } catch (\Throwable $e) {
            return $this->response()->error('Xoá mốc sự kiện thất bại: ' . $e->getMessage())->refresh();
        }
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
