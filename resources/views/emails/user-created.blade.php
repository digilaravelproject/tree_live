<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Email</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }

        .email-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }

        .email-body {
            padding: 40px 30px;
            color: #333333;
            line-height: 1.6;
        }

        .welcome-text {
            font-size: 18px;
            margin-bottom: 20px;
            color: #555555;
        }

        .credentials-box {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }

        .credentials-box h3 {
            margin-top: 0;
            color: #667eea;
            font-size: 18px;
        }

        .credential-item {
            margin: 12px 0;
            padding: 10px;
            background-color: #ffffff;
            border-radius: 4px;
        }

        .credential-label {
            font-weight: 600;
            color: #666666;
            display: inline-block;
            width: 140px;
        }

        .credential-value {
            color: #333333;
            font-family: 'Courier New', monospace;
            background-color: #e9ecef;
            padding: 4px 8px;
            border-radius: 3px;
        }

        .password-highlight {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }

        .password-highlight strong {
            color: #856404;
        }

        .login-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 14px 35px;
            text-decoration: none;
            border-radius: 5px;
            margin: 25px 0;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.2s;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .info-section {
            background-color: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .email-footer {
            background-color: #f8f9fa;
            padding: 25px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            border-top: 1px solid #dee2e6;
        }

        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #dee2e6, transparent);
            margin: 30px 0;
        }

        .security-note {
            font-size: 13px;
            color: #dc3545;
            margin-top: 15px;
            padding: 10px;
            background-color: #f8d7da;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>🎉 Welcome Aboard!</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <p class="welcome-text">
                Dear <strong>{{ $user->name }}</strong>,
            </p>
            <p>
                Your account has been successfully created on <strong>{{ config('app.name') }}</strong>.
                We're excited to have you as part of our team!
            </p>

            <!-- Credentials Box -->
            <div class="credentials-box">
                <h3>📋 Your Account Details</h3>

                <div class="credential-item">
                    <span class="credential-label">Name:</span>
                    <span class="credential-value">{{ $user->name }}</span>
                </div>

                <div class="credential-item">
                    <span class="credential-label">Email:</span>
                    <span class="credential-value">{{ $user->email }}</span>
                </div>

                <div class="credential-item">
                    <span class="credential-label">Phone:</span>
                    <span class="credential-value">{{ $user->phone }}</span>
                </div>

                <div class="credential-item">
                    <span class="credential-label">Role:</span>
                    <span class="credential-value">{{ $roleName }}</span>
                </div>

                @if ($user->designation)
                    <div class="credential-item">
                        <span class="credential-label">Designation:</span>
                        <span class="credential-value">{{ $user->designation }}</span>
                    </div>
                @endif

                @if ($user->district_id)
                    <div class="credential-item">
                        <span class="credential-label">District:</span>
                        <span class="credential-value">{{ $user->district->name ?? 'N/A' }}</span>
                    </div>
                @endif
            </div>

            <!-- Password Section -->
            <div class="password-highlight">
                <strong>🔐 Your Temporary Password:</strong>
                <div style="margin-top: 10px; font-size: 20px; letter-spacing: 2px;">
                    <code
                        style="background-color: #fff; padding: 8px 15px; border-radius: 4px; border: 2px dashed #ffc107;">{{ $password }}</code>
                </div>
            </div>

            <div class="security-note">
                <strong>⚠️ Security Notice:</strong> Please change your password after your first login for security
                purposes.
            </div>

            <div class="divider"></div>

            <!-- Login Information -->
            <div class="info-section">
                <strong>🌐 Login URL:</strong><br>
                <a href="{{ url('/login') }}" style="color: #2196F3; text-decoration: none;">{{ url('/login') }}</a>
            </div>

            <center>
                <a href="{{ url('/login') }}" class="login-button">
                    Login to Your Account →
                </a>
            </center>

            <p style="margin-top: 30px; color: #666;">
                If you have any questions or need assistance, please don't hesitate to contact our support team.
            </p>

            <p style="margin-top: 20px;">
                Best regards,<br>
                <strong>{{ config('app.name') }} Team</strong>
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p style="margin: 5px 0;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p style="margin: 5px 0; font-size: 12px;">
                This is an automated email. Please do not reply to this message.
            </p>
        </div>
    </div>
</body>

</html>
