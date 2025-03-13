<?php
// Simple upload script with minimal complexity
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Content-Type: application/json; charset=UTF-8');

// Basic configuration
$uploadDir = 'images/';
$maxSize = 10 * 1024 * 1024; // 10 MB max file size

// Ensure the upload directory exists with correct permissions
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        die(json_encode([
            'success' => false,
            'message' => 'Failed to create upload directory'
        ]));
    }
    chmod($uploadDir, 0777);
}

// Check if this is a POST request with files
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['image'])) {
    die(json_encode([
        'success' => false,
        'message' => 'No file uploaded or invalid request method'
    ]));
}

// Get the uploaded file information
$file = $_FILES['image'];

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
    ];
    
    $errorMessage = isset($errorMessages[$file['error']]) ? 
                     $errorMessages[$file['error']] : 
                     'Unknown upload error';
    
    die(json_encode([
        'success' => false,
        'message' => $errorMessage
    ]));
}

// Check file size
if ($file['size'] > $maxSize) {
    die(json_encode([
        'success' => false,
        'message' => 'File too large (max ' . ($maxSize / 1024 / 1024) . 'MB)'
    ]));
}

// Get file information and create a safe filename
$originalName = basename($file['name']);
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$newFilename = strtolower(preg_replace('/[^a-zA-Z0-9.\-]/', '-', $originalName));
$uploadPath = $uploadDir . $newFilename;

// Make sure it's an image
$allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array($extension, $allowedTypes)) {
    die(json_encode([
        'success' => false,
        'message' => 'Invalid file type. Allowed types: ' . implode(', ', $allowedTypes)
    ]));
}

// Move the uploaded file to the destination
if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    die(json_encode([
        'success' => false,
        'message' => 'Failed to save the uploaded file'
    ]));
}

// Success! Generate the URL to the file
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$fileUrl = $protocol . $host . dirname($_SERVER['REQUEST_URI']) . '/' . $uploadPath;

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'File uploaded successfully',
    'filename' => $newFilename,
    'url' => $fileUrl
]);
