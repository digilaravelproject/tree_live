<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Password | Tree Expert</title>
</head>

<body style="margin:0; padding:0; background-color:#04011a; font-family: Arial, Helvetica, sans-serif;">

    <div
        style="max-width:600px; margin:40px auto; background-color:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.2);">

        <!-- Header Section -->
        <div style="background-color:#0c7a43; text-align:center; padding:20px;">
            <img src="{{ asset('assets/images/logo/1.png') }}" alt="Tree Expert Logo" width="150"
                style="display:block; margin:auto;">
            <h2 style="color:#fff; margin:15px 0 0; font-size:22px; font-weight:600;">Tree Expert</h2>
        </div>

        <!-- Body Section -->
        <div style="padding:30px; color:#333;">
            <h3 style="text-align:center; color:#0c7a43; margin-bottom:20px;">Reset Your Password</h3>

            <p style="font-size:15px; line-height:1.6; text-align:center;">
                Hi there,<br>
                You recently requested to reset your password for your <b>Tree Expert</b> account.
                Please use the OTP below to complete the reset process.
            </p>

            <!-- OTP Box -->
            <div
                style="background-color:#f1f9f3; border:2px dashed #0c7a43; padding:20px; text-align:center; margin:25px 0; border-radius:8px;">
                <span
                    style="font-size:30px; color:#0c7a43; font-weight:bold; letter-spacing:4px;">{{ $otp }}</span>
            </div>

            <p style="font-size:14px; color:#666; text-align:center;">
                This OTP is valid for <b>10 minutes</b>.<br>
                If you did not request this, please ignore this email.
            </p>

            <div style="text-align:center; margin-top:30px;">
                <a href="{{ url('/') }}"
                    style="background-color:#0c7a43; color:#fff; text-decoration:none; padding:10px 25px; border-radius:5px; font-weight:500;">Visit
                    Website</a>
            </div>
        </div>

        <!-- Footer Section -->
        <div style="background-color:#f9f9f9; text-align:center; padding:15px; border-top:1px solid #eee;">
            <p style="margin:0; color:#888; font-size:13px;">&copy; {{ date('Y') }} Tree Expert. All rights
                reserved.</p>
        </div>

    </div>

</body>

</html>
