<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="x-apple-disable-message-reformatting" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Password Reset Request</title>
    <style type="text/css" rel="stylesheet" media="all">
        @import url("https://fonts.googleapis.com/css?family=Nunito+Sans:400,700&display=swap");
    </style>
    <style type="text/css" rel="stylesheet" media="all">
        body {
            margin: 0;
            padding: 0;
            font-family: "Nunito Sans", Helvetica, Arial, sans-serif;
            background-color: #f4f7ff;
            color: #333;
            text-align: center;
        }
        .email-container {
            max-width: 480px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 30px;
            text-align: center;
        }
        .logo img {
            width: 150px;
            margin-bottom: 20px;
        }
        .email-header {
            font-size: 22px;
            font-weight: 700;
            color: #222;
            margin-bottom: 10px;
        }
        .email-content {
            font-size: 16px;
            color: #555;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .reset-code {
            font-size: 20px;
            font-weight: bold;
            background: #f4f7ff;
            padding: 12px;
            border-radius: 5px;
            display: inline-block;
            margin: 20px 0;
            color: #635ebe;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #635ebe, #4b49a3);
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 6px;
            text-decoration: none;
            margin-top: 20px;
            box-shadow: 0 4px 10px rgba(99, 94, 190, 0.3);
        }
        .cta-button:hover {
            background: linear-gradient(135deg, #5049a3, #3d388a);
        }
        .footer {
            font-size: 13px;
            color: #888;
            margin-top: 30px;
        }
        @media (max-width: 500px) {
            .email-container {
                width: 90%;
                padding: 20px;
            }
            .email-header {
                font-size: 20px;
            }
            .email-content {
                font-size: 14px;
            }
            .reset-code {
                font-size: 18px;
            }
            .cta-button {
                font-size: 14px;
                padding: 10px 18px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="logo">
            <img src="{{ env('APP_URL') }}/assets/images/web/logo.jpg" alt="Company Logo">
        </div>
        <div class="email-header">Reset Your Password</div>
        <p class="email-content">
            Hi {{ $details['user']->fname }} {{ $details['user']->lname }}, <br><br>
            We received a request to reset your password for <strong>{{ env('APP_NAME') }}</strong>.  
            Use the code below to proceed. If you did not request this, ignore this email.
        </p>
        <div class="reset-code">
            {{ $details['reset_link'] }}
        </div>
        <a href="{{ $details['reset_link'] }}" class="cta-button">Reset Password</a>
        <p class="email-content">For security reasons, do not share this code with anyone.</p>
        <div class="footer">
            <p>© {{ date('Y') }} {{ env('APP_NAME') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
