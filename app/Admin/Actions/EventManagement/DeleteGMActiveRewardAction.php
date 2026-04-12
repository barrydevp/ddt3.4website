<?php

namespace App\Admin\Actions\EventManagement;

use App\GMActiveReward;
use App\ServerList;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class DeleteGMActiveRewardAction extends RowAction
{
    public $name = 'Xoá';

    public function dialog()
    {
        $this->confirm('Bạn có chắc muốn xoá phần thưởng sự kiện này không?');
    }

    public function handle(Model $model)
    {
        $server = $this->resolveServer();

        $deleted = GMActiveReward::on($server->Connection)
            ->where('giftId', '=', $this->row->giftId)
            ->where('templateId', '=', $this->row->templateId)
            ->delete();

        if ($deleted) {
            return $this->response()->success('Đã xoá phần thưởng sự kiện.')->refresh();
        }

        return $this->response()->error('Xoá phần thưởng sự kiện thất bại.')->refresh();
    }

    public function retrieveModel(Request $request)
    {
        if (!$key = $request->get('_key')) {
            return false;
        }

        $server = $this->resolveServer($request);

        return GMActiveReward::on($server->Connection)->findOrFail($key);
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
