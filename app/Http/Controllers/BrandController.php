<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrandController extends Controller
{

    public function brand()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $all_brand = Brand::where('admin_or_user_id', '=', $userId)->get();
            return view('admin_panel.brand.brand', [
                'all_brand' => $all_brand
            ]);
        } else {
            return redirect()->back();
        }
    }
    public function store_brand(Request $request)
    {
        if (Auth::id()) {
            $usertype = Auth()->user()->usertype;
            $userId = Auth::id();
            Brand::create([
                'admin_or_user_id'    => $userId,
                'brand'             => $request->brand,
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
            ]);
            return redirect()->back()->with('brand-added', 'brand Added Successfully');
        } else {
            return redirect()->back();
        }
    }
}
