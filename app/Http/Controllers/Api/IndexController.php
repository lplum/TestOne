<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function login(){
        $data= request()->input();
        dd($data);
        echo "ok";
    }

    
}
