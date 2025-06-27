<?php include '../includes/admin_header.php'; ?>

<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Credit User Account</h3>
        <p class="mt-1 text-sm text-gray-500">Manually add funds to a user's account</p>
    </div>
    <div class="px-4 py-5 sm:p-6">
        <form id="credit-form" method="POST" action="process-credit.php">
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700">Select User</label>
                    <select name="user_id" id="user_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md" required>
                        <option value="">Select a user</option>
                        <?php
                        $stmt = $conn->prepare("SELECT id, name, email FROM users ORDER BY name ASC");
                        $stmt->execute();
                        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($users as $user):
                        ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700">Amount (KES)</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">KES</span>
                        </div>
                        <input type="number" name="amount" id="amount" min="1" step="1" class="focus:ring-primary focus:border-primary block w-full pl-12 pr-12 sm:text-sm border-gray-300 rounded-md" placeholder="0.00" required>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">.00</span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Credit Type</label>
                    <select name="type" id="type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md" required>
                        <option value="balance">Main Balance</option>
                        <option value="referral_bonus">Referral Bonus</option>
                    </select>
                </div>
                
                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700">Reason</label>
                    <textarea name="reason" id="reason" rows="3" class="shadow-sm focus:ring-primary focus:border-primary mt-1 block w-full sm:text-sm border border-gray-300 rounded-md" placeholder="Brief description of why you're crediting this account"></textarea>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primaryDark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Credit Account
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Recent Credits -->
<div class="mt-6 bg-white shadow rounded-lg overflow-hidden">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Credits</h3>
    </div>
    <div class="bg-white overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 data-table">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $stmt = $conn->prepare("
                    SELECT al.*, a.name as admin_name, u.name as user_name 
                    FROM admin_logs al 
                    JOIN admins a ON al.admin_id = a.id 
                    JOIN users u ON al.record_id = u.id 
                    WHERE al.action = 'credit_account' 
                    ORDER BY al.created_at DESC 
                    LIMIT 10
                ");
                $stmt->execute();
                $credits = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($credits) > 0):
                    foreach ($credits as $credit):
                        $new_values = json_decode($credit['new_values'], true);
                ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M j, Y H:i', strtotime($credit['created_at'])); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <a href="user-details.php?id=<?php echo $credit['record_id']; ?>" class="text-primary hover:text-primaryDark">
                            <?php echo htmlspecialchars($credit['user_name']); ?>
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">KES <?php echo number_format($new_values['amount'] ?? 0, 2); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo ucfirst(str_replace('_', ' ', $new_values['type'] ?? '')); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($credit['admin_name']); ?></td>
                </tr>
                <?php
                    endforeach;
                else:
                ?>
                <tr>
                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No credits yet</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#credit-form').submit(function(e) {
        e.preventDefault();
        
        if (confirm('Are you sure you want to credit this account?')) {
            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Processing...');
            
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Account credited successfully!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + response.message);
                        submitBtn.prop('disabled', false).text('Credit Account');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    submitBtn.prop('disabled', false).text('Credit Account');
                }
            });
        }
    });
});
</script>

<?php include '../includes/admin_footer.php'; ?>