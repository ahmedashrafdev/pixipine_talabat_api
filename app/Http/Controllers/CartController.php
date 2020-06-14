<?php

namespace App\Http\Controllers;

use App\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function getCartItems(Request $request)
    {
        $id = $request->user()->id;
        $cart = DB::select('call getCart(?) ',
            [
                $id,
            ]);
        if(isset($cart[0]) && $cart[0]->name === null){
            return response()->json([]);
        }
        $total = DB::select('call getTotal(?)', [$id]);
        return response()->json(['items' => $cart, 'total' => $total[0]->total]);
    }
    public function SetCartItem(Request $request)
    {   
        // dd($request->all());
       
        $cart = DB::insert('call addToCart(?, ? , ?) ',
            [
                $request->item,
                $request->user()->id,
                isset($request->qty) ? $request->qty : 1,
            ]
        );
        $addedCart = Cart::where('user_id' , $request->user()->id)->OrderBy('id' ,'DESC')->first();
        $options = $request->options;
        if (isset($options)) {
            
            foreach($options as $option){
                
                // dd($option['option']);
                    DB::insert('call setCartOption(?, ? , ?)',[$addedCart->id,$option['option'] , $option['group_id']]);
                }
            
        }
        $response = ['success' => 'true', 'message' => 'added_to_cart_successfully'];
        $response = $cart == 0 ? ['success' => 'false', 'message' => 'somthing_wrong'] : $response;

        return response()->json($response);
    }
    public function DeleteCartItem($id, Request $request)
    {
        
        $cart = DB::delete('call deleteFromCart(? , ?) ',
            [
                $id,
                $request->user()->id,
            ]
        );
        $response = ['success' => 'true', 'message' => 'deleted_from_cart_successfully'];
        $response = $cart == 0 ? ['success' => 'false', 'message' => 'somthing_wrong'] : $response;
        return response()->json($response);
    }
    public function DecreaseCartItem($id, Request $request)
    {
        $cart = DB::delete('call decreaseCart(? , ?) ',
            [
                $id,
                $request->user()->id
            ]
        );
        $response = ['success' => 'true', 'message' => 'quantity_updated_successfully'];
        $response = $cart == 0 ? ['success' => 'false', 'message' => 'somthing_wrong'] : $response;

        return response()->json($response);
    }
}
