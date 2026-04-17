<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Tree Expert - OTP Verification">
    <link rel="icon" href="{{ asset('assets/images/logo/favicon.png') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('assets/images/logo/favicon.png') }}" type="image/x-icon">
    <title>Tree Expert | OTP Verification</title>

    <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome/css/all.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/vendor/tabler-icons/tabler-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}">
    
    <style>
        :root {
            --brand-primary: #14532d;
            --brand-secondary: #166534;
            --brand-accent: #22c55e;
            --text-main: #0f172a;
            --text-secondary: #64748b;
            --input-bg: #f8fafc;
            --input-border: #e2e8f0;
        }

        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Manrope', sans-serif;
            overflow: hidden; 
            background-color: #ffffff;
        }

        .login-wrapper {
            height: 100vh;
            width: 100%;
            display: flex;
            overflow: hidden;
        }

        .visual-side {
            display: none;
            width: 50%;
            height: 100%;
            background: linear-gradient(135deg, var(--brand-primary) 0%, #064e3b 100%);
            position: relative;
            overflow: hidden;
            color: white;
            padding: 4rem;
            flex-direction: column;
            justify-content: space-between;
        }

        .form-side {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: #ffffff;
            position: relative;
        }

        .form-container {
            width: 100%;
            max-width: 400px;
            animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .logo-box {
            margin-bottom: 2rem;
            display: flex;
            justify-content: center;
        }
        
        .logo-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid var(--brand-accent);
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .logo-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .form-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .form-subtitle {
            color: var(--text-secondary);
            margin-bottom: 2.5rem;
            font-size: 0.95rem;
            text-align: center;
        }

        .otp-input {
            height: 60px;
            font-size: 1.5rem;
            text-align: center;
            letter-spacing: 0.5rem;
            border-radius: 12px;
            border: 2px solid var(--input-border);
            font-weight: 700;
        }

        .otp-input:focus {
            border-color: var(--brand-accent);
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.1);
        }

        .btn-primary {
            height: 54px;
            background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-secondary) 100%);
            border: none;
            border-radius: 12px;
            font-weight: 700;
        }

        .resend-link {
            color: var(--brand-secondary);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (min-width: 992px) {
            .visual-side { display: flex; }
            .form-side { width: 50%; }
        }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <div class="visual-side">
            <div class="visual-content">
                <h1 class="visual-heading" style="font-size: 3rem; font-weight: 800;">Secure<br>Access.</h1>
                <p class="visual-text" style="font-size: 1.1rem; opacity: 0.85;">We've sent a 6-digit verification code to your registered mobile number.</p>
            </div>
        </div>

        <div class="form-side">
            <div class="form-container">
                <div class="logo-box">
                    <div class="logo-circle">
                        <img src="{{ asset('assets/images/logo/1.png') }}" alt="Tree Expert Logo">
                    </div>
                </div>

                <h3 class="form-title">Verify OTP</h3>
                <p class="form-subtitle">Enter the 6-digit code to continue.</p>

                <form action="{{ route('login.otp.verify.store') }}" method="POST">
                    @csrf
                    @if ($errors->any())
                        <div class="alert alert-danger mb-4" style="border-radius: 12px;">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success mb-4" style="border-radius: 12px;">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="mb-5">
                        <input type="text" name="otp" class="form-control otp-input" placeholder="000000" maxlength="6" autofocus required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-4">
                        Verify & Login <i class="ti ti-shield-check ms-2"></i>
                    </button>

                    <p class="text-center small text-secondary">
                        Didn't receive code? 
                        <span id="resend-btn" class="resend-link">Resend OTP</span>
                    </p>
                    
                    <p class="text-center mt-3">
                        <a href="{{ route('login') }}" class="text-decoration-none small fw-bold text-muted">Back to Login</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#resend-btn').on('click', function() {
                const $btn = $(this);
                if ($btn.hasClass('disabled')) return;

                $btn.addClass('disabled').text('Sending...');

                $.post("{{ route('login.otp.resend') }}", {
                    _token: "{{ csrf_token() }}"
                })
                .done(function(data) {
                    alert('OTP has been resent to your mobile.');
                    startTimer(60);
                })
                .fail(function() {
                    alert('Failed to resend OTP. Please try again.');
                    $btn.removeClass('disabled').text('Resend OTP');
                });
            });

            function startTimer(duration) {
                let timer = duration, seconds;
                const interval = setInterval(function () {
                    seconds = parseInt(timer % 60, 10);
                    $('#resend-btn').text('Resend available in ' + seconds + 's');

                    if (--timer < 0) {
                        clearInterval(interval);
                        $('#resend-btn').removeClass('disabled').text('Resend OTP');
                    }
                }, 1000);
            }
        });
    </script>
</body>
</html>
