<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;
class ApiMiddleware
{
    public  $key='1904';
    public  $iv='1904a1904a1904aa';

    public $app_maps=[
        '1904appid'=>'1904password',
        // '1905appid'=>'1905password'
    ];
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $data=$request->input('data');//接受的加密数据
        // dd($data);
        $decrypt=$this->AesDecrypt($data);//进行解密
        // dd($decrypt);
        //验证客户端的签名
        $check=$this->checkSign($decrypt);
        if($check['status']!=200){
            response($check);  //助手函数  获取响应函数
        }
        // var_dump($check);exit;
        return $next($request);
    }

     //加密
    public function AesEncrypt($data)
    {
        //加密参数，数据期望是字符串  判断是否是数组，如果是，转成字符串
        if(is_array($data)){
            $data=json_encode($data);
        }
        // dd($data); 
        $encrypt= openssl_encrypt($data,
            'aes-128-cbc',
            $this->key,
            1,
            $this->iv);
        return \base64_encode($encrypt);
    }
    //解密
    public function AesDecrypt($encrypt)
    {
        $decrypt=openssl_decrypt(
            base64_decode($encrypt),
            'aes-128-cbc',
            $this->key,
            1,
            $this->iv);
        return json_decode($decrypt,true);
    }
    /**
     *  验证签名
     */
    protected function checkSign($decrypt)
    {
        $client_sign=request()->post('sign');
        // dd($client_sign);
        ksort($decrypt);  
        //判断app_id是否存在
        if(isset($this->app_maps[$decrypt['app_id']])){
            //进行解密操作，  字符串拼接appkey
            $json=json_encode($decrypt).'app_key='.$this->app_maps[$decrypt['app_id']];
            // dd($json);
            if($client_sign==md5($json)){ //如果接到的签名== //md5加密后的字符串(数据)和签名
                //检查是否重放攻击   存入redis集合
                #缓存中如果能存入的时间戳和随机数，证明这条信息没有调用过接口
                if(Redis::sAdd('code_set',$decrypt['time'].$decrypt['rand'])){
                    //将数据返回
                    return [
                        "status"=>200,
                        "mag"=>'success',
                        "data"=> md5($json)
                    ];
                }else{
                    #缓存有数据，证明这条信息调用过接口返回失败
                    return [
                        'status'=>1000,
                        'mag'=>'check request fail',
                        'data'=>[]
                    ];
                }
            }else{
                return [
                    'status'=>600,
                    'mag'=>'check sign fail',
                    'data'=>[]
                ];
            }
        }else{
            //否则失败  appid不存在
            return [
                'status'=>600,
                'mag'=>'check sign fail',
                'data'=>[]  //返回给空数据
            ];
        }
    }

}
