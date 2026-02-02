<?php

namespace App\Http\Controllers\Web\Subscription;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerSubscription;
use App\Models\Subscription;
use App\Services\CreditService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class CustomerSubscriptionController extends Controller
{
    protected SubscriptionService $subscriptionService;
    protected CreditService $creditService;

    public function __construct(SubscriptionService $subscriptionService, CreditService $creditService)
    {
        $this->subscriptionService = $subscriptionService;
        $this->creditService = $creditService;
    }

    public function index(Request $request)
    {
        $query = CustomerSubscription::with(['customer.user', 'subscription'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by customer name or phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer.user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $customerSubscriptions = $query->paginate(15);

        return view('customer-subscriptions.index', compact('customerSubscriptions'));
    }

    public function show(CustomerSubscription $customerSubscription)
    {
        $customerSubscription->load(['customer.user', 'subscription', 'creditTransactions']);

        $creditHistory = $customerSubscription->creditTransactions()
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('customer-subscriptions.show', compact('customerSubscription', 'creditHistory'));
    }

    public function adjustCredits(Request $request, CustomerSubscription $customerSubscription)
    {
        $request->validate([
            'credit_type' => 'required|string|in:laundry,clothing,delivery,towel,special,balance',
            'amount' => 'required|numeric|min:0.01',
            'adjustment_type' => 'required|string|in:add,deduct',
            'notes' => 'nullable|string|max:500',
        ]);

        $customer = $customerSubscription->customer;

        // Handle simplified balance type
        if ($request->credit_type === 'balance') {
            $amount = (float) $request->amount;

            if ($request->adjustment_type === 'add') {
                $customerSubscription->credit_balance += $amount;
                $customerSubscription->save();

                // Log transaction
                \App\Models\CreditTransaction::create([
                    'customer_id' => $customer->id,
                    'customer_subscription_id' => $customerSubscription->id,
                    'credit_type' => 'balance',
                    'amount' => $amount,
                    'transaction_type' => 'credit',
                    'reference_type' => 'admin_adjustment',
                    'balance_before' => $customerSubscription->credit_balance - $amount,
                    'balance_after' => $customerSubscription->credit_balance,
                    'notes' => $request->notes ?? 'Admin credit adjustment',
                    'created_by' => auth()->id(),
                ]);
            } else {
                if ($customerSubscription->credit_balance < $amount) {
                    return redirect()->back()
                        ->with('error', __('Insufficient credit balance to deduct'));
                }

                $customerSubscription->credit_balance -= $amount;
                $customerSubscription->total_credits_used += $amount;
                $customerSubscription->save();

                // Log transaction
                \App\Models\CreditTransaction::create([
                    'customer_id' => $customer->id,
                    'customer_subscription_id' => $customerSubscription->id,
                    'credit_type' => 'balance',
                    'amount' => $amount,
                    'transaction_type' => 'debit',
                    'reference_type' => 'admin_adjustment',
                    'balance_before' => $customerSubscription->credit_balance + $amount,
                    'balance_after' => $customerSubscription->credit_balance,
                    'notes' => $request->notes ?? 'Admin debit adjustment',
                    'created_by' => auth()->id(),
                ]);
            }

            return redirect()->back()
                ->with('success', __('Credit balance adjusted successfully'));
        }

        // Legacy credit types
        $transaction = $this->creditService->adjustCredits(
            $customer,
            $request->credit_type,
            (int) $request->amount,
            $request->adjustment_type,
            $request->notes
        );

        if (!$transaction && $request->adjustment_type === 'deduct') {
            return redirect()->back()
                ->with('error', __('Insufficient credits to deduct'));
        }

        return redirect()->back()
            ->with('success', __('Credits adjusted successfully'));
    }

    public function extend(Request $request, CustomerSubscription $customerSubscription)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $this->subscriptionService->extendSubscription($customerSubscription, $request->days);

        return redirect()->back()
            ->with('success', __('Subscription extended by :days days', ['days' => $request->days]));
    }

    public function cancel(CustomerSubscription $customerSubscription)
    {
        $this->subscriptionService->cancelSubscription($customerSubscription);

        return redirect()->back()
            ->with('success', __('Subscription cancelled successfully'));
    }

    public function renew(Request $request, CustomerSubscription $customerSubscription)
    {
        $plan = $customerSubscription->subscription;
        $customer = $customerSubscription->customer;

        try {
            // Calculate credit amount to add (use credit_amount if set, otherwise use price)
            $creditAmount = $plan->credit_amount ? (float) $plan->credit_amount : (float) ($plan->price ?? 0);

            // Store balance before for logging
            $balanceBefore = $customerSubscription->credit_balance;

            // Directly add credits to the existing subscription
            $customerSubscription->credit_balance += $creditAmount;

            // Extend the subscription end date based on plan validity
            $newEndDate = $this->subscriptionService->calculateRenewalEndDate($plan, $customerSubscription->end_date);
            $customerSubscription->end_date = $newEndDate;

            // Update amount paid (add to existing)
            $customerSubscription->amount_paid += $plan->price ?? 0;

            // Ensure status is active after renewal
            $customerSubscription->status = 'active';

            // Save the updated subscription
            $customerSubscription->save();

            // Log the credit transaction
            \App\Models\CreditTransaction::create([
                'customer_id' => $customer->id,
                'customer_subscription_id' => $customerSubscription->id,
                'credit_type' => 'balance',
                'amount' => $creditAmount,
                'transaction_type' => 'credit',
                'reference_type' => 'admin_renewal',
                'balance_before' => $balanceBefore,
                'balance_after' => $customerSubscription->credit_balance,
                'notes' => "تجديد الاشتراك بواسطة الإدارة - خطة: {$plan->name}",
                'created_by' => auth()->id(),
            ]);

            \Log::info('Subscription renewed directly', [
                'customer_subscription_id' => $customerSubscription->id,
                'plan_id' => $plan->id,
                'credits_added' => $creditAmount,
                'new_balance' => $customerSubscription->credit_balance,
                'new_end_date' => $newEndDate,
            ]);

            return redirect()->back()
                ->with('success', __('Subscription renewed successfully. Added :amount SAR credits.', ['amount' => number_format($creditAmount, 2)]));
        } catch (\Exception $e) {
            \Log::error('Subscription renewal failed', [
                'customer_subscription_id' => $customerSubscription->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()
                ->with('error', __('Failed to renew subscription: ' . $e->getMessage()));
        }
    }

    public function assignSubscription(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'subscription_id' => 'required|exists:subscriptions,id',
            'payment_gateway' => 'nullable|string|in:cash,bank_transfer',
        ]);

        $customer = Customer::findOrFail($request->customer_id);
        $subscription = Subscription::findOrFail($request->subscription_id);

        // Check if customer already has active subscription
        if ($this->subscriptionService->hasActiveSubscription($customer)) {
            return redirect()->back()
                ->with('error', __('Customer already has an active subscription'));
        }

        $customerSubscription = $this->subscriptionService->purchaseSubscription(
            $customer,
            $subscription,
            $request->input('payment_gateway', 'cash')
        );

        // Activate immediately for admin-assigned subscriptions
        $this->subscriptionService->activateSubscription($customerSubscription);

        return redirect()->route('customer-subscription.show', $customerSubscription)
            ->with('success', __('Subscription assigned successfully'));
    }

    public function createForm()
    {
        $customers = Customer::with('user')
            ->whereDoesntHave('customerSubscriptions', function ($q) {
                $q->where('status', 'active');
            })
            ->get();

        $subscriptions = Subscription::active()->ordered()->get();

        return view('customer-subscriptions.create', compact('customers', 'subscriptions'));
    }
}
