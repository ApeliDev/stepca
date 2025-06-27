<?php include '../includes/admin_header.php'; ?>

<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Failed Withdrawals</h3>
        <p class="mt-1 text-sm text-gray-500">Process failed withdrawal requests manually</p>
    </div>
    <div class="bg-white overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 data-table">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $stmt = $conn->prepare("
                    SELECT w.*, u.name as user_name 
                    FROM withdrawals w 
                    JOIN users u ON w.user_id = u.id 
                    WHERE w.status = 'failed' 
                    ORDER BY w.created_at DESC
                ");
                $stmt->execute();
                $withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($withdrawals) > 0):
                    foreach ($withdrawals as $withdrawal):
                ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M j, Y H:i', strtotime($withdrawal['created_at'])); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <a href="user-details.php?id=<?php echo $withdrawal['user_id']; ?>" class="text-primary hover:text-primaryDark">
                            <?php echo htmlspecialchars($withdrawal['user_name']); ?>
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">KES <?php echo number_format($withdrawal['amount'], 2); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($withdrawal['phone']); ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($withdrawal['failure_reason']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="retryWithdrawal(<?php echo $withdrawal['id']; ?>)" class="text-primary hover:text-primaryDark mr-3">
                            <i class="fas fa-redo-alt"></i> Retry
                        </button>
                        <button onclick="refundUser(<?php echo $withdrawal['id']; ?>)" class="text-green-600 hover:text-green-900">
                            <i class="fas fa-undo"></i> Refund
                        </button>
                    </td>
                </tr>
                <?php
                    endforeach;
                else:
                ?>
                <tr>
                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No failed withdrawals to process</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function retryWithdrawal(withdrawalId) {
    if (confirm('Are you sure you want to retry this withdrawal?')) {
        $.ajax({
            url: 'retry-withdrawal.php',
            method: 'POST',
            data: { id: withdrawalId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Withdrawal retried successfully!');
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

function refundUser(withdrawalId) {
    if (confirm('Are you sure you want to refund this withdrawal amount back to the user?')) {
        $.ajax({
            url: 'refund-withdrawal.php',
            method: 'POST',
            data: { id: withdrawalId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Amount refunded successfully!');
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
</script>

<?php include '../includes/admin_footer.php'; ?>