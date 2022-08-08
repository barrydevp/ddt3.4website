<?php

namespace App\Http\Controllers\Client;

use App\EmailVerify;
use App\Http\Controllers\Controller;
use App\Http\Requests\AjaxPublic\CheckValidEmailRequest;
use App\Http\Requests\Client\ChangeEmailRequest;
use App\Http\Requests\Client\ChangeNickName\ChangeNickNameRequest;
use App\Http\Requests\Client\ChangePhoneNumberRequest;
use App\Http\Requests\Client\SendCodeToChangeVerifiedEmailRequest;
use App\Http\Requests\Client\SendVerifyCodeToEmailRequest;
use App\Http\Requests\Cliet\ConvertCoinRequest;
use App\LogCardCham;
use App\Mailer;
use App\Member;
use App\MemberHistory;
use App\Notifications\VerifyEmailNotification;
use App\Player;
use App\ServerList;
use App\Setting;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\Request;
use App\Http\Requests\Client\ChangePasswordRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    public function showAccountView()
    {
        return view('client.account.account');
    }

    public function showChangePasswordView(Request $request)
    {
        if($request->ajax()){
            $html = view('client.account.change-password')->render();
            return response()->json( $html );
        }
        return route('home');
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $oldPassword = $request->input('oldPassword');
        $newPassword = $request->input('password');
        $currentHashedPassword = $request->user('member')->Password;
        if (Hash::check($oldPassword, $currentHashedPassword)) {
            $newHashedPassword = Hash::make($newPassword);
            Member::where('UserID', $request->user('member')->UserID)
                ->update(['Password' => $newHashedPassword]);
            return response()->json(['msg' => 'Thay đổi mật khẩu thành công.'],200);
        }
        return response()->json(['msg' => 'Mật khẩu cũ không đúng.'],400);
    }

    public function enable2FA(Request $request)
    {
        $member = $request->user('member');
        if($member->VerifiedEmail || $member->VerifiedEmail == 1){
            Member::where('UserID', $member->UserID)->update(['2fa'=> 1]);
            return response()->json(['msg' => 'Bạn đã bật xác thực 2 yếu tố (2FA) thành công.', 200]);
        }
        return response()->json(['msg' => 'Bạn chưa xác thực email.', 400]);
    }

    public function test2fa(Request $request)
    {

    }

    public function showVerifyEmailView(Request $request)
    {
        if($request->ajax()){
            $html = view('client.account.verify-email')->render();
            return response()->json($html);
        }
        return redirect(route('home'));
    }

    private function sendVerifyContentEmail($email, $token, $urlVerify)
    {
        $mailer = new Mailer();
        $status = $mailer->sendVerifyEmailLink($email, $urlVerify);
        if($status['success']){

            EmailVerify::updateOrCreate(
                ['Email'      => $email,]
                ,[
                    'Token'       => $token,
                    'IsActivated' => 0,
                    'CreatedAt'   => now(),
                ]
            );
        } else return response()->json(['msg'=>'Lỗi hệ thống gửi mail, vui lòng liên hệ quản trị viên!'],400);
    }

    public function sendVerifyCodeToEmail(SendVerifyCodeToEmailRequest $request)
    {
        $member = $request->user('member');

        if($member->VerifiedEmail)
            return response()->json(['msg'=> 'Bạn đã xác thực email rồi'],400);

        $token = Str::random(64);
        $email = $member->Fullname;
        $urlVerify = route('execute-verify-email').'?token='.$token;
        $emailVerify = EmailVerify::where('Email', $email)->first();
        if($emailVerify){
            $latestSentTime = Carbon::create($emailVerify->CreatedAt)->addMinute(1);
//            $isActivated = $emailVerify->IsActivated; //No need
            if(!Carbon::now()->gte($latestSentTime)){
                return response()->json(['msg'=>'Lỗi: Vui lòng đợi 1 phút để có thể gửi thư tiếp theo!'],400);
            }
            $this->sendVerifyContentEmail($email, $token, $urlVerify);

            return back();
        }
        $this->sendVerifyContentEmail($email, $token, $urlVerify);
        return back();
    }

    public function verifyEmail(Request $request)
    {
        if($request->input('token') == null){
            $success = 'Không hợp lệ';
            return view('client.verify-email',compact('success'));
        }
        $emailVerify = EmailVerify::where('token', $request->input('token'))->first();
        if ($emailVerify) {
            if($emailVerify->IsActivated == 0){
                Member::where('UserID',$request->user('member')->UserID)->update(['VerifiedEmail'=> 1]);
                EmailVerify::where('token', $request->input('token'))->update(['IsActivated'=> 1]);
                $success = 'Xác thực email thành công';
                return view('client.verify-email', compact('success'));
            }
        }
        $success = 'Không hợp lệ';
        return view('client.verify-email',compact('success'));

    }

    public function showConvertCoinView()
    {
        $serverList = ServerList::select('ServerID', 'ServerName')->get();
        $heSoCoin = Setting::get('he-so-doi-coin');
        return view('client.account.convert-coin', compact('serverList', 'heSoCoin'));
    }

    public function convertCoin(ConvertCoinRequest $request)
    {
        $serverId = $request->input('server_id');
        $server = ServerList::find($serverId);
        if(!$server)
            return response()->json(['msg' => 'Server không tồn tại'], 400);
        $serverConnection = $server->Connection;

        $member = $request->user('member');
        $coin = $request->input('coin');
        $coin = intval($coin);
        $coin = max(20000, $coin);
        $coin = min(2000000, $coin);
        //Get total charged for checking
        $memHistory = new MemberHistory();

        $totalChanged = $memHistory->memberTotalChargedToday($member->UserID);
        $totalChangeAllowed = Setting::get('gioi-han-chuyen-xu-ngay');
        if ($member->Money < $coin)
            return response()->json(['msg' => 'Không đủ coin để chuyển đổi!'], 400);

        if ($totalChanged > 0 && !empty($totalChangeAllowed) && intval($totalChangeAllowed) <= ($totalChanged + $coin)) {
            if ($totalChanged < intval($totalChangeAllowed))
                return response()->json(['msg' => 'Số xu tối đa bạn có thể đổi trong hôm nay là '.(intval($totalChangeAllowed) - $totalChanged)], 400);
            return response()->json(['msg' => 'Bạn đã đạt tối đa số xu nạp cho phép trong hôm nay, hãy quay lại vào ngày mai!'], 400);
        }

        $players = Player::on($serverConnection)->select('UserID', 'NickName')
            ->where('UserName', $member->Email)
            ->get();
		$playerId = 0;
        if (!empty($players) && $players->count() > 1) {
			$playerId = $request->input('playerId');
			if (empty($playerId)) {
				return response()->json(['msg' => 'Chưa chọn nhân vật cần chuyển!'], 400);
			}
		} elseif (!empty($players) && $players->count() == 1) {
			$playerId = $players->first()->UserID;
		} else {
            return response()->json(['msg' => 'Nhân vật không tồn tại!'], 400);
		}

        DB::connection('sqlsrv_mem')->beginTransaction();
        $memHistory = new MemberHistory();
        if(
//            $memHistory->memberChargeMoneyLog($member->UserID, $coin, $server->ServerName, $request->ip()) && //Without AntiDDos.vn
            $memHistory->memberChargeMoneyLog($member->UserID, $coin, $server->ServerName, $request->header('x-real-ip')) &&
            $member->chargeMoney($coin)
        ){
            $chargeID = md5(uniqid());

			$options = array(
			  'http'=>array(
				'header'=>"User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) 37abc/2.0.6.16 Chrome/60.0.3112.113 Safari/537.36" // i.e. An iPad
			  )
			);
			$context = stream_context_create($options);
            $xu = $coin * Setting::get('he-so-doi-coin');
            $xu += $xu * $member->getVipBonus() / 100;
            $content = file_get_contents(
                trim($server->LinkRequest, '/')
                . "/ChargeMoney.aspx?content="
                . $chargeID
                . "|" . $member->Email
                . "|" . $xu
                . "|0" //payway
                . "|.00" //needMoney
                . "|" . md5($chargeID.$member->Email.$xu.'0'.'.00'.env('CHARGE_KEY'))
                . "&nickname=" . $playerId
            ,false, $context);
            if (substr($content, 0, 1) === "0") {
                DB::connection('sqlsrv_mem')->commit();
                return response()->json(['msg' => 'Nạp xu thành công!'], 200);

            } else {
                DB::connection('sqlsrv_mem')->rollBack();
                return response()->json(['msg' => 'Không thể nạp xu vào game, vui lòng thoát game và thử lại hoặc thông báo tới quản trị viên để được trợ giúp!'], 400);
            }

        }
    }

    public function showHistoryRechargeView(Request $request)
    {
        $cardsCharged = LogCardCham::where('UserName', $request->user('member')->Email)->get();
        return view('client.account.history-recharge', compact('cardsCharged'));
    }

    public function showChangeEmailView(Request $request)
    {
        $currentMail = $request->user('member')->Fullname;
        $isVerified = false;
        if($request->user('member')->VerifiedEmail)
            $isVerified = true;
        return view('client.account.change-email', compact('currentMail','isVerified'));
    }

    public function changeEmail(ChangeEmailRequest $request)
    {
        $newEmail = $request->input('Fullname');
        $member = $request->user('member');
        $emailVerify = EmailVerify::where('Email', $member->Fullname)->first();

        if($request->user('member')->VerifiedEmail){
            $code = $request->input('code');
            if(!isset($code) && strlen($code) < 7) return response()->json(['msg'=>'Bạn phải nhập mã xác thực'],400);

            if($emailVerify && $emailVerify->IsActivated == 0){
                if($emailVerify->Token != $code) return response()->json(['msg'=> 'Mã xác thực không đúng'], 400);
                Member::select('Fullname', 'VerifiedEmail')
                    ->where('UserID', $member->UserID)
                    ->update([
                        'VerifiedEmail' => 0,
                        'Fullname' => $newEmail
                    ]);
                EmailVerify::where('Email', $member->Fullname)->update(['IsActivated' => 1]);
                return response()->json(['msg'=> 'Thay đổi email thành công']);
            }
            else{
                return response()->json(['msg'=> 'Mã xác thực không đúng'], 400);
            }
        }
        else{
            Member::select('Fullname', 'VerifiedEmail')->where('UserID', $request->user('member')->UserID)
                ->update([
                    'VerifiedEmail' => 0,
                    'Fullname' => $newEmail
                ]);
            EmailVerify::where('Email', $member->Fullname)->update(['IsActivated' => 1]);
            return response()->json(['msg' => 'Đã thay đổi địa chỉ email mới!']);
        }
    }

    public function sendCodeToChangeVerifiedEmail(SendCodeToChangeVerifiedEmailRequest $request)
    {
        $mailer = new Mailer();
        $member = $request->user('member');
        $code = rand(100000, 999999);
        if($member->VerifiedEmail){
            $find = EmailVerify::where('Email', $member->Fullname)->first();
            if($find){
                if($find->IsActivated == 1){
                    $status = $mailer->verifyChangeEmailVerifiedCode($member->Fullname, $code);
                    if($status['success']){
                        EmailVerify::where('Email', $member->Fullname)
                            ->update(
                                [
                                    'token' => $code,
                                    'IsActivated' => 0,
                                    'CreatedAt' => now()
                                ]);
                        return response()->json(['msg' => 'Gửi mã xác thực vào Email thành công!']);
                    }
                    return response()->json(['msg' => 'Lỗi hệ thống gửi Email, vui lòng liên hệ quản trị viên!'], 400);
                }
                return response()->json(['msg' => 'Đã gửi mã xác thực đến email rồi!'], 400);

            }
            else{
                $status = $mailer->verifyChangeEmailVerifiedCode($member->Fullname, $code);
                if($status['success']){
                    EmailVerify::create([
                        'CreatedAt' => now(),
                        'Email' => $member->Fullname,
                        'Token' => $code,
                        'IsActivated' => 0,
                        'CreatedAt' => now(),
                    ]);
                    return response()->json(['msg' => 'Gửi mã xác thực vào Email thành công!']);
                }
                return response()->json(['msg' => 'Lỗi hệ thống gửi Email, vui lòng liên hệ quản trị viên!'], 400);

            }
        }
        return response()->json(['msg' => 'Bạn chưa xác thực email!'], 400);
    }

    public function showChangePhoneNumberView()
    {
        return view('client.account.change-phone');
    }

    public function changePhoneNumber(ChangePhoneNumberRequest $request)
    {
        $member = $request->user('member');
        $phone = $request->input('Phone');
        Member::where('UserID', $member->UserID)->update(['Phone' => $phone]);
        return response()->json(['msg' => 'Cập nhật số điện thoại thành công!']);
    }

    public function showChangeNickName()
    {
        $price = (int) Setting::get('change-nickname-price');
        return view('client.account.change-nickname', compact('price'));
    }

    private function isNickNameValid($serverId, $nickName)
    {
        $server = ServerList::find($serverId);
        if (empty($server)) {
            return false;
        }
        $player = Player::on($server->Connection)->select('UserID', 'NickName')
            ->where('NickName', $nickName)
            ->first();
        return empty($player);
    }


    public function changeNickName(ChangeNickNameRequest $request)
    {
        $PRICE_CHANGE_NAME = (int) Setting::get('change-nickname-price');
        //Get input without through magic method
        $server_id = $request->input('server_id');
        $newNickName = $request->input('new_name');

        $server = ServerList::find($server_id);
        if (empty($server))
            return response()->json(['msg'=>'Lỗi: Không tìm thấy máy chủ!'],400);

        $member = $request->user('member');

        $player = Player::on($server->Connection)->select('UserID', 'NickName')
            ->where('UserName', $member->Email)
            ->first();

        if (empty($player))
            return response()->json(['msg'=>'Lỗi: Bạn chưa khởi tạo nhân vật!'],400);

        $currentCoin = (int) $member->Money;

        if($currentCoin < $PRICE_CHANGE_NAME)
            return response()->json(['msg'=>'Lỗi: Bạn không đủ Coin để thay đổi tên nhân vật!'],400);

        //Check is new NickName valid
        $isNickNameValid = $this->isNickNameValid($server_id, $newNickName);

        //Initial variable for logs
        $previousNickName = $player->NickName;

        if($isNickNameValid){
            DB::connection('sqlsrv_mem')->beginTransaction();
            $mem_history = new MemberHistory();
            if (
                $mem_history->memberChangeNickNameLog($member->UserID, $previousNickName, $newNickName ,$PRICE_CHANGE_NAME, $server->ServerName, $request->header('x-real-ip'))
                &&
                $member->chargeMoney($PRICE_CHANGE_NAME)
            ) {
                $player->NickName = $newNickName;

                if($player->save()){
                    DB::connection('sqlsrv_mem')->commit();
                    return response()->json(['msg'=>'Đổi tên nhân vật thành công']);

                }
                else{
                    DB::connection('sqlsrv_mem')->rollBack();
                    return response()->json(['msg'=>'Lỗi: Không thể đổi tên nhân vật, vui lòng thoát game và thử lại hoặc thông báo tới quản trị viên để được trợ giúp!'], 400);
                }
            }
            else
                return response()->json(['msg'=>'Lỗi: Không lưu lịch sử đổ tên, vui lòng thoát game và thử lại hoặc thông báo tới quản trị viên để được trợ giúp!'], 400);
        }
        else{
            return response()->json(['msg'=>'Lỗi: Tên nhân vật đã tồn tại!'], 400);
        }
    }
}
