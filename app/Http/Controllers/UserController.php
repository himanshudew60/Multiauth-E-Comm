<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Orders;
use App\Models\User;
use App\Models\Coupon;
use App\Models\UserProfile;
use App\Models\Product;
use App\Models\ProductQuantity;
use App\Models\Category;
use App\Models\Tag;
use View;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::all();
        $tags = Tag::all();
        $quantity = ProductQuantity::all();


        $query = Product::with(['quantity'])->where('status', 0);

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by tag
        if ($request->filled('tag_id')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('tags.id', $request->tag_id);
            });
        }

        $products = $query->get();

        return view('user.dashboard', compact('products', 'quantity', 'tags', 'categories'));
    }


    public function addToCart(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $cart = session()->get('cart', []);
        if (isset($cart[$id])) {
            $cart[$id]['quantity']++;
        } else {
            $cart[$id] = [
                'id' => $product->id,
                "name" => $product->name,
                "price" => $product->price,
                "photo" => json_decode($product->photo, true)[0] ?? null,
                "quantity" => 1
            ];
        }
        session()->put('cart', $cart);
        return redirect()->back()->with('success', 'Product added to cart!');
    }

    public function showCart()
    {
        $cart = session()->get('cart', []);
        $cartProductIds = array_keys($cart);
        // Get category IDs of products in the cart
        $categoryIds = Product::whereIn('id', $cartProductIds)
            ->pluck('category_id')
            ->unique()
            ->toArray();
        // Get related products from those categories excluding cart items
        $relatedProducts = Product::with('quantity')
            ->whereIn('category_id', $categoryIds)
            ->whereNotIn('id', $cartProductIds)
            ->where('status', 0)
            ->get();
        return view('user.cart', compact('cart', 'relatedProducts'));
    }
    public function updateCartQuantity(Request $request, $id)
    {
        $cart = session()->get('cart', []);
        $action = $request->input('action');
        if (isset($cart[$id])) {
            if ($action === 'increase') {
                $cart[$id]['quantity']++;
            } elseif ($action === 'decrease' && $cart[$id]['quantity'] > 1) {
                $cart[$id]['quantity']--;
            }
            session()->put('cart', $cart);
        }
        return redirect()->route('user.cart')->with('success', 'Cart updated successfully.');
    }
    public function removeFromCart($id)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }
        return redirect()->route('user.cart')->with('success', 'Item removed from cart.');
    }
    public function info()
    {
        $userData = UserProfile::where('user_id', Auth::id())->first();
        return view('user.info', compact('userData'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|digits:10',
            'bio' => 'required|string|max:255',
            'address' => 'required|string',
            'gender' => 'required|in:Male,Female',
            'image' => 'nullable|image|max:2048',
        ]);
        $data = [
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'gender' => $validated['gender'],
            'bio' => $validated['bio'],
        ];
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('uploads', 'public');
        }
        UserProfile::updateOrCreate(
            ['user_id' => Auth::id()],
            $data
        );
        return redirect()->route('user.info')->with('success', 'Profile updated successfully.');
    }

public function validateProfile(Request $request)
{
   $rules = [
    'phone' => [
        'required',
        'regex:/^[6-9][0-9]{9}$/'
    ],
    'bio' => [
        'required',
        'string',
        'max:255',
        'regex:/^[a-zA-Z0-9\s.,\'-]+$/'
    ],
    'address' => [
        'required',
        'string',
        'regex:/^[a-zA-Z0-9\s,.-]+$/'
    ],
    'gender' => 'required|in:Male,Female',
    'image' => 'nullable|image|max:2048',
];


    $validator = \Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    return response()->json(['success' => true]);
}


    public function buyAllCartItems(Request $request)
    {
        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('user.cart')->with('error', 'Your cart is empty.');
        }
        foreach ($cart as $item) {
            $price = $item['discounted_price'] ??  $item['price'];
            
            // Create an order
            Orders::create([
                'user_id' => Auth::id(),
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'unit_price' => $price,
                'total_price' => $price * $item['quantity'],
            ]);
            // Decrease the product quantity
            $productQty = ProductQuantity::where('product_id', $item['id'])->first();
            if ($productQty) {
                $newQty = max(0, $productQty->qty - $item['quantity']); // prevent negative
                $productQty->update(['qty' => $newQty]);
            }
        }
        // Clear the cart
        session()->forget('cart');
        return redirect()->route('user.orders')->with('success', 'items purchased successfully!');
    }

    public function myOrders()
    {
        $orders = Orders::with('product')->where('user_id', Auth::id())->latest()->get();
        return view('user.orders', compact('orders'));
    }
    public function generateBillByDate($date)
    {
        $parsedDate = Carbon::createFromFormat('d M Y', $date);
        $orders = Orders::with('product', 'user')
            ->where('user_id', Auth::id())
            ->whereDate('created_at', $parsedDate)
            ->get();

        

        $profile = UserProfile::where('user_id', Auth::id())->first();
        return View('user.generateBillByDate', compact('orders', 'date', 'profile'));
    }

    public function applyCoupon(Request $request)
{
    $couponCode = $request->input('coupon_code');

    // Fetch coupon from DB
    $coupon = Coupon::where('code', $couponCode)->first();

    // Validate coupon existence
    if (!$coupon) {
        return redirect()->back()->with('error', 'Invalid coupon code.');
    }

    // Optional: If you're not using $coupon->isValid(), remove this check or implement it
    if (!$coupon->isValid()) {
        return redirect()->back()->with('error', 'Coupon has expired or is not active.');
    }

    $cart = session()->get('cart', []);
    $grandTotal = 0;

    // Update each item in cart with discounted_price
    foreach ($cart as $id => $item) {
        $originalPrice = $item['price'];

        if ($coupon->type === 'fixed') {
            $discountedPrice = max(0, $originalPrice - $coupon->value); // prevent negative price
        } elseif ($coupon->type == 'percent') {
            $discountedPrice = $originalPrice - ($originalPrice * ($coupon->value / 100));
        } else {
            $discountedPrice = $originalPrice; // fallback to original if unknown type
        }
        

        $total = $discountedPrice * $item['quantity'];
        $grandTotal += $total;

        // Update item
        $cart[$id]['discounted_price'] = $discountedPrice;
       
    }

    // Save updated cart and coupon to session
    session()->put('cart', $cart);
    session()->put('coupon', $couponCode);
    session()->put('grandTotal', $grandTotal);

    return redirect()->back()->with('success', 'Coupon applied successfully!');
}





}





