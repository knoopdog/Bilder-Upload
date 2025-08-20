#!/bin/bash

# This script creates the upload directory and uploads files to Hostinger FTP server

echo "Starting improved FTP upload to Hostinger..."

# FTP Connection Details
FTP_HOST="ftp.karlknoop.com"
FTP_USER="u613176276.bilderupload"
FTP_PASS="H0kwvsX>XfV?AU]*"
REMOTE_BASE="/home/u613176276/domains/karlknoop.com/public_html"
REMOTE_DIR="$REMOTE_BASE/upload"
LOCAL_DIR="/Users/karlknoop/Documents/Projekte/Visuell-Code/Bilder-Upload"

# Create batch file for FTP commands - first create directory if it does not exist
cat > /tmp/ftp_dir_create.txt << EOF
open $FTP_HOST
user $FTP_USER "$FTP_PASS"
cd $REMOTE_BASE
mkdir upload
bye
EOF

# Create batch file for FTP commands - then upload files
cat > /tmp/ftp_commands.txt << EOF
open $FTP_HOST
user $FTP_USER "$FTP_PASS"
cd $REMOTE_DIR
lcd $LOCAL_DIR
prompt
binary
mput *.html *.js *.css
mkdir images
cd images
lcd images
mput *.jpg
bye
EOF

# First create the directory
echo "Creating upload directory if it does not exist..."
ftp -v -n < /tmp/ftp_dir_create.txt

# Then upload files
echo "Uploading files..."
ftp -v -n < /tmp/ftp_commands.txt

# Remove temporary batch files
rm /tmp/ftp_dir_create.txt
rm /tmp/ftp_commands.txt

echo "FTP upload completed!"

