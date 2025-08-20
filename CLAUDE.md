# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a PHP-based image upload web application that allows users to upload product images, automatically processes them (cropping to 1500x1500px squares and compressing), and generates CSV files with product URLs. The application extracts article numbers from filenames and provides intelligent sorting for front/back product images.

## Architecture

- **Frontend**: Vanilla HTML/CSS/JavaScript with drag-and-drop functionality
  - `index.html` - Main upload interface
  - `script.js` - Client-side logic for file handling, upload progress, and CSV generation
  - `style.css` - UI styling
- **Backend**: PHP scripts for image processing and file management
  - `upload.php` - Main upload handler with comprehensive image processing
  - `simple_upload.php` - Streamlined upload script with enhanced compression
  - Helper scripts: `ensure_dir.php`, `create_dir.php`, `fix_permissions.php`
- **Deployment**: FTP-based deployment to Hostinger hosting
  - `improved-uploader.sh` - FTP deployment script
  - GitHub secrets configuration for automated deployment

## Key Features

- Automatic article number extraction from filenames (6-8 digit numbers)
- Intelligent image sorting (front/back detection for product images)
- Image processing: crop to 1500x1500px squares, compression (JPEG 40% quality, PNG max compression)
- Drag-and-drop interface with progress tracking
- CSV export with full image URLs
- Server-side directory management and permissions handling

## Development Workflow

### Local Testing
No specific build process required. Open `index.html` directly in browser for frontend testing.

For full functionality testing:
```bash
php -S localhost:8000
```

### Deployment
Use the FTP deployment script:
```bash
./improved-uploader.sh
```

Or use the manual update utilities:
- `download_updates.php` - Download files from server
- `manual_upload.php` - Upload specific files

### File Management
- `clear_cache.php` - Clear server cache
- `fix_permissions.php` - Fix directory permissions on server
- Images are stored in `/images/` directory

## Server Configuration

The application is deployed to Hostinger hosting:
- Base URL: `https://upload.karlknoop.com/`
- Images stored at: `/home/u613176276/domains/karlknoop.com/public_html/upload/images/`
- Requires PHP 7.0+ with GD library
- Apache with mod_rewrite and AllowOverride All

## Important Notes

- Article numbers are extracted from filenames using regex patterns for 6-8 digit numbers
- Images with "front" or "back" in filename get special positioning (Image1=back, Image2=front)
- All uploaded images are automatically processed to 1500x1500px squares
- CSV output includes full URLs to uploaded images on the server
- The application handles CORS for cross-origin uploads