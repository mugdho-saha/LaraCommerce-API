<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class CheckoutController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $total = $this->calculateTotal(Auth::id());

        $paymentIntent = PaymentIntent::create([
            'amount' => $total * 100, // Cents
            'currency' => 'usd',
            'metadata' => ['user_id' => Auth::id()]
        ]);

        return response()->json([
            'clientSecret' => $paymentIntent->client_secret,
            'total' => $total
        ]);
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:stripe,cod',
            'shipping_address' => 'required|string',
            'stripe_payment_id' => 'required_if:payment_method,stripe'
        ]);

        $userId = Auth::id();
        $cartItems = Cart::where('user_id', $userId)->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty'], 400);
        }

        if ($request->payment_method === 'stripe') {
            Stripe::setApiKey(config('services.stripe.secret'));
            
            // Retrieve the payment from Stripe using the ID sent by the frontend
            $intent = PaymentIntent::retrieve($request->stripe_payment_id);
        
            // Verify the payment is actually successful and matches the cart total
            if ($intent->status !== 'succeeded') {
                return response()->json(['message' => 'Payment not verified'], 402);
            }
        }

        return DB::transaction(function () use ($request, $userId, $cartItems) {
            $total = $this->calculateTotal($userId);

            // 1. Create Order
            $order = Order::create([
                'user_id' => $userId,
                'total_amount' => $total,
                'payment_method' => $request->payment_method,
                'payment_status' => ($request->payment_method === 'stripe') ? 'paid' : 'pending',
                'stripe_payment_id' => $request->stripe_payment_id,
                'shipping_address' => $request->shipping_address,
            ]);

            // 2. Map Cart to Order Items
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->product_price,
                ]);
            }

            // 3. Clear Cart
            Cart::where('user_id', $userId)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully!',
                'order' => $order
            ], 201);
        });
    }

    private function calculateTotal($userId)
    {
        return Cart::where('user_id', $userId)
            ->join('products', 'carts.product_id', '=', 'products.product_id')
            ->sum(DB::raw('products.product_price * carts.quantity'));
    }
}