<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Customer;
use App\Models\Coupon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $customerCount = Customer::count();
    $productCount = Product::count();
    $categoryCount = Category::count();
    $tagCount = Tag::count();
    $couponCount = Coupon::count();
    
    $latestCustomers = Customer::latest()->take(5)->get();
    $latestProducts = Product::latest()->take(5)->get();
    $latestCategories = Category::latest()->take(5)->get();
    $latestTags = Tag::latest()->take(5)->get();

    
        $maxPrice =Product::max('price');
        $minPrice =Product::min('price');
        $avgPrice =Product::avg('price');
  

    $productsPerCategory = Product::selectRaw('count(*) as total, category_id')
        ->groupBy('category_id')
        ->with('category')
        ->get();

    $genderStats = Customer::selectRaw('gender, count(*) as total')
        ->groupBy('gender')
        ->get();

    
    return view('admin.dashboard', compact(
        'customerCount', 'productCount', 'categoryCount', 'tagCount','couponCount',
        'latestCustomers', 'latestProducts', 'latestCategories', 'latestTags',
         'productsPerCategory', 'genderStats','maxPrice','minPrice','avgPrice'
    ));
}
    
}
