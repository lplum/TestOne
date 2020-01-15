<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AuctionGoodsModel extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'auction_goods';
    protected $guarded = [];
    public $timestamps = false;
}
