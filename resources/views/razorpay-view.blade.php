@extends('layouts.app')

@section('title')
    | Contribute & Plant Trees
@endsection

@section('content')
    <main>
        <div class="container-fluid">

            <div class="row mb-4 mt-3">
                <div class="col-12 text-center">
                    <h3 class="fw-bold text-primary">Choose Your Contribution Plan</h3>
                    <p class="text-secondary">Select a plan to support projects and plant trees. Every donation counts!</p>

                    @if ($message = Session::get('success'))
                        <div class="alert alert-success alert-dismissible fade show d-inline-block px-5" role="alert">
                            <i class="ph-bold ph-check-circle me-2"></i> <strong>Success!</strong> {{ $message }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($message = Session::get('error'))
                        <div class="alert alert-danger alert-dismissible fade show d-inline-block px-5" role="alert">
                            <i class="ph-bold ph-warning-circle me-2"></i> <strong>Error!</strong> {{ $message }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                </div>
            </div>

            @php
                $plans = [
                    ['amount' => 10, 'projects' => 1, 'trees' => 1, 'color' => 'primary', 'name' => 'Starter'],
                    ['amount' => 50, 'projects' => 3, 'trees' => 5, 'color' => 'success', 'name' => 'Green Hand'],
                    ['amount' => 100, 'projects' => 5, 'trees' => 10, 'color' => 'info', 'name' => 'Supporter'],
                    ['amount' => 200, 'projects' => 10, 'trees' => 25, 'color' => 'warning', 'name' => 'Contributor'],
                    ['amount' => 500, 'projects' => 20, 'trees' => 60, 'color' => 'danger', 'name' => 'Changemaker'],
                    [
                        'amount' => 1000,
                        'projects' => 50,
                        'trees' => 120,
                        'color' => 'secondary',
                        'name' => 'Bronze Tier',
                    ],
                    ['amount' => 2000, 'projects' => 100, 'trees' => 250, 'color' => 'dark', 'name' => 'Silver Tier'],
                    ['amount' => 5000, 'projects' => 250, 'trees' => 600, 'color' => 'primary', 'name' => 'Gold Tier'],
                    [
                        'amount' => 10000,
                        'projects' => 500,
                        'trees' => 1500,
                        'color' => 'success',
                        'name' => 'Platinum Hero',
                    ],
                ];
            @endphp

            <div class="row">
                @foreach ($plans as $plan)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm border-0 hover-card">
                            <div class="card-header bg-white border-0 pt-4 text-center">
                                <span
                                    class="badge bg-{{ $plan['color'] }} bg-opacity-10 text-{{ $plan['color'] }} px-3 py-2 rounded-pill fw-bold f-s-14">
                                    {{ $plan['name'] }}
                                </span>
                                <h2 class="mt-3 text-dark fw-bold">₹{{ number_format($plan['amount']) }}</h2>
                            </div>

                            <div class="card-body text-center">
                                <div class="d-flex justify-content-center gap-4 mb-4">
                                    <div class="text-center">
                                        <span
                                            class="bg-primary h-40 w-40 d-flex-center b-r-15 f-s-18 mx-auto mb-2 text-white">
                                            <i class="ph-bold ph-map-pin-line"></i>
                                        </span>
                                        <h5 class="mb-0 fw-bold">{{ $plan['projects'] }}</h5>
                                        <small class="text-muted">Projects</small>
                                    </div>

                                    <div class="vr opacity-25"></div>

                                    <div class="text-center">
                                        <span
                                            class="bg-success h-40 w-40 d-flex-center b-r-15 f-s-18 mx-auto mb-2 text-white">
                                            <i class="ph-bold ph-tree-structure"></i>
                                        </span>
                                        <h5 class="mb-0 fw-bold">{{ $plan['trees'] }}</h5>
                                        <small class="text-muted">Trees</small>
                                    </div>
                                </div>

                                <hr class="text-muted opacity-10">

                                <form action="{{ route('razorpay.payment.store') }}" method="POST">
                                    @csrf

                                    <input type="hidden" name="project_count" value="{{ $plan['projects'] }}">
                                    <input type="hidden" name="tree_count" value="{{ $plan['trees'] }}">

                                    <script src="https://checkout.razorpay.com/v1/checkout.js" data-key="{{ env('RAZORPAY_KEY') }}"
                                        data-amount="{{ $plan['amount'] * 100 }}" data-currency="INR" data-buttontext="Donate ₹{{ $plan['amount'] }}"
                                        data-name="Tree Plantation" data-description="{{ $plan['name'] }} Plan"
                                        data-image="https://cdn.iconscout.com/icon/free/png-256/free-tree-1663-461421.png" {{--
                                            LOGIC: Controller se bheje gaye $user variable ko check karo.
                                            Agar $user exist karta hai (Login hai) to details prefill karo,
                                            nahi to 'Guest' values dalo.
                                        --}}
                                        data-prefill.name="{{ $user ? $user->name : 'Guest User' }}"
                                        data-prefill.email="{{ $user ? $user->email : 'guest@example.com' }}"
                                        data-prefill.contact="{{ $user ? $user->phone ?? '' : '' }}"
                                        data-theme.color="#{{ $plan['color'] == 'warning' ? 'ffc107' : ($plan['color'] == 'danger' ? 'dc3545' : '3b5de7') }}">
                                    </script>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </main>

    <style>
        .razorpay-payment-button {
            width: 100%;
            padding: 12px;
            background-color: #3b5de7;
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .razorpay-payment-button:hover {
            background-color: #2a4ac8;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 93, 231, 0.3);
        }

        .hover-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hover-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05) !important;
        }

        .h-40 {
            height: 40px;
        }

        .w-40 {
            width: 40px;
        }

        .d-flex-center {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .b-r-15 {
            border-radius: 15px;
        }
    </style>
@endsection
