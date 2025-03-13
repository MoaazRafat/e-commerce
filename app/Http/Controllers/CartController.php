<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Stripe\Charge;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Token;
use Surfsidemedia\Shoppingcart\Facades\Cart;

class CartController extends Controller
{
    public function index()
    {
        $items = Cart::instance('cart')->content();
        return view('cart', compact('items'));
    }

    public function add_to_cart(Request $request)
    {
        Cart::instance('cart')->add($request->id, $request->name, $request->quantity, $request->price)->associate('App\Models\Product');
        return redirect()->back();
    }

    public function increase_cart_quantity($rowId)
    {
        $product = Cart::instance('cart')->get($rowId);
        $qty = $product->qty + 1;
        Cart::instance('cart')->update($rowId, $qty);
        return redirect()->back();
    }

    public function decrease_cart_quantity($rowId)
    {
        $product = Cart::instance('cart')->get($rowId);
        $qty = $product->qty - 1;
        Cart::instance('cart')->update($rowId, $qty);
        return redirect()->back();
    }

    public function remove_item($rowId)
    {
        Cart::instance('cart')->remove($rowId);
        return redirect()->back();
    }

    public function empty_cart()
    {
        Cart::instance('cart')->destroy();
        return redirect()->back();
    }

    public function apply_coupon_code(Request $request)
    {
        $coupon_code = $request->coupon_code;
        if (isset($coupon_code))
        {
            $coupon = Coupon::where('code', $coupon_code)
                ->where('expiry_date', '>=', Carbon::today())
                ->where('cart_value', '<=', Cart::instance('cart')->subtotal())
                ->first();
            if (!$coupon)
            {
                return redirect()->back()->with('error', 'Coupon code is not valid!');
            }
            else
            {
                Session::put('coupon', [
                    'code' => $coupon->code,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                    'cart_value' => $coupon->cart_value,
                ]);
                $this->calculateDiscount();
                return redirect()->back()->with('success', 'Coupon has been applied!');
            }
        }
        else
        {
            return redirect()->back()->with('error', 'Coupon code is required!');
        }
    }

    public function calculateDiscount()
    {
        $discount = 0;
        if (Session::has('coupon'))
        {
            if (Session::get('coupon')['type'] == 'fixed')
            {
                $discount = Session::get('coupon')['value'];
            }
            else
            {
                $discount = (Cart::instance('cart')->subtotal() * Session::get('coupon')['value']) / 100;
            }
            $subtotalAfterDiscount = Cart::instance('cart')->subtotal() - $discount;
            $taxAfterDiscount = (config('cart.tax') * $subtotalAfterDiscount) / 100;
            $totalAfterDiscount = $subtotalAfterDiscount + $taxAfterDiscount;
            Session::put('discounts', [
                'discount' => number_format(floatval($discount), 2, '.', ''),
                'subtotal' => $subtotalAfterDiscount,
                'tax' => $taxAfterDiscount,
                'total' => $totalAfterDiscount
            ]);
        }
        else
        {
            return redirect()->back()->with('error', 'Coupon code is required!');
        }
    }

    public function remove_coupon_code()
    {
        Session::forget('coupon');
        Session::forget('discounts');
        return back()->with('success', 'Coupon has been removed!');
    }

    public function checkout()
    {
        if (!Auth::check())
        {
            return redirect()->route('login');
        }
        $address = Address::where('user_id', Auth::user()->id)->where('isDefault', 1)->first();
        return view('checkout', compact('address'));
    }

    public function place_order(Request $request)
    {
        $user_id = Auth::user()->id;
        $address = Address::where('user_id', $user_id)->where('isDefault', true)->first();
        if (!$address)
        {
            $request->validate([
                'name' => 'required',
                'phone' => 'required|digits:11',
                'zip' => 'required|digits:6',
                'state' => 'required',
                'city' => 'required',
                'address' => 'required',
                'locality' => 'required',
                'landmark' => 'required',
            ]);
            $address = new Address();
            $address->name = $request->name;
            $address->phone = $request->phone;
            $address->zip = $request->zip;
            $address->state = $request->state;
            $address->city = $request->city;
            $address->address = $request->address;
            $address->locality = $request->locality;
            $address->landmark = $request->landmark;
            $address->country = 'Egypt';
            $address->user_id = $user_id;
            $address->isDefault = true;
            $address->save();
        }
        $this->setAmountforCheckout();
        $order = new Order();
        $order->user_id = $user_id;
        $order->subtotal = Session::get('checkout')['subtotal'];
        $order->discount = Session::get('checkout')['discount'];
        $order->tax = Session::get('checkout')['tax'];
        $order->total = Session::get('checkout')['total'];
        $order->name = $address->name;
        $order->phone = $address->phone;
        $order->locality = $address->locality;
        $order->address = $address->address;
        $order->city = $address->city;
        $order->state = $address->state;
        $order->country = $address->country;
        $order->landmark = $address->landmark;
        $order->zip = $address->zip;
        $order->save();

        foreach (Cart::instance('cart')->content() as $item)
        {
            $orderItem = new OrderItem();
            $orderItem->product_id = $item->id;
            $orderItem->order_id = $order->id;
            $orderItem->price = $item->price;
            $orderItem->quantity = $item->qty;
            $orderItem->save();
        }

        if ($request->mode == 'card')
        {

            $request->validate(
                [
                    'cardname' => 'required',
                    'cardnumber' => 'required|numeric',
                    'expmonth' => 'required|numeric',
                    'expyear' => 'required|numeric',
                    'cvc' => 'required|numeric',
                ]
            );


            Stripe::setApiKey(env('STRIPE_SECRET'));
            try
            {
                $token = Token::create([
                    'card' => [
                        'number' => $request->cardnumber,
                        'exp_month' => $request->expmonth,
                        'exp_year' => $request->expyear,
                        'cvc' => $request->cvc,
                    ],
                ]);
                if (!isset($token['id']))
                {
                    session()->flash('stripe_error', 'The stripe token is not generated correctly');
                }
                // $customer = $stripe->customers()->create([
                //     'name' => $request->name,
                //     'email' => $request->email,
                //     'phone' => $request->phone,
                //     'zip' => $request->zip,
                //     'state' => $request->state,
                //     'city' => $request->city,
                //     'address' => $request->address,
                //     'source' => $token->id,
                //     'shipping' => [
                //         'name' => $request->name,
                //         'zip' => $request->zip,
                //         'state' => $request->state,
                //         'city' => $request->city,
                //         'address' => $request->address,
                //         'source' => $token->id,
                //     ]
                // ]);
                $charge = Charge::create([
                    // 'customer' => $customer->id,
                    'amount' => session()->get('checkout')['total'], // Convert to cents
                    'currency' => 'usd',
                    'source' => $request->stripeToken, // Token from the Stripe frontend
                    'description' => 'Payment for Order ' . $request->order_id,
                ]);
                if ($charge['status'] == 'succeeded')
                {
                    $this->makeTransaction($order->id, 'approved', 'card');
                    Cart::instance('cart')->destroy();
                    Session::forget('coupon');
                    Session::forget('discounts');
                    Session::forget('checkout');
                    Session::put('order_id', $order->id);
                    return redirect()->route('cart.order.confirmation');
                }
                else
                {
                    session()->flash('stripe_error', 'Error in transaction!');
                }
            }
            catch (Exception $e)
            {
                session()->flash('stripe_error', $e->getMessage());
            }


            // Stripe::setApiKey(env('STRIPE_SECRET'))
            // try
            // {
            //     // Create a PaymentIntent with the order amount and currency
            //     $paymentIntent = PaymentIntent::create([
            //         'amount' => 5000, // Amount in cents (5000 = $50.00)
            //         'currency' => 'usd',
            //         'payment_method_types' => ['card'],
            //     ]);

            //     return response()->json([
            //         'clientSecret' => $paymentIntent->client_secret
            //     ]);
            // }
            // catch (ApiErrorException $e)
            // {
            //     return response()->json(['error' => $e->getMessage()]);
            // }
        }
        elseif ($request->mode == 'paypal')
        {
            //
        }
        elseif ($request->mode == 'cod')
        {
            $this->makeTransaction($order->id, 'pending', 'cod');
            Cart::instance('cart')->destroy();
            Session::forget('coupon');
            Session::forget('discounts');
            Session::forget('checkout');
            Session::put('order_id', $order->id);
            return redirect()->route('cart.order.confirmation');
        }
    }

    public function makeTransaction($order_id, $status, $mode)
    {
        $transaction = new Transaction();
        $transaction->user_id = Auth::user()->id;
        $transaction->order_id = $order_id;
        $transaction->mode = $mode;
        $transaction->status = $status;
        $transaction->save();
    }

    public function setAmountforCheckout()
    {
        if (!Cart::instance('cart')->content()->count() > 0)
        {
            Session::forget('checkout');
            return;
        }
        if (Session::has('coupon'))
        {
            Session::put('checkout', [
                'discount' => Session::get('discounts')['discount'],
                'subtotal' => Session::get('discounts')['subtotal'],
                'tax' => Session::get('discounts')['tax'],
                'total' => Session::get('discounts')['total']
            ]);
        }
        else
        {
            Session::put('checkout', [
                'discount' => 0,
                'subtotal' => Cart::instance('cart')->subtotal(),
                'tax' => Cart::instance('cart')->tax(),
                'total' => Cart::instance('cart')->total()
            ]);
        }
    }

    public function order_confirmation()

    {
        if (Session::has('order_id'))
        {
            $order = Order::find(Session::get('order_id'));
            return view('order-confirmation', compact('order'));
        }
        return redirect()->route('cart.index');
    }
}
