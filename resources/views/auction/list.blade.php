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
            <td>价格</td>
            <td>竞拍人</td>
            <td>时间</td>
        </tr>
        @foreach($auctGoodsInfo as $v)
        <tr>
            <td>{{$v['goods_name']}}</td>
            <td>{{$v['goods_add_price']}}</td>
            <td>{{$v['user_name']}}</td>
            <td></td>
        </tr>
        @endforeach
    </table>
</body>
</html>