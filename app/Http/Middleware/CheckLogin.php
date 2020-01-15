<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use App\Model\WechatUserModel;
class CheckLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $session_id=Session::getId();//获取sessionid
        // dd($session_id);
        $userData=session('userData');
        // dd($userData);
        if($session_id!=$userData['session_id']){
            session(['userData'=>null]);
            session(['session_id'=>null]);
            return redirect('log');
        }
        //超过数据库时间
        if(time()>$userData['log_time']){
            session()->flush(); //清除session ，重新登录
            return redirect('log');
        }

        WechatUserModel::where(['user_name'=>$userData['user_name']])->update([
            "log_time"=>time()+300  //登录时间
        ]);
        return $next($request);
    }
}
