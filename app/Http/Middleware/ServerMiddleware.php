<?php

namespace App\Http\Middleware;

use Closure;

class ServerMiddleware
{
    public  $key='1904';
    public  $iv='1904a1904a1904aa';
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $data=$request->input('data');//接受加密数据
        // dd($data); 
        $decrypt=$this->AesDecrypt($data);//进行解密
        dd($decrypt);
        return $next($request);
    }
    
    //解密
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
