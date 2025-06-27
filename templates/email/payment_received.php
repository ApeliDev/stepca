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
        .highlight {
            background-color: #E8F5E9;
            border-left: 4px solid #4CAF50;
            padding: 15px;
            margin: 20px 0;
        }
        
        /* Referral link */
        .referral-link {
            word-break: break-all;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 4px;
            font-family: monospace;
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
            <!-- Replace with your actual logo URL -->
            <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="<?php echo SITE_NAME; ?> Logo" class="logo">
            <h1>Payment Received!</h1>
        </div>
        
        <div class="content">
            <p>Hello <?php echo htmlspecialchars($name); ?>,</p>
            
            <div class="highlight">
                <p>We've successfully received your payment of <strong>KES <?php echo number_format($amount, 2); ?></strong>.</p>
                <p>Your account is now <strong>fully activated</strong> and ready to earn!</p>
            </div>
            
            <h3>Start Earning Referral Bonuses</h3>
            <p>Share your unique referral link and earn <strong>KES <?php echo REFERRAL_BONUS; ?></strong> for each successful registration:</p>
            
            <div class="referral-link">
                <a href="<?php echo htmlspecialchars($referral_link); ?>"><?php echo htmlspecialchars($referral_link); ?></a>
            </div>
            
            <p class="mt-20">Pro tips to maximize your earnings:</p>
            <ul>
                <li>Share on social media platforms</li>
                <li>Include in your email signature</li>
                <li>Create content about your experience</li>
                <li>Track your referrals in your dashboard</li>
            </ul>
            
            <div class="text-center">
                <a href="<?php echo BASE_URL; ?>/dashboard" class="button">Access Your Dashboard</a>
            </div>
            
            <p class="mt-20">Need help or have questions about the referral program?</p>
            <p>Contact our support team:</p>
            <ul>
                <li>Email: <a href="mailto:<?php echo SUPPORT_EMAIL; ?>" style="color: #4CAF50;"><?php echo SUPPORT_EMAIL; ?></a></li>
                <li>Phone: <?php echo SUPPORT_PHONE; ?></li>
                <li>Live chat: Available 24/7 in your dashboard</li>
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