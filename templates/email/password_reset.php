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
            <h1>Password Reset Request</h1>
        </div>
        
        <div class="content">
            <p>Hello <?php echo htmlspecialchars($name); ?>,</p>
            
            <p>We received a request to reset the password for your <?php echo SITE_NAME; ?> account. To complete the process, please click the button below:</p>
            
            <div class="text-center">
                <a href="<?php echo htmlspecialchars($reset_link); ?>" class="button">Reset Your Password</a>
            </div>
            
            <div class="security-notice">
                <strong>Security Notice:</strong> This link will expire in 1 hour. If you didn't request a password reset, please ignore this email or contact our support team immediately.
            </div>
            
            <p>For your security:</p>
            <ul>
                <li>Never share your password with anyone</li>
                <li>Create a strong, unique password that you don't use elsewhere</li>
                <li>Change your password periodically</li>
                <li>Enable two-factor authentication if available</li>
            </ul>
            
            <p class="mt-20">If you're having trouble with the button above, copy and paste this link into your browser:</p>
            <p><small><?php echo htmlspecialchars($reset_link); ?></small></p>
            
            <p class="mt-20">If you didn't request this change or need assistance, please contact our support team at <a href="mailto:<?php echo SUPPORT_EMAIL; ?>"><?php echo SUPPORT_EMAIL; ?></a> or call <?php echo SUPPORT_PHONE; ?>.</p>
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