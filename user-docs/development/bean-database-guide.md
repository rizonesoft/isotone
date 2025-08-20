---
title: RedBeanPHP Database Guide
description: Complete guide to database operations in Isotone using RedBeanPHP ORM with examples and best practices
tags: [database, redbeanphp, orm, crud, sql, security, development]
category: development
priority: 95
last_updated: 2025-01-20
---

# RedBeanPHP Database Guide for Isotone Developers

## Table of Contents
1. [Introduction](#introduction)
2. [Getting Started](#getting-started)
3. [Basic CRUD Operations](#basic-crud-operations)
4. [Finding and Querying Beans](#finding-and-querying-beans)
5. [Relationships](#relationships)
6. [Advanced Features](#advanced-features)
7. [Best Practices for Isotone](#best-practices-for-isotone)
8. [Security Considerations](#security-considerations)
9. [Plugin Development Examples](#plugin-development-examples)
10. [Theme Development Examples](#theme-development-examples)

## Introduction

Isotone uses **RedBeanPHP 5.7**, a powerful zero-configuration ORM (Object-Relational Mapper) that makes database interactions simple, secure, and intuitive. RedBeanPHP automatically handles SQL injection protection, table creation, and schema management.

### Key Benefits for Developers
- **Zero Configuration**: No XML files, annotations, or configuration needed
- **Automatic SQL Injection Protection**: All values are automatically escaped
- **Fluid Mode**: Tables and columns are created automatically during development
- **Type Safety**: RedBeanPHP enforces data types
- **Simple API**: Intuitive methods that are easy to remember

### RedBeanPHP Version
Isotone uses RedBeanPHP **version 5.7** (`gabordemooij/redbean: ^5.7`)

## Getting Started

### Accessing RedBeanPHP in Isotone

```php
// RedBeanPHP is automatically loaded in Isotone
// Just ensure the database is connected
require_once dirname(__DIR__) . '/iso-includes/database.php';
isotone_db_connect();

// Now you can use RedBeanPHP
use RedBeanPHP\R;
```

### Understanding Beans

In RedBeanPHP, a "bean" is an object that represents a database record. Each bean corresponds to a row in a table.

```php
// Create a new bean (represents a new record)
$post = R::dispense('post');

// Beans are just objects with properties
$post->title = 'Hello World';
$post->content = 'This is my first post';
$post->created_at = date('Y-m-d H:i:s');

// Store the bean (saves to database)
$id = R::store($post);
```

## Basic CRUD Operations

### Create (INSERT)

```php
// Create a new record
$product = R::dispense('product');
$product->name = 'Isotone Pro License';
$product->price = 99.99;
$product->stock = 100;
$product->active = true;
$product->created_at = date('Y-m-d H:i:s');

// Save to database (returns the ID)
$product_id = R::store($product);
echo "Product created with ID: $product_id";
```

### Read (SELECT)

```php
// Load a single record by ID
$product = R::load('product', $product_id);

// Access properties
echo $product->name;      // "Isotone Pro License"
echo $product->price;     // 99.99

// Check if bean exists
if ($product->id) {
    // Product was found
} else {
    // Product with this ID doesn't exist
}
```

### Update

```php
// Load the record
$product = R::load('product', $product_id);

// Modify properties
$product->price = 79.99;
$product->updated_at = date('Y-m-d H:i:s');

// Save changes (same method as create)
R::store($product);
```

### Delete

```php
// Load and delete
$product = R::load('product', $product_id);
R::trash($product);

// Or delete multiple
$products = R::find('product', 'stock < ?', [10]);
R::trashAll($products);
```

## Finding and Querying Beans

### Finding Single Records

```php
// Find one record matching criteria
$user = R::findOne('user', 'email = ?', ['admin@isotone.io']);

// Find with multiple conditions
$post = R::findOne('post', 
    'status = ? AND author_id = ?', 
    ['published', $author_id]
);

// Returns NULL if not found
if ($user) {
    echo "User found: " . $user->name;
}
```

### Finding Multiple Records

```php
// Find all matching records
$posts = R::find('post', 'status = ?', ['published']);

// Find with ORDER BY and LIMIT
$recent_posts = R::find('post', 
    'status = ? ORDER BY created_at DESC LIMIT 10', 
    ['published']
);

// Find all records of a type
$all_users = R::findAll('user');

// Find with more complex conditions
$products = R::find('product', 
    'price BETWEEN ? AND ? AND stock > ?', 
    [10, 100, 0]
);
```

### Counting Records

```php
// Count all records
$total_users = R::count('user');

// Count with conditions
$active_users = R::count('user', 'status = ?', ['active']);

// Count with complex query
$low_stock = R::count('product', 
    'stock < ? AND active = ?', 
    [10, true]
);
```

### Using SQL Queries

```php
// Get all rows
$results = R::getAll('SELECT * FROM post WHERE status = ?', ['published']);

// Get single row
$user = R::getRow('SELECT * FROM user WHERE id = ?', [$user_id]);

// Get single column
$emails = R::getCol('SELECT email FROM user WHERE status = ?', ['active']);

// Get single cell
$total = R::getCell('SELECT COUNT(*) FROM product WHERE active = 1');

// Execute non-SELECT queries
R::exec('UPDATE product SET price = price * 1.1 WHERE category = ?', ['electronics']);
```

## Relationships

### One-to-Many (Parent owns Children)

```php
// Create author with books
$author = R::dispense('author');
$author->name = 'Douglas Adams';

// Create books
$book1 = R::dispense('book');
$book1->title = 'The Hitchhiker\'s Guide';

$book2 = R::dispense('book');
$book2->title = 'Restaurant at the End of the Universe';

// Assign books to author (one-to-many)
$author->ownBookList = [$book1, $book2];

// Save (cascades to books)
R::store($author);

// Load and access related beans
$author = R::load('author', $author_id);
foreach ($author->ownBookList as $book) {
    echo $book->title . "\n";
}

// Add more books later
$new_book = R::dispense('book');
$new_book->title = 'Life, the Universe and Everything';
$author->ownBookList[] = $new_book;
R::store($author);
```

### Many-to-One (Child belongs to Parent)

```php
// Create a comment belonging to a post
$comment = R::dispense('comment');
$comment->content = 'Great post!';
$comment->author = 'John Doe';

// Assign to post (many-to-one)
$post = R::load('post', $post_id);
$comment->post = $post;
R::store($comment);

// Access parent from child
$comment = R::load('comment', $comment_id);
$parent_post = $comment->post;
echo "Comment on: " . $parent_post->title;
```

### Many-to-Many (Shared relationships)

```php
// Create tags
$tag1 = R::dispense('tag');
$tag1->name = 'PHP';

$tag2 = R::dispense('tag');
$tag2->name = 'Database';

// Create post
$post = R::dispense('post');
$post->title = 'RedBeanPHP Tutorial';

// Assign tags to post (many-to-many)
$post->sharedTagList = [$tag1, $tag2];
R::store($post);

// Load and access shared beans
$post = R::load('post', $post_id);
foreach ($post->sharedTagList as $tag) {
    echo "#" . $tag->name . " ";
}

// Add existing tag to another post
$another_post = R::dispense('post');
$another_post->title = 'Another PHP Post';
$php_tag = R::findOne('tag', 'name = ?', ['PHP']);
$another_post->sharedTagList[] = $php_tag;
R::store($another_post);
```

### Eager Loading (Preloading relationships)

```php
// Load posts with their authors in one query
$posts = R::find('post');
R::preload($posts, ['author']);

// Now accessing authors won't trigger additional queries
foreach ($posts as $post) {
    echo $post->title . ' by ' . $post->author->name;
}
```

## Advanced Features

### Transactions

```php
// Start transaction
R::begin();

try {
    $order = R::dispense('order');
    $order->total = 299.99;
    $order->status = 'pending';
    R::store($order);
    
    // Update inventory
    $product = R::load('product', $product_id);
    $product->stock -= 1;
    
    if ($product->stock < 0) {
        throw new Exception('Out of stock');
    }
    
    R::store($product);
    
    // Commit if everything succeeded
    R::commit();
    echo "Order placed successfully";
    
} catch (Exception $e) {
    // Rollback on error
    R::rollback();
    echo "Order failed: " . $e->getMessage();
}
```

### Bean Validation with FUSE

```php
// Create a model class for validation
class Model_User extends RedBeanPHP\SimpleModel {
    
    // Called before storing
    public function update() {
        if (empty($this->bean->email)) {
            throw new Exception('Email is required');
        }
        
        if (!filter_var($this->bean->email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        // Hash password if changed
        if (isset($this->bean->password)) {
            $this->bean->password = password_hash($this->bean->password, PASSWORD_DEFAULT);
        }
    }
    
    // Called after loading
    public function open() {
        // Hide password from exports
        unset($this->bean->password);
    }
}

// Now validation happens automatically
$user = R::dispense('user');
$user->email = 'invalid-email';  // Will throw exception on store
R::store($user);
```

### Duplicate Beans

```php
// Duplicate a bean
$original = R::load('product', $product_id);
$copy = R::dup($original);
$copy->name = $copy->name . ' (Copy)';
$copy->created_at = date('Y-m-d H:i:s');
R::store($copy);

// Duplicate with relationships
$author = R::load('author', $author_id);
$author_copy = R::dup($author, ['ownBookList']); // Includes books
R::store($author_copy);
```

### Export/Import

```php
// Export bean to array
$user = R::load('user', $user_id);
$data = R::exportAll($user);

// Export with relationships
$author = R::load('author', $author_id);
$data = R::exportAll($author, TRUE); // Include all relationships

// Import from array
$new_user = R::dispense('user');
$new_user->import(['name' => 'John', 'email' => 'john@example.com']);
R::store($new_user);
```

### Fluid vs Frozen Mode

```php
// Development: Fluid mode (tables/columns created automatically)
R::freeze(false);

// Production: Frozen mode (no schema changes allowed)
R::freeze(true);

// Check current mode
$is_frozen = R::isFrozen();
```

## Best Practices for Isotone

### 1. Use Isotone's Database Connection

```php
// Always use Isotone's centralized connection
require_once dirname(__DIR__) . '/iso-includes/database.php';
isotone_db_connect();

// Don't create your own connection
// DON'T: R::setup('mysql:host=localhost;dbname=test', 'user', 'pass');
```

### 2. Follow Naming Conventions

```php
// Use singular, lowercase names for beans (tables)
$user = R::dispense('user');         // ✅ Good
$User = R::dispense('User');         // ❌ Avoid
$users = R::dispense('users');       // ❌ Avoid

// Use camelCase for properties (columns)
$user->firstName = 'John';           // ✅ Good
$user->first_name = 'John';          // ❌ Avoid (but works)
```

### 3. Always Validate Input

```php
// Sanitize and validate before storing
$comment = R::dispense('comment');
$comment->content = strip_tags($_POST['content']);
$comment->author = htmlspecialchars($_POST['author']);
$comment->email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

if (!filter_var($comment->email, FILTER_VALIDATE_EMAIL)) {
    throw new Exception('Invalid email');
}

R::store($comment);
```

### 4. Use Transactions for Critical Operations

```php
function transfer_credits($from_user_id, $to_user_id, $amount) {
    R::begin();
    
    try {
        $from = R::load('user', $from_user_id);
        $to = R::load('user', $to_user_id);
        
        if ($from->credits < $amount) {
            throw new Exception('Insufficient credits');
        }
        
        $from->credits -= $amount;
        $to->credits += $amount;
        
        R::store($from);
        R::store($to);
        
        // Log transaction
        $log = R::dispense('credittransfer');
        $log->from_user_id = $from_user_id;
        $log->to_user_id = $to_user_id;
        $log->amount = $amount;
        $log->timestamp = date('Y-m-d H:i:s');
        R::store($log);
        
        R::commit();
        return true;
        
    } catch (Exception $e) {
        R::rollback();
        error_log('Credit transfer failed: ' . $e->getMessage());
        return false;
    }
}
```

## Security Considerations

### 1. Automatic SQL Injection Protection

```php
// RedBeanPHP automatically escapes all values
$email = $_POST['email'];  // Could contain SQL injection attempt

// This is SAFE - RedBeanPHP handles escaping
$user = R::findOne('user', 'email = ?', [$email]);

// Even property assignment is safe
$user->email = $email;  // Automatically escaped on store
R::store($user);
```

### 2. Never Use Unescaped SQL

```php
// ❌ NEVER do this
$email = $_POST['email'];
R::getAll("SELECT * FROM user WHERE email = '$email'");

// ✅ Always use parameter binding
R::getAll('SELECT * FROM user WHERE email = ?', [$email]);
```

### 3. Validate Permissions

```php
// Always check user permissions before database operations
function update_post($post_id, $user_id, $data) {
    $post = R::load('post', $post_id);
    
    // Check ownership
    if ($post->author_id != $user_id) {
        throw new Exception('Permission denied');
    }
    
    // Update allowed fields only
    $post->title = $data['title'];
    $post->content = $data['content'];
    $post->updated_at = date('Y-m-d H:i:s');
    
    return R::store($post);
}
```

## Plugin Development Examples

### Example 1: Contact Form Plugin

```php
<?php
/**
 * Contact Form Plugin for Isotone
 */

class ContactFormPlugin {
    
    /**
     * Create the contact form table
     */
    public function activate() {
        // Create a test bean to ensure table exists
        $contact = R::dispense('contactform');
        $contact->name = '';
        $contact->email = '';
        $contact->subject = '';
        $contact->message = '';
        $contact->ip_address = '';
        $contact->created_at = date('Y-m-d H:i:s');
        $contact->status = 'unread';
        $id = R::store($contact);
        R::trash($contact); // Remove test record
    }
    
    /**
     * Save a contact form submission
     */
    public function save_submission($data) {
        R::begin();
        
        try {
            // Create submission record
            $submission = R::dispense('contactform');
            $submission->name = htmlspecialchars($data['name']);
            $submission->email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
            $submission->subject = htmlspecialchars($data['subject']);
            $submission->message = htmlspecialchars($data['message']);
            $submission->ip_address = $_SERVER['REMOTE_ADDR'];
            $submission->created_at = date('Y-m-d H:i:s');
            $submission->status = 'unread';
            
            // Validate
            if (!filter_var($submission->email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email address');
            }
            
            if (strlen($submission->message) < 10) {
                throw new Exception('Message too short');
            }
            
            // Check for spam (rate limiting)
            $recent = R::count('contactform', 
                'ip_address = ? AND created_at > ?', 
                [$submission->ip_address, date('Y-m-d H:i:s', strtotime('-5 minutes'))]
            );
            
            if ($recent >= 3) {
                throw new Exception('Too many submissions. Please wait.');
            }
            
            $id = R::store($submission);
            
            // Send email notification
            $this->send_notification($submission);
            
            R::commit();
            
            return [
                'success' => true,
                'message' => 'Thank you! We\'ll get back to you soon.',
                'id' => $id
            ];
            
        } catch (Exception $e) {
            R::rollback();
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get submissions for admin panel
     */
    public function get_submissions($page = 1, $per_page = 20) {
        $offset = ($page - 1) * $per_page;
        
        $submissions = R::find('contactform', 
            'ORDER BY created_at DESC LIMIT ?,?', 
            [$offset, $per_page]
        );
        
        $total = R::count('contactform');
        
        return [
            'submissions' => R::exportAll($submissions),
            'total' => $total,
            'pages' => ceil($total / $per_page),
            'current_page' => $page
        ];
    }
    
    /**
     * Mark submission as read
     */
    public function mark_as_read($id) {
        $submission = R::load('contactform', $id);
        
        if ($submission->id) {
            $submission->status = 'read';
            $submission->read_at = date('Y-m-d H:i:s');
            R::store($submission);
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete old submissions
     */
    public function cleanup_old_submissions($days = 90) {
        $cutoff = date('Y-m-d H:i:s', strtotime("-$days days"));
        
        $old_submissions = R::find('contactform', 
            'created_at < ?', 
            [$cutoff]
        );
        
        $count = count($old_submissions);
        R::trashAll($old_submissions);
        
        return $count;
    }
    
    private function send_notification($submission) {
        // Email notification logic here
        $admin_email = R::getCell('SELECT value FROM setting WHERE name = ?', ['admin_email']);
        
        if ($admin_email) {
            $subject = "New Contact Form: " . $submission->subject;
            $message = "Name: {$submission->name}\n";
            $message .= "Email: {$submission->email}\n";
            $message .= "Message: {$submission->message}\n";
            
            // Use Isotone's mail system
            iso_send_mail($admin_email, $subject, $message);
        }
    }
}

// Usage
$plugin = new ContactFormPlugin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $plugin->save_submission($_POST);
    echo json_encode($result);
}

// Display submissions in admin
if (isset($_GET['action']) && $_GET['action'] === 'list') {
    $page = $_GET['page'] ?? 1;
    $data = $plugin->get_submissions($page);
    
    foreach ($data['submissions'] as $submission) {
        echo "<div class='submission'>";
        echo "<h3>" . htmlspecialchars($submission['subject']) . "</h3>";
        echo "<p>From: " . htmlspecialchars($submission['name']) . " (" . htmlspecialchars($submission['email']) . ")</p>";
        echo "<p>" . nl2br(htmlspecialchars($submission['message'])) . "</p>";
        echo "<small>" . $submission['created_at'] . "</small>";
        echo "</div>";
    }
}
```

### Example 2: Custom Analytics Plugin

```php
<?php
/**
 * Analytics Plugin for Isotone
 */

class AnalyticsPlugin {
    
    /**
     * Track page view
     */
    public function track_page_view($page_url, $user_id = null) {
        $view = R::dispense('pageview');
        $view->url = $page_url;
        $view->user_id = $user_id;
        $view->ip_address = $_SERVER['REMOTE_ADDR'];
        $view->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $view->referrer = $_SERVER['HTTP_REFERER'] ?? '';
        $view->timestamp = date('Y-m-d H:i:s');
        
        // Get or create session
        $session_id = $_COOKIE['analytics_session'] ?? null;
        if (!$session_id) {
            $session_id = bin2hex(random_bytes(16));
            setcookie('analytics_session', $session_id, time() + 3600, '/');
        }
        $view->session_id = $session_id;
        
        R::store($view);
    }
    
    /**
     * Track custom event
     */
    public function track_event($category, $action, $label = null, $value = null) {
        $event = R::dispense('analyticevent');
        $event->category = $category;
        $event->action = $action;
        $event->label = $label;
        $event->value = $value;
        $event->user_id = $_SESSION['user_id'] ?? null;
        $event->timestamp = date('Y-m-d H:i:s');
        
        R::store($event);
    }
    
    /**
     * Get statistics for dashboard
     */
    public function get_stats($start_date = null, $end_date = null) {
        if (!$start_date) {
            $start_date = date('Y-m-d', strtotime('-30 days'));
        }
        if (!$end_date) {
            $end_date = date('Y-m-d');
        }
        
        // Total page views
        $total_views = R::getCell(
            'SELECT COUNT(*) FROM pageview WHERE DATE(timestamp) BETWEEN ? AND ?',
            [$start_date, $end_date]
        );
        
        // Unique visitors
        $unique_visitors = R::getCell(
            'SELECT COUNT(DISTINCT ip_address) FROM pageview WHERE DATE(timestamp) BETWEEN ? AND ?',
            [$start_date, $end_date]
        );
        
        // Top pages
        $top_pages = R::getAll(
            'SELECT url, COUNT(*) as views FROM pageview 
             WHERE DATE(timestamp) BETWEEN ? AND ? 
             GROUP BY url 
             ORDER BY views DESC 
             LIMIT 10',
            [$start_date, $end_date]
        );
        
        // Views by day
        $daily_views = R::getAll(
            'SELECT DATE(timestamp) as date, COUNT(*) as views 
             FROM pageview 
             WHERE DATE(timestamp) BETWEEN ? AND ? 
             GROUP BY DATE(timestamp) 
             ORDER BY date',
            [$start_date, $end_date]
        );
        
        // Top events
        $top_events = R::getAll(
            'SELECT category, action, COUNT(*) as count 
             FROM analyticevent 
             WHERE DATE(timestamp) BETWEEN ? AND ? 
             GROUP BY category, action 
             ORDER BY count DESC 
             LIMIT 10',
            [$start_date, $end_date]
        );
        
        return [
            'total_views' => $total_views,
            'unique_visitors' => $unique_visitors,
            'top_pages' => $top_pages,
            'daily_views' => $daily_views,
            'top_events' => $top_events,
            'date_range' => [
                'start' => $start_date,
                'end' => $end_date
            ]
        ];
    }
    
    /**
     * Clean old data
     */
    public function cleanup($days_to_keep = 365) {
        $cutoff = date('Y-m-d H:i:s', strtotime("-$days_to_keep days"));
        
        // Delete old page views
        R::exec('DELETE FROM pageview WHERE timestamp < ?', [$cutoff]);
        
        // Delete old events
        R::exec('DELETE FROM analyticevent WHERE timestamp < ?', [$cutoff]);
        
        return true;
    }
}
```

## Theme Development Examples

### Example: Theme Settings Manager

```php
<?php
/**
 * Theme Settings Manager using RedBeanPHP
 */

class ThemeSettings {
    
    private $theme_name;
    
    public function __construct($theme_name) {
        $this->theme_name = $theme_name;
    }
    
    /**
     * Get a theme setting
     */
    public function get($key, $default = null) {
        $setting = R::findOne('themesetting', 
            'theme = ? AND setting_key = ?', 
            [$this->theme_name, $key]
        );
        
        if ($setting) {
            // Unserialize if needed
            $value = $setting->setting_value;
            if ($setting->is_serialized) {
                $value = unserialize($value);
            }
            return $value;
        }
        
        return $default;
    }
    
    /**
     * Set a theme setting
     */
    public function set($key, $value) {
        $setting = R::findOne('themesetting', 
            'theme = ? AND setting_key = ?', 
            [$this->theme_name, $key]
        );
        
        if (!$setting) {
            $setting = R::dispense('themesetting');
            $setting->theme = $this->theme_name;
            $setting->setting_key = $key;
        }
        
        // Serialize arrays and objects
        if (is_array($value) || is_object($value)) {
            $setting->setting_value = serialize($value);
            $setting->is_serialized = true;
        } else {
            $setting->setting_value = $value;
            $setting->is_serialized = false;
        }
        
        $setting->updated_at = date('Y-m-d H:i:s');
        
        return R::store($setting);
    }
    
    /**
     * Get all settings for the theme
     */
    public function get_all() {
        $settings = R::find('themesetting', 
            'theme = ?', 
            [$this->theme_name]
        );
        
        $result = [];
        foreach ($settings as $setting) {
            $value = $setting->setting_value;
            if ($setting->is_serialized) {
                $value = unserialize($value);
            }
            $result[$setting->setting_key] = $value;
        }
        
        return $result;
    }
    
    /**
     * Save multiple settings at once
     */
    public function save_bulk($settings) {
        R::begin();
        
        try {
            foreach ($settings as $key => $value) {
                $this->set($key, $value);
            }
            
            R::commit();
            return true;
            
        } catch (Exception $e) {
            R::rollback();
            error_log('Failed to save theme settings: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Reset to defaults
     */
    public function reset() {
        $settings = R::find('themesetting', 'theme = ?', [$this->theme_name]);
        R::trashAll($settings);
        
        // Apply defaults
        $defaults = $this->get_defaults();
        $this->save_bulk($defaults);
    }
    
    /**
     * Get default settings
     */
    private function get_defaults() {
        return [
            'primary_color' => '#00D9FF',
            'secondary_color' => '#00FF88',
            'font_family' => 'Inter, sans-serif',
            'header_layout' => 'centered',
            'sidebar_position' => 'left',
            'show_breadcrumbs' => true,
            'animations_enabled' => true,
            'custom_css' => '',
            'footer_widgets' => 3,
            'social_links' => [
                'twitter' => '',
                'facebook' => '',
                'github' => ''
            ]
        ];
    }
}

// Usage in theme
$theme_settings = new ThemeSettings('isotone-default');

// Get settings
$primary_color = $theme_settings->get('primary_color', '#00D9FF');
$sidebar_position = $theme_settings->get('sidebar_position', 'left');

// Save settings from theme customizer
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'primary_color' => $_POST['primary_color'],
        'secondary_color' => $_POST['secondary_color'],
        'font_family' => $_POST['font_family'],
        'header_layout' => $_POST['header_layout'],
        'custom_css' => $_POST['custom_css']
    ];
    
    if ($theme_settings->save_bulk($settings)) {
        echo "Settings saved successfully!";
    }
}

// In theme template
$settings = $theme_settings->get_all();
?>
<style>
:root {
    --primary-color: <?php echo $settings['primary_color']; ?>;
    --secondary-color: <?php echo $settings['secondary_color']; ?>;
    --font-family: <?php echo $settings['font_family']; ?>;
}
<?php echo $settings['custom_css']; ?>
</style>
```

## Common Patterns and Solutions

### Pagination

```php
function get_paginated_posts($page = 1, $per_page = 10, $status = 'published') {
    $offset = ($page - 1) * $per_page;
    
    // Get posts
    $posts = R::find('post', 
        'status = ? ORDER BY created_at DESC LIMIT ?,?', 
        [$status, $offset, $per_page]
    );
    
    // Get total count
    $total = R::count('post', 'status = ?', [$status]);
    
    return [
        'posts' => $posts,
        'pagination' => [
            'total' => $total,
            'per_page' => $per_page,
            'current_page' => $page,
            'total_pages' => ceil($total / $per_page),
            'from' => $offset + 1,
            'to' => min($offset + $per_page, $total)
        ]
    ];
}
```

### Search Implementation

```php
function search_posts($query, $limit = 20) {
    $search_term = '%' . $query . '%';
    
    $posts = R::find('post', 
        '(title LIKE ? OR content LIKE ?) AND status = ? ORDER BY created_at DESC LIMIT ?',
        [$search_term, $search_term, 'published', $limit]
    );
    
    // Add relevance scoring
    foreach ($posts as $post) {
        $title_matches = substr_count(strtolower($post->title), strtolower($query));
        $content_matches = substr_count(strtolower($post->content), strtolower($query));
        $post->relevance_score = ($title_matches * 3) + $content_matches;
    }
    
    // Sort by relevance
    usort($posts, function($a, $b) {
        return $b->relevance_score - $a->relevance_score;
    });
    
    return $posts;
}
```

### Caching with RedBeanPHP

```php
class CachedQuery {
    
    private static $cache = [];
    
    public static function get($key, $callback, $ttl = 300) {
        // Check memory cache first
        if (isset(self::$cache[$key])) {
            return self::$cache[$key]['data'];
        }
        
        // Check database cache
        $cached = R::findOne('querycache', 'cache_key = ? AND expires_at > ?', 
            [$key, date('Y-m-d H:i:s')]
        );
        
        if ($cached) {
            $data = unserialize($cached->cache_data);
            self::$cache[$key] = ['data' => $data];
            return $data;
        }
        
        // Generate fresh data
        $data = $callback();
        
        // Store in database cache
        $cache_entry = R::dispense('querycache');
        $cache_entry->cache_key = $key;
        $cache_entry->cache_data = serialize($data);
        $cache_entry->expires_at = date('Y-m-d H:i:s', time() + $ttl);
        R::store($cache_entry);
        
        // Store in memory cache
        self::$cache[$key] = ['data' => $data];
        
        // Clean old cache entries
        R::exec('DELETE FROM querycache WHERE expires_at < ?', [date('Y-m-d H:i:s')]);
        
        return $data;
    }
    
    public static function clear($key = null) {
        if ($key) {
            unset(self::$cache[$key]);
            R::exec('DELETE FROM querycache WHERE cache_key = ?', [$key]);
        } else {
            self::$cache = [];
            R::exec('TRUNCATE TABLE querycache');
        }
    }
}

// Usage
$popular_posts = CachedQuery::get('popular_posts_week', function() {
    return R::getAll(
        'SELECT p.*, COUNT(v.id) as view_count 
         FROM post p 
         LEFT JOIN pageview v ON v.url LIKE CONCAT("%/post/", p.id) 
         WHERE p.status = "published" 
         AND v.timestamp > DATE_SUB(NOW(), INTERVAL 7 DAY)
         GROUP BY p.id 
         ORDER BY view_count DESC 
         LIMIT 10'
    );
}, 3600); // Cache for 1 hour
```

## Troubleshooting

### Common Issues and Solutions

1. **Table not found errors**
   ```php
   // Ensure you're in fluid mode during development
   R::freeze(false);
   
   // Or manually create the table structure
   $bean = R::dispense('yourtable');
   $bean->setMeta('buildcommand.unique', array(array('email')));
   R::store($bean);
   ```

2. **Foreign key constraints**
   ```php
   // Disable foreign key checks temporarily
   R::exec('SET FOREIGN_KEY_CHECKS = 0');
   // Your operations
   R::exec('SET FOREIGN_KEY_CHECKS = 1');
   ```

3. **Performance issues**
   ```php
   // Use indexes for frequently queried columns
   R::exec('CREATE INDEX idx_post_status ON post(status)');
   R::exec('CREATE INDEX idx_post_created ON post(created_at)');
   
   // Use partial loading for large datasets
   $posts = R::findCollection('post', 'status = ?', ['published']);
   while ($post = $posts->next()) {
       // Process one at a time to save memory
       process_post($post);
   }
   ```

## Conclusion

RedBeanPHP in Isotone provides a powerful, secure, and intuitive way to work with databases. By following these patterns and best practices, you can build robust plugins and themes that are maintainable, secure, and performant.

### Key Takeaways

1. **Always use beans** instead of raw SQL for automatic security
2. **Follow Isotone conventions** for consistency across plugins
3. **Use transactions** for critical operations
4. **Validate and sanitize** all user input
5. **Leverage relationships** for clean, maintainable code
6. **Cache expensive queries** for better performance

### Additional Resources

- [RedBeanPHP Official Documentation](https://redbeanphp.com)
- [Isotone Plugin Development Guide](/user-docs/development/plugin-development.md)
- [Isotone Security Best Practices](/user-docs/development/security.md)

---

*Last Updated: 2025-01-19*  
*Isotone Version: 1.0.0*  
*RedBeanPHP Version: 5.7*