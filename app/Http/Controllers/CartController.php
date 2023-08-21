<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        $cartItems = CartItem::with('product')->where('user_id', Auth::user()->id)->get();

        return view('cart.index')->with('cartItems', $cartItems);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $added_cart = CartItem::with('product')->where('product_id', $request->id)
            ->where('user_id', Auth::user()->id)
            ->first();

        if ($request->quantity > 1) {
            $added = $request->quantity;
        } else {
            $added = 1;
        }

        if ($added_cart) {
            if ($added_cart->quantity + $added > $added_cart->product->number_in_stock) {
                return back()->with('fail', trans('cart.add.fail'));
            }

            $added_cart->quantity = $added_cart->quantity + $added;

            $added_cart->save();
        } else {
            $cart = new CartItem;

            $cart->product_id = $request->id;
            $cart->user_id = Auth::user()->id;
            $cart->quantity = $added;

            $cart->save();
        }

        return back()->with('success', trans('cart.add.success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CartItem $cart)
    {
        $cart->quantity = $request->quantity;

        $cart->save();

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function destroy(CartItem $cart)
    {
        $cart->delete();

        return back()->with('success', trans('cart.delete.success'));
    }
}
