<?php

namespace App\Http\Controllers\Server;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\WechatUserModel;
class ServerController extends Controller
{
    public function index(){
        echo "11111";
    }

    public function apply(){
        return view('server/apply');
        
    }

    public function applyDo(){
       $data=request()->input();

       $where=['user_name'=>$data['user_name']];
       $userInfo=WechatUserModel::where($where)->first();
       if(empty($userInfo)){
               $user_id=WechatUserModel::select('user_id')->orderBy('user_id','desc')->first()['user_id']+1;
               $appId='WX'.str_repeat('0',5).uniqid($user_id);//appid
               $secret=md5($data['user_name']);  //secret
               $userInfo=WechatUserModel::create([
                   "user_name"=>$data['user_name'],
                   "user_pwd"=>$data['user_pwd'],
                   "secret"=>$secret,
                   "app_id"=>$appId
               ]);
           }
        
        // $data=$this->AesEncrypt($userInfo);
        // $decrypt=$this->AesDecrypt($encrypt);
        // dd($encrypt);
        // dd($decrypt);
        return $data;
    } 

    public  $key='1904';
    public  $iv='1904a1904a1904aa';

    //加密
    public function AesEncrypt($userInfo){
        $encrypt=openssl_encrypt(
            $userInfo,
            'aes-128-cbc',
            $this->key,
            1,
            $this->iv
        );
        return \base64_encode($encrypt);
    }

    public function AesDecrypt($encrypt){
        $data=base64_decode($encrypt);
        $decrypt=openssl_decrypt(
            $data,
            'aes-128-cbc',
            $this->key,
            1,
            $this->iv
        );
        return json_encode($decrypt);
    }
}
