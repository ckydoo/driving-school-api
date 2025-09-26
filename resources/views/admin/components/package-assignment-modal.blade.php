{{-- Create this as resources/views/admin/components/package-assignment-modal.blade.php --}}
<!-- Package Assignment Modal -->
<div class="modal fade" id="assignPackageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-box"></i> Assign Package to {{ $school->name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.subscriptions.assign-package', $school) }}">
                @csrf
                <div class="modal-body">
                    <!-- Current Package Info -->
                    @if($school->subscriptionPackage)
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Current Package</h6>
                        <strong>{{ $school->subscriptionPackage->name }}</strong> - 
                        {{ $school->subscriptionPackage->getFormattedMonthlyPrice() }}/month
                        <br>
                        <small>Status: {{ ucfirst($school->subscription_status) }}</small>
                    </div>
                    @endif

                    <!-- Package Selection -->
                    <div class="form-group">
                        <label for="modal_package_id" class="font-weight-bold">Select New Package <span class="text-danger">*</span></label>
                        <select class="form-control" id="modal_package_id" name="package_id" required>
                            <option value="">Choose a package...</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" 
                                        data-monthly-price="{{ $package->monthly_price }}"
                                        data-yearly-price="{{ $package->yearly_price }}"
                                        data-features="{{ json_encode($package->features) }}"
                                        data-limits="{{ json_encode($package->limits) }}"
                                        {{ $school->subscription_package_id == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }} 
                                    ({{ $package->getFormattedMonthlyPrice() }}/month)
                                    @if($package->hasYearlyPricing())
                                        - {{ $package->getFormattedYearlyPrice() }}/year
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Billing Period -->
                    <div class="form-group">
                        <label for="modal_billing_period" class="font-weight-bold">Billing Period</label>
                        <select class="form-control" id="modal_billing_period" name="billing_period">
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly (Save money!)</option>
                        </select>
                    </div>

                    <!-- Package Preview -->
                    <div id="modal-package-preview" style="display: none;">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Package Preview</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Pricing:</strong>
                                        <div id="modal-pricing-info"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Limits:</strong>
                                        <div id="modal-limits-info"></div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <strong>Features:</strong>
                                    <div id="modal-features-info"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Usage Warning -->
                    <div id="usage-warning" class="alert alert-warning" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> <span id="warning-message"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Assign Package
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const packageSelect = document.getElementById('modal_package_id');
    const billingSelect = document.getElementById('modal_billing_period');
    const preview = document.getElementById('modal-package-preview');
    const usageWarning = document.getElementById('usage-warning');
    
    // Current usage data
    const currentUsage = {
        students: {{ $school->getCurrentUsage('max_students') }},
        instructors: {{ $school->getCurrentUsage('max_instructors') }},
        vehicles: {{ $school->getCurrentUsage('max_vehicles') }}
    };

    function updatePackagePreview() {
        const selectedOption = packageSelect.options[packageSelect.selectedIndex];
        
        if (selectedOption.value) {
            const monthlyPrice = parseFloat(selectedOption.getAttribute('data-monthly-price'));
            const yearlyPrice = parseFloat(selectedOption.getAttribute('data-yearly-price'));
            const features = JSON.parse(selectedOption.getAttribute('data-features') || '[]');
            const limits = JSON.parse(selectedOption.getAttribute('data-limits') || '{}');
            const billingPeriod = billingSelect.value;

            // Update pricing info
            let pricingHtml = `<div>Monthly: $${monthlyPrice.toFixed(2)}</div>`;
            if (yearlyPrice) {
                const savings = (monthlyPrice * 12) - yearlyPrice;
                const discountPercent = Math.round((savings / (monthlyPrice * 12)) * 100);
                pricingHtml += `<div>Yearly: $${yearlyPrice.toFixed(2)} <small class="text-success">(${discountPercent}% off)</small></div>`;
            }
            document.getElementById('modal-pricing-info').innerHTML = pricingHtml;

            // Update limits info
            let limitsHtml = '';
            Object.keys(limits).forEach(key => {
                const value = limits[key];
                const displayValue = value === -1 ? 'Unlimited' : value;
                const labelName = key.replace('max_', '').replace('_', ' ');
                limitsHtml += `<div>${labelName}: ${displayValue}</div>`;
            });
            document.getElementById('modal-limits-info').innerHTML = limitsHtml;

            // Update features info
            let featuresHtml = '<ul class="mb-0">';
            features.forEach(feature => {
                featuresHtml += `<li>${feature}</li>`;
            });
            featuresHtml += '</ul>';
            document.getElementById('modal-features-info').innerHTML = featuresHtml;

            // Check for usage warnings
            let warningMessages = [];
            if (limits.max_students !== -1 && currentUsage.students > limits.max_students) {
                warningMessages.push(`Current students (${currentUsage.students}) exceeds package limit (${limits.max_students})`);
            }
            if (limits.max_instructors !== -1 && currentUsage.instructors > limits.max_instructors) {
                warningMessages.push(`Current instructors (${currentUsage.instructors}) exceeds package limit (${limits.max_instructors})`);
            }
            if (limits.max_vehicles !== -1 && currentUsage.vehicles > limits.max_vehicles) {
                warningMessages.push(`Current vehicles (${currentUsage.vehicles}) exceeds package limit (${limits.max_vehicles})`);
            }

            if (warningMessages.length > 0) {
                document.getElementById('warning-message').innerHTML = warningMessages.join('<br>');
                usageWarning.style.display = 'block';
            } else {
                usageWarning.style.display = 'none';
            }

            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
            usageWarning.style.display = 'none';
        }
    }

    packageSelect.addEventListener('change', updatePackagePreview);
    billingSelect.addEventListener('change', updatePackagePreview);

    // Update billing period text based on selection
    billingSelect.addEventListener('change', function() {
        const yearlyOption = this.querySelector('option[value="yearly"]');
        if (this.value === 'yearly') {
            yearlyOption.textContent = 'Yearly (Save money!)';
        } else {
            yearlyOption.textContent = 'Yearly';
        }
    });
});
</script>