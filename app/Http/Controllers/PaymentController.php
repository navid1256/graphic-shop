<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Payment\Exceptions\PaymentException;
use App\Services\Payment\Requests\PaymentRequest;
use App\Services\Payment\Results\PaymentVerification;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class PaymentController extends Controller
{
    public function pay(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'family' => ['required', 'string', 'max:120'],
            'mobile' => ['required', 'string', 'regex:/^09[0-9]{9}$/'],
            'email' => ['nullable', 'email', 'max:254'],
            'provider' => ['sometimes', Rule::in([PaymentService::ZIBAL, PaymentService::ZARINPAL])],
        ]);

        $basket = json_decode($request->cookie('basket', '[]'), true);
        $productIds = is_array($basket) ? array_keys($basket) : [];
        $products = Product::whereIn('id', $productIds)->get(['id', 'price']);
        $totalTomans = $products->sum('price');

        if ($products->isEmpty() || $totalTomans <= 0 || $totalTomans > intdiv(PHP_INT_MAX, 10)) {
            return redirect()->route('frontend.checkout')
                ->withErrors(['payment' => 'سبد خرید خالی است یا مبلغ آن معتبر نیست.']);
        }

        $paymentId = (string) Str::uuid();
        $payment = [
            'provider' => $data['provider'] ?? config('payments.default', PaymentService::ZIBAL),
            'order_id' => $paymentId,
            'amount_rials' => (int) $totalTomans * 10,
            'callback_url' => route('frontend.payment.callback', ['payment' => $paymentId]),
            'description' => 'پرداخت سبد خرید برای ' . $data['family'],
            'mobile' => $data['mobile'],
            'email' => $data['email'] ?? null,
            'product_ids' => $products->modelKeys(),
            'status' => 'pending',
        ];

        try {
            $result = $this->paymentService($payment)->pay();
        } catch (PaymentException | InvalidArgumentException $exception) {
            report($exception);

            return redirect()->route('frontend.checkout')->withInput()
                ->withErrors(['payment' => 'ارتباط با درگاه پرداخت برقرار نشد. دوباره تلاش کنید.']);
        }

        $payment['transaction_id'] = $result->transactionId;
        $request->session()->put('payments.' . $paymentId, $payment);

        return redirect()->away($result->redirectUrl);
    }

    public function callback(Request $request, string $payment): RedirectResponse
    {
        $storedPayment = $request->session()->get('payments.' . $payment);

        if (! is_array($storedPayment)) {
            return redirect()->route('frontend.checkout')
                ->withErrors(['payment' => 'اطلاعات پرداخت پیدا نشد یا نشست شما منقضی شده است.']);
        }

        if ($storedPayment['status'] === PaymentVerification::VERIFIED) {
            return redirect()->route('frontend.checkout')->with('success', 'این پرداخت قبلاً تأیید شده است.');
        }

        $callback = $request->validate([
            'trackId' => ['sometimes', 'string', 'regex:/^[0-9]+$/', 'max:128'],
            'success' => ['sometimes', Rule::in(['0', '1'])],
            'Authority' => ['sometimes', 'string', 'max:128'],
            'Status' => ['sometimes', 'string', 'max:20'],
        ]);

        dd($callback);

        try {
            $verification = $this->paymentService($storedPayment)->verifyCallback(
                $callback,
                $storedPayment['transaction_id'],
            );
        } catch (PaymentException | InvalidArgumentException $exception) {
            report($exception);

            return redirect()->route('frontend.checkout')
                ->withErrors(['payment' => 'تأیید پرداخت انجام نشد. در صورت کسر وجه، پیش از پرداخت مجدد وضعیت را پیگیری کنید.']);
        }

        $reconciledZibalPayment = $verification->provider === PaymentService::ZIBAL
            && $verification->status === PaymentVerification::ALREADY_VERIFIED
            && $verification->paidAmountRials === $storedPayment['amount_rials'];

        if (! $verification->isNewlyVerified() && ! $reconciledZibalPayment) {
            return redirect()->route('frontend.checkout')->withErrors([
                'payment' => $verification->status === PaymentVerification::ALREADY_VERIFIED
                    ? 'این تراکنش قبلاً در درگاه تأیید شده است؛ وضعیت آن نیاز به پیگیری دارد.'
                    : 'پرداخت لغو شد یا ناموفق بود.',
            ]);
        }

        $storedPayment['status'] = PaymentVerification::VERIFIED;
        $storedPayment['reference_id'] = $verification->referenceId;
        $request->session()->put('payments.' . $payment, $storedPayment);

        $receipt = $verification->referenceId
            ? 'کد پیگیری: ' . $verification->referenceId
            : 'شناسه تراکنش زیبال: ' . $verification->transactionId;

        return redirect()->route('frontend.checkout')
            ->with('success', 'پرداخت با موفقیت تأیید شد. ' . $receipt);
    }

    private function paymentService(array $payment): PaymentService
    {
        return new PaymentService($payment['provider'], new PaymentRequest(
            orderId: $payment['order_id'],
            amountRials: $payment['amount_rials'],
            callbackUrl: $payment['callback_url'],
            description: $payment['description'],
            mobile: $payment['mobile'],
            email: $payment['email'],
        ));
    }
}
