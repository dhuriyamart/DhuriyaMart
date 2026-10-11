@extends('layouts.admin')

@section('title', 'Marketplace Settings - DhuriyaMart Admin')
@section('page_title', 'Marketplace Configuration & Preferences')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Platform Settings</h4>
            <p class="text-muted small mb-0">Manage global operational rules, commissions, payment gateways, and shipping parameters</p>
        </div>
        <button type="submit" form="settingsForm" class="btn btn-sm btn-primary rounded-pill px-4 shadow-sm">
            <i class="bi bi-check-lg me-1"></i> Save Changes
        </button>
    </div>

    @include('components.alerts')

    <form id="settingsForm" action="{{ route('admin.settings.update') }}" method="POST">
        @csrf

        <div class="row g-4">
            <!-- Left Column: General & Business -->
            <div class="col-lg-7">
                <!-- General Store Info -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                    <div class="card-header bg-white border-0 py-3 px-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-3">
                                <i class="bi bi-shop fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">General Information</h6>
                                <small class="text-muted">Branding, site identity and public storefront info</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4 pt-1">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Marketplace Name</label>
                                <input type="text" name="site_name" class="form-control form-control-sm rounded-3" value="{{ $settings['site_name'] ?? 'DhuriyaMart' }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Currency Code</label>
                                <input type="text" name="site_currency" class="form-control form-control-sm rounded-3" value="{{ $settings['site_currency'] ?? 'INR' }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Tagline / Motto</label>
                                <input type="text" name="site_tagline" class="form-control form-control-sm rounded-3" value="{{ $settings['site_tagline'] ?? 'India\'s Trusted Multi-Vendor Marketplace' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Support Email</label>
                                <input type="email" name="contact_email" class="form-control form-control-sm rounded-3" value="{{ $settings['contact_email'] ?? 'support@dhuriyamart.com' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Support Helpline Phone</label>
                                <input type="text" name="contact_phone" class="form-control form-control-sm rounded-3" value="{{ $settings['contact_phone'] ?? '+91 98765 43210' }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Registered Office Address</label>
                                <textarea name="office_address" rows="2" class="form-control form-control-sm rounded-3">{{ $settings['office_address'] ?? 'DhuriyaMart Technologies Pvt Ltd, Bengaluru, India' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipping & GST Tax Rules -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                    <div class="card-header bg-white border-0 py-3 px-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 text-success p-2 rounded-3 me-3">
                                <i class="bi bi-truck fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Logistics, Delivery & Tax Rules</h6>
                                <small class="text-muted">Thresholds for free customer shipping and default taxes</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4 pt-1">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Free Delivery Threshold (Paise)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-muted">₹</span>
                                    <input type="number" name="free_shipping_threshold_paise" class="form-control rounded-end-3" value="{{ $settings['free_shipping_threshold_paise'] ?? 49900 }}" required>
                                </div>
                                <div class="form-text small">e.g. <code>49900</code> = Free shipping on orders above ₹499.00</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Standard Flat Shipping Fee (Paise)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-muted">₹</span>
                                    <input type="number" name="standard_shipping_fee_paise" class="form-control rounded-end-3" value="{{ $settings['standard_shipping_fee_paise'] ?? 4900 }}" required>
                                </div>
                                <div class="form-text small">e.g. <code>4900</code> = ₹49.00 flat delivery charge</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Default GST Tax Rate (%)</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" name="tax_rate_percent" class="form-control rounded-start-3" value="{{ $settings['tax_rate_percent'] ?? '5.00' }}" required>
                                    <span class="input-group-text bg-light text-muted">%</span>
                                </div>
                                <div class="form-text small">Default Goods and Services Tax percentage for new products</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Commissions & Payment Gateway -->
            <div class="col-lg-5">
                <!-- Marketplace Commission -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                    <div class="card-header bg-white border-0 py-3 px-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 text-warning p-2 rounded-3 me-3">
                                <i class="bi bi-pie-chart fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Platform Commission</h6>
                                <small class="text-muted">Take rate automatically deducted on seller orders</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4 pt-1">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Default Marketplace Commission (%)</label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.01" name="default_commission_rate" class="form-control rounded-start-3" value="{{ $settings['default_commission_rate'] ?? '10.00' }}" required>
                                <span class="input-group-text bg-light text-muted">%</span>
                            </div>
                            <div class="form-text small">Applied when individual seller or category commission is not customized.</div>
                        </div>
                        <div class="p-3 bg-light rounded-3 small text-muted">
                            <i class="bi bi-info-circle text-primary me-1"></i>
                            Individual sellers can be granted customized commission agreements from the <strong>Sellers &gt; View Profile</strong> admin panel.
                        </div>
                    </div>
                </div>

                <!-- Payments & Razorpay Configuration -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                    <div class="card-header bg-white border-0 py-3 px-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-info bg-opacity-10 text-info p-2 rounded-3 me-3">
                                <i class="bi bi-credit-card-2-front fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Payment Gateways</h6>
                                <small class="text-muted">Online checkout and Razorpay API credentials</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4 pt-1">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Razorpay Key ID</label>
                            <input type="text" name="razorpay_key_id" class="form-control form-control-sm font-monospace rounded-3" value="{{ $settings['razorpay_key_id'] ?? 'rzp_test_DhuriyaMartSandbox' }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Razorpay Key Secret</label>
                            <input type="password" name="razorpay_key_secret" class="form-control form-control-sm font-monospace rounded-3" value="{{ $settings['razorpay_key_secret'] ?? 'rzp_test_DhuriyaMartSecretKey' }}">
                        </div>

                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="paymentSandboxSwitch" name="payment_sandbox_mode" value="1" {{ ($settings['payment_sandbox_mode'] ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label small fw-semibold" for="paymentSandboxSwitch">Enable Sandbox / Mock Gateway Simulator</label>
                            <div class="form-text small text-muted">Allows instant testing of full payment lifecycle without live funds.</div>
                        </div>

                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="codEnabledSwitch" name="cod_enabled" value="1" {{ ($settings['cod_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label small fw-semibold" for="codEnabledSwitch">Accept Cash on Delivery (COD)</label>
                            <div class="form-text small text-muted">Customers can choose to pay cash upon order arrival.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mb-4">
            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="bi bi-save me-1"></i> Save Platform Settings
            </button>
        </div>
    </form>
</div>
@endsection

