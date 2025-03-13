<?php
// This script creates a basic upload form to manually upload script.js and other key files
header('Content-Type: text/html; charset=UTF-8');

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['file'])) {
    $file = $_FILES['file'];
    $targetPath = $_POST['path'] ?? '';
    
    // Validate path input
    if (empty($targetPath) || strpos($targetPath, '..') !== false) {
        $error = 'Invalid path specified';
    } else {
        // Create target directory if it doesn't exist
        $targetDir = dirname($targetPath);
        if (!file_exists($targetDir) && $targetDir !== '.') {
            mkdir($targetDir, 0777, true);
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $success = 'File uploaded successfully to: ' . $targetPath;
        } else {
            $error = 'Failed to upload file. Error code: ' . $file['error'];
        }
    }
}

// List existing files in current directory
function listFiles($dir = '.') {
    $files = [];
    if ($handle = opendir($dir)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != '.' && $entry != '..') {
                $path = $dir . '/' . $entry;
                $type = is_dir($path) ? 'dir' : 'file';
                $files[] = [
                    'name' => $entry,
                    'path' => $path,
                    'type' => $type,
                    'size' => $type === 'file' ? filesize($path) : 0,
                    'modified' => date('Y-m-d H:i:s', filemtime($path))
                ];
            }
        }
        closedir($handle);
    }
    return $files;
}

$files = listFiles('.');
$scriptContent = '';
if (file_exists('script.js')) {
    $scriptContent = file_get_contents('script.js');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual File Upload</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            display: flex;
            gap: 20px;
        }
        .file-list {
            width: 40%;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
        }
        .upload-form {
            width: 60%;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        tr:hover {background-color: #f5f5f5;}
        input, textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {background-color: #45a049;}
        .message {padding: 10px; margin-bottom: 15px; border-radius: 4px;}
        .success {background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;}
        .error {background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;}
        .code-preview {
            max-height: 300px;
            overflow: auto;
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f8f9fa;
            font-family: monospace;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Manual File Upload</h1>
    
    <?php if (!empty($success)): ?>
        <div class="message success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="container">
        <div class="file-list">
            <h2>Existing Files</h2>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Modified</th>
                </tr>
                <?php foreach ($files as $file): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($file['name']); ?></td>
                        <td><?php echo $file['type']; ?></td>
                        <td><?php echo $file['type'] === 'file' ? number_format($file['size']) . ' B' : '-'; ?></td>
                        <td><?php echo $file['modified']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
        <div class="upload-form">
            <h2>Upload File</h2>
            <form method="post" enctype="multipart/form-data">
                <div>
                    <label for="file">Select File:</label>
                    <input type="file" id="file" name="file" required>
                </div>
                <div>
                    <label for="path">Target Path:</label>
                    <input type="text" id="path" name="path" placeholder="e.g., script.js, style.css" required>
                </div>
                <button type="submit">Upload File</button>
            </form>
            
            <h3>Current script.js Content</h3>
            <div class="code-preview"><?php echo htmlspecialchars($scriptContent); ?></div>
        </div>
    </div>
</body>
</html>