<?php

namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use App\Repositories\TransactionRepository;
use App\Repositories\PaymentGatewayRepository;
use App\Http\Requests\PaymentGatewayRequest;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\PayTabsService;
use Exception;

class PaymentGatewayController extends Controller
{
    public function __construct(
        protected PayTabsService $paytabsService
    ) {}

    /**
     * Show payment gateway
     */
    public function index()
    {
        $paymentGateways = PaymentGateway::get();

        return view('paymentGateway.index', compact('paymentGateways'));
    }

    /**
     * Update payment gateway
     */
    public function update(PaymentGatewayRequest $request, PaymentGateway $paymentGateway)
    {
        $repository = new PaymentGatewayRepository();
        $repository->updateByRequest($request, $paymentGateway);

        return back()->withSuccess(__('Payment Gateway Updated Successfully'));
    }

    /**
     * Toggle payment gateway status
     */
    public function toggle(PaymentGateway $paymentGateway)
    {
        $paymentGateway->update([
            'is_active' => ! $paymentGateway->is_active,
        ]);
        return back()->withSuccess(__('Status Updated Successfully'));
    }

    public function payment()
    {
        return view('subscriptionPurchase.payment');
    }

    public function process(Request $request, $order)
    {
        $paymentGateway = PaymentGateway::where('name', $request->payment_method)->first();
        $config = (object) ($paymentGateway->config ?? []);

        $request['paid_amount'] = $request->total_amount ??  null;
        $request['description'] = $order->instruction ?? null;
        $request['mode'] = $paymentGateway->mode ?? null;

        $this->{$request->payment_method . 'Service'}->paymentProcess($request, $config);

        $transactionRepo = new TransactionRepository();
        $transactionRepo->updateWhenComplatePay($order, $request->payment_method);

        return response()->json([
            'success' => true,
            'message' => 'Payment successfully processed',
        ], 200);

    }

}
