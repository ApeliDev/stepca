<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Transactions | Stepacashier Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    <style>
        .transaction-tab {
            transition: all 0.3s ease;
        }
        .transaction-tab.active {
            border-bottom: 3px solid #4CAF50;
            color: #4CAF50;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar would be included from your main dashboard -->
        <div class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64 bg-darkest text-white">
                <div class="flex items-center justify-center h-16 px-4 bg-darker">
                    <span class="text-xl font-bold text-primary">Stepacashier</span>
                </div>
                <div class="flex flex-col flex-grow px-4 py-4 overflow-y-auto">
                    <nav class="flex-1 space-y-2">
                        <a href="#dashboard" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg bg-primaryDark text-white">
                            <i class="fas fa-tachometer-alt mr-3"></i>
                            Dashboard
                        </a>
                        <a href="#users" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg text-gray-300 hover:bg-darker hover:text-white">
                            <i class="fas fa-users mr-3"></i>
                            Users
                        </a>
                        <a href="#payments" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg text-gray-300 hover:bg-darker hover:text-white">
                            <i class="fas fa-money-bill-wave mr-3"></i>
                            Payments
                        </a>
                        <a href="#withdrawals" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg text-gray-300 hover:bg-darker hover:text-white">
                            <i class="fas fa-wallet mr-3"></i>
                            Withdrawals
                        </a>
                        <a href="#transfers" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg text-gray-300 hover:bg-darker hover:text-white">
                            <i class="fas fa-exchange-alt mr-3"></i>
                            Transfers
                        </a>
                        <a href="#referrals" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg text-gray-300 hover:bg-darker hover:text-white">
                            <i class="fas fa-user-friends mr-3"></i>
                            Referrals
                        </a>
                        <a href="#notifications" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg text-gray-300 hover:bg-darker hover:text-white">
                            <i class="fas fa-bell mr-3"></i>
                            Notifications
                        </a>
                        <a href="#email-logs" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg text-gray-300 hover:bg-darker hover:text-white">
                            <i class="fas fa-envelope mr-3"></i>
                            Email Logs
                        </a>
                        <a href="#settings" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg text-gray-300 hover:bg-darker hover:text-white">
                            <i class="fas fa-cog mr-3"></i>
                            Settings
                        </a>
                    </nav>
                </div>
                <div class="p-4 border-t border-gray-700">
                    <div class="flex items-center">
                        <img class="w-10 h-10 rounded-full" src="https://via.placeholder.com/40" alt="Admin avatar">
                        <div class="ml-3">
                            <p class="text-sm font-medium text-white">Super Admin</p>
                            <p class="text-xs text-gray-300">admin@stepcashier.com</p>
                        </div>
                    </div>
                    <button class="mt-4 w-full px-4 py-2 text-sm font-medium rounded-lg text-white bg-darker hover:bg-primaryDark">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile sidebar -->
        <div class="md:hidden fixed inset-0 z-40" id="mobile-sidebar" style="display: none;">
            <div class="fixed inset-0 bg-gray-600 bg-opacity-75" id="sidebar-backdrop"></div>
            <div class="relative flex flex-col w-full max-w-xs bg-darkest h-full">
                <div class="flex items-center justify-between h-16 px-4 bg-darker">
                    <span class="text-xl font-bold text-primary">Stepacashier</span>
                    <button id="close-sidebar" class="text-gray-300 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto py-4 px-2">
                    <nav class="space-y-2">
                        <a href="#dashboard" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg bg-primaryDark text-white">
                            <i class="fas fa-tachometer-alt mr-3"></i>
                            Dashboard
                        </a>
                        <a href="#users" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg text-gray-300 hover:bg-darker hover:text-white">
                            <i class="fas fa-users mr-3"></i>
                            Users
                        </a>
                        <a href="#payments" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg text-gray-300 hover:bg-darker hover:text-white">
                            <i class="fas fa-money-bill-wave mr-3"></i>
                            Payments
                        </a>
                        <a href="#withdrawals" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg text-gray-300 hover:bg-darker hover:text-white">
                            <i class="fas fa-wallet mr-3"></i>
                            Withdrawals
                        </a>
                        <a href="#transfers" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg text-gray-300 hover:bg-darker hover:text-white">
                            <i class="fas fa-exchange-alt mr-3"></i>
                            Transfers
                        </a>
                        <a href="#referrals" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg text-gray-300 hover:bg-darker hover:text-white">
                            <i class="fas fa-user-friends mr-3"></i>
                            Referrals
                        </a>
                        <a href="#notifications" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg text-gray-300 hover:bg-darker hover:text-white">
                            <i class="fas fa-bell mr-3"></i>
                            Notifications
                        </a>
                        <a href="#email-logs" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg text-gray-300 hover:bg-darker hover:text-white">
                            <i class="fas fa-envelope mr-3"></i>
                            Email Logs
                        </a>
                        <a href="#settings" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg text-gray-300 hover:bg-darker hover:text-white">
                            <i class="fas fa-cog mr-3"></i>
                            Settings
                        </a>
                    </nav>
                </div>
                <div class="p-4 border-t border-gray-700">
                    <div class="flex items-center">
                        <img class="w-10 h-10 rounded-full" src="https://via.placeholder.com/40" alt="Admin avatar">
                        <div class="ml-3">
                            <p class="text-sm font-medium text-white">Super Admin</p>
                            <p class="text-xs text-gray-300">admin@stepcashier.com</p>
                        </div>
                    </div>
                    <button class="mt-4 w-full px-4 py-2 text-sm font-medium rounded-lg text-white bg-darker hover:bg-primaryDark">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </button>
                </div>
            </div>
        </div>
        <!-- Main content -->
        <div class="flex flex-col flex-1 overflow-hidden">
            <header class="flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200">
                <div class="flex items-center">
                    <button id="open-sidebar" class="mr-4 text-gray-500 hover:text-gray-600 md:hidden">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="text-xl font-semibold text-gray-800" id="page-title">Dashboard</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <button class="p-1 text-gray-500 hover:text-gray-600">
                            <i class="fas fa-bell"></i>
                            <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>
                    </div>
                    <div class="relative">
                        <button class="flex items-center space-x-2">
                            <img class="w-8 h-8 rounded-full" src="https://via.placeholder.com/32" alt="Admin avatar">
                            <span class="hidden md:inline text-sm font-medium">Super Admin</span>
                        </button>
                    </div>
                </div>
            </header>
            <!-- Main content area -->
            <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
                <div class="mb-6 flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">Manual Transaction Processing</h1>
                </div>

                <!-- Tabs Navigation -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <button id="withdrawal-tab" class="transaction-tab active py-4 px-6 text-center border-transparent">
                                <i class="fas fa-wallet mr-2"></i> Process Withdrawals
                            </button>
                            <button id="credit-tab" class="transaction-tab py-4 px-6 text-center border-transparent">
                                <i class="fas fa-money-bill-wave mr-2"></i> Credit Accounts
                            </button>
                            <button id="bulk-tab" class="transaction-tab py-4 px-6 text-center border-transparent">
                                <i class="fas fa-users mr-2"></i> Bulk Processing
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Withdrawal Processing Section -->
                <div id="withdrawal-section" class="transaction-section">
                    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-800">Pending Withdrawals</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <input type="checkbox" class="rounded text-primary focus:ring-primary">
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <!-- Example Withdrawal Row -->
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="checkbox" class="rounded text-primary focus:ring-primary">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#WDR1001</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full" src="https://via.placeholder.com/40" alt="">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">John Doe</div>
                                                    <div class="text-sm text-gray-500">john@example.com</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">KES 1,500.00</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">254712345678</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2 hours ago</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-primary hover:text-primaryDark mr-3 process-single" data-id="WDR1001">
                                                <i class="fas fa-check-circle mr-1"></i> Process
                                            </button>
                                            <button class="text-red-600 hover:text-red-900 reject-single" data-id="WDR1001">
                                                <i class="fas fa-times-circle mr-1"></i> Reject
                                            </button>
                                        </td>
                                    </tr>
                                    <!-- Additional rows would be dynamically populated -->
                                </tbody>
                            </table>
                        </div>
                        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-gray-500">
                                    <span class="font-medium">1</span> to <span class="font-medium">5</span> of <span class="font-medium">12</span> pending withdrawals
                                </div>
                                <div class="flex space-x-2">
                                    <button class="px-3 py-1 rounded-md bg-gray-200 text-gray-700 cursor-not-allowed" disabled>
                                        Previous
                                    </button>
                                    <button class="px-3 py-1 rounded-md bg-primary text-white hover:bg-primaryDark">
                                        Next
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Batch Processing Card -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Batch Process Selected Withdrawals</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">MPesa Bulk Reference</label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary" placeholder="BULK20230530">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Processing Date</label>
                                <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="flex items-end">
                                <button class="w-full px-4 py-2 bg-primary text-white rounded-lg hover:bg-primaryDark">
                                    <i class="fas fa-paper-plane mr-2"></i> Process Selected
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Crediting Section -->
                <div id="credit-section" class="transaction-section hidden">
                    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-800">Credit User Account</h2>
                        </div>
                        <div class="p-6">
                            <form id="credit-form">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Select User</label>
                                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                                            <option value="">Search user by email or phone</option>
                                            <option value="7">livingstoneapeli@gmail.com (0703416091)</option>
                                            <option value="8">tonnytrevix@gmail.com (0788888234)</option>
                                            <option value="9">tonnytrevix2@gmail.com (0703416092)</option>
                                            <option value="10">lopezjane237@gmail.com (0703416094)</option>
                                            <option value="11">lopezjane2387@gmail.com (0703416099)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount (KES)</label>
                                        <input type="number" step="0.01" min="10" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary" placeholder="0.00">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Reference</label>
                                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary" placeholder="MPesa code or manual reference">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Type</label>
                                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                                            <option value="deposit">Deposit</option>
                                            <option value="bonus">Bonus Credit</option>
                                            <option value="referral">Referral Bonus</option>
                                            <option value="correction">Balance Correction</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                        <textarea rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary" placeholder="Optional notes about this transaction"></textarea>
                                    </div>
                                    <div class="md:col-span-2">
                                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primaryDark">
                                            <i class="fas fa-plus-circle mr-2"></i> Credit Account
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Recent Credits -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-800">Recent Manual Credits</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Processed By</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2025-05-30 14:22</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">tonnytrevix@gmail.com</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">KES 500.00</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Deposit</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">MP123456789</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">admin@stepcashier.com</td>
                                    </tr>
                                    <!-- Additional rows would be dynamically populated -->
                                </tbody>
                            </table>
                        </div>
                        <div class="px-6 py-4 border-t border-gray-200">
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-gray-500">
                                    Showing <span class="font-medium">1</span> to <span class="font-medium">5</span> of <span class="font-medium">8</span> results
                                </div>
                                <div class="flex space-x-2">
                                    <button class="px-3 py-1 rounded-md bg-gray-200 text-gray-700 cursor-not-allowed" disabled>
                                        Previous
                                    </button>
                                    <button class="px-3 py-1 rounded-md bg-primary text-white hover:bg-primaryDark">
                                        Next
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bulk Processing Section -->
                <div id="bulk-section" class="transaction-section hidden">
                    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-800">Bulk Process Transactions</h2>
                        </div>
                        <div class="p-6">
                            <div class="mb-6">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-1">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Upload CSV File</label>
                                        <div class="mt-1 flex items-center">
                                            <input type="file" id="bulk-upload" class="hidden" accept=".csv">
                                            <label for="bulk-upload" class="cursor-pointer bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                                <i class="fas fa-upload mr-2"></i> Choose File
                                            </label>
                                            <span id="file-name" class="ml-4 text-sm text-gray-500">No file chosen</span>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-500">
                                            Download the <a href="#" class="text-primary hover:underline">template file</a> for proper formatting
                                        </p>
                                    </div>
                                    <button class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primaryDark self-end">
                                        <i class="fas fa-file-import mr-2"></i> Process File
                                    </button>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 pt-6">
                                <h3 class="text-md font-medium text-gray-800 mb-4">Bulk Processing History</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch ID</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Records</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Processed</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">BULK20230530-01</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Withdrawals</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">12</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2025-05-30 09:45</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="#" class="text-primary hover:text-primaryDark">View Details</a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Processing Modal -->
    <div id="processing-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <i class="fas fa-cog fa-spin text-green-600"></i>
                </div>
                <h3 class="mt-3 text-lg font-medium text-gray-900" id="modal-title">Processing Withdrawal</h3>
                <div class="mt-2">
                    <p class="text-sm text-gray-500" id="modal-message">Please wait while we process transaction #WDR1001</p>
                </div>
                <div class="mt-4">
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-primary h-2.5 rounded-full" style="width: 45%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab switching functionality
        const tabs = document.querySelectorAll('.transaction-tab');
        const sections = document.querySelectorAll('.transaction-section');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Update active tab
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Show corresponding section
                const targetId = this.id.replace('-tab', '-section');
                sections.forEach(section => {
                    section.classList.add('hidden');
                    if (section.id === targetId) {
                        section.classList.remove('hidden');
                    }
                });
            });
        });

        // File upload display
        document.getElementById('bulk-upload').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'No file chosen';
            document.getElementById('file-name').textContent = fileName;
        });

        // Single withdrawal processing
        document.querySelectorAll('.process-single').forEach(btn => {
            btn.addEventListener('click', function() {
                const withdrawalId = this.getAttribute('data-id');
                const modal = document.getElementById('processing-modal');
                document.getElementById('modal-title').textContent = `Processing Withdrawal #${withdrawalId}`;
                document.getElementById('modal-message').textContent = `Initiating KES 1,500 transfer to 254712345678...`;
                modal.classList.remove('hidden');
                
                // Simulate processing
                setTimeout(() => {
                    document.getElementById('modal-message').textContent = 'Confirming transaction with MPesa...';
                    // In a real app, you would have AJAX calls here
                }, 2000);
                
                setTimeout(() => {
                    document.getElementById('modal-message').textContent = 'Transaction completed successfully!';
                    // Close modal after a delay
                    setTimeout(() => {
                        modal.classList.add('hidden');
                        // In a real app, you would update the UI to show completed status
                        alert(`Withdrawal #${withdrawalId} processed successfully!`);
                    }, 1000);
                }, 4000);
            });
        });

        // Form submission for account crediting
        document.getElementById('credit-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const modal = document.getElementById('processing-modal');
            document.getElementById('modal-title').textContent = 'Crediting User Account';
            document.getElementById('modal-message').textContent = 'Processing manual credit transaction...';
            modal.classList.remove('hidden');
            
            // Simulate processing
            setTimeout(() => {
                modal.classList.add('hidden');
                alert('Account credited successfully!');
                // In a real app, you would submit the form via AJAX and update the recent credits table
            }, 2000);
        });
    </script>
</body>
</html>