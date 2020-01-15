<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <table border="1">
        <tr>
            <td>商品名称：</td>
            <td>保证金：</td>
            <td>开始时间</td>
            <td>结束时间</td>
            <td>操作</td>
        </tr>
        @foreach($auctionInfo as $v)
        <tr>
            <td>{{$v['goods_name']}}</td>
            <td>{{$v['bond']}}</td>
            <td>{{date("Y-m-d H:i:s",$v['start_time'])}}</td>
            <td>{{date("Y-m-d H:i:s",$v['end_time'])}}</td>
            <td><a href="{{url('auction/goods',$v['auction_id'])}}">拍卖</a></td>
        </tr>
        @endforeach
    </table>
</body>
</html>