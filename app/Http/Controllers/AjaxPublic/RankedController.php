<?php

namespace App\Http\Controllers\AjaxPublic;

use App\Http\Controllers\Controller;
//use Illuminate\Http\Request;
use App\Http\Requests\AjaxPublic\GetPublicRankRequest;
use App\ServerList;
use App\Player;
use Illuminate\Support\Facades\DB;

class RankedController extends Controller
{
    /**
     * @param GetPublicRankRequest $request
     * $request->type => 1 (FirePower) | => 2 (Grade [aka Level]) | => 3 (Win)
     */
    public function getPublicRanked(GetPublicRankRequest $request)
    {
//        $type = $request->input('type') == 1 ? 'FightPower' : 'Grade';
        $typeInput = $request->input('type');
        switch ($typeInput){
            case 1:
                $type = 'FightPower';
                break;
            case 2:
                $type = 'Grade';
                break;
            case 3:
                $type = 'OnlineTime';
                break;
            case 4:
                $type = 'charmGP';
                break;
        }
        $serverId = $request->input('server_id');

        $server = ServerList::find($serverId);
        if(!$server)
            return response()->json(['data' => 'Server Not found']);

        $serverConnection = $server->Connection;

        $player = new Player;
        $player->setConnection($serverConnection);

        $ranked = Player::on($serverConnection)
            ->select('NickName', $type)
            ->orderBy($type, 'desc')
            ->take(10)
            ->get();
        return response()->json($ranked);
    }
}
