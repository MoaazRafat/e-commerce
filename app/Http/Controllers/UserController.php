<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        return view('user.index');
    }

    public function orders()
    {
        $orders = Order::where('user_id', Auth::user()->id)->paginate(10);
        return view('user.orders', compact('orders'));
    }

    public function order_details($id)
    {
        $order = Order::where('user_id', Auth::user()->id)->where('id', $id)->first();
        if ($order)
        {
            $orderItems = OrderItem::where('order_id', $id)->orderBy('id')->paginate(12);
            return view('user.order-details', compact('order', 'orderItems'));
        }
        else
        {
            return redirect()->route('login');
        }
    }

    public function cancel_order(Request $request)
    {
        $order = Order::find($request->id);
        $order->status = 'canceled';
        $order->canceled_date = Carbon::now();
        $order->save();
        return back()->with('status', 'Order Has Been Canceled Successfully!');
    }
}
