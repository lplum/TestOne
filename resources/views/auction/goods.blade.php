<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="/admin/js/jquery-3.2.1.min.js"></script>
    <title>Document</title>
</head>
<body>
   
        <div style="width:900px ;background:">
            <div style="float:left;  border-right:solid">
                商品名字: {{$auctionInfo->goods_name}}  <br>
                保证金:    {{$auctionInfo->bond}}  <br>
                底价:      {{$auctionInfo->starting_price}}  <br>   
                <!-- 底价:      {{$priceInfo['goods_add_price']}}  <br>    -->
                每次加价:  {{$auctionInfo->add_price}}  <br>
                开始时间 : {{date("Y-m-d H:i:s",$auctionInfo->start_time)}} <br>
                结束时间 : {{date("Y-m-d H:i:s",$auctionInfo->end_time)}}   <br>

                说明：结束后价高者得<br>
                当前状态:{{$status}}
            </div>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <div style="width:400px ;background:; float:left;" >
                <input type="hidden" name="auction_id" value="{{$auctionInfo->auction_id}}">
                我要加价  <br>
                当前价格：<span id="newprice" style="color:red;" starting_price="{{$priceInfo['goods_add_price']}}">{{$priceInfo['goods_add_price']}}</span>  <br>
                价格: <input type="text" name="goods_add_price"> <br>
                @if($status=='拍卖结束')
                    拍卖已结束
                @elseif($status=='未开始拍卖')
                    未开始
                @else
                    <a href="javascript:;" id="but">出价</a>
                @endif
            </div>
        </div>
    
</body>
</html>
<script>
$(function(){
    $(document).on('click','#but',function(){
        var starting_price=$("span").attr('starting_price');
        var goods_add_price=$("[name='goods_add_price']").val();
        var auction_id=$("[name='auction_id']").val();
        // alert(auction_id);
        $.ajax({
            url:"{{url('auction/addprice')}}",
            data:{starting_price:starting_price,goods_add_price:goods_add_price,auction_id:auction_id},
            dataType:"json",
            success:function(res){
               
                $("#newprice").text(res);
            }
        })
    })
})
</script>