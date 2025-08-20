<?php
// Enhanced upload script with image processing
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Content-Type: application/json; charset=UTF-8');

// Include directory helper
require_once 'ensure_dir.php';

// Image processing configuration
$uploadDir = __DIR__ . '/images/';
if (!file_exists($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}
$uploadDirWeb = 'images/'; // For web URLs
$maxSize = 10 * 1024 * 1024; // 10 MB max file size
$targetWidth = 1500;         // Target width
$targetHeight = 1500;        // Target height
$jpegQuality = 40;           // Higher compression (lower quality) for JPEGs
$pngCompression = 9;         // Maximum compression for PNGs (0-9)

// Ensure upload directory exists
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        die(json_encode([
            'success' => false,
            'message' => 'Failed to create upload directory: ' . $uploadDir
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

// Get image information
$imageInfo = getimagesize($file['tmp_name']);
if ($imageInfo === false) {
    die(json_encode([
        'success' => false,
        'message' => 'Invalid image file'
    ]));
}

// Create image resource based on type
$sourceImage = null;
switch ($imageInfo[2]) {
    case IMAGETYPE_JPEG:
        $sourceImage = imagecreatefromjpeg($file['tmp_name']);
        break;
    case IMAGETYPE_PNG:
        $sourceImage = imagecreatefrompng($file['tmp_name']);
        break;
    case IMAGETYPE_GIF:
        $sourceImage = imagecreatefromgif($file['tmp_name']);
        break;
    default:
        die(json_encode([
            'success' => false,
            'message' => 'Unsupported image format'
        ]));
}

if (!$sourceImage) {
    die(json_encode([
        'success' => false,
        'message' => 'Failed to process image'
    ]));
}

// Get original dimensions
$sourceWidth = imagesx($sourceImage);
$sourceHeight = imagesy($sourceImage);

// Determine crop coordinates (to maintain aspect ratio)
if ($sourceWidth > $sourceHeight) {
    // Landscape image
    $squareSize = $sourceHeight;
    $sourceX = floor(($sourceWidth - $sourceHeight) / 2);
    $sourceY = 0;
} else {
    // Portrait image
    $squareSize = $sourceWidth;
    $sourceX = 0;
    $sourceY = floor(($sourceHeight - $sourceWidth) / 2);
}

// Create new canvas for the resized image
$targetImage = imagecreatetruecolor($targetWidth, $targetHeight);

// Handle transparency for PNGs
if ($imageInfo[2] === IMAGETYPE_PNG) {
    // Set blend mode
    imagealphablending($targetImage, false);
    imagesavealpha($targetImage, true);
    
    // Allocate transparent color
    $transparent = imagecolorallocatealpha($targetImage, 0, 0, 0, 127);
    
    // Fill with transparent color
    imagefilledrectangle($targetImage, 0, 0, $targetWidth, $targetHeight, $transparent);
}

// Resize and crop the image
imagecopyresampled(
    $targetImage,    // Destination image
    $sourceImage,    // Source image
    0, 0,            // Destination x, y
    $sourceX, $sourceY, // Source x, y
    $targetWidth, $targetHeight, // Destination width, height
    $squareSize, $squareSize      // Source width, height (square crop)
);

// Save the processed image
$saveResult = false;
switch ($imageInfo[2]) {
    case IMAGETYPE_JPEG:
        $saveResult = imagejpeg($targetImage, $uploadPath, $jpegQuality);
        break;
    case IMAGETYPE_PNG:
        $saveResult = imagepng($targetImage, $uploadPath, $pngCompression);
        break;
    case IMAGETYPE_GIF:
        $saveResult = imagegif($targetImage, $uploadPath);
        break;
}

// Clean up
imagedestroy($sourceImage);
imagedestroy($targetImage);

if (!$saveResult) {
    die(json_encode([
        'success' => false,
        'message' => 'Failed to save processed image'
    ]));
}

// Get the final file size
$finalSize = filesize($uploadPath);
$finalSizeKB = round($finalSize / 1024, 2);

// Success! Generate the URL to the file
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$fileUrl = $protocol . $host . '/' . $uploadDirWeb . $newFilename;

// Log the file information for debugging
error_log("File saved to: {$uploadPath} (Size: {$finalSizeKB} KB)");
error_log("Generated URL: {$fileUrl}");

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'File uploaded and processed successfully',
    'filename' => $newFilename,
    'url' => $fileUrl,
    'dimensions' => $targetWidth . 'x' . $targetHeight,
    'size' => $finalSizeKB . ' KB'
]);
