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
        
        /* Celebration style */
        .celebration {
            background-color: #E8F5E9;
            border-left: 4px solid #4CAF50;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        
        .bonus-amount {
            font-size: 24px;
            font-weight: bold;
            color: #2E7D32;
            margin: 10px 0;
        }
        
        /* Referral link */
        .referral-link-box {
            word-break: break-all;
            padding: 12px;
            background-color: #f5f5f5;
            border-radius: 4px;
            font-family: monospace;
            margin: 15px 0;
            border: 1px dashed #4CAF50;
        }
        
        /* Earnings summary */
        .earnings-summary {
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
            <h1>🎉 You've Earned a Referral Bonus! 🎉</h1>
        </div>
        
        <div class="content">
            <p>Hello <?php echo htmlspecialchars($name); ?>,</p>
            
            <div class="celebration">
                <p>Congratulations! Your referral was successful!</p>
                <div class="bonus-amount">KES <?php echo number_format($amount, 2); ?></div>
                <p>has been credited to your account for referring <strong><?php echo htmlspecialchars($referred_name); ?></strong></p>
            </div>
            
            <div class="earnings-summary">
                <h3>Your Referral Earnings Summary</h3>
                <p><strong>This referral:</strong> KES <?php echo number_format($amount, 2); ?></p>
                <p><strong>Total earnings:</strong> KES <?php echo number_format($total_earnings, 2); ?></p>
                <p><strong>Available for withdrawal:</strong> KES <?php echo number_format($withdrawable_amount, 2); ?></p>
            </div>
            
            <h3>Keep the Earnings Coming!</h3>
            <p>Share your unique referral link with more friends:</p>
            
            <div class="referral-link-box">
                <a href="<?php echo htmlspecialchars($referral_link); ?>"><?php echo htmlspecialchars($referral_link); ?></a>
            </div>
            
            <p>Pro tips to get more referrals:</p>
            <ul>
                <li>Share on WhatsApp groups and social media</li>
                <li>Explain how <?php echo SITE_NAME; ?> has helped you</li>
                <li>Offer to guide them through registration</li>
                <li>Post about your earnings (screenshots work well)</li>
            </ul>
            
            <div class="text-center">
                <a href="<?php echo htmlspecialchars(BASE_URL); ?>/dashboard" class="button">View Complete Earnings Report</a>
            </div>
            
            <p class="mt-20">Need help or have questions about withdrawals?</p>
            <p>Contact our support team:</p>
            <ul>
                <li>📧 <a href="mailto:<?php echo SUPPORT_EMAIL; ?>" style="color: #4CAF50;"><?php echo SUPPORT_EMAIL; ?></a></li>
                <li>📞 <?php echo SUPPORT_PHONE; ?></li>
                <li>💬 Live chat in your dashboard</li>
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