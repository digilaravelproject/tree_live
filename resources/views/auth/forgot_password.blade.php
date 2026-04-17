<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Tree Expert - Forgot Password">
    <meta name="author" content="la-themes">
    <link rel="icon" href="{{ asset('assets/images/logo/favicon.png') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('assets/images/logo/favicon.png') }}" type="image/x-icon">
    <title>Tree Expert | Forgot Password</title>

    <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome/css/all.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/vendor/tabler-icons/tabler-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}">
    
    <style>
        :root {
            --brand-primary: #14532d; /* Deep Forest Green */
            --brand-secondary: #166534; /* Lighter Green */
            --brand-accent: #22c55e; /* Vibrant Green */
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

        /* LEFT SIDE - VISUAL */
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

        .visual-side::before {
            content: '';
            position: absolute;
            top: -10%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
            border-radius: 50%;
        }

        .visual-content {
            position: relative;
            z-index: 2;
        }

        .visual-heading {
            font-size: 3rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            letter-spacing: -0.02em;
        }

        .visual-text {
            font-size: 1.1rem;
            opacity: 0.85;
            line-height: 1.6;
            max-width: 80%;
        }

        .glass-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 2rem;
        }

        /* RIGHT SIDE - FORM */
        .form-side {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: #ffffff;
            position: relative;
            overflow-y: auto;
        }
        
        @media (max-width: 991.98px) {
            .form-side {
                background: radial-gradient(circle at 50% 0%, #f0fdf4 0%, #ffffff 50%);
            }
        }
        
        .form-side::-webkit-scrollbar { display: none; }
        .form-side { -ms-overflow-style: none; scrollbar-width: none; }

        .form-container {
            width: 100%;
            max-width: 400px;
            animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* CENTERED CIRCULAR LOGO */
        .logo-box {
            margin-bottom: 2rem;
            display: flex;
            justify-content: center; /* Always Center */
            text-align: center;
        }
        
        .logo-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 3px solid var(--brand-accent);
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }

        .logo-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .logo-circle:hover {
            transform: scale(1.05);
            border-color: var(--brand-primary);
        }

        .form-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 0.5rem;
            text-align: center; /* Always Center */
        }

        .form-subtitle {
            color: var(--text-secondary);
            margin-bottom: 2.5rem;
            font-size: 0.95rem;
            text-align: center; /* Always Center */
        }

        /* Inputs & Buttons */
        .input-group-text {
            background: transparent;
            border-right: none;
            border-color: var(--input-border);
            color: var(--text-secondary);
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }

        .form-control {
            height: 54px;
            border-left: none;
            border-color: var(--input-border);
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control {
            border-color: var(--brand-secondary);
        }

        .btn-primary {
            height: 54px;
            background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-secondary) 100%);
            border: none;
            border-radius: 12px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(20, 83, 45, 0.15);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(20, 83, 45, 0.25);
        }

        .back-link {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            transition: color 0.2s;
        }
        
        .back-link:hover {
            color: var(--brand-primary);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Desktop Adjustments */
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
                <div class="glass-badge">
                    <i class="fas fa-shield-alt me-2"></i> Account Security
                </div>
                <h1 class="visual-heading">Lost your key?<br>Let's recover it.</h1>
                <p class="visual-text">Enter your email details below to securely recover access to your Tree Expert dashboard.</p>
            </div>
            <div class="visual-footer">
                <small style="opacity: 0.7;">&copy; {{ date('Y') }} Tree Expert. All rights reserved.</small>
            </div>
        </div>

        <div class="form-side">
            <div class="form-container">
                <div class="logo-box">
                    <a href="/" class="logo-circle">
                        <img src="{{ asset('assets/images/logo/1.png') }}" alt="Tree Expert Logo">
                    </a>
                </div>

                <h3 class="form-title">Forgot Password?</h3>
                <p class="form-subtitle">Enter your registered email to receive an OTP.</p>

                <form action="{{ route('admin.send.otp') }}" method="POST">
                    @csrf
                    @if ($errors->any())
                        <div class="alert alert-danger mb-4" style="border-radius: 12px;">
                            <i class="fas fa-exclamation-circle me-2"></i> {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <label class="form-label fw-bold small ms-1">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-mail"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="admin@treeexpert.com" required autofocus>
                        </div>
                        <div class="form-text mt-2 text-muted small ms-1">We'll send a verification code to this email.</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-4">
                        Send OTP <i class="ti ti-arrow-right ms-2"></i>
                    </button>

                    <div class="text-center">
                        <a href="{{ route('login') }}" class="back-link">
                            <i class="ti ti-arrow-left me-2"></i> Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/jquery-3.6.3.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body>

</html>