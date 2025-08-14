<?php
/**
 * User Management Page
 * 
 * @package Isotone
 */

// Check authentication
require_once 'auth.php';

// Only admins can manage users
requireRole('admin');

$userObj = new IsotoneUser();

// Page setup
$page_title = 'Users';
$breadcrumbs = [
    ['title' => 'Users']
];

// Handle user actions
$message = '';
$messageType = '';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $userId = intval($_GET['id']);
    
    switch ($_GET['action']) {
        case 'delete':
            if ($userId != $current_user_id) { // Can't delete yourself
                if ($userObj->delete($userId)) {
                    $message = 'User deleted successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to delete user.';
                    $messageType = 'error';
                }
            } else {
                $message = 'You cannot delete your own account.';
                $messageType = 'error';
            }
            break;
            
        case 'deactivate':
            if ($userObj->update($userId, ['status' => 'inactive'])) {
                $message = 'User deactivated successfully.';
                $messageType = 'success';
            } else {
                $message = 'Failed to deactivate user.';
                $messageType = 'error';
            }
            break;
            
        case 'activate':
            if ($userObj->update($userId, ['status' => 'active'])) {
                $message = 'User activated successfully.';
                $messageType = 'success';
            } else {
                $message = 'Failed to activate user.';
                $messageType = 'error';
            }
            break;
    }
}

// Get all users
$users = $userObj->getAll();

// Start output buffering for content
ob_start();
?>

<?php if ($message): ?>
    <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-800 border border-green-600 text-green-200' : 'bg-red-800 border border-red-600 text-red-200'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Page Header -->
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-semibold">User Management</h1>
    <a href="/isotone/iso-admin/user-edit.php?action=new" 
       class="inline-flex items-center px-4 py-2 bg-cyan-600 hover:bg-cyan-700 rounded transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
        </svg>
        Add New User
    </a>
</div>

<!-- Users Table -->
<div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Role</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Last Login</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            <?php foreach ($users as $user): ?>
                <tr class="hover:bg-gray-700 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gradient-to-r from-cyan-400 to-green-400 rounded-full flex items-center justify-center text-gray-900 font-semibold mr-3">
                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                            </div>
                            <div>
                                <div class="text-sm font-medium"><?php echo htmlspecialchars($user['display_name'] ?? $user['username']); ?></div>
                                <div class="text-xs text-gray-500">@<?php echo htmlspecialchars($user['username']); ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php echo htmlspecialchars($user['email']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full bg-gray-700">
                            <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php if ($user['status'] === 'active'): ?>
                            <span class="px-2 py-1 text-xs rounded-full bg-green-800 text-green-200">Active</span>
                        <?php elseif ($user['status'] === 'inactive'): ?>
                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-800 text-yellow-200">Inactive</span>
                        <?php else: ?>
                            <span class="px-2 py-1 text-xs rounded-full bg-red-800 text-red-200">Banned</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                        <?php echo $user['last_login'] ? date('M j, Y', strtotime($user['last_login'])) : 'Never'; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex space-x-2">
                            <a href="/isotone/iso-admin/user-edit.php?id=<?php echo $user['id']; ?>" 
                               class="text-cyan-400 hover:text-cyan-300">Edit</a>
                            
                            <?php if ($user['id'] != $current_user_id): ?>
                                <?php if ($user['status'] === 'active'): ?>
                                    <a href="?action=deactivate&id=<?php echo $user['id']; ?>" 
                                       class="text-yellow-400 hover:text-yellow-300"
                                       onclick="return confirm('Deactivate this user?')">Deactivate</a>
                                <?php else: ?>
                                    <a href="?action=activate&id=<?php echo $user['id']; ?>" 
                                       class="text-green-400 hover:text-green-300">Activate</a>
                                <?php endif; ?>
                                
                                <a href="?action=delete&id=<?php echo $user['id']; ?>" 
                                   class="text-red-400 hover:text-red-300"
                                   onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">Delete</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        No users found. <a href="/isotone/iso-admin/user-edit.php?action=new" class="text-cyan-400 hover:text-cyan-300">Add the first user</a>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$page_content = ob_get_clean();

// Include the new layout
include 'includes/admin-layout.php';
?>