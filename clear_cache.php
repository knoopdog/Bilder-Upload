<?php
// Set cache-control headers to prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Cache Clearing Utility</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1, h2, h3 {
            color: #4a6fa5;
        }
        .container {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .warning {
            color: #856404;
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
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
            margin: 10px 5px 10px 0;
            cursor: pointer;
            border: none;
        }
        .button:hover {
            background-color: #45a049;
        }
        .button.blue {
            background-color: #4a6fa5;
        }
        .button.blue:hover {
            background-color: #3d5d8a;
        }
        .button.red {
            background-color: #d9534f;
        }
        .button.red:hover {
            background-color: #c9302c;
        }
        code {
            background-color: #f8f9fa;
            padding: 2px 4px;
            border-radius: 4px;
            font-family: monospace;
        }
        .browser-instructions {
            margin-top: 15px;
            margin-bottom: 15px;
        }
        .browser-instructions details {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .browser-instructions summary {
            cursor: pointer;
            font-weight: bold;
            color: #4a6fa5;
        }
        .browser-instructions details[open] summary {
            margin-bottom: 10px;
        }
        ol {
            padding-left: 20px;
        }
        .status {
            display: none;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .test-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Cache Clearing Utility</h1>
    
    <div class="container">
        <h2>Clear Browser Cache</h2>
        <p>If you're experiencing issues with the image upload function, it might be due to cached JavaScript or PHP files. Here are several ways to clear your cache:</p>
        
        <div class="success">
            <strong>Server-side caching headers have been disabled for this page.</strong>
        </div>
        
        <h3>1. Quick Cache Clear</h3>
        <p>Click the button below to attempt to clear your browser cache for this site:</p>
        <button id="clearCacheBtn" class="button red">Clear Cache & Reload</button>
        <div id="cacheStatus" class="status"></div>
        
        <h3>2. Force-Reload Specific Pages</h3>
        <p>Visit these pages with cache bypassing enabled:</p>
        <a href="index.html?nocache=<?php echo time(); ?>" class="button blue">Main Application (Force Reload)</a>
        <a href="test_page.html?nocache=<?php echo time(); ?>" class="button blue">Test Page (Force Reload)</a>
        
        <h3>3. Manual Browser Cache Clearing</h3>
        <div class="browser-instructions">
            <details>
                <summary>Chrome</summary>
                <ol>
                    <li>Press <code>Ctrl+Shift+Delete</code> (Windows/Linux) or <code>Cmd+Shift+Delete</code> (Mac)</li>
                    <li>Set the time range to "All time"</li>
                    <li>Check only "Cached images and files"</li>
                    <li>Click "Clear data"</li>
                </ol>
            </details>
            
            <details>
                <summary>Firefox</summary>
                <ol>
                    <li>Press <code>Ctrl+Shift+Delete</code> (Windows/Linux) or <code>Cmd+Shift+Delete</code> (Mac)</li>
                    <li>Set the time range to "Everything"</li>
                    <li>Check only "Cache"</li>
                    <li>Click "Clear Now"</li>
                </ol>
            </details>
            
            <details>
                <summary>Safari</summary>
                <ol>
                    <li>Click Safari in the menu bar</li>
                    <li>Select "Preferences"</li>
                    <li>Go to the "Advanced" tab</li>
                    <li>Check "Show Develop menu in menu bar"</li>
                    <li>Close Preferences</li>
                    <li>Click Develop in the menu bar</li>
                    <li>Select "Empty Caches"</li>
                </ol>
            </details>
            
            <details>
                <summary>Edge</summary>
                <ol>
                    <li>Press <code>Ctrl+Shift+Delete</code></li>
                    <li>Check "Cached images and files"</li>
                    <li>Click "Clear Now"</li>
                </ol>
            </details>
        </div>
    </div>
    
    <div class="container">
        <h2>Disable Browser Cache for Development</h2>
        <p>To disable the cache completely during development:</p>
        
        <h3>Using Chrome DevTools:</h3>
        <ol>
            <li>Open Chrome DevTools (F12 or Right-click > Inspect)</li>
            <li>Go to the Network tab</li>
            <li>Check the "Disable cache" checkbox</li>
            <li><strong>Important:</strong> The DevTools must remain open for this to work</li>
        </ol>
        
        <h3>Using Firefox DevTools:</h3>
        <ol>
            <li>Open Firefox DevTools (F12 or Right-click > Inspect)</li>
            <li>Go to the Network tab</li>
            <li>Check the "Disable Cache" checkbox</li>
            <li><strong>Important:</strong> The DevTools must remain open for this to work</li>
        </ol>
    </div>
    
    <div class="container">
        <h2>PHP Troubleshooting</h2>
        
        <h3>Add Cache-Busting to URLs</h3>
        <p>When linking to JS or CSS files, add a query parameter with the current timestamp:</p>
        <code>&lt;script src="script.js?v=&lt;?php echo time(); ?&gt;"&gt;&lt;/script&gt;</code>
        
        <div class="warning">
            <p><strong>Note:</strong> If the issue persists after clearing your cache, it might be a server-side caching issue. Some possible solutions:</p>
            <ul>
                <li>Check if your hosting has server-side caching enabled</li>
                <li>Temporarily disable any caching plugins if you're using a CMS</li>
                <li>Contact your hosting provider about their caching policies</li>
            </ul>
        </div>
    </div>
    
    <div class="container">
        <h2>Next Steps</h2>
        <p>After clearing your cache, please try the following:</p>
        <a href="index.html?nocache=<?php echo time(); ?>" class="button">Go to Main Application</a>
        <a href="test_page.html?nocache=<?php echo time(); ?>" class="button">Go to Test Page</a>
    </div>
    
    <div class="test-section">
        <h3>Cache Test</h3>
        <p>Current server time: <strong><?php echo date('Y-m-d H:i:s'); ?></strong></p>
        <p>If you reload this page and the time updates, your browser is not caching this page.</p>
    </div>
    
    <script>
        // Cache clearing JavaScript
        document.getElementById('clearCacheBtn').addEventListener('click', function() {
            const status = document.getElementById('cacheStatus');
            status.style.display = 'block';
            status.style.backgroundColor = '#fff3cd';
            status.textContent = 'Clearing cache...';
            
            // Attempt to clear application cache (older browsers)
            if (window.applicationCache) {
                try {
                    window.applicationCache.addEventListener('updateready', function() {
                        window.applicationCache.swapCache();
                    });
                } catch (e) {
                    console.error('Application cache clear failed:', e);
                }
            }
            
            // Clear localStorage cache if used
            try {
                localStorage.clear();
            } catch (e) {
                console.error('LocalStorage clear failed:', e);
            }
            
            // Clear sessionStorage
            try {
                sessionStorage.clear();
            } catch (e) {
                console.error('SessionStorage clear failed:', e);
            }
            
            // Force reload from server
            setTimeout(function() {
                status.style.backgroundColor = '#d4edda';
                status.textContent = 'Cache cleared! Reloading page...';
                
                setTimeout(function() {
                    window.location.reload(true);
                }, 1000);
            }, 1000);
        });
    </script>
</body>
</html>
