<?php
/**
 * User Edit/Add Page
 * 
 * @package Isotone
 */

// Check authentication
require_once 'auth.php';

// Only admins can manage users
requireRole('admin');

$userObj = new IsotoneUser();

$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$isNew = $userId === 0 || isset($_GET['action']) && $_GET['action'] === 'new';
$userData = [];
$errors = [];
$success = '';

// Page setup
$page_title = $isNew ? 'Add New User' : 'Edit User';
$breadcrumbs = [
    ['title' => 'Users', 'url' => '/isotone/iso-admin/users.php'],
    ['title' => $isNew ? 'Add New' : 'Edit']
];

// Load existing user data
if (!$isNew && $userId) {
    $userData = $userObj->getById($userId);
    if (!$userData) {
        header('Location: /isotone/iso-admin/users.php');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $formData = [
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'display_name' => trim($_POST['display_name'] ?? ''),
        'role' => $_POST['role'] ?? 'subscriber',
        'status' => $_POST['status'] ?? 'active',
        'bio' => trim($_POST['bio'] ?? '')
    ];
    
    // Validation
    if (empty($formData['username'])) {
        $errors[] = 'Username is required.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $formData['username'])) {
        $errors[] = 'Username must be 3-20 characters and contain only letters, numbers, and underscores.';
    }
    
    if (empty($formData['email'])) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }
    
    // Check for duplicate username/email
    if ($isNew || $formData['username'] !== $userData['username']) {
        if ($userObj->usernameExists($formData['username'])) {
            $errors[] = 'Username already exists.';
        }
    }
    
    if ($isNew || $formData['email'] !== $userData['email']) {
        if ($userObj->emailExists($formData['email'])) {
            $errors[] = 'Email already exists.';
        }
    }
    
    // Handle password
    if ($isNew) {
        if (empty($_POST['password'])) {
            $errors[] = 'Password is required for new users.';
        } elseif (strlen($_POST['password']) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        } else {
            $formData['password'] = $_POST['password'];
        }
    } elseif (!empty($_POST['password'])) {
        if (strlen($_POST['password']) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        } else {
            $formData['password'] = $_POST['password'];
        }
    }
    
    // Save if no errors
    if (empty($errors)) {
        if ($isNew) {
            $newUserId = $userObj->create($formData);
            if ($newUserId) {
                header('Location: /isotone/iso-admin/user-edit.php?id=' . $newUserId . '&saved=1');
                exit;
            } else {
                $errors[] = 'Failed to create user.';
            }
        } else {
            if ($userObj->update($userId, $formData)) {
                $success = 'User updated successfully.';
                $userData = $userObj->getById($userId); // Reload data
            } else {
                $errors[] = 'Failed to update user.';
            }
        }
    }
    
    // Keep form data on error
    if (!empty($errors)) {
        $userData = array_merge($userData, $formData);
    }
}

// Check for saved message
if (isset($_GET['saved'])) {
    $success = 'User created successfully.';
}

// Start output buffering for content
ob_start();
?>

<div class="max-w-4xl">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-semibold dark:text-white text-gray-900"><?php echo $isNew ? 'Add New User' : 'Edit User'; ?></h1>
        <a href="/isotone/iso-admin/users.php" 
           class="inline-flex items-center px-4 py-2 dark:bg-gray-700 bg-gray-200 dark:hover:bg-gray-600 hover:bg-gray-300 dark:text-white text-gray-900 rounded transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Users
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="mb-6 p-4 rounded-lg dark:bg-red-800 bg-red-50 dark:border-red-600 border-red-300 dark:text-red-200 text-red-800">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="mb-6 p-4 rounded-lg dark:bg-green-800 bg-green-50 dark:border-green-600 border-green-300 dark:text-green-200 text-green-800">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" class="dark:bg-gray-800 bg-white rounded-lg dark:border-gray-700 border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Username -->
            <div>
                <label for="username" class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">Username *</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       value="<?php echo htmlspecialchars($userData['username'] ?? ''); ?>"
                       required
                       class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 rounded focus:border-cyan-500 focus:outline-none">
                <p class="mt-1 text-xs dark:text-gray-500 text-gray-500">3-20 characters, letters, numbers, and underscores only</p>
            </div>
            
            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">Email *</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>"
                       required
                       class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 rounded focus:border-cyan-500 focus:outline-none">
            </div>
            
            <!-- Display Name -->
            <div>
                <label for="display_name" class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">Display Name</label>
                <input type="text" 
                       id="display_name" 
                       name="display_name" 
                       value="<?php echo htmlspecialchars($userData['display_name'] ?? ''); ?>"
                       class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 rounded focus:border-cyan-500 focus:outline-none">
                <p class="mt-1 text-xs dark:text-gray-500 text-gray-500">Leave blank to use username</p>
            </div>
            
            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                    Password <?php echo $isNew ? '*' : '(leave blank to keep current)'; ?>
                </label>
                <input type="password" 
                       id="password" 
                       name="password"
                       <?php echo $isNew ? 'required' : ''; ?>
                       class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 rounded focus:border-cyan-500 focus:outline-none">
                <p class="mt-1 text-xs dark:text-gray-500 text-gray-500">Minimum 6 characters</p>
            </div>
            
            <!-- Role -->
            <div>
                <label for="role" class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">Role</label>
                <select id="role" 
                        name="role"
                        class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 rounded focus:border-cyan-500 focus:outline-none">
                    <option value="subscriber" <?php echo ($userData['role'] ?? '') === 'subscriber' ? 'selected' : ''; ?>>Subscriber</option>
                    <option value="contributor" <?php echo ($userData['role'] ?? '') === 'contributor' ? 'selected' : ''; ?>>Contributor</option>
                    <option value="author" <?php echo ($userData['role'] ?? '') === 'author' ? 'selected' : ''; ?>>Author</option>
                    <option value="editor" <?php echo ($userData['role'] ?? '') === 'editor' ? 'selected' : ''; ?>>Editor</option>
                    <option value="admin" <?php echo ($userData['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="super_admin" <?php echo ($userData['role'] ?? '') === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                </select>
            </div>
            
            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">Status</label>
                <select id="status" 
                        name="status"
                        class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 rounded focus:border-cyan-500 focus:outline-none">
                    <option value="active" <?php echo ($userData['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($userData['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="banned" <?php echo ($userData['status'] ?? '') === 'banned' ? 'selected' : ''; ?>>Banned</option>
                </select>
            </div>
        </div>
        
        <!-- Bio (full width) -->
        <div class="mt-6">
            <label for="bio" class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">Bio</label>
            <textarea id="bio" 
                      name="bio"
                      rows="4"
                      class="w-full px-4 py-2 dark:bg-gray-900 bg-gray-50 dark:border-gray-700 border-gray-300 dark:text-white text-gray-900 rounded focus:border-cyan-500 focus:outline-none"><?php echo htmlspecialchars($userData['bio'] ?? ''); ?></textarea>
        </div>
        
        <!-- Submit Buttons -->
        <div class="mt-6 flex space-x-4">
            <button type="submit" class="px-6 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded transition-colors">
                <?php echo $isNew ? 'Create User' : 'Update User'; ?>
            </button>
            <a href="/isotone/iso-admin/users.php" class="px-6 py-2 dark:bg-gray-700 bg-gray-200 dark:hover:bg-gray-600 hover:bg-gray-300 dark:text-white text-gray-900 rounded transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>

<?php
$page_content = ob_get_clean();

// Include the new layout
include 'includes/admin-layout.php';
?>