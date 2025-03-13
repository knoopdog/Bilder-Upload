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
    'create_dir.php'
];

// Check which files exist and copy them to temp directory
$foundFiles = [];
foreach ($filesToInclude as $file) {
    if (file_exists($file)) {
        copy($file, $tempDir . '/' . $file);
        $foundFiles[] = $file;
    }
}

// Create ZIP file
$zipName = 'bilder_upload_update.zip';
$zipFile = $tempDir . '/' . $zipName;

if (file_exists($zipFile)) {
    unlink($zipFile);
}

$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
    foreach ($foundFiles as $file) {
        $zip->addFile($tempDir . '/' . $file, $file);
    }
    
    // Add current script.js and styles.css from GitHub
    $scriptJsContent = file_get_contents('https://raw.githubusercontent.com/knoopdog/Bilder-Upload/main/script.js');
    if ($scriptJsContent !== false) {
        $zip->addFromString('script.js', $scriptJsContent);
    }
    
    $stylesCssContent = file_get_contents('https://raw.githubusercontent.com/knoopdog/Bilder-Upload/main/style.css');
    if ($stylesCssContent !== false) {
        $zip->addFromString('style.css', $stylesCssContent);
    }
    
    $zip->close();
    $zipCreated = true;
} else {
    $zipCreated = false;
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
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
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
                ZIP file created successfully with the latest versions of all script files.
            </div>
            
            <p>The following files are included in the update package:</p>
            <ul>
                <?php foreach ($foundFiles as $file): ?>
                    <li><?php echo htmlspecialchars($file); ?></li>
                <?php endforeach; ?>
                <li>script.js (latest from GitHub)</li>
                <li>style.css (latest from GitHub)</li>
            </ul>
            
            <p>
                <a href="<?php echo $tempDir . '/' . $zipName; ?>" class="button" download>Download Update Package</a>
            </p>
            
            <div class="note">
                <strong>How to install the update:</strong>
                <ol>
                    <li>Download the ZIP file using the button above</li>
                    <li>Extract all files from the ZIP</li>
                    <li>Upload all files to your web server, replacing the existing files</li>
                    <li>Test the application to ensure it's working correctly</li>
                </ol>
            </div>
        <?php else: ?>
            <div class="error">
                Failed to create ZIP file. Please check server permissions.
            </div>
        <?php endif; ?>
    </div>
    
    <div class="container">
        <h2>Manual File Upload</h2>
        <p>If you prefer to upload files individually, you can use the manual upload utility:</p>
        <p>
            <a href="manual_upload.php" class="button">Go to Manual Upload</a>
        </p>
    </div>
</body>
</html>