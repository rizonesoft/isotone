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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isNew ? 'Add New User' : 'Edit User'; ?> - Isotone Admin</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Favicon -->
    <link rel="icon" href="/isotone/favicon.ico">
</head>
<body class="bg-gray-900 text-gray-100 flex h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-800 border-r border-gray-700">
        <div class="p-6 border-b border-gray-700">
            <h2 class="text-2xl font-bold bg-gradient-to-r from-cyan-400 to-green-400 bg-clip-text text-transparent">
                Isotone
            </h2>
            <p class="text-xs text-gray-500 mt-1 tracking-wider">ADMIN PANEL</p>
        </div>
        
        <nav class="mt-6">
            <a href="/isotone/iso-admin/" class="flex items-center px-6 py-3 text-gray-400 hover:bg-gray-700 hover:text-cyan-400 transition-colors">
                <span class="mr-3">📊</span> Dashboard
            </a>
            <a href="/isotone/iso-admin/posts.php" class="flex items-center px-6 py-3 text-gray-400 hover:bg-gray-700 hover:text-cyan-400 transition-colors">
                <span class="mr-3">📝</span> Posts
            </a>
            <a href="/isotone/iso-admin/pages.php" class="flex items-center px-6 py-3 text-gray-400 hover:bg-gray-700 hover:text-cyan-400 transition-colors">
                <span class="mr-3">📄</span> Pages
            </a>
            <a href="/isotone/iso-admin/media.php" class="flex items-center px-6 py-3 text-gray-400 hover:bg-gray-700 hover:text-cyan-400 transition-colors">
                <span class="mr-3">🖼️</span> Media
            </a>
            <a href="/isotone/iso-admin/users.php" class="flex items-center px-6 py-3 bg-gray-700 text-cyan-400 border-l-4 border-cyan-400">
                <span class="mr-3">👥</span> Users
            </a>
            <a href="/isotone/iso-admin/plugins.php" class="flex items-center px-6 py-3 text-gray-400 hover:bg-gray-700 hover:text-cyan-400 transition-colors">
                <span class="mr-3">🔌</span> Plugins
            </a>
            <a href="/isotone/iso-admin/themes.php" class="flex items-center px-6 py-3 text-gray-400 hover:bg-gray-700 hover:text-cyan-400 transition-colors">
                <span class="mr-3">🎨</span> Themes
            </a>
            <a href="/isotone/iso-admin/settings.php" class="flex items-center px-6 py-3 text-gray-400 hover:bg-gray-700 hover:text-cyan-400 transition-colors">
                <span class="mr-3">⚙️</span> Settings
            </a>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="flex-1 flex flex-col">
        <!-- Header -->
        <header class="bg-gray-800 border-b border-gray-700 px-8 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="/isotone/iso-admin/users.php" class="text-gray-400 hover:text-gray-200">← Back to Users</a>
                <h1 class="text-2xl font-semibold"><?php echo $isNew ? 'Add New User' : 'Edit User'; ?></h1>
            </div>
            
            <div class="flex items-center space-x-4">
                <span class="text-gray-400">Welcome, <?php echo htmlspecialchars($current_user); ?></span>
                <div class="w-8 h-8 bg-gradient-to-r from-cyan-400 to-green-400 rounded-full flex items-center justify-center text-gray-900 font-semibold">
                    <?php echo strtoupper(substr($current_user, 0, 1)); ?>
                </div>
                <a href="/isotone/iso-admin/logout.php" class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded text-sm transition-colors">
                    Logout
                </a>
            </div>
        </header>
        
        <!-- Form Content -->
        <div class="flex-1 overflow-y-auto p-8">
            <div class="max-w-2xl">
                <?php if (!empty($errors)): ?>
                    <div class="mb-6 p-4 rounded-lg bg-red-800 border border-red-600 text-red-200">
                        <ul class="list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="mb-6 p-4 rounded-lg bg-green-800 border border-green-600 text-green-200">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="space-y-6">
                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-medium mb-2">Username *</label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               value="<?php echo htmlspecialchars($userData['username'] ?? ''); ?>"
                               required
                               class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded focus:border-cyan-500 focus:outline-none">
                        <p class="mt-1 text-xs text-gray-500">3-20 characters, letters, numbers, and underscores only</p>
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium mb-2">Email *</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>"
                               required
                               class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded focus:border-cyan-500 focus:outline-none">
                    </div>
                    
                    <!-- Display Name -->
                    <div>
                        <label for="display_name" class="block text-sm font-medium mb-2">Display Name</label>
                        <input type="text" 
                               id="display_name" 
                               name="display_name" 
                               value="<?php echo htmlspecialchars($userData['display_name'] ?? ''); ?>"
                               class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded focus:border-cyan-500 focus:outline-none">
                        <p class="mt-1 text-xs text-gray-500">Leave blank to use username</p>
                    </div>
                    
                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium mb-2">
                            Password <?php echo $isNew ? '*' : '(leave blank to keep current)'; ?>
                        </label>
                        <input type="password" 
                               id="password" 
                               name="password"
                               <?php echo $isNew ? 'required' : ''; ?>
                               class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded focus:border-cyan-500 focus:outline-none">
                        <p class="mt-1 text-xs text-gray-500">Minimum 6 characters</p>
                    </div>
                    
                    <!-- Role -->
                    <div>
                        <label for="role" class="block text-sm font-medium mb-2">Role</label>
                        <select id="role" 
                                name="role"
                                class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded focus:border-cyan-500 focus:outline-none">
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
                        <label for="status" class="block text-sm font-medium mb-2">Status</label>
                        <select id="status" 
                                name="status"
                                class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded focus:border-cyan-500 focus:outline-none">
                            <option value="active" <?php echo ($userData['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($userData['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="banned" <?php echo ($userData['status'] ?? '') === 'banned' ? 'selected' : ''; ?>>Banned</option>
                        </select>
                    </div>
                    
                    <!-- Bio -->
                    <div>
                        <label for="bio" class="block text-sm font-medium mb-2">Bio</label>
                        <textarea id="bio" 
                                  name="bio"
                                  rows="4"
                                  class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded focus:border-cyan-500 focus:outline-none"><?php echo htmlspecialchars($userData['bio'] ?? ''); ?></textarea>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="flex space-x-4">
                        <button type="submit" class="px-6 py-2 bg-cyan-600 hover:bg-cyan-700 rounded transition-colors">
                            <?php echo $isNew ? 'Create User' : 'Update User'; ?>
                        </button>
                        <a href="/isotone/iso-admin/users.php" class="px-6 py-2 bg-gray-700 hover:bg-gray-600 rounded transition-colors">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>