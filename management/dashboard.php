<?php include '../includes/admin_header.php'; ?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <!-- Total Users Card -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-primary rounded-md p-3">
                    <i class="fas fa-users text-white text-xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                    <dd class="flex items-baseline">
                        <?php
                        $stmt = $conn->prepare("SELECT COUNT(*) FROM users");
                        $stmt->execute();
                        $total_users = $stmt->fetchColumn();
                        ?>
                        <div class="text-2xl font-semibold text-gray-900"><?php echo number_format($total_users); ?></div>
                    </dd>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Users Card -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                    <i class="fas fa-user-check text-green-600 text-xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dt class="text-sm font-medium text-gray-500 truncate">Active Users</dt>
                    <dd class="flex items-baseline">
                        <?php
                        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE is_active = 1");
                        $stmt->execute();
                        $active_users = $stmt->fetchColumn();
                        ?>
                        <div class="text-2xl font-semibold text-gray-900"><?php echo number_format($active_users); ?></div>
                    </dd>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Withdrawn Card -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                    <i class="fas fa-money-bill-wave text-blue-600 text-xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Withdrawn</dt>
                    <dd class="flex items-baseline">
                        <?php
                        $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) FROM withdrawals WHERE status = 'completed'");
                        $stmt->execute();
                        $total_withdrawn = $stmt->fetchColumn();
                        ?>
                        <div class="text-2xl font-semibold text-gray-900">KES <?php echo number_format($total_withdrawn, 2); ?></div>
                    </dd>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="bg-white shadow rounded-lg overflow-hidden mb-6">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Activity</h3>
    </div>
    <div class="bg-white overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 data-table">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $stmt = $conn->prepare("
                    SELECT al.*, a.name as admin_name 
                    FROM admin_logs al 
                    JOIN admins a ON al.admin_id = a.id 
                    ORDER BY al.created_at DESC 
                    LIMIT 10
                ");
                $stmt->execute();
                $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($logs) > 0):
                    foreach ($logs as $log):
                ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M j, Y H:i', strtotime($log['created_at'])); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($log['admin_name']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($log['action']); ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <?php 
                        if ($log['table_name'] && $log['record_id']) {
                            echo "{$log['table_name']} #{$log['record_id']}";
                        }
                        ?>
                    </td>
                </tr>
                <?php
                    endforeach;
                else:
                ?>
                <tr>
                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No activity yet</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Withdrawals -->
<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Withdrawals</h3>
    </div>
    <div class="bg-white overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 data-table">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $stmt = $conn->prepare("
                    SELECT w.*, u.name as user_name 
                    FROM withdrawals w 
                    JOIN users u ON w.user_id = u.id 
                    ORDER BY w.created_at DESC 
                    LIMIT 10
                ");
                $stmt->execute();
                $withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($withdrawals) > 0):
                    foreach ($withdrawals as $withdrawal):
                ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M j, Y H:i', strtotime($withdrawal['created_at'])); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($withdrawal['user_name']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">KES <?php echo number_format($withdrawal['amount'], 2); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?php echo $withdrawal['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                   ($withdrawal['status'] == 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                            <?php echo ucfirst($withdrawal['status']); ?>
                        </span>
                    </td>
                </tr>
                <?php
                    endforeach;
                else:
                ?>
                <tr>
                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No withdrawals yet</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>