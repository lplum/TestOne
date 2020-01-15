<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <form action="{{url('auction/add')}}" method="post">
        <table border="1">
            <tr>
                <td>商品名字</td>
                <td><input type="text" name="goods_name"></td>
            </tr>
            <tr>
                <td>保证金</td>
                <td><input type="text" name="bond"></td>
            </tr>
            <tr>
                <td>底价</td>
                <td><input type="text" name="starting_price"></td>
            </tr>
            <tr>
                <td>每次加价</td>
                <td><input type="text" name="add_price"></td>
            </tr>
            <tr>
                <td>竞拍时间</td>
                <td>
                    <input type="text" name="start_time">---<input type="text" name="end_time">  
                </td>
            </tr>
            <tr>
                <td></td>
                <td><input type="submit" value="添加"></td>
            </tr>
        </table>
    </form>
</body>
</html>