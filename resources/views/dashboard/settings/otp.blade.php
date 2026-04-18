@extends('layouts.app')
@section('title')
| {{ $page_title }}
@endsection

@section('content')
<main>
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-12 ">
                <h4 class="main-title">OTP & SMS Settings</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="{{ route('home') }}" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone ph-house f-s-16"></i> Dashboard
                            </span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">OTP Settings</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <div class="row">
            <div class="col-12">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('admin.settings.otp.update') }}" method="POST" class="app-form">
                    @csrf
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>General Configuration</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Global OTP Login Enabled</label>
                                        <select name="global_otp_login_enabled" class="form-select">
                                            <option value="0" {{ ($settings['global_otp_login_enabled'] ?? '0') == '0' ? 'selected' : '' }}>Disabled (Standard Password Login)</option>
                                            <option value="1" {{ ($settings['global_otp_login_enabled'] ?? '0') == '1' ? 'selected' : '' }}>Enabled (OTP Required for Staff)</option>
                                        </select>
                                        <small class="text-muted">When enabled, all staff login attempts will require an OTP after password verification.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">SMS Provider</label>
                                        <select name="sms_provider" class="form-select" id="provider_selector">
                                            <option value="2factor" {{ ($settings['sms_provider'] ?? '2factor') == '2factor' ? 'selected' : '' }}>2Factor.in</option>
                                            <option value="vilpower" {{ ($settings['sms_provider'] ?? '') == 'vilpower' ? 'selected' : '' }}>Vilpower.in</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2Factor Settings -->
                    <div class="card provider-settings" id="settings_2factor">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">2Factor.in Credentials</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">API Key</label>
                                        <input type="text" name="two_factor_api_key" class="form-control" value="{{ $settings['two_factor_api_key'] ?? '' }}" placeholder="Enter 2Factor API Key">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Template Name (DLT)</label>
                                        <input type="text" name="two_factor_template" class="form-control" value="{{ $settings['two_factor_template'] ?? '' }}" placeholder="Enter Template Name">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">OTP Method</label>
                                        <select name="two_factor_method" class="form-select">
                                            <option value="SMS" {{ ($settings['two_factor_method'] ?? 'SMS') == 'SMS' ? 'selected' : '' }}>SMS (Text Message)</option>
                                            <option value="VOICE" {{ ($settings['two_factor_method'] ?? '') == 'VOICE' ? 'selected' : '' }}>VOICE (Call)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vilpower Settings -->
                    <div class="card provider-settings" id="settings_vilpower" style="display:none;">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Vilpower.in Credentials</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">API Key</label>
                                        <input type="text" name="vilpower_api_key" class="form-control" value="{{ $settings['vilpower_api_key'] ?? '' }}" placeholder="Enter Vilpower API Key">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Sender ID</label>
                                        <input type="text" name="vilpower_sender_id" class="form-control" value="{{ $settings['vilpower_sender_id'] ?? '' }}" placeholder="Enter Sender ID">
                                    </div>
                                </div>
                                <div class="col-md-6 mt-3">
                                    <div class="mb-3">
                                        <label class="form-label">Template ID (DLT)</label>
                                        <input type="text" name="vilpower_template_id" class="form-control" value="{{ $settings['vilpower_template_id'] ?? '' }}" placeholder="Enter Template ID">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary px-5">Save Settings</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selector = document.getElementById('provider_selector');
    const settings2Factor = document.getElementById('settings_2factor');
    const settingsVilpower = document.getElementById('settings_vilpower');

    function toggleProviders() {
        if (selector.value === '2factor') {
            settings2Factor.style.display = 'block';
            settingsVilpower.style.display = 'none';
        } else {
            settings2Factor.style.display = 'none';
            settingsVilpower.style.display = 'block';
        }
    }

    selector.addEventListener('change', toggleProviders);
    toggleProviders(); // Run on load
});
</script>
@endsection
