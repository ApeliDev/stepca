<?php
/**
 * Auth Header Component
 * Renders the common HTML head section for authentication pages
 * 
 * @param string $pageTitle - Specific page title (e.g., "Login", "Register")
 * @param string $pageDescription - Optional custom description
 */
function renderAuthHeader($pageTitle = '', $pageDescription = null) {
    $defaultDescription = "Join Stepcashier and start earning money through affiliate referrals today. Enjoy direct M-Pesa withdrawals with a minimum of just KES 100. Simple, fast, and reliable earning platform in Kenya.";
    $description = $pageDescription ?: $defaultDescription;
    $fullTitle = $pageTitle ? "Stepcashier - $pageTitle | Earn Money Through Affiliate Referrals" : "Stepcashier - Earn Money Through Affiliate Referrals | Direct M-Pesa Withdrawals";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Primary SEO Meta Tags -->
    <title><?php echo htmlspecialchars($fullTitle); ?></title>
    <meta name="title" content="<?php echo htmlspecialchars($fullTitle); ?>">
    <meta name="description" content="<?php echo htmlspecialchars($description); ?>">
    <meta name="keywords" content="affiliate marketing Kenya, M-Pesa withdrawals, earn money online Kenya, referral program, affiliate commissions, online earning platform, make money Kenya, direct withdrawals">
    <meta name="author" content="Stepcashier">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://stepcashier.com">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://stepcashier.com">
    <meta property="og:title" content="<?php echo htmlspecialchars($fullTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($description); ?>">
    <meta property="og:image" content="https://stepcashier.com/assets/images/stepcashier-social-preview.jpg">
    <meta property="og:site_name" content="Stepcashier">
    <meta property="og:locale" content="en_KE">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://stepcashier.com">
    <meta property="twitter:title" content="<?php echo htmlspecialchars($fullTitle); ?>">
    <meta property="twitter:description" content="<?php echo htmlspecialchars($description); ?>">
    <meta property="twitter:image" content="https://stepcashier.com/assets/images/stepcashier-social-preview.jpg">

    <!-- Additional SEO Meta Tags -->
    <meta name="google-site-verification" content="your-google-site-verification-code">
    <meta name="theme-color" content="#4CAF50">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Stepcashier">

    <!-- Geo Tags for Kenya -->
    <meta name="geo.region" content="KE">
    <meta name="geo.country" content="Kenya">
    <meta name="geo.placename" content="Nairobi">

    <!-- Language and Location -->
    <meta http-equiv="content-language" content="en-KE">
    <link rel="alternate" hreflang="en-ke" href="https://stepcashier.com">
    <link rel="alternate" hreflang="sw-ke" href="https://stepcashier.com/sw">

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">

    <!-- Preconnect for Performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">

    <!-- External Stylesheets -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Stepcashier",
        "description": "Affiliate marketing platform with direct M-Pesa withdrawals in Kenya",
        "url": "https://stepcashier.com",
        "logo": "https://stepcashier.com/assets/images/logo.png",
        "sameAs": [
            "https://twitter.com/stepcashier",
            "https://facebook.com/stepcashier"
        ],
        "address": {
            "@type": "PostalAddress",
            "addressCountry": "Kenya",
            "addressRegion": "Nairobi"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "customer service",
            "availableLanguage": ["English", "Swahili"]
        }
    }
    </script>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "Stepcashier",
        "description": "Earn money through affiliate referrals with direct M-Pesa withdrawals",
        "url": "https://stepcashier.com",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "https://stepcashier.com/search?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Stepcashier Affiliate Program",
        "description": "Earn money through affiliate referrals with minimum withdrawal of KES 100 via M-Pesa",
        "provider": {
            "@type": "Organization",
            "name": "Stepcashier"
        },
        "areaServed": {
            "@type": "Country",
            "name": "Kenya"
        },
        "serviceType": "Affiliate Marketing Platform",
        "offers": {
            "@type": "Offer",
            "description": "Direct M-Pesa withdrawals starting from KES 100",
            "priceCurrency": "KES",
            "price": "0",
            "priceSpecification": {
                "@type": "PriceSpecification",
                "minPrice": "100",
                "priceCurrency": "KES",
                "description": "Minimum withdrawal amount"
            }
        }
    }
    </script>

    <!-- Custom Tailwind Configuration -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4CAF50',
                        primaryDark: '#45a049',
                        dark: '#0f0f23',
                        darker: '#1a1a2e',
                        darkest: '#16213e',
                        lightGray: '#9CA3AF',
                        lighterGray: '#D1D5DB',
                    },
                    animation: {
                        float: 'float 6s ease-in-out infinite',
                        slideIn: 'slideIn 0.3s ease-out',
                        pulse: 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px) rotate(0deg)' },
                            '33%': { transform: 'translateY(-20px) rotate(5deg)' },
                            '66%': { transform: 'translateY(10px) rotate(-3deg)' },
                        },
                        slideIn: {
                            'from': { opacity: '0', transform: 'translateY(-10px)' },
                            'to': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>

    <!-- Performance and Analytics -->
    <link rel="dns-prefetch" href="//www.google-analytics.com">
    
    <!-- Google Analytics (replace with your GA4 measurement ID) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'GA_MEASUREMENT_ID');
    </script>
</head>
<?php
}
?>