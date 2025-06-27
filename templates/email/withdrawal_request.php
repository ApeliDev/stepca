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
        
        /* Utility classes */
        .text-center { text-align: center; }
        .mt-20 { margin-top: 20px; }
        .mb-20 { margin-bottom: 20px; }
        
        /* Highlight box */
        .highlight {
            background-color: #F3F4F6;
            border-left: 4px solid #6B7280;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 6px;
        }
        
        .highlight p {
            margin: 8px 0;
        }
        
        /* Security notice */
        .security-notice {
            background-color: #FFF8E1;
            border-left: 4px solid #FFC107;
            padding: 12px 15px;
            margin: 20px 0;
            font-size: 14px;
        }
        
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
            <h1>Withdrawal Request Received</h1>
        </div>
        
        <div class="content">
            <p>Hello {{name}},</p>
            
            <p>We've received your withdrawal request for <strong>KES {{amount}}</strong>.</p>
            
            <div class="highlight">
                <p><strong>Reference Number:</strong> WD{{withdrawal_id}}</p>
                <p><strong>Date Submitted:</strong> {{date}}</p>
            </div>
            
            <p>Your request is now being processed. Typically, withdrawals are completed within 1-24 hours.</p>
            
            <p>You'll receive another notification once your withdrawal has been processed.</p>
            
            <div class="security-notice">
                <strong>Security Notice:</strong> If you didn't initiate this withdrawal or have any questions, please contact our support team immediately at <?php echo SUPPORT_PHONE; ?> or reply to this email.
            </div>
            
            <p class="mt-20">Thank you for using <?php echo SITE_NAME; ?>!</p>
        </div>
        
        <div class="footer">
            <p>&copy; <?php echo SITE_NAME; ?>. All rights reserved.</p>
            <p>
                <a href="<?php echo PRIVACY_POLICY_URL; ?>" style="color: #4CAF50; text-decoration: none;">Privacy Policy</a> | 
                <a href="<?php echo TERMS_OF_SERVICE_URL; ?>" style="color: #4CAF50; text-decoration: none;">Terms of Service</a> |
                <a href="<?php echo SECURITY_TIPS_URL; ?>" style="color: #4CAF50; text-decoration: none;">Security Tips</a>
            </p>
            <p><?php echo COMPANY_ADDRESS; ?></p>
            <p>
                <a href="<?php echo TWITTER_URL; ?>" style="color: #4CAF50; text-decoration: none;">Twitter</a> | 
                <a href="<?php echo FACEBOOK_URL; ?>" style="color: #4CAF50; text-decoration: none;">Facebook</a> | 
                <a href="<?php echo LINKEDIN_URL; ?>" style="color: #4CAF50; text-decoration: none;">LinkedIn</a> |
                <a href="<?php echo INSTAGRAM_URL; ?>" style="color: #4CAF50; text-decoration: none;">Instagram</a> 
            </p>
        </div>
    </div>
</body>
</html>