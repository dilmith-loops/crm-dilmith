<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset OTP</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f1f5f9; margin: 0; padding: 20px; color: #334155;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 550px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
        <!-- Header -->
        <tr>
            <td style="background: linear-gradient(135deg, #ff0878 0%, #8035ca 50%, #0057be 100%); padding: 30px 25px; text-align: center;">
                <div style="margin-bottom: 12px;">
                    <img src="{{ url('images/logo_loops_light.png') }}" alt="Loops Integrated" style="height: 48px; width: auto; display: inline-block;">
                </div>
                <h1 style="color: #ffffff; margin: 0; font-size: 20px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase;">
                    Password Reset Request
                </h1>
                <p style="color: rgba(255,255,255,0.85); margin: 6px 0 0 0; font-size: 12px;">
                    Loops Integrated System Security
                </p>
            </td>
        </tr>

        <!-- Content -->
        <tr>
            <td style="padding: 35px 30px;">
                <p style="font-size: 15px; font-weight: 600; color: #0f172a; margin-top: 0;">
                    Hello {{ $user->name }},
                </p>
                <p style="font-size: 14px; color: #475569; line-height: 1.6; margin-bottom: 25px;">
                    We received a request to reset your password for your Loops Integrated account. Use the 6-digit verification code below to proceed with resetting your password:
                </p>

                <!-- OTP Code Box -->
                <div style="background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 20px; text-align: center; margin-bottom: 25px;">
                    <span style="display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">
                        Your 6-Digit Verification Code (OTP)
                    </span>
                    <span style="font-family: 'Courier New', Courier, monospace; font-size: 36px; font-weight: 900; color: #8035ca; letter-spacing: 8px; display: inline-block;">
                        {{ $otp }}
                    </span>
                </div>

                <p style="font-size: 13px; color: #64748b; line-height: 1.5;">
                    <strong style="color: #e11d48;">Important:</strong> This verification code is valid for <strong>15 minutes</strong>. Please do not share this code with anyone.
                </p>
                
                <p style="font-size: 13px; color: #94a3b8; line-height: 1.5; margin-top: 20px; padding-top: 20px; border-top: 1px solid #f1f5f9;">
                    If you did not request a password reset, you can safely ignore this email. Your password will remain unchanged.
                </p>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background-color: #f8fafc; padding: 20px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 11px; color: #94a3b8;">
                &copy; {{ date('Y') }} Loops Integrated. All rights reserved.
            </td>
        </tr>
    </table>
</body>
</html>
