document.addEventListener('DOMContentLoaded', function() {
    // Initialize elements
    const walletOverview = document.getElementById('wallet-overview');
    const recentTransactions = document.getElementById('recent-transactions');
    const quickActions = document.getElementById('quick-actions');
    const viewHistoryBtn = document.getElementById('view-history-btn');
    
    // Animation delay for elements
    const animateElements = () => {
        const elements = document.querySelectorAll('[data-animate]');
        elements.forEach((el, index) => {
            const delay = index * 100;
            el.style.animationDelay = `${delay}ms`;
            el.classList.add('animate-slideIn');
        });
    };

    // Format currency values
    const formatCurrency = (value) => {
        return new Intl.NumberFormat('en-KE', {
            style: 'currency',
            currency: 'KES',
            minimumFractionDigits: 2
        }).format(value).replace('KES', '').trim();
    };

    // Update wallet balances
    const updateWalletBalances = async () => {
        try {
            const response = await fetch('/api/wallet/balance');
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            
            document.getElementById('total-balance').textContent = formatCurrency(data.total_balance);
            document.getElementById('available-balance').textContent = formatCurrency(data.balance);
            document.getElementById('referral-balance').textContent = formatCurrency(data.referral_bonus_balance);
            
            // Update quick action max amounts
            document.querySelectorAll('.withdraw-max').forEach(el => {
                el.textContent = formatCurrency(data.max_withdrawal);
            });
            
            document.querySelectorAll('.transfer-max').forEach(el => {
                el.textContent = formatCurrency(data.balance);
            });
            
        } catch (error) {
            console.error('Error fetching wallet balances:', error);
            showToast('Failed to load wallet balances', 'error');
        }
    };

    // Load recent transactions
    const loadRecentTransactions = async () => {
        try {
            const response = await fetch('/api/transactions/recent');
            if (!response.ok) throw new Error('Network response was not ok');
            
            const transactions = await response.json();
            const transactionsContainer = document.getElementById('transactions-container');
            
            if (transactions.length === 0) {
                transactionsContainer.innerHTML = `
                    <div class="py-8 text-center animate-fadeIn">
                        <div class="bg-primary/10 p-4 rounded-full inline-block mb-4">
                            <i class="fas fa-exchange-alt text-primary text-2xl"></i>
                        </div>
                        <p class="text-lightGray">No transactions yet</p>
                        <p class="text-lightGray/60 text-sm mt-1">Your transaction history will appear here</p>
                    </div>
                `;
                return;
            }
            
            let html = '';
            transactions.forEach((tx, index) => {
                const animationDelay = index * 100;
                
                html += `
                    <div class="flex items-center justify-between p-4 bg-darker/40 rounded-xl border border-primary/10" 
                         style="animation-delay: ${animationDelay}ms">
                        <div class="flex items-center">
                            <div class="p-3 rounded-lg mr-4 ${getTransactionTypeClass(tx.type)}">
                                <i class="fas ${getTransactionIcon(tx.type)}"></i>
                            </div>
                            <div>
                                <h4 class="font-medium">${formatTransactionType(tx.type)}</h4>
                                <p class="text-xs text-lightGray">
                                    ${formatDate(tx.created_at)}
                                    ${tx.destination ? `• To: ${tx.destination}` : ''}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold ${tx.type === 'withdrawal' ? 'text-red-400' : 'text-white'}">
                                ${tx.type === 'withdrawal' ? '-' : ''}${formatCurrency(tx.amount)}
                            </p>
                            ${getStatusBadge(tx.status)}
                        </div>
                    </div>
                `;
            });
            
            transactionsContainer.innerHTML = html;
            animateElements();
            
        } catch (error) {
            console.error('Error loading transactions:', error);
            showToast('Failed to load recent transactions', 'error');
        }
    };

    // Helper functions
    const formatTransactionType = (type) => {
        const types = {
            'withdrawal': 'Withdrawal',
            'transfer': 'Transfer',
            'deposit': 'Deposit',
            'investment': 'Investment'
        };
        return types[type] || type.charAt(0).toUpperCase() + type.slice(1);
    };

    const getTransactionTypeClass = (type) => {
        return type === 'withdrawal' ? 'bg-red-500/10 text-red-400' :
               type === 'transfer' ? 'bg-blue-500/10 text-blue-400' :
               'bg-primary/10 text-primary';
    };

    const getTransactionIcon = (type) => {
        return type === 'withdrawal' ? 'fa-money-bill-wave' :
               type === 'transfer' ? 'fa-exchange-alt' :
               'fa-wallet';
    };

    const formatDate = (dateString) => {
        const options = { month: 'short', day: 'numeric', year: 'numeric' };
        return new Date(dateString).toLocaleDateString('en-US', options);
    };

    const getStatusBadge = (status) => {
        const statusClasses = {
            'completed': 'bg-green-500/20 text-green-400',
            'failed': 'bg-red-500/20 text-red-400',
            'pending': 'bg-yellow-500/20 text-yellow-400',
            'processing': 'bg-blue-500/20 text-blue-400'
        };
        
        const statusIcons = {
            'completed': 'fa-check-circle',
            'failed': 'fa-times-circle',
            'pending': 'fa-clock',
            'processing': 'fa-spinner fa-pulse'
        };
        
        const statusClass = statusClasses[status] || 'bg-gray-500/20 text-gray-400';
        const statusIcon = statusIcons[status] || 'fa-question-circle';
        
        return `
            <span class="px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">
                <i class="fas ${statusIcon} mr-1"></i>
                ${status.charAt(0).toUpperCase() + status.slice(1)}
            </span>
        `;
    };

    // Toast notification
    const showToast = (message, type = 'success') => {
        const toast = document.createElement('div');
        const typeClass = type === 'success' ? 'bg-green-500' : 'bg-red-500';
        
        toast.className = `fixed bottom-4 right-4 ${typeClass} text-white px-4 py-2 rounded-lg shadow-lg animate-slideIn`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.remove('animate-slideIn');
            toast.classList.add('animate-fadeOut');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    };

    // Event listeners
    viewHistoryBtn?.addEventListener('click', () => {
        window.location.href = 'transactions.php';
    });

    // Initialize
    animateElements();
    updateWalletBalances();
    loadRecentTransactions();

    // Refresh data every 30 seconds
    setInterval(() => {
        updateWalletBalances();
        loadRecentTransactions();
    }, 30000);
});