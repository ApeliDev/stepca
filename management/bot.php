<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StepCashier Chatbot Interface</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
                        slideUp: 'slideUp 0.3s ease-out',
                        bounce: 'bounce 1s infinite',
                        pulse: 'pulse 2s infinite',
                        fadeIn: 'fadeIn 0.3s ease-out',
                        scaleIn: 'scaleIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-8px)' },
                        },
                        slideIn: {
                            'from': { opacity: '0', transform: 'translateY(-10px)' },
                            'to': { opacity: '1', transform: 'translateY(0)' },
                        },
                        slideUp: {
                            'from': { opacity: '0', transform: 'translateY(20px)' },
                            'to': { opacity: '1', transform: 'translateY(0)' },
                        },
                        fadeIn: {
                            'from': { opacity: '0' },
                            'to': { opacity: '1' },
                        },
                        scaleIn: {
                            'from': { opacity: '0', transform: 'scale(0.8)' },
                            'to': { opacity: '1', transform: 'scale(1)' },
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans bg-gradient-to-br from-dark via-darker to-darkest min-h-screen p-4 relative overflow-x-hidden">

    <!-- Demo Content -->
    <div class="max-w-4xl mx-auto py-20">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-white mb-4">StepCashier Platform</h1>
            <p class="text-lightGray text-lg">Your investment journey starts here</p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-darker/50 backdrop-blur-sm rounded-xl p-6 border border-primary/20">
                <div class="w-12 h-12 bg-gradient-to-r from-primary to-primaryDark rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-chart-line text-white"></i>
                </div>
                <h3 class="text-white font-semibold mb-2">Portfolio Tracking</h3>
                <p class="text-lightGray text-sm">Monitor your investments in real-time with advanced analytics.</p>
            </div>
            
            <div class="bg-darker/50 backdrop-blur-sm rounded-xl p-6 border border-primary/20">
                <div class="w-12 h-12 bg-gradient-to-r from-primary to-primaryDark rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-shield-alt text-white"></i>
                </div>
                <h3 class="text-white font-semibold mb-2">Secure Trading</h3>
                <p class="text-lightGray text-sm">Bank-level security for all your trading activities.</p>
            </div>
            
            <div class="bg-darker/50 backdrop-blur-sm rounded-xl p-6 border border-primary/20">
                <div class="w-12 h-12 bg-gradient-to-r from-primary to-primaryDark rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-headset text-white"></i>
                </div>
                <h3 class="text-white font-semibold mb-2">24/7 Support</h3>
                <p class="text-lightGray text-sm">Get help whenever you need it with our chat support.</p>
            </div>
        </div>
    </div>

    <!-- Chat System Container -->
    <div id="chatSystem" class="fixed bottom-0 right-0 z-50">
        
        <!-- Chat Button -->
        <div id="chatButton" class="absolute bottom-6 right-6 cursor-pointer group transition-all duration-300">
            <div class="relative">
                <!-- Notification Badge -->
                <div id="chatBadge" class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center text-white text-xs font-bold animate-pulse z-10">
                    1
                </div>
                
                <!-- Main Button -->
                <div class="w-16 h-16 bg-gradient-to-r from-primary to-primaryDark rounded-full shadow-2xl shadow-primary/30 flex items-center justify-center text-white text-xl transition-all duration-300 hover:shadow-primary/50 hover:scale-110 animate-float">
                    <i id="chatIcon" class="fas fa-comments transition-transform duration-300"></i>
                </div>
                
                <!-- Tooltip -->
                <div class="absolute bottom-20 right-0 bg-gray-800 text-white px-3 py-2 rounded-lg text-sm whitespace-nowrap opacity-0 group-hover:opacity-100 transition-all duration-300 shadow-lg transform translate-y-2 group-hover:translate-y-0">
                    Need help? Chat with us!
                    <div class="absolute top-full right-4 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800"></div>
                </div>
            </div>
        </div>

        <!-- Chat Interface -->
        <div id="chatInterface" class="absolute bottom-6 right-6 w-96 h-[36rem] bg-darker/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-primary/20 transform scale-95 opacity-0 transition-all duration-300 flex flex-col overflow-hidden">
            
            <!-- Chat Header -->
            <div class="relative flex items-center justify-between p-4 border-b border-gray-600/50 bg-gradient-to-r from-primary/10 to-primaryDark/10">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-primary to-primaryDark rounded-full flex items-center justify-center text-white mr-3 shadow-lg">
                        <i class="fas fa-headset text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-semibold text-lg">StepCashier Support</h3>
                        <p class="text-primary text-sm flex items-center">
                            <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                            Online • Available 24/7
                        </p>
                    </div>
                </div>
                <button id="closeChat" class="text-lightGray hover:text-white transition-all duration-200 p-2 rounded-lg hover:bg-gray-700/50">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Chat Messages -->
            <div id="chatMessages" class="flex-1 p-4 overflow-y-auto space-y-4 scroll-smooth">
                <!-- Welcome Message -->
                <div class="flex items-start animate-slideUp">
                    <div class="w-10 h-10 bg-gradient-to-br from-primary to-primaryDark rounded-full flex items-center justify-center text-white text-sm mr-3 flex-shrink-0">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="bg-gray-800/80 rounded-xl p-4 max-w-xs shadow-lg">
                        <p class="text-white text-sm leading-relaxed">👋 Welcome to StepCashier! I'm here to help you with any questions about your investments, account, or our platform. How can I assist you today?</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="p-4 border-t border-gray-600/50 bg-gradient-to-r from-dark/50 to-darker/50">
                <div class="mb-4">
                    <p class="text-lightGray text-xs mb-3 font-medium">Quick Actions:</p>
                    <div class="flex flex-wrap gap-2">
                        <button class="quick-action px-3 py-2 bg-gray-800/80 hover:bg-primary/20 text-lightGray hover:text-primary text-xs rounded-full transition-all duration-200 border border-gray-600/50 hover:border-primary/50">
                            <i class="fas fa-wallet mr-1"></i>Account Balance
                        </button>
                        <button class="quick-action px-3 py-2 bg-gray-800/80 hover:bg-primary/20 text-lightGray hover:text-primary text-xs rounded-full transition-all duration-200 border border-gray-600/50 hover:border-primary/50">
                            <i class="fas fa-exchange-alt mr-1"></i>Recent Trades
                        </button>
                        <button class="quick-action px-3 py-2 bg-gray-800/80 hover:bg-primary/20 text-lightGray hover:text-primary text-xs rounded-full transition-all duration-200 border border-gray-600/50 hover:border-primary/50">
                            <i class="fas fa-shield-alt mr-1"></i>Security
                        </button>
                    </div>
                </div>

                <!-- Message Input -->
                <div class="flex items-center space-x-3">
                    <div class="flex-1 relative">
                        <input 
                            type="text" 
                            id="chatInput" 
                            placeholder="Type your message..." 
                            class="w-full px-4 py-3 bg-gray-800/80 border border-gray-600/50 rounded-xl text-white placeholder-gray-400 text-sm transition-all focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 focus:bg-gray-800"
                        >
                    </div>
                    <button id="sendMessage" class="w-12 h-12 bg-gradient-to-r from-primary to-primaryDark rounded-xl flex items-center justify-center text-white hover:shadow-lg hover:shadow-primary/30 transition-all duration-200 hover:scale-105">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Typing Indicator Template -->
    <div id="typingTemplate" class="hidden flex items-start">
        <div class="w-10 h-10 bg-gradient-to-br from-primary to-primaryDark rounded-full flex items-center justify-center text-white text-sm mr-3 flex-shrink-0">
            <i class="fas fa-robot"></i>
        </div>
        <div class="bg-gray-800/80 rounded-xl p-4 shadow-lg">
            <div class="flex space-x-1">
                <div class="w-2 h-2 bg-primary rounded-full animate-bounce"></div>
                <div class="w-2 h-2 bg-primary rounded-full animate-bounce" style="animation-delay: 0.1s;"></div>
                <div class="w-2 h-2 bg-primary rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
            </div>
        </div>
    </div>

    <script>
        // Chat functionality
        const chatSystem = document.getElementById('chatSystem');
        const chatButton = document.getElementById('chatButton');
        const chatInterface = document.getElementById('chatInterface');
        const closeChat = document.getElementById('closeChat');
        const chatMessages = document.getElementById('chatMessages');
        const chatInput = document.getElementById('chatInput');
        const sendMessage = document.getElementById('sendMessage');
        const chatIcon = document.getElementById('chatIcon');
        const chatBadge = document.getElementById('chatBadge');
        const quickActions = document.querySelectorAll('.quick-action');

        let isChatOpen = false;

        // Toggle chat interface
        function toggleChat() {
            isChatOpen = !isChatOpen;
            
            if (isChatOpen) {
                chatInterface.classList.remove('scale-95', 'opacity-0');
                chatInterface.classList.add('scale-100', 'opacity-100');
                chatButton.style.display = 'none';
                chatBadge.style.display = 'none';
                setTimeout(() => chatInput.focus(), 300);
            } else {
                chatInterface.classList.add('scale-95', 'opacity-0');
                chatInterface.classList.remove('scale-100', 'opacity-100');
                chatButton.style.display = 'block';
            }
        }

        // Add message to chat
        function addMessage(message, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'flex items-start animate-slideUp';
            
            if (isUser) {
                messageDiv.innerHTML = `
                    <div class="flex-1"></div>
                    <div class="bg-gradient-to-r from-primary to-primaryDark rounded-xl p-4 max-w-xs ml-8 shadow-lg">
                        <p class="text-white text-sm leading-relaxed">${message}</p>
                    </div>
                    <div class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center text-white text-sm ml-3 flex-shrink-0">
                        <i class="fas fa-user"></i>
                    </div>
                `;
            } else {
                messageDiv.innerHTML = `
                    <div class="w-10 h-10 bg-gradient-to-br from-primary to-primaryDark rounded-full flex items-center justify-center text-white text-sm mr-3 flex-shrink-0">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="bg-gray-800/80 rounded-xl p-4 max-w-xs shadow-lg">
                        <p class="text-white text-sm leading-relaxed">${message}</p>
                    </div>
                `;
            }
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Show typing indicator
        function showTyping() {
            const typingDiv = document.getElementById('typingTemplate').cloneNode(true);
            typingDiv.id = 'typing';
            typingDiv.classList.remove('hidden');
            chatMessages.appendChild(typingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Hide typing indicator
        function hideTyping() {
            const typing = document.getElementById('typing');
            if (typing) {
                typing.remove();
            }
        }

        // Simulate bot response
        function simulateBotResponse(userMessage) {
            showTyping();
            
            setTimeout(() => {
                hideTyping();
                
                // Enhanced response logic
                let response = "Thank you for your message! Our support team will assist you shortly. Is there anything specific about your investment portfolio you'd like to know?";
                
                if (userMessage.toLowerCase().includes('balance') || userMessage.toLowerCase().includes('account')) {
                    response = "💰 Your current account balance is <strong>$12,456.78</strong>. Your portfolio has grown by 8.5% this month! Would you like to see a detailed breakdown of your investments?";
                } else if (userMessage.toLowerCase().includes('trade')) {
                    response = "📈 Your last trade was executed yesterday: <strong>Bought 10 shares of AAPL at $185.50</strong>. The trade was successful and your portfolio is performing well. You can view all your trading history in the Transactions section.";
                } else if (userMessage.toLowerCase().includes('security')) {
                    response = "🔒 Your account security is fully up to date! You have 2FA enabled, your last login was from a verified device, and all security protocols are active. Is there a specific security concern you'd like to address?";
                } else if (userMessage.toLowerCase().includes('help')) {
                    response = "🚀 I'm here to help! You can ask me about:<br>• Account balance & portfolio performance<br>• Recent trades & transaction history<br>• Security settings & account protection<br>• General questions about StepCashier<br><br>What would you like to know?";
                }
                
                addMessage(response);
            }, Math.random() * 1000 + 1000);
        }

        // Send message
        function handleSendMessage() {
            const message = chatInput.value.trim();
            if (message) {
                addMessage(message, true);
                chatInput.value = '';
                simulateBotResponse(message);
            }
        }

        // Event listeners
        chatButton.addEventListener('click', toggleChat);
        closeChat.addEventListener('click', toggleChat);
        sendMessage.addEventListener('click', handleSendMessage);

        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                handleSendMessage();
            }
        });

        // Quick action buttons
        quickActions.forEach(button => {
            button.addEventListener('click', () => {
                const action = button.textContent.trim();
                addMessage(action, true);
                simulateBotResponse(action);
            });
        });

        // Auto-resize chat for mobile
        function adjustChatSize() {
            if (window.innerWidth < 768) {
                chatInterface.style.width = 'calc(100vw - 2rem)';
                chatInterface.style.height = 'calc(100vh - 6rem)';
                chatInterface.style.right = '1rem';
                chatInterface.style.bottom = '1rem';
                
                chatButton.style.right = '1rem';
                chatButton.style.bottom = '1rem';
            } else {
                chatInterface.style.width = '24rem';
                chatInterface.style.height = '36rem';
                chatInterface.style.right = '1.5rem';
                chatInterface.style.bottom = '1.5rem';
                
                chatButton.style.right = '1.5rem';
                chatButton.style.bottom = '1.5rem';
            }
        }

        window.addEventListener('resize', adjustChatSize);
        adjustChatSize();

        // Add some interactivity to the page
        setTimeout(() => {
            if (!isChatOpen) {
                chatBadge.style.display = 'flex';
            }
        }, 3000);
    </script>
</body>
</html>