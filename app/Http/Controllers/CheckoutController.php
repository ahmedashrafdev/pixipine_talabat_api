<?php

namespace App\Http\Controllers;

use App\Order;
use App\Address;
use App\Cart;
use App\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $id = $request->user()->id;
        $cart = Cart::where('user_id', $id)->get();
        if (count($cart) == 0) {
            return response()->json(['success' => false ,'message' => 'no_items_on_your_cart']);
        }
        $shipping = Address::find($request->address_id)->state->shipping_fees;

        $subtotal = DB::select('call getTotal(?)' , [$id]);
        $subtotal = $subtotal[0]->total;
        $total = $shipping + $subtotal;
        DB::insert('call setOrder(?, ? , ? , ? ,?) ', 
        [
            $id,
            1,
            $request->address_id,   
            $total, 
            $request->payment
            ]
        );
        
        $order = Order::where('user_id' , $id)->OrderBy('id' ,'DESC')->first();
        foreach($cart as $item){
            $orderDetail = OrderDetail::create(['item_id' => $item->item_id , 'order_id' => $order->id , 'qty' => $item->qty]);
            $optionsInstance = DB::table('option_relations')->where('relation_type' , 'cart')->where('relation_id', $item->id);
            $optionsInstance->count() > 0 ? $optionsInstance->update(['relation_type' => 'order' , 'relation_id' => $orderDetail->id]) : "";
           
        }
        $this->destroyCart($id);
        return response()->json(['success' => true , 'message' => 'order_placed_successfully']);

    }

    protected function destroyCart($id)
    {        
      return DB::delete('CALL destroyCart(?)' , [$id]);
    }
}
