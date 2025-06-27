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
        
        /* Status indicators */
        .success {
            color: #4CAF50;
            font-weight: 600;
            background-color: #E8F5E9;
            padding: 12px 15px;
            border-left: 4px solid #4CAF50;
            margin: 20px 0;
        }
        
        .warning {
            color: #FF5722;
            font-weight: 600;
            background-color: #FFF3E0;
            padding: 12px 15px;
            border-left: 4px solid #FF5722;
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
            <!-- Replace with your actual logo URL -->
            <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="<?php echo SITE_NAME; ?> Logo" class="logo">
            <h1>Password Changed Successfully</h1>
        </div>
        
        <div class="content">
            <p>Hello <?php echo htmlspecialchars($name); ?>,</p>
            
            <div class="success">
                <p>Your <?php echo SITE_NAME; ?> account password has been successfully updated.</p>
            </div>
            
            <p>If you made this change, no further action is required. If you didn't change your password, please take immediate action:</p>
            
            <ol>
                <li>Reset your password immediately using our <a href="<?php echo BASE_URL; ?>/forgot-password" style="color: #4CAF50;">password reset</a> option</li>
                <li>Review your recent account activity for anything suspicious</li>
                <li>Contact our security team if you notice unauthorized changes</li>
            </ol>
            
            <div class="warning">
                <p><strong>Security reminder:</strong> Never share your password with anyone. <?php echo SITE_NAME; ?> staff will never ask for your password.</p>
            </div>
            
            <p class="mt-20">If you have any questions or need assistance, please contact our support team:</p>
            <ul>
                <li>Email: <a href="mailto:<?php echo SUPPORT_EMAIL; ?>" style="color: #4CAF50;"><?php echo SUPPORT_EMAIL; ?></a></li>
                <li>Phone: <?php echo SUPPORT_PHONE; ?></li>
                <li>Live chat: Available on our website</li>
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