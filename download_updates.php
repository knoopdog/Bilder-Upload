<?php
// Script to create a zip file of all updated files
header('Content-Type: text/html; charset=UTF-8');

// Create temporary directory
$tempDir = 'temp_zip';
if (!file_exists($tempDir)) {
    mkdir($tempDir, 0777, true);
}

// List of files to include in the update
$filesToInclude = [
    'script.js',
    'style.css',
    'index.html',
    'simple_upload.php',
    'ensure_dir.php',
    'manual_upload.php',
    'test_upload.php',
    'direct_upload_test.html',
    'create_dir.php',
    'upload.php'
];

// Check which files exist and copy them to temp directory
$foundFiles = [];
foreach ($filesToInclude as $file) {
    if (file_exists($file)) {
        copy($file, $tempDir . '/' . $file);
        $foundFiles[] = [
            'name' => $file,
            'size' => filesize($file),
            'modified' => date('Y-m-d H:i:s', filemtime($file))
        ];
    }
}

// Create ZIP file
$zipName = 'bilder_upload_update_' . date('Ymd_His') . '.zip';
$zipFile = $tempDir . '/' . $zipName;

if (file_exists($zipFile)) {
    unlink($zipFile);
}

$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
    foreach ($foundFiles as $fileInfo) {
        $file = $fileInfo['name'];
        $zip->addFile($tempDir . '/' . $file, $file);
    }
    
    // Include a README file with timestamp
    $readmeContent = "# Bilder Upload Update\n\n";
    $readmeContent .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
    $readmeContent .= "This package contains the latest files for the Bilder Upload application.\n\n";
    $readmeContent .= "## Files Included\n\n";
    
    foreach ($foundFiles as $fileInfo) {
        $readmeContent .= "- " . $fileInfo['name'] . " (Size: " . round($fileInfo['size']/1024, 2) . " KB, Modified: " . $fileInfo['modified'] . ")\n";
    }
    
    $zip->addFromString('UPDATE_README.md', $readmeContent);
    
    // Create an .htaccess file for images directory
    $htaccessContent = "# Allow access to images\nOptions +Indexes\nAllow from all\n";
    $zip->addFromString('images/.htaccess', $htaccessContent);
    
    // Create the images directory in the ZIP
    if (!$zip->addEmptyDir('images')) {
        $errorMsg = "Failed to add images directory to ZIP";
    }
    
    $zip->close();
    $zipCreated = true;
} else {
    $zipCreated = false;
}

// Helper function to format file size
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes > 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Updated Files</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
        .button:hover {
            background-color: #45a049;
        }
        .note {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Download Updated Files</h1>
    
    <div class="container">
        <h2>Update Package</h2>
        
        <?php if ($zipCreated): ?>
            <div class="success">
                ZIP file created successfully with the current local files.
                <p>Package generated: <?= date('Y-m-d H:i:s') ?></p>
            </div>
            
            <h3>Files Included in Package:</h3>
            <table>
                <tr>
                    <th>File</th>
                    <th>Size</th>
                    <th>Last Modified</th>
                </tr>
                <?php foreach ($foundFiles as $fileInfo): ?>
                <tr>
                    <td><?= htmlspecialchars($fileInfo['name']) ?></td>
                    <td><?= formatFileSize($fileInfo['size']) ?></td>
                    <td><?= $fileInfo['modified'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            
            <p>
                <a href="<?php echo $tempDir . '/' . $zipName; ?>" class="button" download>Download Update Package</a>
            </p>
            
            <div class="note">
                <strong>How to install the update:</strong>
                <ol>
                    <li>Download the ZIP file using the button above</li>
                    <li>Extract all files to your local computer</li>
                    <li>Upload all files to your web server, preserving the directory structure</li>
                    <li>Make sure the <code>images</code> directory exists and has write permissions (0777)</li>
                    <li>Test the application by uploading some images</li>
                    <li>Check that the images are saved to the <code>images</code> directory</li>
                </ol>
            </div>
        <?php else: ?>
            <div class="error">
                Failed to create ZIP file. Please check server permissions.
                <?php if (isset($errorMsg)): ?>
                <p>Error: <?= $errorMsg ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="container">
        <h2>Directory Structure Check</h2>
        
        <p>Checking if the images directory exists and is writable:</p>
        
        <?php
        // Check images directory
        if (!file_exists('images')) {
            echo '<div class="error">The "images" directory does not exist! Create it with this command:<br><code>mkdir images</code><br>And set permissions: <code>chmod 777 images</code></div>';
        } elseif (!is_writable('images')) {
            echo '<div class="error">The "images" directory exists but is not writable! Set permissions with:<br><code>chmod 777 images</code></div>';
        } else {
            echo '<div class="success">The "images" directory exists and is writable.</div>';
        }
        ?>
        
        <h3>Test Creating a File in Images Directory</h3>
        <?php
        // Try to create a test file in the images directory
        $testFile = 'images/test_' . time() . '.txt';
        $testContent = 'Test file created at ' . date('Y-m-d H:i:s');
        
        if (@file_put_contents($testFile, $testContent)) {
            echo '<div class="success">Successfully created a test file in the images directory: ' . htmlspecialchars($testFile) . '</div>';
            @unlink($testFile); // Clean up
        } else {
            echo '<div class="error">Failed to create a test file in the images directory. Check permissions.</div>';
        }
        ?>
    </div>
    
    <div class="container">
        <h2>Other Tools</h2>
        <p>
            <a href="manual_upload.php" class="button">Go to Manual Upload</a>
        </p>
    </div>
</body>
</html>
