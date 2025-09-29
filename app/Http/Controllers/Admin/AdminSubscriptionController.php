<?php
// app/Http/Controllers/Admin/AdminSubscriptionController.php - ENHANCED VERSION

namespace App\Http\Controllers\Admin;

use App\Models\School;
use Illuminate\Http\Request;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPackage;
use App\Models\SubscriptionPayment;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminSubscriptionController extends Controller
{
  public function index(Request $request)
{
    $currentUser = Auth::user();

    if (!$currentUser->isSuperAdmin()) {
        abort(403, 'Access denied. Super Administrator privileges required.');
    }

    $query = School::with(['users', 'subscriptionPackage', 'subscriptionInvoices', 'subscriptionPayments']);

    // Search functionality
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    // Filter by subscription status
    if ($request->filled('subscription_status')) {
        $query->where('subscription_status', $request->subscription_status);
    }

    // Filter by package
    if ($request->filled('package_id')) {
        $query->where('subscription_package_id', $request->package_id);
    }

    $schools = $query->orderBy('created_at', 'desc')->paginate(20);

    // Get packages for filter dropdown
    $packages = SubscriptionPackage::active()->ordered()->get();

    // Statistics (UPDATED - uses subscription billing)
    $stats = [
        'total_schools' => School::count(),
        'trial_subscriptions' => School::where('subscription_status', 'trial')->count(),
        'active_subscriptions' => School::where('subscription_status', 'active')->count(),
        'suspended_subscriptions' => School::where('subscription_status', 'suspended')->count(),
        'expired_subscriptions' => School::where('subscription_status', 'expired')->count(),
        'monthly_revenue' => SubscriptionPayment::where('status', 'completed')
                                ->whereMonth('payment_date', now()->month)
                                ->whereYear('payment_date', now()->year)
                                ->sum('amount'),
    ];

    return view('admin.subscriptions.index', compact('schools', 'stats', 'packages', 'currentUser'));
}
    public function create()
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        $packages = SubscriptionPackage::active()->ordered()->get();

        return view('admin.subscriptions.create', compact('packages', 'currentUser'));
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:schools,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'subscription_package_id' => 'required|exists:subscription_packages,id',
            'subscription_status' => 'required|in:trial,active,suspended,expired,cancelled',
            'billing_period' => 'nullable|in:monthly,yearly',
            'trial_ends_at' => 'nullable|date|after:today',
            'subscription_expires_at' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $package = SubscriptionPackage::findOrFail($request->subscription_package_id);
        $billingPeriod = $request->billing_period ?? 'monthly';

        // Calculate monthly fee based on package and billing period
        $monthlyFee = $billingPeriod === 'yearly' && $package->yearly_price
            ? ($package->yearly_price / 12)
            : $package->monthly_price;

        $school = School::create([
            'name' => $request->name,
            'slug' => \Str::slug($request->name),
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'subscription_package_id' => $request->subscription_package_id,
            'subscription_status' => $request->subscription_status,
            'trial_ends_at' => $request->trial_ends_at,
            'subscription_expires_at' => $request->subscription_expires_at,
            'monthly_fee' => $monthlyFee,
            'subscription_started_at' => now(),
            'invitation_code' => School::generateUniqueInvitationCode(),
        ]);

        return redirect()->route('admin.subscriptions.index')
                        ->with('success', 'School subscription created successfully.');
    }

    public function show(School $subscription)
{
    $currentUser = Auth::user();

    if (!$currentUser->isSuperAdmin()) {
        abort(403, 'Access denied. Super Administrator privileges required.');
    }

    // Load relationships including new billing relationships
    $subscription->load([
        'users',
        'fleet',
        'courses',
        'subscriptionPackage',
        'subscriptionInvoices' => function($query) {
            $query->orderBy('invoice_date', 'desc')->limit(10);
        },
        'subscriptionPayments' => function($query) {
            $query->orderBy('payment_date', 'desc')->limit(10);
        },
        'subscriptionHistory' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }
    ]);

    // Get subscription stats (UPDATED - uses new billing methods)
    $stats = [
        'total_users' => $subscription->users()->count(),
        'students' => $subscription->students()->count(),
        'instructors' => $subscription->instructors()->count(),
        'admins' => $subscription->admins()->count(),
        'vehicles' => $subscription->fleet()->count(),
        'courses' => $subscription->courses()->count(),
        // Updated billing stats
        'subscription_stats' => $subscription->getSubscriptionStats(),
    ];

    return view('admin.subscriptions.show', compact('subscription', 'currentUser', 'stats'));
}

    public function edit(School $subscription)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        $packages = SubscriptionPackage::active()->ordered()->get();

        return view('admin.subscriptions.edit', compact('subscription', 'packages', 'currentUser'));
    }

    public function update(Request $request, School $subscription)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:schools,email,' . $subscription->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'subscription_package_id' => 'required|exists:subscription_packages,id',
            'subscription_status' => 'required|in:trial,active,suspended,expired,cancelled',
            'billing_period' => 'nullable|in:monthly,yearly',
            'trial_ends_at' => 'nullable|date',
            'subscription_expires_at' => 'nullable|date',
            'monthly_fee' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive,suspended',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        try {
            $package = SubscriptionPackage::findOrFail($request->subscription_package_id);
            $billingPeriod = $request->billing_period ?? 'monthly';

            // Calculate monthly fee if not provided
            $monthlyFee = $request->monthly_fee;
            if (!$monthlyFee) {
                $monthlyFee = $billingPeriod === 'yearly' && $package->yearly_price
                    ? ($package->yearly_price / 12)
                    : $package->monthly_price;
            }

            // Prepare update data
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'subscription_package_id' => $request->subscription_package_id,
                'subscription_status' => $request->subscription_status,
                'trial_ends_at' => $request->trial_ends_at,
                'subscription_expires_at' => $request->subscription_expires_at,
                'monthly_fee' => $monthlyFee,
                'status' => $request->status ?? $subscription->status,
            ];

            // Update the subscription
            $subscription->update($updateData);

            return redirect()->route('admin.subscriptions.show', $subscription)
                            ->with('success', 'Subscription updated successfully.');

        } catch (\Exception $e) {
            \Log::error('Subscription update failed: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'Failed to update subscription. Please try again.')
                           ->withInput();
        }
    }

    public function destroy(School $subscription)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        // Check if school has users
        if ($subscription->users()->count() > 0) {
            return back()->with('error', 'Cannot delete school with existing users. Please transfer or remove users first.');
        }

        $schoolName = $subscription->name;
        $subscription->delete();

        return redirect()->route('admin.subscriptions.index')
                        ->with('success', "School '{$schoolName}' deleted successfully.");
    }

    public function toggleStatus(School $subscription)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        $newStatus = match($subscription->subscription_status) {
            'trial' => 'active',
            'active' => 'suspended',
            'suspended' => 'active',
            'expired' => 'trial',
            'cancelled' => 'trial',
            default => 'trial'
        };

        $subscription->update(['subscription_status' => $newStatus]);

        return back()->with('success', "Subscription status updated to {$newStatus}!");
    }
/**
 * Assign a package to a school (UPDATED - creates invoice)
 */
public function assignPackage(Request $request, School $subscription)
{
    $currentUser = Auth::user();

    if (!$currentUser->isSuperAdmin()) {
        abort(403, 'Access denied. Super Administrator privileges required.');
    }

    $request->validate([
        'package_id' => 'required|exists:subscription_packages,id',
        'billing_period' => 'nullable|in:monthly,yearly'
    ]);

    $package = SubscriptionPackage::findOrFail($request->package_id);
    $billingPeriod = $request->billing_period ?? 'monthly';

    // Use the new upgrade method that creates proper invoices
    $success = $subscription->upgradeTo($package, $billingPeriod);

    if ($success) {
        return back()->with('success', "Package '{$package->name}' assigned successfully! Invoice created.");
    } else {
        return back()->with('error', 'Failed to assign package. Please try again.');
    }
}

/**
 * Upgrade school to the next tier package (UPDATED)
 */
public function upgradePackage(School $subscription)
{
    $currentUser = Auth::user();

    if (!$currentUser->isSuperAdmin()) {
        abort(403, 'Access denied. Super Administrator privileges required.');
    }

    $currentPackage = $subscription->subscriptionPackage;
    if (!$currentPackage) {
        return back()->with('error', 'School has no current package to upgrade from.');
    }

    // Find next tier package (higher sort_order and price)
    $nextPackage = SubscriptionPackage::active()
        ->where('sort_order', '>', $currentPackage->sort_order)
        ->where('monthly_price', '>', $currentPackage->monthly_price)
        ->orderBy('sort_order')
        ->first();

    if (!$nextPackage) {
        return back()->with('error', 'No higher tier package available for upgrade.');
    }

    // Use the updated upgrade method
    $success = $subscription->upgradeTo($nextPackage, 'monthly');

    if ($success) {
        return back()->with('success', "Successfully upgraded to '{$nextPackage->name}'! New invoice created.");
    } else {
        return back()->with('error', 'Failed to upgrade package. Please try again.');
    }
}


    /**
     * Reset trial period for a school
     */
    public function resetTrial(School $subscription)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        $trialPackage = $subscription->subscriptionPackage ??
                       SubscriptionPackage::where('slug', 'trial')->first();

        if (!$trialPackage) {
            return back()->with('error', 'No trial package found.');
        }

        $subscription->update([
            'subscription_status' => 'trial',
            'trial_ends_at' => now()->addDays($trialPackage->trial_days),
            'subscription_expires_at' => null,
            'monthly_fee' => 0.00,
        ]);

        return back()->with('success', 'Trial period reset successfully!');
    }

    /**
     * Cancel subscription
     */
    public function cancel(School $subscription)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        $subscription->update([
            'subscription_status' => 'cancelled',
            'subscription_expires_at' => now()->addDays(30), // 30-day grace period
        ]);

        return back()->with('success', 'Subscription cancelled with 30-day grace period.');
    }

    /**
     * Reactivate cancelled subscription
     */
    public function reactivate(School $subscription)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Super Administrator privileges required.');
        }

        if ($subscription->subscriptionPackage) {
            $subscription->update([
                'subscription_status' => 'active',
                'subscription_expires_at' => now()->addMonth(),
            ]);

            return back()->with('success', 'Subscription reactivated successfully!');
        }

        return back()->with('error', 'Cannot reactivate subscription without a package. Please assign a package first.');
    }



    /**
 * Create manual subscription payment
 */
public function createPayment(Request $request, School $subscription)
{
    $currentUser = Auth::user();

    if (!$currentUser->isSuperAdmin()) {
        abort(403, 'Access denied. Super Administrator privileges required.');
    }

    $request->validate([
        'invoice_id' => 'required|exists:subscription_invoices,id',
        'amount' => 'required|numeric|min:0.01',
        'payment_method' => 'required|in:stripe,paypal,bank_transfer,manual,credit_card',
        'payment_date' => 'required|date',
        'reference_number' => 'nullable|string',
        'notes' => 'nullable|string'
    ]);

    $invoice = SubscriptionInvoice::findOrFail($request->invoice_id);

    $paymentData = [
        'amount' => $request->amount,
        'payment_method' => $request->payment_method,
        'status' => 'completed', // Manual payments are immediately completed
        'payment_date' => $request->payment_date,
        'reference_number' => $request->reference_number,
        'notes' => $request->notes
    ];

    $payment = $subscription->processSubscriptionPayment($invoice, $paymentData);

    return back()->with('success', "Payment of \${$payment->amount} recorded successfully!");
}

/**
 * View subscription billing details
 */
public function billing(School $subscription)
{
    $currentUser = Auth::user();

    if (!$currentUser->isSuperAdmin()) {
        abort(403, 'Access denied. Super Administrator privileges required.');
    }

    $subscription->load([
        'subscriptionPackage',
        'subscriptionInvoices' => function($query) {
            $query->orderBy('invoice_date', 'desc');
        },
        'subscriptionPayments' => function($query) {
            $query->orderBy('payment_date', 'desc');
        }
    ]);

    $billingStats = $subscription->getSubscriptionStats();

    return view('admin.subscriptions.billing', compact('subscription', 'currentUser', 'billingStats'));
}

/**
 * Generate subscription invoice manually
 */
public function generateInvoice(Request $request, School $subscription)
{
    $currentUser = Auth::user();

    if (!$currentUser->isSuperAdmin()) {
        abort(403, 'Access denied. Super Administrator privileges required.');
    }

    $request->validate([
        'billing_period' => 'required|in:monthly,yearly',
        'invoice_date' => 'nullable|date',
        'due_date' => 'nullable|date|after:invoice_date'
    ]);

    if (!$subscription->subscriptionPackage) {
        return back()->with('error', 'Cannot generate invoice - no package assigned to this school.');
    }

    $billingPeriod = $request->billing_period;
    $package = $subscription->subscriptionPackage;

    // Create the invoice
    $invoice = $subscription->createSubscriptionInvoice($package, $billingPeriod);

    // Override dates if provided
    if ($request->invoice_date) {
        $invoice->update(['invoice_date' => $request->invoice_date]);
    }
    if ($request->due_date) {
        $invoice->update(['due_date' => $request->due_date]);
    }

    return back()->with('success', "Invoice #{$invoice->invoice_number} generated successfully!");
}

/**
 * Mark invoice as paid
 */
public function markInvoicePaid(SubscriptionInvoice $invoice)
{
    $currentUser = Auth::user();

    if (!$currentUser->isSuperAdmin()) {
        abort(403, 'Access denied. Super Administrator privileges required.');
    }

    if ($invoice->status === 'paid') {
        return back()->with('error', 'Invoice is already marked as paid.');
    }

    $paymentData = [
        'amount' => $invoice->total_amount,
        'payment_method' => 'manual',
        'status' => 'completed',
        'payment_date' => now(),
        'notes' => 'Manually marked as paid by admin: ' . $currentUser->name
    ];

    $payment = $invoice->school->processSubscriptionPayment($invoice, $paymentData);

    return back()->with('success', "Invoice #{$invoice->invoice_number} marked as paid!");
}

/**
 * Cancel subscription invoice
 */
public function cancelInvoice(SubscriptionInvoice $invoice)
{
    $currentUser = Auth::user();

    if (!$currentUser->isSuperAdmin()) {
        abort(403, 'Access denied. Super Administrator privileges required.');
    }

    if ($invoice->status === 'paid') {
        return back()->with('error', 'Cannot cancel a paid invoice.');
    }

    $invoice->update(['status' => 'cancelled']);

    return back()->with('success', "Invoice #{$invoice->invoice_number} cancelled successfully!");
}

/**
 * Process refund
 */
public function processRefund(Request $request, SubscriptionPayment $payment)
{
    $currentUser = Auth::user();

    if (!$currentUser->isSuperAdmin()) {
        abort(403, 'Access denied. Super Administrator privileges required.');
    }

    $request->validate([
        'refund_amount' => 'required|numeric|min:0.01|max:' . $payment->amount,
        'refund_reason' => 'required|string',
        'refund_method' => 'required|in:original_method,bank_transfer,manual'
    ]);

    // Create refund payment record
    $refund = $payment->school->subscriptionPayments()->create([
        'subscription_invoice_id' => $payment->subscription_invoice_id,
        'payment_number' => SubscriptionPayment::generatePaymentNumber(),
        'amount' => -$request->refund_amount, // Negative amount for refund
        'payment_method' => $request->refund_method,
        'status' => 'completed',
        'payment_date' => now(),
        'reference_number' => 'REFUND-' . $payment->payment_number,
        'notes' => "Refund: {$request->refund_reason}",
    ]);

    // Update original payment if fully refunded
    if ($request->refund_amount >= $payment->amount) {
        $payment->update(['status' => 'refunded']);
    }

    return back()->with('success', "Refund of \${$request->refund_amount} processed successfully!");
}

/**
 * Bulk assign packages to multiple schools (UPDATED)
 */
public function bulkAssignPackage(Request $request)
{
    $currentUser = Auth::user();

    if (!$currentUser->isSuperAdmin()) {
        abort(403, 'Access denied. Super Administrator privileges required.');
    }

    $request->validate([
        'school_ids' => 'required|array',
        'school_ids.*' => 'exists:schools,id',
        'package_id' => 'required|exists:subscription_packages,id',
        'billing_period' => 'required|in:monthly,yearly'
    ]);

    $package = SubscriptionPackage::findOrFail($request->package_id);
    $billingPeriod = $request->billing_period;
    $schools = School::whereIn('id', $request->school_ids)->get();

    $successCount = 0;
    $failCount = 0;

    foreach ($schools as $school) {
        try {
            $school->upgradeTo($package, $billingPeriod);
            $successCount++;
        } catch (\Exception $e) {
            $failCount++;
            Log::error("Failed to assign package to school {$school->id}: " . $e->getMessage());
        }
    }

    $message = "Package '{$package->name}' assigned to {$successCount} schools successfully.";
    if ($failCount > 0) {
        $message .= " {$failCount} assignments failed.";
    }

    return back()->with('success', $message);
}

/**
 * Export subscription data
 */
public function exportSubscriptions(Request $request)
{
    $currentUser = Auth::user();

    if (!$currentUser->isSuperAdmin()) {
        abort(403, 'Access denied. Super Administrator privileges required.');
    }

    $format = $request->get('format', 'csv');
    $schools = School::with(['subscriptionPackage', 'subscriptionInvoices', 'subscriptionPayments'])->get();

    $data = $schools->map(function ($school) {
        $stats = $school->getSubscriptionStats();
        return [
            'School Name' => $school->name,
            'Email' => $school->email,
            'Package' => $school->subscriptionPackage?->name ?? 'None',
            'Status' => ucfirst($school->subscription_status),
            'Monthly Fee' => $school->monthly_fee,
            'Total Paid' => $stats['total_paid'],
            'Outstanding Balance' => $stats['outstanding_balance'],
            'Current MRR' => $stats['current_mrr'],
            'Next Billing' => $stats['next_billing_date']?->format('Y-m-d'),
            'Total Invoices' => $stats['total_invoices'],
            'Paid Invoices' => $stats['paid_invoices'],
            'Overdue Invoices' => $stats['overdue_invoices'],
            'Created At' => $school->created_at->format('Y-m-d'),
        ];
    });

    if ($format === 'csv') {
        $filename = 'subscriptions_' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');

            // Write headers
            if ($data->isNotEmpty()) {
                fputcsv($file, array_keys($data->first()));
            }

            // Write data
            foreach ($data as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    return back()->with('error', 'Invalid export format requested.');
}
}
