<?php include '../includes/admin_header.php'; ?>

<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Registration Payments</h3>
        <p class="mt-1 text-sm text-gray-500">View and manage user registration payments</p>
    </div>
    <div class="px-4 py-5 sm:p-6">
        <div class="flex justify-between items-center mb-4">
            <div class="flex space-x-2">
                <select id="status-filter" class="block pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                </select>
                
                <input type="text" id="search-filter" placeholder="Search by name, email or MPESA code" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md">
            </div>
            
            <button id="refresh-btn" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                <i class="fas fa-sync-alt mr-2"></i> Refresh
            </button>
        </div>
        
        <div class="bg-white overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 data-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">MPESA Code</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                    $stmt = $conn->prepare("
                        SELECT p.*, u.name as user_name, u.email as user_email 
                        FROM payments p 
                        JOIN users u ON p.user_id = u.id 
                        ORDER BY p.created_at DESC
                    ");
                    $stmt->execute();
                    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($payments) > 0):
                        foreach ($payments as $payment):
                    ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M j, Y H:i', strtotime($payment['created_at'])); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div><?php echo htmlspecialchars($payment['user_name']); ?></div>
                            <div class="text-gray-400"><?php echo htmlspecialchars($payment['user_email']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">KES <?php echo number_format($payment['amount'], 2); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $payment['mpesa_code'] ? htmlspecialchars($payment['mpesa_code']) : 'N/A'; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php echo $payment['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                       ($payment['status'] == 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                <?php echo ucfirst($payment['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <?php if ($payment['status'] == 'pending'): ?>
                            <button onclick="verifyPayment(<?php echo $payment['id']; ?>)" class="text-primary hover:text-primaryDark mr-3">
                                <i class="fas fa-check-circle"></i> Verify
                            </button>
                            <?php endif; ?>
                            <a href="user-details.php?id=<?php echo $payment['user_id']; ?>" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-user"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php
                        endforeach;
                    else:
                    ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No payments yet</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function verifyPayment(paymentId) {
    if (confirm('Are you sure you want to mark this payment as verified?')) {
        $.ajax({
            url: 'verify-payment.php',
            method: 'POST',
            data: { id: paymentId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Payment verified successfully!');
                    window.location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    }
}

// Filter payments
$(document).ready(function() {
    var table = $('.data-table').DataTable();
    
    $('#status-filter').change(function() {
        table.column(4).search($(this).val()).draw();
    });
    
    $('#search-filter').keyup(function() {
        table.search($(this).val()).draw();
    });
    
    $('#refresh-btn').click(function() {
        window.location.reload();
    });
});
</script>

<?php include '../includes/admin_footer.php'; ?>