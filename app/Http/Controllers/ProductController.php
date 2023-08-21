<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\SearchProductRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        $products = DB::table('products')->where('salesman_id', '<>', Auth::user()->id)
            ->orderBy('id', 'desc')->paginate(config('app.pagination.per_page'));

        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function create()
    {
        return view('products.create');
    }

    public function search(Request $request)
    {
        $searchTerm = $request->search;
        $categoryId = $request->category ?? 0;

        $query = Product::with('categories')
            ->where('name', 'LIKE', '%' . $searchTerm . '%')
            ->where('salesman_id', '<>', Auth::user()->id);

        if ($categoryId > 0) {
            $query->whereHas('categories', function ($query) use ($categoryId) {
                $query->where('categories.id', $categoryId);
            });
        }

        $products = $query->orderBy('id', 'desc')
            ->paginate(config('app.pagination.per_page'));

        return view('products.index', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function store(StoreProductRequest $request)
    {
        $product = new Product;
        $validated = $request->validated();

        $product->name = $validated['name'];
        $product->description = $validated['description'];
        $product->price = $validated['price'];
        $product->number_in_stock = $validated['number_in_stock'];
        $product->salesman_id = Auth::user()->id;
        $product->number_of_purchase = 0;
        $product->number_of_rate = 0;
        $product->rate = 0;

        if ($request->hasFile('photo')) {
            $image = $request -> file('photo');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $imagePath = env('IMAGE_PATH');
            $product->photo = $imagePath . $imageName;
        }

        $product->save();

        $products = DB::table('products')->where('salesman_id', $product->salesman_id)
            ->orderBy('id', 'desc')
            ->paginate(config('app.pagination.per_page'));

        return view('users.products')->with('products', $products);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        $count = DB::table('product_reviews')->where('product_id', $product->id)->count();
        $product->review = $count;
        $reviews = ProductReview::with('user')->where('product_id', $product->id)->get();
        $sameUserProducts = DB::table('products')
            ->where('salesman_id', $product->salesman_id)
            ->where('id', '<>', $product->id)
            ->get();

        return view('products.show', compact('product', 'reviews', 'sameUserProducts'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return Application|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Routing\Redirector
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $validated = $request->validated();
        $product->name = $validated['name'];
        $product->description = $validated['description'];
        $product->price = $validated['price'];
        $product->number_in_stock = $validated['number_in_stock'];

        if ($request->hasFile('photo')) {
            $image = $request -> file('photo');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);

            if (!empty($product->photo)) {
                if (strpos($product->photo, 'https://via.placeholder.com/') != 0) {
                    unlink(public_path($product->photo));
                }
            }

            $imagePath = env('IMAGE_PATH');
            $product->photo = $imagePath . $imageName;
        }

        $product->save();
        $products = DB::table('products')->where('salesman_id', $product->salesman_id)
            ->orderBy('id', 'desc')
            ->paginate(config('app.pagination.per_page'));

        return view('users.products')->with('products', $products);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        DB::transaction(function () use ($product) {
            $product->load('cartItems');
            $product->cartItems()->delete();
            $product->delete();
        });

        $products = DB::table('products')->where('salesman_id', $product->salesman_id)
            ->orderBy('id', 'desc')
            ->paginate(config('app.pagination.per_page'));

        return view('users.products')->with('products', $products);
    }
}
