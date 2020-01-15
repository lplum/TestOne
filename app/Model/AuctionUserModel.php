<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AuctionUserModel extends Model
{
    protected $primaryKey = 'user_id';
    protected $table = 'auction_user';
    protected $guarded = [];
    public $timestamps = false;
}
