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
        
        /* Success box */
        .success-box {
            background-color: #E8F5E8;
            border-left: 4px solid #4CAF50;
            padding: 15px 20px;
            margin: 20px 0;
            font-size: 16px;
            border-radius: 4px;
        }
        
        /* Details box */
        .details {
            background-color: #F8F9FA;
            border: 1px solid #E9ECEF;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 6px;
        }
        
        .details p {
            margin: 8px 0;
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
            <h1>Money Received!</h1>
        </div>
        
        <div class="content">
            <p>Hello {{name}},</p>
            
            <div class="success-box">
                <p><strong>Great news!</strong> You've received <strong>KES {{amount}}</strong> from {{sender_name}}!</p>
            </div>
            
            <div class="details">
                <p><strong>Reference Number:</strong> TR{{transfer_id}}</p>
                <p><strong>Date Received:</strong> {{date}}</p>
                <p><strong>New Balance:</strong> KES {{balance}}</p>
            </div>
            
            <p>You can now use these funds for:</p>
            <ul>
                <li>Withdrawing to M-Pesa</li>
                <li>Sending to other <?php echo SITE_NAME; ?> users</li>
                <li>Paying for services</li>
                <li>Online shopping and bill payments</li>
            </ul>
            
            <div class="text-center">
                <a href="<?php echo BASE_URL; ?>/dashboard" class="button">View Dashboard</a>
            </div>
            
            <p class="mt-20">Thank you for using <?php echo SITE_NAME; ?>!</p>
            
            <p>If you have any questions about this transaction, please contact our support team at <a href="mailto:<?php echo SUPPORT_EMAIL; ?>"><?php echo SUPPORT_EMAIL; ?></a> or call <?php echo SUPPORT_PHONE; ?>.</p>
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