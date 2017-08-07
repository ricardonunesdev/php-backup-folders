<?php

// Limits and errors
set_time_limit(0);
ini_set('memory_limit', '-1');
error_reporting(E_ALL);
ini_set('display_errors', 'On');

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
if (!is_dir(BACKUP_DIR) && !mkdir(BACKUP_DIR, 0755, true)) {
    die('Error creating backup folder - '.BACKUP_DIR);
}

// Get files and folders from root path
$scan_items = scandir(ROOT_PATH);

// Exclude specific files and folders
$exclude = array('.', '..'); // add more if needed

// Folders to backup
$folders = array();

// Debug
foreach ($scan_items as $scan_item) {
    if (!in_array($scan_item, $exclude)) {
        if (is_dir(ROOT_PATH.$scan_item)) {
            $folders[] = $scan_item;
            // echo 'Folder: '.$scan_item.'<br />'.PHP_EOL;
        } else {
            // echo 'File: '.$scan_item.'<br />'.PHP_EOL;
        }
    }
}

// Override folders if needed
// $folders = array();

foreach ($folders as $folder) {

    $path = ROOT_PATH.$folder;

    echo 'Backing up folder - '.$path.'<br />'.PHP_EOL;

    // Get all files recursively
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path),
        RecursiveIteratorIterator::LEAVES_ONLY,
        RecursiveIteratorIterator::CATCH_GET_CHILD
    );

    if (iterator_count($files) > 0) {

        $zip = new ZipArchive();
        $zip->open(BACKUP_DIR.$folder.'-'.date('Ymd').'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($files as $file) {
            if (!is_dir($file)) {
                $relative = substr($file, strlen(ROOT_PATH.$folder) + 1);

                // echo 'Compressing file - '.$relative.'<br />'.PHP_EOL;

                $zip->addFile($file, $relative);
            }
        }

        $zip->close();

        echo 'Finished compressing folder<br /><br />'.PHP_EOL;
    } else {
        echo 'No files to compress<br /><br />'.PHP_EOL;
    }

    // break; // one folder
}

echo 'Done<br />'.PHP_EOL;

// Revert umask
umask($old_mask);
