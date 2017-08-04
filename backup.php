<?php

// Limits and errors
set_time_limit(0);
ini_set('memory_limit', '-1');
error_reporting(E_ALL);

// Temporary config file outside of git
include('config.php');

// Config fallback
if (!defined('BACKUP_DIR')) {
    define('ALLOWED_IP', '');
    define('FTP_USER', '');
    define('ROOT_PATH', '');
    define('BACKUP_DIR', '');
}

// Clear umask to allow better permission settings
$old_mask = umask(0);

// Create backup dir if it doesn't exist, with full permissions
if (!is_dir(BACKUP_DIR) && !mkdir(BACKUP_DIR, 0777, true)) {
    die('Error creating backup folder - '.BACKUP_DIR);
}

// Get files and folders from root path
$files = scandir(ROOT_PATH);

// Debug
foreach ($files as $file) {
    if (is_dir($file)) {
        echo 'Folder: '.$file.'<br />'.PHP_EOL;
    } else {
        echo 'Folder: '.$file.'<br />'.PHP_EOL;
    }
}

// Revert umask
umask($old_mask);
