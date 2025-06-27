<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($subject); ?> -<?php echo SITE_NAME; ?> Admin Alert</title>
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
        
        /* Header - Red for alerts */
        .header {
            background: linear-gradient(135deg, #f44336, #d32f2f);
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
            background: linear-gradient(135deg, #f44336, #d32f2f);
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
        
        /* Alert notice */
        .alert-notice {
            background-color: #FFEBEE;
            border-left: 4px solid #f44336;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 6px;
        }
        
        /* System info */
        .system-info {
            background-color: #F3F4F6;
            border-left: 4px solid #6B7280;
            padding: 12px 15px;
            margin: 20px 0;
            font-size: 14px;
            border-radius: 6px;
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
            <h1>Admin Alert</h1>
        </div>
        
        <div class="content">
            <div class="alert-notice">
                <h3><?php echo htmlspecialchars($subject); ?></h3>
            </div>
            
            <p><?php echo nl2br(htmlspecialchars($message)); ?></p>
            
            <div class="system-info">
                <strong>System Information:</strong> This is an automated notification from the <?php echo SITE_NAME; ?> system generated on <?php echo date('Y-m-d H:i:s'); ?>.
            </div>
            
            <p class="mt-20">Please review this alert and take appropriate action if necessary.</p>
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