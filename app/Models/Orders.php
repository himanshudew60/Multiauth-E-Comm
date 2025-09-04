<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\UserProfile;
use App\Models\User;
class Orders extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price'
    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function profile()
    {
        return $this->belongsTo(UserProfile::class, 'user_id');
    }
}
