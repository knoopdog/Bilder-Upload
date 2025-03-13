<?php
// Test script to diagnose server setup issues

// Enable detailed error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Display PHP info
echo '<h1>Server Environment</h1>';
echo '<pre>';
echo 'PHP Version: ' . phpversion() . "\n";
echo 'Server Software: ' . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo 'Document Root: ' . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo 'Current Script: ' . __FILE__ . "\n";
echo 'Current Directory: ' . __DIR__ . "\n";
echo '</pre>';

// Check GD Library
echo '<h1>GD Library</h1>';
if (function_exists('gd_info')) {
    echo '<pre>';
    print_r(gd_info());
    echo '</pre>';
} else {
    echo '<p style="color: red;">GD Library is not installed!</p>';
}

// Directory tests
echo '<h1>Directory Tests</h1>';

// Test various directory paths
$testPaths = [
    'images/',
    './images/',
    __DIR__ . '/images/',
    $_SERVER['DOCUMENT_ROOT'] . '/images/',
    $_SERVER['DOCUMENT_ROOT'] . '/upload/images/'
];

echo '<table border="1" cellpadding="5">';
echo '<tr><th>Path</th><th>Exists</th><th>Writable</th><th>Action Result</th></tr>';

foreach ($testPaths as $path) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($path) . '</td>';
    
    // Check if exists
    $exists = file_exists($path);
    echo '<td>' . ($exists ? 'Yes' : 'No') . '</td>';
    
    // Check if writable
    $writable = is_writable($path);
    echo '<td>' . ($writable ? 'Yes' : 'No') . '</td>';
    
    // Try action
    $result = '';
    if (!$exists) {
        // Try to create
        $created = @mkdir($path, 0777, true);
        $result = $created ? 'Created directory' : 'Failed to create';
    } else if ($exists && $writable) {
        // Try to write a test file
        $testFile = $path . '/test_' . time() . '.txt';
        $written = @file_put_contents($testFile, 'Test content');
        $result = $written ? 'Wrote test file: ' . basename($testFile) : 'Failed to write test file';
    } else {
        $result = 'Directory exists but not writable';
    }
    
    echo '<td>' . $result . '</td>';
    echo '</tr>';
}

echo '</table>';

// Test image creation
echo '<h1>Image Processing Test</h1>';

try {
    // Create a test image
    $width = 200;
    $height = 200;
    $testImage = imagecreatetruecolor($width, $height);
    
    // Fill with a color
    $red = imagecolorallocate($testImage, 255, 0, 0);
    imagefill($testImage, 0, 0, $red);
    
    // Draw some shapes
    $blue = imagecolorallocate($testImage, 0, 0, 255);
    imagefilledellipse($testImage, $width/2, $height/2, $width/2, $height/2, $blue);
    
    // Try to save to each directory
    foreach ($testPaths as $path) {
        if (file_exists($path) && is_writable($path)) {
            $imagePath = $path . '/test_image_' . time() . '.png';
            $saved = imagepng($testImage, $imagePath);
            echo '<p>';
            echo 'Saving to ' . htmlspecialchars($imagePath) . ': ' . ($saved ? 'Success' : 'Failed');
            if ($saved) {
                echo ' <a href="' . str_replace($_SERVER['DOCUMENT_ROOT'], '', $imagePath) . '" target="_blank">View Image</a>';
            }
            echo '</p>';
        }
    }
    
    // Clean up
    imagedestroy($testImage);
    
    echo '<p>Image test completed.</p>';
} catch (Exception $e) {
    echo '<p style="color: red;">Error during image test: ' . $e->getMessage() . '</p>';
}
