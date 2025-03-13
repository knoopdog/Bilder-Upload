<?php
// This script is included at the beginning of upload scripts to ensure directories exist

/**
 * Create directory with proper permissions and make sure it's writable
 *
 * @param string $dirPath Path to directory that should exist
 * @return bool Success status
 */
function ensureDirectoryExists($dirPath) {
    // Check if directory already exists
    if (file_exists($dirPath)) {
        // If exists but not writable, try to make it writable
        if (!is_writable($dirPath)) {
            @chmod($dirPath, 0777);
            return is_writable($dirPath);
        }
        return true;
    }
    
    // Doesn't exist, try to create it
    $created = @mkdir($dirPath, 0777, true);
    
    if ($created) {
        // Ensure permissions are set
        @chmod($dirPath, 0777);
        return true;
    }
    
    return false;
}

// Always ensure the images directory exists
ensureDirectoryExists(__DIR__ . '/images');
ensureDirectoryExists('images'); // Try relative path as fallback
