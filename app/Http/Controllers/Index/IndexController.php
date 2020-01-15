<?php

namespace App\Http\Controllers\Index;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\WechatUserModel;
use Illuminate\Support\Facades\Redis;
use Session;
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

    public function huifu()
    {
        $data = file_get_contents("php://input");
        $xml = simplexml_load_string($data,'SimpleXMLElement', LIBXML_NOCDATA);        //将 xml字符串 转换成对象
        $xml = (array)$xml; //转化成数组
        //写入日志
        $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n".'<<<<<<<';
        file_put_contents(storage_path('logs/Receive_normal_messages.log'),$log_str,FILE_APPEND);
        if($xml['MsgType']=='text'){
            $preg_result = preg_match('/.*?油价/',$xml['Content']);
//            dd($preg_result);
            if($preg_result){
                //查询油价
                $city = substr($xml['Content'],0,-6);
                $price_info = file_get_contents('http://www.wenjianliang.top/youjia/api');
                $price_arr = json_decode($price_info,1);
//                dd($price_arr);
                $support_arr = [];
                foreach($price_arr['result'] as $v){
                    $support_arr[] = $v['city'];
                }
                if(!in_array($city,$support_arr)){
                    $message = '查询城市不支持！';
                    $xml_str = '<xml><ToUserName><![CDATA['.$xml['FromUserName'].']]></ToUserName><FromUserName><![CDATA['.$xml['ToUserName'].']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['.$message.']]></Content></xml>';
                    echo $xml_str;
                    die();
                }
                foreach($price_arr['result'] as $v){
                    if($city == $v['city']){
                        $this->redis->incr($city);
                        $find_num = $this->redis->get($city);
                        //缓存操作
                        if($find_num >1){
                            if($this->redis->exists($city.'youjia')){
                                //存在
                                $v_info = $this->redis->get($city.'youjia');
                                $v = json_decode($v_info,1);
                            }else{
                                $this->redis->set($city.'youjia',json_encode($v),300);
                            }
                        }
                        //$message = $city.'目前油价：'."\n";
                        $message = $city.'目前油价：'."\n".'92h：'.$v['92h']."\n".'95h：'.$v['95h']."\n".'98h：'.$v['98h']."\n".'0h：'.$v['0h'];
                        $xml_str = '<xml><ToUserName><![CDATA['.$xml['FromUserName'].']]></ToUserName><FromUserName><![CDATA['.$xml['ToUserName'].']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['.$message.']]></Content></xml>';
                        echo $xml_str;
                        die();
                    }
                }
            }
        }elseif($xml['MsgType']=='event'){
            if($xml['Event']=='subscribe'){
                $open_id=$xml['FromUserName'];
                $user_info=$this->wechat->get_user_info($open_id);
//                dd($user_info);
                $user_name=$user_info['nickname'];
                $us=DB::connection('mysql_shop')->table('user_wechat')->insert([
                    'name'=>$user_name,
                    'state'=>1,
                    'register_time'=>time(),
                    'password'=>''
                ]);
//                dd($open_id);
//                $open_id=implode('',$open_id);
                $huanying="欢迎";
                $jiewei="进入选课系统";
                $message = $huanying.$user_name.$jiewei;
                $xml_str = '<xml><ToUserName><![CDATA['.$xml['FromUserName'].']]></ToUserName><FromUserName><![CDATA['.$xml['ToUserName'].']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['.$message.']]></Content></xml>';
                echo $xml_str;
                //表白 -无故会出现故障 或者无反应
            }elseif($xml['Event'] == 'CLICK'){
                if($xml['EventKey'] == 'my_biaobai'){
                    $open_id=$xml['FromUserName'];
                    //此处的打印结果是openid 拿到openid 去查youjia 取出名字 放入条件
//                    dd($open_id);
                    $nickname_info=$this->wechat->get_user_info($open_id);
//                    dd($nickname_info);
                    $nickname_1=$nickname_info['nickname'];
//                    dd($nickname_1);
                    $biaobai_info = DB::connection('mysql_shop')->table('wechat_biaobai')->where(['user_name'=>$nickname_1])->get()->toArray();
//                    dd($biaobai_info);
                    $num=count($biaobai_info);
//                    dd($num);
                    $message = '';
                    foreach($biaobai_info as $k=>$v){
                        $message .= intval($k+1).'、'."《《收到》》".$v->push_user.'表白内容：'.$v->biaobai_content."\n";
                    }
                    $xml_str = '<xml><ToUserName><![CDATA['.$xml['FromUserName'].']]></ToUserName><FromUserName><![CDATA['.$xml['ToUserName'].']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['."共收到".$num.'条'."\n".$message.']]></Content></xml>';
                    echo $xml_str;
                }elseif ($xml['EventKey'] == 'my_kecheng'){
                    $user_name=session('kecheng_user_name');
                    $panduan_kecheng=DB::connection('mysql_shop')->table('bayue_yuekao_kecheng')->where('user_name',$user_name)->get()->toarray();
                        //已经有课程
                        $open_id=$xml['FromUserName'];
                        //此处的打印结果是openid 拿到openid 去查youjia 取出名字 放入条件
//                    dd($open_id);
                        $nickname_info=$this->wechat->get_user_info($open_id);
//                    dd($nickname_info);
                        $nickname_1=$nickname_info['nickname'];
//                    dd($nickname_1);
                        $kecheng_info = DB::connection('mysql_shop')->table('bayue_yuekao_kecheng')->where(['user_name'=>$nickname_1])->orderBy('kecheng_id','desc')->first();
//                    dd($biaobai_info);
                        $kecheng_info=json_decode(json_encode($kecheng_info),1);
                        $tishi="你好,".$this->wechat->get_user_info($xml['FromUserName'])['nickname']."同学，你当前的课程安排如下";
//                    dd($kecheng_info);
//                    $message = '';
                        $message ="第一节".$kecheng_info['kecheng_1']."\n"."第二节".$kecheng_info['kecheng_2']."\n"."第三节".$kecheng_info['kecheng_3']."\n"."第四节".$kecheng_info['kecheng_4']."\n";
                        $xml_str = '<xml><ToUserName><![CDATA['.$xml['FromUserName'].']]></ToUserName><FromUserName><![CDATA['.$xml['ToUserName'].']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['.$tishi."\n".$message.']]></Content></xml>';
//                    $xml_str = '<xml><ToUserName><![CDATA['.$xml['FromUserName'].']]></ToUserName><FromUserName><![CDATA['.$xml['ToUserName'].']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['.$message.']]></Content></xml>';
//                    dump($xml_str);
                        echo $xml_str;
                }
                //老师的地理位置 看不懂
            }elseif($xml['Event'] == 'location_select') {
                $message = $xml['SendLocationInfo']->Label;
                \Log::Info($message);
                $xml_str = '<xml><ToUserName><![CDATA[otAUQ1UtX-nKATwQMq5euKLME2fg]]></ToUserName><FromUserName><![CDATA[' . $xml['ToUserName'] . ']]></FromUserName><CreateTime>' . time() . '</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[' . $message . ']]></Content></xml>';
                echo $xml_str;
            }
        }
    }
}
