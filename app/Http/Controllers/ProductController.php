<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Product;
use Illuminate\Http\Request;
use File;

class ProductController extends Controller
{
    protected $user;
    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = $this->user->products()->get(['id', 'title', 'description', 'price', 'image', 'created_by'])->toArray();
        return $products;
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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // validate
        $this->validate($request, [
            'title' => 'required',
            'description' => 'required',
            'price' => 'required',
            'image' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // save product into database
        $product = new Product();
        $product->title = $request->title;
        $product->description = $request->description;
        $product->price = $request->price;
        
        // image store
        $image = $request->file('image');

        $imagename = $image->getClientOriginalName();
        $ext = $image->getClientOriginalExtension();

        $image_title = time().$imagename;
        $image->move('images/products/', $image_title);
        $product->image = "images/products/".$image_title;

        if ($this->user->products()->save($product)) {
            return response()->json([
                'status' => true,
                'product' => $product,
                'message' => 'Product created successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Product could not be saved'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        // update product into database
        $product->title = $request->title;
        $product->description = $request->description;
        $product->price = $request->price;
        
        // image update
        if ($request->file('image')) {
            if (File::exists($product->image)) {
                File::delete($product->image);
            }

            $image = $request->file('image');

            $imagename = $image->getClientOriginalName();
            $ext = $image->getClientOriginalExtension();

            $image_title = time().$imagename.'.'.$ext;
            $image->move('images/products/', $image_title);
            $product->image = "images/products/".$image_title;
        }
        
        if ($this->user->products()->save($product)) {
            return response()->json([
                'status' => true,
                'product' => $product,
                'message' => 'Product updated successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Product could not be updated'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        if (File::exists($product->image)) {
            File::delete($product->image);
        }
        
        if ($product->delete()) {
            return response()->json([
                'status' => true,
                'product' => $product,
                'message' => 'Product deleted successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Product could not be deleted'
            ]);
        }
    }
}
