<?php

namespace App\Http\Controllers;

use App\Mail\DocumentMail;
use App\Models\Customer;
use App\Models\Order;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewOrderAdminMail;
use App\Models\ReferralCode;
use App\Models\ServiceSubmission;
use Illuminate\Support\Facades\URL;
use App\Models\Settings;
use App\Models\User;
use Dom\Document;

class PaymentController extends Controller
{
    //with referral code handling
    public function processPayment(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required',
            'service_id'        => 'required|exists:services,id',
            'submission_id'     => 'required|exists:service_submissions,id',
            'customer_name'     => 'required|string',
            'customer_email'    => 'required|email',
            'state'             => 'required|string',
            'referral_code'     => 'nullable|string|exists:referral_codes,code',
        ]);

        $service = Service::findOrFail($request->service_id);
        $submission = ServiceSubmission::findOrFail($request->submission_id);

        $originalPrice = $service->price;
        $discount = 0;
        $referralCodeId = null;
        $refCode = null;

        if ($request->filled('referral_code')) {
            $refCode = ReferralCode::where('code', strtoupper($request->referral_code))
                ->where('is_active', true)
                ->first();

            if ($refCode) {
                $referralCodeId = $refCode->id;

                if ($refCode->type === 'percentage') {
                    $discount = ($originalPrice * $refCode->amount) / 100;
                } elseif ($refCode->type === 'fixed') {
                    $discount = $refCode->amount;
                }
            }
        }

        $finalPrice = max(0, $originalPrice - $discount);
        $amountInCents = round($finalPrice * 100);

        Stripe::setApiKey(config('services.stripe.secret'));

        DB::beginTransaction();

        try {
            $intent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => 'usd',
                'payment_method' => $request->payment_method_id,
                'confirm' => true,
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never',
                ],
            ]);

            if (Order::where('stripe_transaction_id', $intent->id)->exists()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate transaction detected'
                ]);
            }

            $paymentMethod = PaymentMethod::retrieve($request->payment_method_id);

            $lastOrder = Order::latest()->first();
            $nextId = $lastOrder ? $lastOrder->id + 1 : 1;
            $orderNumber = 'ORD-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

            $order = Order::create([
                'order_number'          => $orderNumber,
                'service_id'            => $service->id,
                'service_name'          => $service->title,
                'customer_name'         => $request->customer_name,
                'customer_email'        => $request->customer_email,
                'state'                 => $request->state,
                'amount'                => $finalPrice,
                'status'                => 'Completed',
                'card_brand'            => ucfirst($paymentMethod->card->brand),
                'card_last4'            => $paymentMethod->card->last4,
                'document_status'       => 'Ready',
                'stripe_transaction_id' => $intent->id,
                'referral_code_id'      => $referralCodeId,
                'discount_amount'       => $discount,
            ]);

            $adminEmails = User::role('admin')
                ->where('new_order_e_notification', true)
                ->pluck('email');

            if ($adminEmails->isNotEmpty()) {
                Mail::to($adminEmails->all())
                    ->queue(new NewOrderAdminMail($order));
            }

            $customer = Customer::firstOrCreate(
                ['email' => $request->customer_email],
                [
                    'name' => $request->customer_name,
                    'state' => $request->state,
                    'join_date' => now(),
                    'total_orders' => 0,
                    'total_spent' => 0,
                    'last_activity' => now()
                ]
            );

            $customer->update([
                'total_orders'  => $customer->total_orders + 1,
                'total_spent'   => $customer->total_spent + $finalPrice,
                'last_activity' => now()
            ]);

            if ($refCode) {
                $refCode->increment('used_count');
            }

            DB::commit();

            if ($submission) {
                Mail::to($request->customer_email)
                    ->queue(new DocumentMail($order, $submission));
            }

            return response()->json([
                'success' => true,
                'message' => 'Order processed successfully!',
                'order_details' => $order
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function validateCode(Request $request)
    {
        $request->validate([
            'code'       => 'required|string',
            'service_id' => 'required|exists:services,id',
        ]);

        $refCode = ReferralCode::where('code', strtoupper($request->code))
            ->where('is_active', true)
            ->first();

        if (!$refCode) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive referral code.'
            ], 404);
        }

        $service = Service::findOrFail($request->service_id);
        $originalPrice = $service->price;
        $discountAmount = 0;

        if ($refCode->type === 'percentage') {
            $discountAmount = ($originalPrice * $refCode->amount) / 100;
        } elseif ($refCode->type === 'fixed') {
            $discountAmount = $refCode->amount;
        }

        $finalPrice = max(0, $originalPrice - $discountAmount);

        return response()->json([
            'success' => true,
            'message' => 'Referral code applied successfully!',
            'data' => [
                'code'            => $refCode->code,
                'title'           => $refCode->title,
                'discount_type'   => $refCode->type,
                'discount_value'  => $refCode->amount,
                'original_price'  => round($originalPrice, 2),
                'discount_amount' => round($discountAmount, 2),
                'final_price'     => round($finalPrice, 2),     
            ]
        ], 200);
    }



    //without referral code handling

    // public function processPayment(Request $request)
    // {
    //     $request->validate([
    //         'payment_method_id' => 'required',
    //         'service_id'        => 'required|exists:services,id',
    //         'submission_id'     => 'required|exists:service_submissions,id',
    //         'customer_name'     => 'required|string',
    //         'customer_email'    => 'required|email',
    //         'state'             => 'required|string',
    //     ]);

    //     $service = Service::findOrFail($request->service_id);
    //     $submission = ServiceSubmission::findOrFail($request->submission_id);
    //     $price = $service->price;
    //     $amountInCents = $price * 100;

    //     Stripe::setApiKey(config('services.stripe.secret'));

    //     DB::beginTransaction();

    //     try {
    //         $intent = PaymentIntent::create([
    //             'amount' => $amountInCents,
    //             'currency' => 'usd',
    //             'payment_method' => $request->payment_method_id,
    //             'confirm' => true,
    //             'automatic_payment_methods' => [
    //                 'enabled' => true,
    //                 'allow_redirects' => 'never',
    //             ],
    //         ]);

    //         if (Order::where('stripe_transaction_id', $intent->id)->exists()) {
    //             DB::rollBack();
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Duplicate transaction detected'
    //             ]);
    //         }

    //         $paymentMethod = PaymentMethod::retrieve($request->payment_method_id);

    //         $lastOrder = Order::latest()->first();
    //         $nextId = $lastOrder ? $lastOrder->id + 1 : 1;
    //         $orderNumber = 'ORD-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

    //         $order = Order::create([
    //             'order_number'          => $orderNumber,
    //             'service_id'            => $service->id,
    //             'service_name'          => $service->title,
    //             'customer_name'         => $request->customer_name,
    //             'customer_email'        => $request->customer_email,
    //             'state'                 => $request->state,
    //             'amount'                => $price,
    //             'status'                => 'Completed',
    //             'card_brand'            => ucfirst($paymentMethod->card->brand),
    //             'card_last4'            => $paymentMethod->card->last4,
    //             'document_status'       => 'Ready',
    //             'stripe_transaction_id' => $intent->id,
    //         ]);

    //         $adminEmails = User::role('admin')
    //             ->where('new_order_e_notification', true)
    //             ->pluck('email');

    //         if ($adminEmails->isNotEmpty()) {
    //             Mail::to($adminEmails->all())
    //                 ->queue(new NewOrderAdminMail($order));
    //         }

    //         $customer = Customer::firstOrCreate(
    //             ['email' => $request->customer_email],
    //             [
    //                 'name' => $request->customer_name,
    //                 'state' => $request->state,
    //                 'join_date' => now(),
    //                 'total_orders' => 0,
    //                 'total_spent' => 0,
    //             ]
    //         );

    //         $customer->update([
    //             'total_orders' => $customer->total_orders + 1,
    //             'total_spent'  => $customer->total_spent + $price,
    //             'last_activity' => now()
    //         ]);

    //         DB::commit();

    //         if ($submission) {
    //             Mail::to($request->customer_email)
    //                 ->queue(new DocumentMail($order, $submission));
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Order processed successfully!',
    //             'order_details' => $order
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }
}
