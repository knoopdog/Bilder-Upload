<?php
// Enable detailed error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Log function to help with debugging
function logMessage($message) {
    file_put_contents('upload_log.txt', date('[Y-m-d H:i:s] ') . $message . "\n", FILE_APPEND);
}

logMessage('Upload script started');

// Configuration
$uploadDir = __DIR__ . '/images/';
logMessage('Upload directory: ' . $uploadDir);
$maxDimension = 1500; // Maximum dimension for longest side
$jpegQuality = 40;  // Higher compression (was 60)
$pngCompression = 9; // Maximum PNG compression (0-9 scale)

// Improved directory creation with error handling
try {
    // Check if directory exists, if not create it with full permissions
    if (!file_exists($uploadDir)) {
        logMessage('Directory does not exist, attempting to create');
        if (!mkdir($uploadDir, 0777, true)) {
            throw new Exception("Failed to create directory: $uploadDir");
        }
        logMessage('Directory created successfully');
        chmod($uploadDir, 0777); // Ensure permissions are set correctly
    } else {
        logMessage('Directory already exists');
    }
    
    // Verify directory is writable
    if (!is_writable($uploadDir)) {
        logMessage('Directory is not writable, attempting to update permissions');
        chmod($uploadDir, 0777); // Try to make it writable
        if (!is_writable($uploadDir)) {
            throw new Exception("Directory exists but is not writable: $uploadDir");
        }
    } else {
        logMessage('Directory is writable');
    }
} catch (Exception $e) {
    logMessage('Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

// Check if any files were uploaded
if (empty($_FILES['image'])) {
    logMessage('No file uploaded');
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['image'];
logMessage('File received: ' . $file['name']);
$response = [];

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    logMessage('Upload error: ' . $file['error']);
    $response = ['success' => false, 'message' => 'Upload failed with error code: ' . $file['error']];
} else {
    // Sanitize filename
    $originalFilename = basename($file['name']);
    $sanitizedFilename = strtolower(preg_replace('/[^a-zA-Z0-9.\-]/', '-', $originalFilename));
    $uploadPath = $uploadDir . $sanitizedFilename;
    logMessage('Sanitized filename: ' . $sanitizedFilename);
    logMessage('Full upload path: ' . $uploadPath);
    
    // Process the image (crop and compress)
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo !== false) {
        logMessage('Image dimensions: ' . $imageInfo[0] . 'x' . $imageInfo[1]);
        
        // Create image resource based on type
        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($file['tmp_name']);
                logMessage('Image type: JPEG');
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($file['tmp_name']);
                logMessage('Image type: PNG');
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($file['tmp_name']);
                logMessage('Image type: GIF');
                break;
            default:
                logMessage('Unsupported image format: ' . $imageInfo[2]);
                $response = ['success' => false, 'message' => 'Unsupported image format'];
                echo json_encode($response);
                exit;
        }
        
        // Calculate new dimensions (proportional scaling based on longest side)
        $srcWidth = imagesx($image);
        $srcHeight = imagesy($image);
        logMessage('Source dimensions: ' . $srcWidth . 'x' . $srcHeight);
        
        // Calculate target dimensions
        if ($srcWidth > $srcHeight) {
            // Landscape image - width is the longest side
            if ($srcWidth > $maxDimension) {
                $targetWidth = $maxDimension;
                $targetHeight = round(($srcHeight * $maxDimension) / $srcWidth);
            } else {
                $targetWidth = $srcWidth;
                $targetHeight = $srcHeight;
            }
        } else {
            // Portrait or square image - height is the longest side
            if ($srcHeight > $maxDimension) {
                $targetHeight = $maxDimension;
                $targetWidth = round(($srcWidth * $maxDimension) / $srcHeight);
            } else {
                $targetWidth = $srcWidth;
                $targetHeight = $srcHeight;
            }
        }
        
        logMessage('Target dimensions: ' . $targetWidth . 'x' . $targetHeight);
        
        // Create a new canvas for the resized image
        $resized = imagecreatetruecolor($targetWidth, $targetHeight);
        
        // For PNG, preserve alpha channel
        if ($imageInfo[2] === IMAGETYPE_PNG) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            
            // Allocate transparent color
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            
            // Fill with transparent color
            imagefilledrectangle($resized, 0, 0, $targetWidth, $targetHeight, $transparent);
        }
        
        // Resize proportionally (no cropping)
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $srcWidth, $srcHeight);
        logMessage('Image resized to ' . $targetWidth . 'x' . $targetHeight);
        
        // Save the processed image
        $saveResult = false;
        try {
            switch ($imageInfo[2]) {
                case IMAGETYPE_JPEG:
                    $saveResult = imagejpeg($resized, $uploadPath, $jpegQuality);
                    logMessage('Saved as JPEG with quality: ' . $jpegQuality);
                    break;
                case IMAGETYPE_PNG:
                    $saveResult = imagepng($resized, $uploadPath, $pngCompression);
                    logMessage('Saved as PNG with compression: ' . $pngCompression);
                    break;
                case IMAGETYPE_GIF:
                    $saveResult = imagegif($resized, $uploadPath);
                    logMessage('Saved as GIF');
                    break;
            }
            
            if (!$saveResult) {
                throw new Exception("Failed to save image to $uploadPath");
            }
        } catch (Exception $e) {
            logMessage('Error saving image: ' . $e->getMessage());
            $response = ['success' => false, 'message' => $e->getMessage()];
            echo json_encode($response);
            exit;
        }
        
        // Free memory
        imagedestroy($image);
        imagedestroy($resized);
        
        // Get final file size for logging
        $finalSize = filesize($uploadPath);
        $finalSizeKB = round($finalSize / 1024, 2);
        logMessage('Final image size: ' . $finalSizeKB . ' KB');
        
        // Generate full URL to the image (use the web-accessible path)
        $webImagePath = 'images/' . $sanitizedFilename; // Path relative to the web root
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $fullUrl = $protocol . $host . '/' . $webImagePath;
        logMessage('Full image URL: ' . $fullUrl);
        
        $response = [
            'success' => true, 
            'message' => 'File uploaded successfully',
            'filename' => $sanitizedFilename,
            'url' => $fullUrl,
            'original_dimensions' => $srcWidth . 'x' . $srcHeight,
            'new_dimensions' => $targetWidth . 'x' . $targetHeight,
            'size' => $finalSizeKB . ' KB'
        ];
    } else {
        logMessage('Invalid image file');
        $response = ['success' => false, 'message' => 'Invalid image file'];
    }
}

logMessage('Response: ' . json_encode($response));
echo json_encode($response);
