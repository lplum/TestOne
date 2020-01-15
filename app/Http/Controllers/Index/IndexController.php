<?php

namespace App\Http\Controllers\Index;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\WechatUserModel;
use Illuminate\Support\Facades\Redis;
use Session;
use Illuminate\Support\Facades\Cache;
use App\Tools\Tools;
class IndexController extends Common
{
    // public  $key='1904';
    // public  $iv='1904a1904a1904aa';
    // public function index(){

    //     $data=[
    //         "user_name"=>"胡汉三",
    //         "user_pwd"=>"123123"
    //     ];
    //     $encrypt=$this->_AesEncrypt($data);
    //     $decrypt=$this->_AesDecrypt($encrypt);
    //     echo $encrypt;
    //     echo "<br>";
    //     print_r($decrypt) ;exit;
    // }
    public $tools;
    public $request;
    public function __construct(Tools $tools,Request $request)
    {
        $this->tools = $tools;
        $this->request = $request;
    }
    public function login()
    {
        $data=[
            "user_name"=>"胡汉三",
            "user_pwd"=>"123123"
        ];
        $url="http://api.laravel.com/login";
     
        $api_result=$this->curlPost($url,$data);
        print_r($api_result);
    }

    public function test(){
        $data_str=str_repeat("0123456789",15);
        \openssl_encrypt(
            $data_str,      
        ); 
        var_dump($data_str) ;
        // public_path();//助手函数  返回绝对路径
    }
    
    public function log(){
        return view('index.login');
    }  
    
    public function logDo(){
        $user_name=request()->input('user_name');
        $user_pwd=request()->input('user_pwd');
        //获取sessionid
        $session_id=Session::getId();
        $error_number='';
        $userData=WechatUserModel::where(['user_name'=>$user_name])->first();
        if (!$userData){
            return redirect('log')->withErrors(['用户名不存在']);
        }
        // session(['userData',$userData]);
        session()->put('userData', $userData);

        // $a=session('userData');
        // dd($a);
        $locking_time=$userData['locking_time'];//最后一次错误，锁定时间
        if(!empty($userData)){
            if($userData['user_pwd']!=md5($user_pwd)){ 
                //第一次错误
                if($userData['error_number']==0){
                    WechatUserModel::where(['user_name'=>$user_name])->update([
                        "error_number"=>$error_number=1
                    ]);
                    echo "密码错误,还有2次机会";exit; 
                } 
                //累加
                if($userData['error_number']==1){
                    WechatUserModel::where(['user_name'=>$user_name])->update([
                        "error_number"=>$userData['error_number']+1
                    ]);
                    echo "密码错误,还有1次机会";exit;
                }
                if($userData['error_number']==2){
                    WechatUserModel::where(['user_name'=>$user_name])->update([
                        "error_number"=>$userData['error_number']+1,
                        "locking_time"=>time()+600 //错误时间+2小时
                    ]);
                    echo "密码错误,账号被锁定";exit;
                }    
            }  
        }

        if(time()-$locking_time<600){
            $mins=ceil(($locking_time-time())/60);
            echo "账号锁定中".$mins."分钟后进行登录";exit;
        }
       WechatUserModel::where(['user_name'=>$user_name])->update([
            "error_number"=>0,
            'session_id'=>$session_id,
            "locking_time"=>0,
            "log_time"=>time()+300  //登录时间
        ]);

        return redirect('list');
    }

    public function list(){
       
        echo "11111";
    }
    public function wechat()
    {
        $token=$this->tools->get_wechat_access_token();
        $name=md5(uniqid());
        // dd($token);
        //调用带二维码参数
        $url="https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={$token}";
        // dd($url);
        $data='{"expire_seconds": 3600, "action_name": "QR_STR_SCENE", "action_info": {"scene": {"scene_str": "'.$name.'"}}}';
        // dd($data);
        $data=$this->tools->curl_post($url,$data);
        //
        $img=json_decode($data,true);
        $ticket=$img['ticket'];
        // dd($ticket);
        $imgUrl="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".$ticket;
        // dd($imgUrl);
        return view('index/wechat',['imgUrl'=>$imgUrl]);
    }
    public function index()
   {
      $echostr=request()->echostr;
      if(!empty($echostr)){
         echo $echostr;
      }
      $xmlData=file_get_contents("php://input");
      file_put_contents('1.txt',$xmlData);
      //将xml格式转成xml对象
      $xmlObj=simplexml_load_string($xmlData,'SimpleXMLElement',LIBXML_NOCDATA);
      //判断用户未关注过
      if($xmlObj->MsgType=="event"&&$xmlObj->Event=="subscribe"){
            //获取openid
            $openId=(string)$xmlObj->FromUserName;
            //获取二维码标识
            $EventKey=(string)$xmlObj->EventKey;
            $status=ltrim($EventKey,'qrscene_');
            if(!empty($status)){
               //带参数关注事件
               Cache::put($status,$openId,20);
               //回复文本消息
               echo $msg="正在扫描登录，耐心等待";
               $this->tools->responseText($msg,$xmlObj);
            }
      }
      //判断用户关注过
      if($xmlObj->MsgType=="event"&&$xmlObj->Event=="SCAN"){
         //获取openid
         $openId=(string)$xmlObj->FromUserName;
         //获取二维码
         $status=(string)$xmlObj->EventKey;
         if(!empty($status)){
            //带参数关注事件
            Cache::put($status,$openId,20);
            //回复文本消息
            echo $status;
            echo $msg="已关注扫描登录，耐心等待";
            $this->tools->responseText($msg,$xmlObj);
         }
      }
     
   }


   public function checkWechatLogin(){
      $name=request()->name;
      $openId=Cache::get($name);
      // dd($openId);
      if(!$openId){
          return json_encode(['font'=>'用户未登录','msg'=>2]);
      }
      return json_encode(['font'=>'用户已扫描','msg'=>1]);
   }
}
