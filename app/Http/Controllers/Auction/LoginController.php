<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\AuctionModel;
use App\Model\AuctionUserModel;
class LoginController extends Controller
{
    /**
     *  
     */
    public function loginDo(){
        $user_name=request()->user_name;
        $user_pwd=request()->user_pwd;
        $userInfo=AuctionUserModel::where('user_name',$user_name)->first();
        if(!empty($userInfo)){
            if($user_pwd!=$userInfo['user_pwd']){
                echo "密码错误";exit;
            }
        }
        Session(['userData'=>$userInfo]);//存session
        // dd(Session('userData'));
        return \redirect('auction/index');
    }
}
