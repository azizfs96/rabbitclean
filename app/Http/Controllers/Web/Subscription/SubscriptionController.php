<?php

namespace App\Http\Controllers\Web\Subscription;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Enum\ValidityTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::ordered()->paginate(15);
        return view('subscriptions.index', compact('subscriptions'));
    }

    public function create()
    {
        $validityTypes = ValidityTypeEnum::cases();
        return view('subscriptions.create', compact('validityTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'validity' => 'required|integer|min:1',
            'validity_type' => 'required|string|in:days,months,years',
            'laundry_credits' => 'nullable|integer|min:0',
            'clothing_credits' => 'nullable|integer|min:0',
            'delivery_credits' => 'nullable|integer|min:0',
            'towel_credits' => 'nullable|integer|min:0',
            'special_credits' => 'nullable|integer|min:0',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'color' => 'nullable|string|max:50',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');
        $validated['features'] = array_filter($request->input('features', []));
        $validated['sort_order'] = Subscription::max('sort_order') + 1;
        
        // Auto-calculate credit_amount as 30% bonus of price (زبادة)
        // credit_amount = price + (price * 0.30) = price * 1.30
        $validated['credit_amount'] = $validated['price'] * 1.30;

        Subscription::create($validated);

        return redirect()->route('subscription.index')
            ->with('success', __('Subscription plan created successfully'));
    }

    public function edit(Subscription $subscription)
    {
        $validityTypes = ValidityTypeEnum::cases();
        return view('subscriptions.edit', compact('subscription', 'validityTypes'));
    }

    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'validity' => 'required|integer|min:1',
            'validity_type' => 'required|string|in:days,months,years',
            'laundry_credits' => 'nullable|integer|min:0',
            'clothing_credits' => 'nullable|integer|min:0',
            'delivery_credits' => 'nullable|integer|min:0',
            'towel_credits' => 'nullable|integer|min:0',
            'special_credits' => 'nullable|integer|min:0',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'color' => 'nullable|string|max:50',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');
        $validated['features'] = array_filter($request->input('features', []));
        
        // Auto-calculate credit_amount as 30% bonus of price (زبادة)
        // credit_amount = price + (price * 0.30) = price * 1.30
        $validated['credit_amount'] = $validated['price'] * 1.30;

        $subscription->update($validated);

        return redirect()->route('subscription.index')
            ->with('success', __('Subscription plan updated successfully'));
    }

    public function toggle(Subscription $subscription)
    {
        $subscription->is_active = !$subscription->is_active;
        $subscription->save();

        return redirect()->back()
            ->with('success', __('Subscription status updated'));
    }

    public function destroy(Subscription $subscription)
    {
        // Check if there are active subscriptions
        if ($subscription->customerSubscriptions()->where('status', 'active')->exists()) {
            return redirect()->back()
                ->with('error', __('Cannot delete subscription with active customers'));
        }

        $subscription->delete();

        return redirect()->route('subscription.index')
            ->with('success', __('Subscription plan deleted successfully'));
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'integer|exists:subscriptions,id',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->orders as $index => $id) {
                Subscription::where('id', $id)->update(['sort_order' => $index]);
            }
        });

        return response()->json(['success' => true]);
    }
}
