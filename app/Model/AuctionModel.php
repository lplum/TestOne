<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AuctionModel extends Model
{
    protected $primaryKey = 'auction_id';
    protected $table = 'auction';
    protected $guarded = [];
    public $timestamps = false;
}
