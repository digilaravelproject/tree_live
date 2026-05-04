<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verify OTP</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .otp { font-size: 34px; font-weight: bold; background: #f6fff6; padding: 20px; border: 2px dashed #2d8f5a; display: inline-block; }
        .btn { display: inline-block; padding: 10px 16px; background: #2d8f5a; color: #fff; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Verify Your Email</h2>
    <p>Hi there,</p>
    <p>Use the OTP below to verify your email address and complete the process.</p>
    <p class="otp">{{ $otp }}</p>
    <p>This OTP is valid for 10 minutes. If you did not request this, please ignore this email.</p>
    <p><a class="btn" href="{{ url('/') }}">Visit Website</a></p>
</div>
</body>
</html>