<?php include '../includes/admin_header.php'; ?>

<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900">User Management</h3>
        <p class="mt-1 text-sm text-gray-500">View and manage all user accounts</p>
    </div>
    <div class="px-4 py-5 sm:p-6">
        <div class="flex justify-between items-center mb-4">
            <div class="flex space-x-2">
                <select id="status-filter" class="block pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md">
                    <option value="">All Statuses</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                
                <input type="text" id="search-filter" placeholder="Search by name, email or phone" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md">
            </div>
            
            <button id="refresh-btn" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                <i class="fas fa-sync-alt mr-2"></i> Refresh
            </button>
        </div>
        
        <div class="bg-white overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 data-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                    $stmt = $conn->prepare("
                        SELECT u.*, 
                               (SELECT COUNT(*) FROM referrals WHERE referrer_id = u.id) as referral_count,
                               (SELECT COALESCE(SUM(amount), 0) FROM referral_earnings WHERE user_id = u.id) as referral_earnings
                        FROM users u 
                        ORDER BY u.created_at DESC
                    ");
                    $stmt->execute();
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($users) > 0):
                        foreach ($users as $user):
                    ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full" src="<?php echo $user['profile_pic'] ? '../assets/images/profile/'.$user['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($user['name']).'&background=4CAF50&color=fff'; ?>" alt="<?php echo htmlspecialchars($user['name']); ?>">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo htmlspecialchars($user['phone']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div>KES <?php echo number_format($user['balance'], 2); ?></div>
                            <div class="text-xs text-gray-400">
                                Referrals: <?php echo $user['referral_count']; ?> (KES <?php echo number_format($user['referral_earnings'], 2); ?>)
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $user['is_active'] ? 'Active' : 'Suspended'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="user-details.php?id=<?php echo $user['id']; ?>" class="text-primary hover:text-primaryDark mr-3">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="suspend-user.php?user_id=<?php echo $user['id']; ?>" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-user-slash"></i> <?php echo $user['is_active'] ? 'Suspend' : 'Unsuspend'; ?>
                            </a>
                        </td>
                    </tr>
                    <?php
                        endforeach;
                    else:
                    ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No users yet</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Filter users
$(document).ready(function() {
    var table = $('.data-table').DataTable();
    
    $('#status-filter').change(function() {
        table.column(3).search($(this).val()).draw();
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