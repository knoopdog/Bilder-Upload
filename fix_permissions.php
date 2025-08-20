<?php
// This script attempts to fix common issues with the images directory

// Enable detailed error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to log messages to file and screen
function log_message($message, $type = 'info') {
    $datetime = date('[Y-m-d H:i:s]');
    $formatted = "$datetime [$type] $message";
    file_put_contents('fix_log.txt', $formatted . PHP_EOL, FILE_APPEND);
    
    // Return for display
    return $formatted;
}

// Start with clean output
$output = [];

// Get server information
$output[] = log_message("Server information: " . php_uname());
$output[] = log_message("PHP Version: " . phpversion());
$output[] = log_message("Current script: " . __FILE__);
$output[] = log_message("Absolute path: " . realpath(__FILE__));
$output[] = log_message("Current directory: " . __DIR__);
$output[] = log_message("Document root: " . $_SERVER['DOCUMENT_ROOT']);

// 1. Delete images directory to start fresh
$imagesDir = __DIR__ . '/images';
$output[] = log_message("Images directory path: $imagesDir");

if (file_exists($imagesDir)) {
    $output[] = log_message("Images directory exists, attempting to delete it to start fresh");
    
    // Recursive function to delete a directory
    function removeDirectory($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object == "." || $object == "..") {
                continue;
            }
            
            if (is_dir($dir . "/" . $object)) {
                removeDirectory($dir . "/" . $object);
            } else {
                unlink($dir . "/" . $object);
            }
        }
        
        return rmdir($dir);
    }
    
    if (removeDirectory($imagesDir)) {
        $output[] = log_message("Successfully deleted existing images directory", "success");
    } else {
        $output[] = log_message("Failed to delete existing images directory", "error");
    }
}

// 2. Create the images directory
if (!file_exists($imagesDir)) {
    $output[] = log_message("Creating new images directory");
    
    if (mkdir($imagesDir, 0777, true)) {
        $output[] = log_message("Successfully created images directory", "success");
    } else {
        $output[] = log_message("Failed to create images directory", "error");
    }
} else {
    $output[] = log_message("Images directory already exists (shouldn't happen after deletion)", "warning");
}

// 3. Set permissions to 777 (required for web server writing)
$output[] = log_message("Setting permissions on images directory to 777");

if (chmod($imagesDir, 0777)) {
    $output[] = log_message("Successfully set permissions on images directory", "success");
} else {
    $output[] = log_message("Failed to set permissions on images directory", "error");
}

// 4. Check that it's writable
if (is_writable($imagesDir)) {
    $output[] = log_message("Images directory is writable", "success");
} else {
    $output[] = log_message("Images directory is NOT writable", "error");
}

// 5. Try to create a test file in the images directory
$testFile = $imagesDir . '/test_' . time() . '.txt';
$testContent = 'Test file created at ' . date('Y-m-d H:i:s');

$output[] = log_message("Attempting to write test file: $testFile");

if (file_put_contents($testFile, $testContent)) {
    $output[] = log_message("Successfully wrote test file to images directory", "success");
    
    // Read it back to verify
    $readContent = file_get_contents($testFile);
    if ($readContent === $testContent) {
        $output[] = log_message("Successfully read test file from images directory", "success");
    } else {
        $output[] = log_message("Read test file but content doesn't match", "error");
    }
    
    // Clean up test file
    if (unlink($testFile)) {
        $output[] = log_message("Successfully removed test file", "success");
    } else {
        $output[] = log_message("Failed to remove test file", "error");
    }
} else {
    $output[] = log_message("Failed to write test file to images directory", "error");
}

// 6. Create an .htaccess file to ensure proper permissions
$htaccessContent = <<<EOF
# Ensure images directory is accessible
<IfModule mod_authz_core.c>
    Require all granted
</IfModule>
<IfModule !mod_authz_core.c>
    Order allow,deny
    Allow from all
</IfModule>

# Allow directory listing (optional, can be removed if not needed)
Options +Indexes

# Enable following symlinks (if needed)
Options +FollowSymLinks

# Disable execution of PHP files in this directory (security measure)
<FilesMatch "\.(php|phtml|php3|php4|php5|php7)$">
    Deny from all
</FilesMatch>
EOF;

$htaccessFile = $imagesDir . '/.htaccess';
$output[] = log_message("Creating .htaccess file in images directory");

if (file_put_contents($htaccessFile, $htaccessContent)) {
    $output[] = log_message("Successfully created .htaccess file", "success");
} else {
    $output[] = log_message("Failed to create .htaccess file", "error");
}

// 7. Try to create images directory as a relative path as well
$relativeImagesDir = 'images';
if (!file_exists($relativeImagesDir)) {
    $output[] = log_message("Creating images directory with relative path: $relativeImagesDir");
    
    if (mkdir($relativeImagesDir, 0777, true)) {
        $output[] = log_message("Successfully created images directory with relative path", "success");
    } else {
        $output[] = log_message("Failed to create images directory with relative path", "error");
    }
    
    if (chmod($relativeImagesDir, 0777)) {
        $output[] = log_message("Successfully set permissions on relative images directory", "success");
    } else {
        $output[] = log_message("Failed to set permissions on relative images directory", "error");
    }
} else {
    $output[] = log_message("Relative images directory already exists", "info");
    
    if (chmod($relativeImagesDir, 0777)) {
        $output[] = log_message("Successfully updated permissions on existing relative images directory", "success");
    } else {
        $output[] = log_message("Failed to update permissions on existing relative images directory", "error");
    }
}

// 8. Create a test file in the relative images directory
$relativeTestFile = $relativeImagesDir . '/test_relative_' . time() . '.txt';
$output[] = log_message("Attempting to write test file to relative path: $relativeTestFile");

if (file_put_contents($relativeTestFile, $testContent)) {
    $output[] = log_message("Successfully wrote test file to relative images directory", "success");
    
    // Clean up relative test file
    if (unlink($relativeTestFile)) {
        $output[] = log_message("Successfully removed relative test file", "success");
    } else {
        $output[] = log_message("Failed to remove relative test file", "error");
    }
} else {
    $output[] = log_message("Failed to write test file to relative images directory", "error");
}

// Return results as HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Permissions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1, h2 {
            color: #4a6fa5;
        }
        .container {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .log {
            font-family: monospace;
            white-space: pre;
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .success {
            color: #155724;
            background-color: #d4edda;
            padding: 2px 5px;
            border-radius: 3px;
        }
        .error {
            color: #721c24;
            background-color: #f8d7da;
            padding: 2px 5px;
            border-radius: 3px;
        }
        .warning {
            color: #856404;
            background-color: #fff3cd;
            padding: 2px 5px;
            border-radius: 3px;
        }
        .info {
            color: #0c5460;
            background-color: #d1ecf1;
            padding: 2px 5px;
            border-radius: 3px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin: 10px 0;
        }
        .button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Directory Permission Repair Tool</h1>
    
    <div class="container">
        <h2>Results</h2>
        <div class="log">
<?php 
foreach ($output as $line) {
    // Color code the output
    if (strpos($line, '[success]') !== false) {
        echo '<span class="success">' . htmlspecialchars($line) . '</span>' . PHP_EOL;
    } elseif (strpos($line, '[error]') !== false) {
        echo '<span class="error">' . htmlspecialchars($line) . '</span>' . PHP_EOL;
    } elseif (strpos($line, '[warning]') !== false) {
        echo '<span class="warning">' . htmlspecialchars($line) . '</span>' . PHP_EOL;
    } else {
        echo '<span class="info">' . htmlspecialchars($line) . '</span>' . PHP_EOL;
    }
}
?>
        </div>
    </div>
    
    <div class="container">
        <h2>Next Steps</h2>
        <p>Now that the permissions have been fixed, try uploading an image using the main application.</p>
        <p><a href="index.html" class="button">Go to Main Application</a></p>
        <p><a href="test_page.html" class="button">Go to Test Page</a></p>
    </div>
</body>
</html>
