<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Password Reset Request</title>
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
            background: linear-gradient(135deg, #D32F2F, #B71C1C);
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
            background: linear-gradient(135deg, #D32F2F, #B71C1C);
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
            background-color: #FFEBEE;
            border-left: 4px solid #F44336;
            padding: 12px 15px;
            margin: 20px 0;
            font-size: 14px;
        }

        .admin-notice {
            background-color: #E3F2FD;
            border-left: 4px solid #2196F3;
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
            <h1>🔐 Admin Password Reset</h1>
        </div>
        
        <div class="content">
            <p>Hello <?php echo htmlspecialchars($name); ?>,</p>
            
            <div class="admin-notice">
                <strong>Admin Account Notice:</strong> This is a password reset request for your administrator account on <?php echo SITE_NAME; ?>.
            </div>
            
            <p>We received a request to reset the password for your admin account (<?php echo htmlspecialchars($admin_email); ?>). To complete the process, please click the button below:</p>
            
            <div class="text-center">
                <a href="<?php echo htmlspecialchars($reset_link); ?>" class="button">🔓 Reset Admin Password</a>
            </div>
            
            <div class="security-notice">
                <strong>🚨 High Security Alert:</strong> 
                <ul style="margin: 5px 0; padding-left: 20px;">
                    <li>This link will expire in <strong>30 minutes</strong></li>
                    <li>Only use this link if you requested the password reset</li>
                    <li>If you didn't request this, contact the system administrator immediately</li>
                    <li>This affects an administrator account with elevated privileges</li>
                </ul>
            </div>
            
            <p><strong>Admin Security Guidelines:</strong></p>
            <ul>
                <li>🔒 Use a strong, unique password (minimum 12 characters)</li>
                <li>🎯 Include uppercase, lowercase, numbers, and special characters</li>
                <li>🚫 Never share your admin credentials with anyone</li>
                <li>⏰ Change your password regularly (every 90 days recommended)</li>
                <li>🔐 Enable two-factor authentication immediately after reset</li>
                <li>💻 Only access admin panel from secure, trusted devices</li>
                <li>🌐 Avoid using admin accounts on public networks</li>
            </ul>
            
            <p class="mt-20">If the button above doesn't work, copy and paste this link into your browser:</p>
            <p style="word-break: break-all; background-color: #f5f5f5; padding: 10px; border-radius: 4px; font-family: monospace; font-size: 12px;"><?php echo htmlspecialchars($reset_link); ?></p>
            <p class="mt-20">If you have any questions or need assistance, please contact our support team:</p>
            <ul>
                <li>Email: <a href="mailto:<?php echo SUPPORT_EMAIL; ?>"><?php echo SUPPORT_EMAIL; ?></a></li>
                <li>Phone: <?php echo SUPPORT_PHONE; ?></li>
            </ul>
        </div>
        <div class="footer">
            <p>&copy; <?php echo date("Y"); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            <p><small>This is an automated message. Please do not reply directly to this email.</small></p>
        </div>
    </div>
    <script>
        document.querySelector('.button').addEventListener('click', function(event) {
            if (!confirm('Are you sure you want to reset your password? This action cannot be undone.')) {
                event.preventDefault();
            }
        });
    </script>
</body>
</html>