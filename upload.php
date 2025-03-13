<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration
$uploadDir = 'images/';
$maxWidth = 1500;
$maxHeight = 1500;
$compressionQuality = 60;

// Create upload directory if it doesn't exist
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Check if any files were uploaded
if (empty($_FILES['image'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['image'];
$response = [];

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    $response = ['success' => false, 'message' => 'Upload failed with error code: ' . $file['error']];
} else {
    // Sanitize filename
    $originalFilename = basename($file['name']);
    $sanitizedFilename = strtolower(preg_replace('/[^a-zA-Z0-9.\-]/', '-', $originalFilename));
    $uploadPath = $uploadDir . $sanitizedFilename;
    
    // Process the image (crop and compress)
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo !== false) {
        // Create image resource based on type
        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($file['tmp_name']);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($file['tmp_name']);
                break;
            default:
                $response = ['success' => false, 'message' => 'Unsupported image format'];
                echo json_encode($response);
                exit;
        }
        
        // Calculate new dimensions (crop to square 1500x1500)
        $srcWidth = imagesx($image);
        $srcHeight = imagesy($image);
        
        // Determine crop dimensions
        if ($srcWidth > $srcHeight) {
            $squareSize = $srcHeight;
            $srcX = floor(($srcWidth - $srcHeight) / 2);
            $srcY = 0;
        } else {
            $squareSize = $srcWidth;
            $srcX = 0;
            $srcY = floor(($srcHeight - $srcWidth) / 2);
        }
        
        // Create a new canvas for the resized image
        $resized = imagecreatetruecolor($maxWidth, $maxHeight);
        
        // For PNG, preserve alpha channel
        if ($imageInfo[2] === IMAGETYPE_PNG) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }
        
        // Resize and crop
        imagecopyresampled($resized, $image, 0, 0, $srcX, $srcY, $maxWidth, $maxHeight, $squareSize, $squareSize);
        
        // Save the processed image
        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                imagejpeg($resized, $uploadPath, $compressionQuality);
                break;
            case IMAGETYPE_PNG:
                // PNG quality is 0-9, convert from 0-100
                $pngQuality = round(9 - (($compressionQuality / 100) * 9));
                imagepng($resized, $uploadPath, $pngQuality);
                break;
        }
        
        // Free memory
        imagedestroy($image);
        imagedestroy($resized);
        
        // Generate full URL to the image
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $fullUrl = $protocol . $host . '/upload/' . $uploadPath;
        
        $response = [
            'success' => true, 
            'message' => 'File uploaded successfully',
            'filename' => $sanitizedFilename,
            'url' => $fullUrl
        ];
    } else {
        $response = ['success' => false, 'message' => 'Invalid image file'];
    }
}

echo json_encode($response);
