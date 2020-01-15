<?php

namespace App\Http\Controllers\Index;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Common extends Controller
{
    public  $key='1904';
    public  $iv='1904a1904a1904aa';
   
    //加密
    protected function AesEncrypt($data)
    {
        //加密参数，数据期望是字符串  判断是否是数组，如果是，转成字符串
        if(is_array($data)){
            $data=json_encode($data);
        }
        // dd($data); 
        $encrypt= openssl_encrypt($data, //要加密的字符串  //openssl_encrypt()加密函数，PHP7基层带的
            'aes-128-cbc',   //密码学方式  加密模式
            $this->key,      
            1,               
            $this->iv);   //16位秘钥
        //return openssl_encrypt($data,'aes-128-cbc',$this->key,0,$this->iv);
        return \base64_encode($encrypt);
    }
    //解密
    protected function AesDecrypt($encrypt)
    {
        $decrypt=openssl_decrypt(
            base64_decode($encrypt),
            'aes-128-cbc',$this->key,
            1,
            $this->iv);
        // return openssl_decrypt($data,'aes-128-cbc',$this->key,0,$this->iv);
        return json_decode($decrypt,true);
    }

    public function getAppIdAndKey(){
        return [
            'app_id'=>'1904appid',
            'app_key'=>'1904password'
        ];
    }

    /**
     *  curlPost方式
     */
    protected function curlPost($url,array $data,$is_post=1)
    {
       //初始化: curl_init
       $ch=curl_init();
       $app_safe=$this->getAppIdAndKey();
       $data['app_id']=$app_safe['app_id'];
       //客户端添加时间戳、随机数,防止重放攻击
       $data['rand']=rand(100000,999999);
       $data['time']=time();


       //生成客户端签名
       $all_data=[
            'data'=>$this->AesEncrypt($data),  //给数据加密
            'sign'=>$this->_createSign($data,$app_safe['app_key'])//生成客户端签名
       ];
       
       //设置: 判断是否是Post提交
       if($is_post){
            curl_setopt( $ch, CURLOPT_POST, 1 );//提交post方式
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $all_data );//post提交数据
       }else{
            $url =$url.'?'.http_build_query($data);
       }
       //设置: curl_setopt
       curl_setopt($ch,CURLOPT_URL,$url);
       curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);//返回数据格式
       //RETURN 返回   TRANSFER格式   1是以数据的方式返回  不设置1，就会将数据直接抛给浏览器输出
       //执行  curl_exec
       $result=curl_exec($ch);
       //关闭释放  curl_close
       curl_close($ch);
       return $result;
    }
    /**
     * 生成签名
     */
    private function _createSign($data,$app_key)
    {
        // ksort  字典排序
        ksort($data);
        //将数组转字符串
        $json_str=\json_encode($data);
        return md5($json_str.'app_key='.$app_key);
       
    }
    

}
