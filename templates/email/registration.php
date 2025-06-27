<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($subject); ?></title>
    <style>
        /* Base styles */
        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
        }
        
        /* Container */
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #4CAF50, #2E7D32);
            color: white;
            padding: 25px 20px;
            text-align: center;
        }
        
        .logo {
            max-width: 180px;
            height: auto;
            margin-bottom: 15px;
        }
        
        /* Content */
        .content {
            padding: 25px 30px;
        }
        
        /* Footer */
        .footer {
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #777777;
            background-color: #f9f9f9;
            border-top: 1px solid #eeeeee;
        }
        
        /* Button */
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #4CAF50, #2E7D32);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
        }
        
        /* Highlight box */
        .activation-box {
            background-color: #E8F5E9;
            border-left: 4px solid #4CAF50;
            padding: 15px;
            margin: 20px 0;
        }
        
        /* Benefits list */
        .benefits {
            background-color: #FFF8E1;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        
        /* Utility classes */
        .text-center { text-align: center; }
        .mt-20 { margin-top: 20px; }
        .mb-20 { margin-bottom: 20px; }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="<?php echo SITE_NAME; ?> Logo" class="logo">
            <h1>Welcome to <?php echo SITE_NAME; ?>! 🎉</h1>
        </div>
        
        <div class="content">
            <p>Hello <?php echo htmlspecialchars($name); ?>,</p>
            
            <p>We're thrilled to have you join the <?php echo SITE_NAME; ?> community! Your account has been successfully created.</p>
            
            <div class="activation-box">
                <h3>One Final Step to Activate Your Account</h3>
                <p>To unlock all features and start earning, please complete your registration by making a one-time payment of:</p>
                <p class="text-center" style="font-size: 24px; font-weight: bold; color: #2E7D32;">KES 500</p>
            </div>
            
            <div class="text-center">
                <a href="<?php echo htmlspecialchars(BASE_URL); ?>/payment" class="button">Complete Registration Now</a>
            </div>
            
            <div class="benefits">
                <h3>Here's what you'll get when you activate:</h3>
                <ul>
                    <li>💰 <strong>Earn KES 200</strong> for every successful referral</li>
                    <li>🚀 <strong>Instant access</strong> to your referral dashboard</li>
                    <li>📈 <strong>Real-time tracking</strong> of your earnings</li>
                    <li>💳 <strong>Multiple withdrawal options</strong> for your earnings</li>
                    <li>🎁 <strong>Exclusive bonuses</strong> for top performers</li>
                </ul>
            </div>
            
            <h3>How It Works:</h3>
            <ol>
                <li>Complete your registration (KES 500 one-time payment)</li>
                <li>Get your unique referral link</li>
                <li>Share with friends and earn KES 200 per referral</li>
                <li>Withdraw your earnings anytime</li>
            </ol>
            
            <p class="mt-20">Need help or have questions?</p>
            <p>Our support team is ready to assist you:</p>
            <ul>
                <li>📧 <a href="mailto:<?php echo SUPPORT_EMAIL; ?>" style="color: #4CAF50;"><?php echo SUPPORT_EMAIL; ?></a></li>
                <li>📞 <?php echo htmlspecialchars(SUPPORT_PHONE); ?></li>
                <li>💬 Live chat available on our website</li>
            </ul>
        </div>
        
        <div class="footer">
            <p>&copy; <?php echo SITE_NAME; ?>. All rights reserved.</p>
            <p>
                <a href="<?php echo PRIVACY_POLICY_URL; ?>" style="color: #4CAF50; text-decoration: none;">Privacy Policy</a> | 
                <a href="<?php echo TERMS_OF_SERVICE_URL; ?>" style="color: #4CAF50; text-decoration: none;">Terms of Service</a> |
                <a href="<?php echo SECURITY_TIPS_URL; ?>" style="color: #4CAF50; text-decoration: none;">Security Tips</a>
            </p>
            <p><?php echo COMPANY_ADDRESS; ?></p>
        </div>
    </div>
</body>
</html>