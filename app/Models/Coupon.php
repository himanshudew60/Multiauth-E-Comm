<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'start_date',
        'expiry_date',
    ];
   public function isValid()
{
    $now = Carbon::now();
    return !$this->expires_at || $now->lessThanOrEqualTo($this->expires_at);
}

}
