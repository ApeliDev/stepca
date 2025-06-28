<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stepcashier - Merchant Services & Crypto Loading | Dollar Exchange</title>
    <meta name="description" content="Stepcashier provides merchant services, dollar exchange, and crypto loading solutions for businesses and agents in Kenya.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        }
        .service-card {
            transition: all 0.3s ease;
            border: 1px solid rgba(76, 175, 80, 0.2);
        }
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(76, 175, 80, 0.1);
            border-color: rgba(76, 175, 80, 0.5);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen text-white">
    <!-- Navigation -->
    <nav class="container mx-auto px-4 py-6 flex justify-between items-center">
        <div class="flex items-center">
            <i class="fas fa-wallet text-3xl text-green-500 mr-2"></i>
            <span class="text-2xl font-bold">Step<span class="text-green-500">cashier</span></span>
        </div>
        <div class="hidden md:flex space-x-6">
            <a href="#" class="hover:text-green-400">Home</a>
            <a href="#services" class="hover:text-green-400">Services</a>
            <a href="#exchange" class="hover:text-green-400">Dollar Exchange</a>
            <a href="#crypto" class="hover:text-green-400">Crypto Loading</a>
            <a href="#faq" class="hover:text-green-400">FAQ</a>
        </div>
        <div class="flex space-x-4">
            <a href="login.php" class="px-4 py-2 rounded hover:bg-green-600 transition">Login</a>
            <a href="register.php" class="px-4 py-2 bg-green-500 rounded hover:bg-green-600 transition">Register</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="container mx-auto px-4 py-16 md:py-24">
        <div class="flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 mb-12 md:mb-0">
                <h1 class="text-4xl md:text-5xl font-bold mb-6">
                    Merchant Services & <span class="text-green-500">Dollar Exchange</span> 
                    <br>With <span class="text-green-500">Crypto Loading</span> Solutions
                </h1>
                <p class="text-lg text-gray-300 mb-8">
                    Professional financial services for merchants, agents, and businesses. 
                    Exchange dollars, load crypto wallets, and manage your transactions securely.
                </p>
                <div class="mb-8">
                    <div class="bg-green-600 bg-opacity-20 border border-green-500 rounded-lg p-4 flex items-center space-x-4">
                        <i class="fas fa-gift text-2xl text-green-400"></i>
                        <div>
                            <span class="font-semibold text-green-400">Register with KES 500 and get full access to all features!</span><br>
                            <span class="text-gray-200 text-sm">Earn a <span class="font-bold text-green-300">KES 200 bonus</span> for every friend you refer who registers.</span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="register.php" class="px-6 py-3 bg-green-500 rounded-lg font-semibold text-center hover:bg-green-600 transition">
                        <i class="fas fa-user-tie mr-2"></i> Become a Merchant
                    </a>
                    <a href="register.php" class="px-6 py-3 border border-green-500 rounded-lg font-semibold text-center hover:bg-green-900 transition">
                        <i class="fas fa-exchange-alt mr-2"></i> Exchange Rates
                    </a>
                </div>
            </div>
            <div class="md:w-1/2 flex justify-center">
                <div class="relative w-full max-w-md">
                    <div class="absolute -top-10 -left-10 w-32 h-32 bg-green-500 rounded-full opacity-20 animate-float"></div>
                    <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-green-500 rounded-full opacity-20 animate-float" style="animation-delay: 1s;"></div>
                    <div class="relative bg-gray-800 bg-opacity-50 backdrop-blur-sm rounded-xl p-6 border border-green-500 border-opacity-30 shadow-lg">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-chart-line text-white text-xl"></i>
                            </div>
                            <h3 class="text-xl font-bold">Today's Exchange Rates</h3>
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between p-3 bg-gray-700 rounded-lg">
                                <span>USD → KES</span>
                                <span class="font-bold text-green-400">1 : 132.50</span>
                            </div>
                            <div class="flex justify-between p-3 bg-gray-700 rounded-lg">
                                <span>EUR → KES</span>
                                <span class="font-bold text-green-400">1 : 145.20</span>
                            </div>
                            <div class="flex justify-between p-3 bg-gray-700 rounded-lg">
                                <span>GBP → KES</span>
                                <span class="font-bold text-green-400">1 : 165.75</span>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-gray-400 text-right">
                            Rates updated: 12:45 PM
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="bg-gray-800 py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="text-center p-6">
                    <div class="text-4xl font-bold text-green-500 mb-2">850+</div>
                    <p class="text-gray-300">Active Merchants</p>
                </div>
                <div class="text-center p-6">
                    <div class="text-4xl font-bold text-green-500 mb-2">KES 25M+</div>
                    <p class="text-gray-300">Monthly Volume</p>
                </div>
                <div class="text-center p-6">
                    <div class="text-4xl font-bold text-green-500 mb-2">98%</div>
                    <p class="text-gray-300">Success Rate</p>
                </div>
                <div class="text-center p-6">
                    <div class="text-4xl font-bold text-green-500 mb-2">24/7</div>
                    <p class="text-gray-300">Support</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="container mx-auto px-4 py-16">
        <h2 class="text-3xl font-bold text-center mb-4">Our <span class="text-green-500">Merchant Services</span></h2>
        <p class="text-center text-gray-300 max-w-2xl mx-auto mb-12">
            Comprehensive financial solutions tailored for businesses and agents
        </p>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Service 1 -->
            <div class="service-card bg-gray-800 rounded-xl p-6">
                <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center text-white text-2xl mb-4">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Dollar Exchange</h3>
                <p class="text-gray-300 mb-4">
                    Competitive USD to KES exchange rates for merchants with high volume needs.
                </p>
                <ul class="text-sm text-gray-400 space-y-2">
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Best market rates
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Bulk exchange available
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Secure transactions
                    </li>
                </ul>
            </div>
            
            <!-- Service 2 -->
            <div class="service-card bg-gray-800 rounded-xl p-6">
                <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center text-white text-2xl mb-4">
                    <i class="fab fa-bitcoin"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Crypto Loading</h3>
                <p class="text-gray-300 mb-4">
                    Load crypto wallets with USDT, Bitcoin, and other major cryptocurrencies.
                </p>
                <ul class="text-sm text-gray-400 space-y-2">
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Multiple crypto options
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Instant wallet funding
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Competitive fees
                    </li>
                </ul>
            </div>
            
            <!-- Service 3 -->
            <div class="service-card bg-gray-800 rounded-xl p-6">
                <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center text-white text-2xl mb-4">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Agent Network</h3>
                <p class="text-gray-300 mb-4">
                    Join our agent network and earn commissions on every transaction.
                </p>
                <ul class="text-sm text-gray-400 space-y-2">
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Attractive commissions
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Dedicated support
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Marketing materials
                    </li>
                </ul>
            </div>
            
            <!-- Service 4 -->
            <div class="service-card bg-gray-800 rounded-xl p-6">
                <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center text-white text-2xl mb-4">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">M-Pesa Integration</h3>
                <p class="text-gray-300 mb-4">
                    Seamless M-Pesa integration for your business transactions.
                </p>
                <ul class="text-sm text-gray-400 space-y-2">
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Instant deposits
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Bulk payments
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Transaction reports
                    </li>
                </ul>
            </div>
            
            <!-- Service 5 -->
            <div class="service-card bg-gray-800 rounded-xl p-6">
                <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center text-white text-2xl mb-4">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Currency Conversion</h3>
                <p class="text-gray-300 mb-4">
                    Convert between multiple currencies with competitive rates.
                </p>
                <ul class="text-sm text-gray-400 space-y-2">
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        USD, EUR, GBP supported
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Low conversion fees
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Fast processing
                    </li>
                </ul>
            </div>
            
            <!-- Service 6 -->
            <div class="service-card bg-gray-800 rounded-xl p-6">
                <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center text-white text-2xl mb-4">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Secure Transactions</h3>
                <p class="text-gray-300 mb-4">
                    Enterprise-grade security for all your financial transactions.
                </p>
                <ul class="text-sm text-gray-400 space-y-2">
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Encrypted transfers
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        2FA authentication
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Fraud protection
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Exchange Section -->
    <section id="exchange" class="bg-gray-800 py-16">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 mb-8 md:mb-0">
                    <h2 class="text-3xl font-bold mb-4">Dollar <span class="text-green-500">Exchange Services</span></h2>
                    <p class="text-gray-300 mb-6">
                        Get the best USD to KES exchange rates for your business. Our merchant services provide:
                    </p>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-check text-white"></i>
                            </div>
                            <span>Competitive rates better than banks</span>
                        </li>
                        <li class="flex items-center">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-check text-white"></i>
                            </div>
                            <span>Bulk exchange for high volumes</span>
                        </li>
                        <li class="flex items-center">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-check text-white"></i>
                            </div>
                            <span>Same-day settlement</span>
                        </li>
                        <li class="flex items-center">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-check text-white"></i>
                            </div>
                            <span>Dedicated account manager</span>
                        </li>
                    </ul>
                    <a href="register.php" class="inline-block px-6 py-3 bg-green-500 rounded-lg font-semibold hover:bg-green-600 transition">
                        <i class="fas fa-user-tie mr-2"></i> Apply for Merchant Account
                    </a>
                </div>
                <div class="md:w-1/2 md:pl-12">
                    <div class="bg-gray-900 rounded-xl p-6 border border-green-500 border-opacity-30">
                        <h3 class="text-xl font-bold mb-4">Exchange Rate Calculator</h3>
                        <div class="mb-4">
                            <label class="block text-gray-400 mb-2">Amount</label>
                            <div class="relative">
                                <input type="number" class="w-full bg-gray-800 rounded-lg py-3 px-4 pr-16" placeholder="Enter amount">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <span class="text-gray-400">USD</span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-400 mb-2">Convert to</label>
                            <select class="w-full bg-gray-800 rounded-lg py-3 px-4">
                                <option>Kenya Shilling (KES)</option>
                                <option>Euro (EUR)</option>
                                <option>British Pound (GBP)</option>
                            </select>
                        </div>
                        <div class="bg-gray-800 rounded-lg p-4 mb-4">
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-400">Exchange Rate</span>
                                <span class="font-bold">1 USD = 132.50 KES</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">You Receive</span>
                                <span class="text-green-500 font-bold text-xl">13,250 KES</span>
                            </div>
                        </div>
                        <button class="w-full py-3 bg-green-500 rounded-lg font-semibold hover:bg-green-600 transition">
                            Exchange Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Crypto Section -->
    <section id="crypto" class="container mx-auto px-4 py-16">
        <h2 class="text-3xl font-bold text-center mb-4">Crypto <span class="text-green-500">Loading Services</span></h2>
        <p class="text-center text-gray-300 max-w-2xl mx-auto mb-12">
            Load your crypto wallets instantly with our secure platform
        </p>
        
        <div class="grid md:grid-cols-3 gap-8 mb-12">
            <!-- Crypto 1 -->
            <div class="bg-gray-800 rounded-xl p-6 border border-green-500 border-opacity-20 hover:border-opacity-50 transition">
                <div class="flex items-center mb-4">
                    <img src="https://cryptologos.cc/logos/tether-usdt-logo.png" class="w-10 h-10 mr-4">
                    <h3 class="text-xl font-bold">USDT Loading</h3>
                </div>
                <p class="text-gray-300 mb-4">
                    Load USDT (Tether) to your wallet with competitive rates and fast processing.
                </p>
                <div class="text-sm text-gray-400">
                    <div class="flex justify-between py-2 border-b border-gray-700">
                        <span>Network</span>
                        <span>TRC20, ERC20</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-700">
                        <span>Minimum</span>
                        <span>$50 equivalent</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span>Processing Time</span>
                        <span>5-15 minutes</span>
                    </div>
                </div>
            </div>
            
            <!-- Crypto 2 -->
            <div class="bg-gray-800 rounded-xl p-6 border border-green-500 border-opacity-20 hover:border-opacity-50 transition">
                <div class="flex items-center mb-4">
                    <img src="https://cryptologos.cc/logos/bitcoin-btc-logo.png" class="w-10 h-10 mr-4">
                    <h3 class="text-xl font-bold">Bitcoin Loading</h3>
                </div>
                <p class="text-gray-300 mb-4">
                    Buy and load Bitcoin to your wallet with secure transactions.
                </p>
                <div class="text-sm text-gray-400">
                    <div class="flex justify-between py-2 border-b border-gray-700">
                        <span>Network</span>
                        <span>Bitcoin</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-700">
                        <span>Minimum</span>
                        <span>$100 equivalent</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span>Processing Time</span>
                        <span>15-30 minutes</span>
                    </div>
                </div>
            </div>
            
            <!-- Crypto 3 -->
            <div class="bg-gray-800 rounded-xl p-6 border border-green-500 border-opacity-20 hover:border-opacity-50 transition">
                <div class="flex items-center mb-4">
                    <img src="https://cryptologos.cc/logos/ethereum-eth-logo.png" class="w-10 h-10 mr-4">
                    <h3 class="text-xl font-bold">Ethereum Loading</h3>
                </div>
                <p class="text-gray-300 mb-4">
                    Load Ethereum to your wallet with competitive rates.
                </p>
                <div class="text-sm text-gray-400">
                    <div class="flex justify-between py-2 border-b border-gray-700">
                        <span>Network</span>
                        <span>ERC20</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-700">
                        <span>Minimum</span>
                        <span>$75 equivalent</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span>Processing Time</span>
                        <span>10-20 minutes</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center">
            <a href="register.php" class="inline-block px-8 py-3 bg-green-500 rounded-lg font-semibold hover:bg-green-600 transition">
                <i class="fas fa-coins mr-2"></i> Start Loading Crypto Now
            </a>
        </div>
    </section>

    <!-- Agent Benefits Section -->
    <section class="bg-gray-800 py-16">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 mb-8 md:mb-0 md:pr-12">
                    <img src="https://images.unsplash.com/photo-1556740738-b6a63e27c4df?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" alt="Agent working" class="rounded-xl shadow-lg">
                </div>
                <div class="md:w-1/2">
                    <h2 class="text-3xl font-bold mb-4">Become a <span class="text-green-500">Stepcashier Agent</span></h2>
                    <p class="text-gray-300 mb-6">
                        Join our network of agents and earn commissions on every transaction you facilitate.
                    </p>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-start">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3 mt-1 flex-shrink-0">
                                <i class="fas fa-money-bill-wave text-white"></i>
                            </div>
                            <div>
                                <h4 class="font-bold">Earn Attractive Commissions</h4>
                                <p class="text-gray-400 text-sm">Get paid for every dollar exchange and crypto loading transaction you bring to the platform.</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3 mt-1 flex-shrink-0">
                                <i class="fas fa-tools text-white"></i>
                            </div>
                            <div>
                                <h4 class="font-bold">Full Agent Toolkit</h4>
                                <p class="text-gray-400 text-sm">Get access to marketing materials, rate calculators, and transaction tracking tools.</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3 mt-1 flex-shrink-0">
                                <i class="fas fa-headset text-white"></i>
                            </div>
                            <div>
                                <h4 class="font-bold">Dedicated Support</h4>
                                <p class="text-gray-400 text-sm">Our support team is available 24/7 to help you with any questions or issues.</p>
                            </div>
                        </li>
                    </ul>
                    <a href="register.php" class="inline-block px-6 py-3 bg-green-500 rounded-lg font-semibold hover:bg-green-600 transition">
                        <i class="fas fa-user-plus mr-2"></i> Apply to Become an Agent
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="container mx-auto px-4 py-16">
        <h2 class="text-3xl font-bold text-center mb-4">Frequently Asked <span class="text-green-500">Questions</span></h2>
        <p class="text-center text-gray-300 max-w-2xl mx-auto mb-12">
            Find answers to common questions about our merchant services
        </p>
        
        <div class="max-w-3xl mx-auto">
            <!-- FAQ 1 -->
            <div class="mb-4 border-b border-gray-700 pb-4">
                <button class="flex justify-between items-center w-full text-left">
                    <h3 class="text-lg font-semibold">How do I become a Stepcashier merchant?</h3>
                    <i class="fas fa-chevron-down transition-transform"></i>
                </button>
                <div class="mt-2 text-gray-300 hidden">
                    To become a merchant, register for an account and complete the merchant application process. You'll need to provide some business documentation and go through a verification process. Once approved, you'll gain access to all merchant services.
                </div>
            </div>
            
            <!-- FAQ 2 -->
            <div class="mb-4 border-b border-gray-700 pb-4">
                <button class="flex justify-between items-center w-full text-left">
                    <h3 class="text-lg font-semibold">What are the fees for dollar exchange?</h3>
                    <i class="fas fa-chevron-down transition-transform"></i>
                </button>
                <div class="mt-2 text-gray-300 hidden">
                    Our exchange fees vary based on volume and market conditions. Registered merchants get access to our most competitive rates. Please contact our support team for current fee structures.
                </div>
            </div>
            
            <!-- FAQ 3 -->
            <div class="mb-4 border-b border-gray-700 pb-4">
                <button class="flex justify-between items-center w-full text-left">
                    <h3 class="text-lg font-semibold">How long do crypto loading transactions take?</h3>
                    <i class="fas fa-chevron-down transition-transform"></i>
                </button>
                <div class="mt-2 text-gray-300 hidden">
                    Most crypto loading transactions are completed within 5-30 minutes depending on the cryptocurrency and network congestion. USDT transactions are typically the fastest.
                </div>
            </div>
            
            <!-- FAQ 4 -->
            <div class="mb-4 border-b border-gray-700 pb-4">
                <button class="flex justify-between items-center w-full text-left">
                    <h3 class="text-lg font-semibold">What's the minimum amount for dollar exchange?</h3>
                    <i class="fas fa-chevron-down transition-transform"></i>
                </button>
                <div class="mt-2 text-gray-300 hidden">
                    The minimum exchange amount is $100 or equivalent for standard customers. Registered merchants can exchange smaller amounts starting from $50.
                </div>
            </div>
            
            <!-- FAQ 5 -->
            <div class="mb-4 border-b border-gray-700 pb-4">
                <button class="flex justify-between items-center w-full text-left">
                    <h3 class="text-lg font-semibold">How do agent commissions work?</h3>
                    <i class="fas fa-chevron-down transition-transform"></i>
                </button>
                <div class="mt-2 text-gray-300 hidden">
                    Agents earn a percentage commission on every transaction they facilitate. Commission rates vary by service type and volume, with higher volumes earning better rates.
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-green-500 py-16">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">Ready to Grow Your Business?</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto">
                Join our network of merchants and agents today
            </p>
            <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                <a href="register.php" class="px-8 py-3 bg-white text-green-500 rounded-lg font-bold hover:bg-gray-100 transition">
                    Apply as Merchant
                </a>
                <a href="register.php" class="px-8 py-3 border border-white text-white rounded-lg font-bold hover:bg-green-600 transition">
                    Become an Agent
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 py-12">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <i class="fas fa-wallet text-2xl text-green-500 mr-2"></i>
                        <span class="text-xl font-bold">Step<span class="text-green-500">cashier</span></span>
                    </div>
                    <p class="text-gray-400 mb-4">
                        Professional merchant services and financial solutions for businesses and agents.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-green-500 transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-green-500 transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-green-500 transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-green-500 transition">
                            <i class="fab fa-telegram"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Services</h3>
                    <ul class="space-y-2">
                        <li><a href="#services" class="text-gray-400 hover:text-green-500 transition">Merchant Services</a></li>
                        <li><a href="#exchange" class="text-gray-400 hover:text-green-500 transition">Dollar Exchange</a></li>
                        <li><a href="#crypto" class="text-gray-400 hover:text-green-500 transition">Crypto Loading</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-green-500 transition">Agent Network</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Company</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-green-500 transition">About Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-green-500 transition">Contact</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-green-500 transition">Careers</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-green-500 transition">Blog</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Support</h3>
                    <ul class="space-y-2">
                        <li class="flex items-center">
                            <i class="fas fa-envelope text-gray-400 mr-2"></i>
                            <a href="mailto:support@stepcashier.com" class="text-gray-400 hover:text-green-500 transition">support@stepcashier.com</a>
                        </li>
                        <li class="flex items-center">
                            <i class="fab fa-whatsapp text-gray-400 mr-2"></i>
                            <a href="https://wa.me/254754497441" class="text-gray-400 hover:text-green-500 transition">+254 754 497441</a>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>
                            <span class="text-gray-400">Nairobi, Kenya</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-500">
                <p>© 2025 Stepcashier. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // FAQ Accordion
        document.querySelectorAll('#faq button').forEach(button => {
            button.addEventListener('click', () => {
                const answer = button.nextElementSibling;
                const icon = button.querySelector('i');
                
                // Toggle answer
                answer.classList.toggle('hidden');
                
                // Rotate icon
                if (answer.classList.contains('hidden')) {
                    icon.style.transform = 'rotate(0deg)';
                } else {
                    icon.style.transform = 'rotate(180deg)';
                }
            });
        });
        
        // Exchange calculator functionality
        const exchangeInput = document.querySelector('#exchange input[type="number"]');
        if (exchangeInput) {
            exchangeInput.addEventListener('input', () => {
                const amount = parseFloat(exchangeInput.value) || 0;
                const rate = 132.50;
                const resultElement = document.querySelector('#exchange .text-xl');
                if (resultElement) {
                    resultElement.textContent = (amount * rate).toLocaleString('en-KE', {
                        style: 'currency',
                        currency: 'KES',
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).replace('KES', '') + ' KES';
                }
            });
        }
    </script>
</body>
</html>