<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pageTitle = 'Coupons';
        $coupons = Coupon::latest()->paginate(10); // paginated
        return view('admin.coupons.index', compact('pageTitle', 'coupons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pageTitle = 'Create Coupon';
        return view('admin.coupons.create', compact('pageTitle'));
    }   

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'start_date' => 'required|date|before_or_equal:expiry_date',
            'expiry_date' => 'required|date|after_or_equal:start_date',
        ]);

        Coupon::create($validated);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $coupon = Coupon::findOrFail($id);
        $pageTitle = 'Edit Coupon';

        return view('admin.coupons.edit', compact('coupon', 'pageTitle'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code,' . $coupon->id,
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'start_date' => 'required|date|before_or_equal:expiry_date',
            'expiry_date' => 'required|date|after_or_equal:start_date',
        ]);

        $coupon->update($validated);

 return response()->json([
        'success' => true,
        'message' => 'Coupon updated successfully!',
    ]);    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon deleted successfully!');
    }
}
