<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\AuctionModel;
use App\Model\AuctionGoodsModel;
class AuctionController extends Controller
{
    /**
     *  商品添加
     */
    public function add(){
        $data=request()->all();
        // dd($data);
        $data['start_time']=strtotime($data['start_time']);
        $data['end_time']=strtotime($data['end_time']);
        $res=AuctionModel::create($data);
        return redirect('auction/index');
    }

    /**
     *  商品展示
     */
    public function index(){
        $auctionInfo=AuctionModel::get()->toArray();
        return view('auction/index',['auctionInfo'=>$auctionInfo]);
    
    }

    /**
     *  拍卖商品
     */
    public function goods($auction_id){
        $auctionInfo=AuctionModel::where('auction_id',$auction_id)->first();
        // dd($auctionInfo);
        $userData=Session('userData');
        // dd($userData);
        $user_id=$userData['user_id'];
        // $AuctionGoodsData=AuctionGoodsModel::create([
        //     "user_id"=>$user_id,
        //     "auction_id"=>$auction_id,
        //     "goods_add_price"=>$auctionInfo['starting_price'],
        // ]);
        $where=[
            "auction_id"=>$auction_id
        ];
        $priceInfo=AuctionGoodsModel::orderBy("goods_add_price","desc")->where($where)->first();
        
        // dd($priceInfo);

        $time=time();
        $q=date('Y-m-d H:i:s',$time);
        // dd($q);
        $status='';
        if($time>$auctionInfo['end_time']){
            $status='拍卖结束';
        }
        if($time<$auctionInfo['start_time']){
            $status='未开始拍卖';
        }
        if($time>$auctionInfo['start_time']&&$time<$auctionInfo['end_time']){
            $status="正在进行";
        }
        
        return view('auction/goods',['auctionInfo'=>$auctionInfo,'status'=>$status,"priceInfo"=>$priceInfo]);
    }

    /**
     *  竞拍、加价
     */
    public function addPrice(){
        $userData=Session('userData');
        $user_id=$userData['user_id'];
        
        
        $starting_price=request()->get('starting_price');
        $goods_add_price= request()->get('goods_add_price');
        $auction_id= request()->get('auction_id');
      
        $new_add_price=$starting_price+$goods_add_price;
        // dd($new_add_price);
        $where=[
            "user_id"=>$user_id,
            "auction_id"=>$auction_id
        ];
        // dd($where);
       AuctionGoodsModel::where($where)->update(["goods_add_price"=>$new_add_price]);
       $priceInfo=AuctionGoodsModel::orderBy("goods_add_price","desc")->where($where)->first();
       
    //    dd($priceInfo['goods_add_price']);
    //    dd($priceInfo['goods_add_price']);
       $newprice=$priceInfo['goods_add_price'];
    //    dd($newprice);
       return $newprice;
    }

    public function list(){
        $auctGoodsInfo=AuctionGoodsModel::join("auction_user","auction_goods.user_id","=","auction_user.user_id")
        ->join("auction","auction.auction_id","=","auction_goods.auction_id")->get()->toArray();
        // dd($auctGoodsInfo);
        return view('auction/list',["auctGoodsInfo"=>$auctGoodsInfo]);
    }
}
