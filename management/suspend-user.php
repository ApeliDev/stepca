<?php include '../includes/admin_header.php'; ?>

<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Suspend User Account</h3>
        <p class="mt-1 text-sm text-gray-500">Temporarily or permanently suspend a user's account</p>
    </div>
    <div class="px-4 py-5 sm:p-6">
        <form id="suspend-form" method="POST" action="process-suspend.php">
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700">Select User</label>
                    <select name="user_id" id="user_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md" required>
                        <option value="">Select a user</option>
                        <?php
                        $stmt = $conn->prepare("SELECT id, name, email, is_active FROM users ORDER BY name ASC");
                        $stmt->execute();
                        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($users as $user):
                        ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo $user['is_active'] ? '' : 'selected'; ?>>
                            <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                            <?php echo $user['is_active'] ? '' : '(Currently Suspended)'; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="action" class="block text-sm font-medium text-gray-700">Action</label>
                    <select name="action" id="action" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md" required>
                        <option value="suspend">Suspend Account</option>
                        <option value="unsuspend">Unsuspend Account</option>
                    </select>
                </div>
                
                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700">Reason</label>
                    <textarea name="reason" id="reason" rows="3" class="shadow-sm focus:ring-primary focus:border-primary mt-1 block w-full sm:text-sm border border-gray-300 rounded-md" placeholder="Brief description of why you're suspending this account" required></textarea>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Confirm Action
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Recently Suspended Users -->
<div class="mt-6 bg-white shadow rounded-lg overflow-hidden">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Recently Suspended Users</h3>
    </div>
    <div class="bg-white overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 data-table">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $stmt = $conn->prepare("
                    SELECT al.*, a.name as admin_name, u.name as user_name 
                    FROM admin_logs al 
                    JOIN admins a ON al.admin_id = a.id 
                    JOIN users u ON al.record_id = u.id 
                    WHERE al.action IN ('suspend_user', 'unsuspend_user') 
                    ORDER BY al.created_at DESC 
                    LIMIT 10
                ");
                $stmt->execute();
                $suspensions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($suspensions) > 0):
                    foreach ($suspensions as $suspension):
                        $new_values = json_decode($suspension['new_values'], true);
                ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M j, Y H:i', strtotime($suspension['created_at'])); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <a href="user-details.php?id=<?php echo $suspension['record_id']; ?>" class="text-primary hover:text-primaryDark">
                            <?php echo htmlspecialchars($suspension['user_name']); ?>
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $suspension['action'] == 'suspend_user' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                            <?php echo $suspension['action'] == 'suspend_user' ? 'Suspended' : 'Unsuspended'; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($suspension['admin_name']); ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($new_values['reason'] ?? ''); ?></td>
                </tr>
                <?php
                    endforeach;
                else:
                ?>
                <tr>
                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No suspensions yet</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    // Toggle action based on user's current status
    $('#user_id').change(function() {
        var selectedOption = $(this).find('option:selected');
        if (selectedOption.text().includes('(Currently Suspended)')) {
            $('#action').val('unsuspend');
        } else {
            $('#action').val('suspend');
        }
    });
    
    $('#suspend-form').submit(function(e) {
        e.preventDefault();
        
        var action = $('#action').val();
        var confirmMessage = action === 'suspend' 
            ? 'Are you sure you want to suspend this account?' 
            : 'Are you sure you want to unsuspend this account?';
        
        if (confirm(confirmMessage)) {
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
                        alert(action === 'suspend' ? 'Account suspended successfully!' : 'Account unsuspended successfully!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + response.message);
                        submitBtn.prop('disabled', false).text('Confirm Action');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    submitBtn.prop('disabled', false).text('Confirm Action');
                }
            });
        }
    });
});
</script>

<?php include '../includes/admin_footer.php'; ?>