<?php
// Script to create the images directory and set permissions

// Directory path
$imagesDir = 'images';

// Check if directory exists
if (!file_exists($imagesDir)) {
    // Create directory with permissions
    if (mkdir($imagesDir, 0777, true)) {
        echo "Directory '$imagesDir' created successfully with full permissions.";
    } else {
        echo "Failed to create directory '$imagesDir'. Please check server permissions.";
    }
} else {
    echo "Directory '$imagesDir' already exists.";
    
    // Ensure directory has proper permissions
    if (chmod($imagesDir, 0777)) {
        echo "<br>Permissions set to 0777.";
    } else {
        echo "<br>Failed to set directory permissions. Please check server configuration.";
    }
}

// Create .htaccess file in images directory to allow direct access
$htaccessContent = "# Allow direct access to image files\nOptions +Indexes\nOrder Allow,Deny\nAllow from all\n\n# Enable directory browsing\nOptions +Indexes\n";

$htaccessFile = $imagesDir . '/.htaccess';
if (file_put_contents($htaccessFile, $htaccessContent)) {
    echo "<br>.htaccess file created in images directory.";
} else {
    echo "<br>Failed to create .htaccess file. Please check permissions.";
}

echo "<br><br><strong>Next steps:</strong>";
echo "<br>1. Visit <a href=\"$imagesDir\" target=\"_blank\">/$imagesDir/</a> to check if the directory is accessible.";
echo "<br>2. Try the image upload functionality.";
