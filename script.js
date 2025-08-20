document.addEventListener('DOMContentLoaded', function() {
    // DOM-Elemente
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('file-input');
    const processButton = document.getElementById('process-button');
    const resultsSection = document.getElementById('results-section');
    const imageCount = document.getElementById('image-count');
    const articleCount = document.getElementById('article-count');
    const imageTable = document.getElementById('image-tbody');
    const downloadCsvButton = document.getElementById('download-csv');
    const autoRenameCheckbox = document.getElementById('auto-rename');
    
    // Globale Variablen
    let uploadedFiles = [];
    let processedArticles = {};
    let hasFrontBack = {};
    let dragSrcEl = null; // Element, das gezogen wird
    const serverUrl = 'https://upload.karlknoop.com/'; // URL zur PHP-Datei
    
    // Notification system
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            color: white;
            font-weight: 500;
            z-index: 1000;
            min-width: 300px;
            max-width: 400px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
        `;
        
        const backgroundColor = {
            'success': '#10b981',
            'error': '#ef4444',
            'warning': '#f59e0b',
            'info': '#3b82f6'
        };
        
        notification.style.backgroundColor = backgroundColor[type] || backgroundColor.info;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Trigger animation
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }
    
    // Event-Listener für Drag & Drop
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight() {
        dropArea.classList.add('highlight');
    }
    
    function unhighlight() {
        dropArea.classList.remove('highlight');
    }
    
    // Event-Listener für Datei-Drop
    dropArea.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }
    
    // Event-Listener für Datei-Auswahl über Button
    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });
    
    // Dateien verarbeiten
    function handleFiles(files) {
        uploadedFiles = [...files].filter(file => {
            const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            return validTypes.includes(file.type);
        });
        
        if (uploadedFiles.length > 0) {
            processButton.classList.remove('disabled');
            processButton.disabled = false;
            
            // Show file count feedback
            const fileCountInfo = document.createElement('div');
            fileCountInfo.className = 'file-count-info';
            fileCountInfo.style.cssText = 'margin-top: 1rem; padding: 0.75rem; background: #dbeafe; border-radius: 0.5rem; color: #1e40af; text-align: center; font-weight: 500;';
            fileCountInfo.textContent = `${uploadedFiles.length} image${uploadedFiles.length > 1 ? 's' : ''} selected and ready for processing`;
            
            // Remove any existing file count info
            const existingInfo = dropArea.parentNode.querySelector('.file-count-info');
            if (existingInfo) {
                existingInfo.remove();
            }
            
            dropArea.parentNode.insertBefore(fileCountInfo, dropArea.nextSibling);
        } else {
            showNotification('Please select valid image files (JPEG, JPG, PNG).', 'error');
        }
    }
    
    // Event-Listener für Verarbeiten-Button
    processButton.addEventListener('click', processImages);
    
    // Bilder verarbeiten
    async function processImages() {
        resetResults();
        
        // Status-Anzeige hinzufügen
        const statusElement = document.createElement('div');
        statusElement.className = 'upload-status';
        statusElement.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <span style="font-size: 1.5rem; color: var(--primary-color); animation: spin 1s linear infinite;">⚙️</span>
                    <p style="margin: 0; font-weight: 600; color: var(--gray-700);">Processing and uploading images...</p>
                </div>
                <button id="cancel-upload" style="padding: 0.5rem 1rem; background: #ef4444; color: white; border: none; border-radius: 0.375rem; cursor: pointer; font-size: 0.875rem;">
                    Cancel Upload
                </button>
            </div>
            <div class="progress-bar"><div class="progress"></div></div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem;">
                <p id="upload-progress-text" style="margin: 0; font-size: 0.9rem; color: var(--gray-600);">Initializing...</p>
                <div id="upload-stats" style="font-size: 0.8rem; color: var(--gray-500);">
                    <span id="success-count">0</span> ✅ | 
                    <span id="error-count">0</span> ❌ | 
                    <span id="queue-status">Preparing...</span>
                </div>
            </div>
        `;
        document.querySelector('.upload-card').appendChild(statusElement);
        
        const progressBar = statusElement.querySelector('.progress');
        
        // Cancel functionality
        let uploadCancelled = false;
        const cancelButton = document.getElementById('cancel-upload');
        cancelButton.addEventListener('click', () => {
            uploadCancelled = true;
            showNotification('Upload cancelled by user', 'warning');
            setTimeout(() => {
                statusElement.remove();
            }, 1000);
        });
        
        // Dateien nach Artikelnummern gruppieren
        const autoRename = autoRenameCheckbox.checked;
        const articleFiles = {};
        
        // Zuerst prüfen, welche Artikel front/back Bilder haben
        uploadedFiles.forEach(file => {
            const fileName = file.name;
            const articleNumber = extractArticleNumber(fileName);
            
            if (articleNumber) {
                if (!hasFrontBack[articleNumber]) {
                    hasFrontBack[articleNumber] = false;
                }
                
                if (fileName.toLowerCase().includes('front') || fileName.toLowerCase().includes('back')) {
                    hasFrontBack[articleNumber] = true;
                }
            }
        });
        
        // Batch upload system to prevent server overload
        const batchSize = 5; // Process 5 images at a time
        const totalFiles = uploadedFiles.length;
        let processedCount = 0;
        let successCount = 0;
        let errorCount = 0;
        const uploadResults = [];
        
        // Split files into batches
        const batches = [];
        for (let i = 0; i < uploadedFiles.length; i += batchSize) {
            batches.push(uploadedFiles.slice(i, i + batchSize));
        }
        
        // Update queue status
        const queueStatus = document.getElementById('queue-status');
        if (queueStatus) {
            queueStatus.textContent = `${batches.length} batches queued`;
        }
        
        // Process each batch sequentially
        for (let batchIndex = 0; batchIndex < batches.length; batchIndex++) {
            // Check for cancellation
            if (uploadCancelled) {
                console.log('Upload cancelled at batch', batchIndex + 1);
                break;
            }
            
            const batch = batches[batchIndex];
            const batchPromises = [];
            
            // Update progress text for current batch
            const progressText = document.getElementById('upload-progress-text');
            const queueStatus = document.getElementById('queue-status');
            if (progressText) {
                progressText.textContent = `Processing batch ${batchIndex + 1} of ${batches.length} (${processedCount}/${totalFiles} images completed)`;
            }
            if (queueStatus) {
                queueStatus.textContent = `Batch ${batchIndex + 1}/${batches.length}`;
            }
            
            for (const file of batch) {
                let fileName = file.name;
                const articleNumber = extractArticleNumber(fileName);
                
                if (articleNumber) {
                    // Dateiname anpassen, wenn Option aktiviert
                    if (autoRename) {
                        fileName = fileName.toLowerCase().replace(/[ _]/g, '-');
                    }
                    
                    // Upload-Promise erstellen
                    const uploadPromise = uploadFileToServer(file, fileName)
                        .then(response => {
                            // Fortschritt aktualisieren
                            processedCount++;
                            successCount++;
                            const percentage = (processedCount / totalFiles) * 100;
                            progressBar.style.width = percentage + '%';
                            
                            // Statistiken aktualisieren
                            const successCountEl = document.getElementById('success-count');
                            if (successCountEl) {
                                successCountEl.textContent = successCount;
                            }
                            
                            // Fortschrittstext aktualisieren
                            const progressText = document.getElementById('upload-progress-text');
                            if (progressText) {
                                progressText.textContent = `Processing ${processedCount} of ${totalFiles} images (${Math.round(percentage)}%)`;
                            }
                            
                            // Datei in Artikelstruktur einfügen
                            if (!articleFiles[articleNumber]) {
                                articleFiles[articleNumber] = [];
                            }
                            
                            // Speichere die Dateiinformationen mit der URL vom Server
                            articleFiles[articleNumber].push({
                                file: file,
                                fileName: fileName,
                                url: URL.createObjectURL(file), // Lokale Vorschau
                                serverUrl: response.url // Tatsächliche URL auf dem Server
                            });
                            
                            return { articleNumber, fileName, response };
                        })
                        .catch(error => {
                            processedCount++; // Still count failed uploads for progress
                            errorCount++;
                            const percentage = (processedCount / totalFiles) * 100;
                            progressBar.style.width = percentage + '%';
                            
                            // Statistiken aktualisieren
                            const errorCountEl = document.getElementById('error-count');
                            if (errorCountEl) {
                                errorCountEl.textContent = errorCount;
                            }
                            
                            console.error('Fehler beim Hochladen:', error);
                            return { error: true, fileName, message: error.message };
                        });
                    
                    batchPromises.push(uploadPromise);
                }
            }
            
            // Wait for current batch to complete before proceeding to next batch
            const batchResults = await Promise.all(batchPromises);
            uploadResults.push(...batchResults);
            
            // Add small delay between batches to prevent server overload
            if (batchIndex < batches.length - 1) {
                await new Promise(resolve => setTimeout(resolve, 1000)); // 1 second delay
            }
        }
        
        // Final status update
        const queueStatus = document.getElementById('queue-status');
        if (queueStatus) {
            queueStatus.textContent = `Completed! ${successCount}✅ ${errorCount}❌`;
        }
        
        // Remove status display after a short delay to show final results
        setTimeout(() => {
            statusElement.remove();
        }, 2000);
        
        // Fehler überprüfen
        const errors = uploadResults.filter(result => result.error);
        if (errors.length > 0) {
            showNotification(`${errors.length} images could not be uploaded. Check console for details.`, 'error');
            console.error('Upload errors:', errors);
        } else {
            showNotification(`Successfully processed ${uploadResults.length} images!`, 'success');
        }
        
        // Bilder in Artikelstruktur sortieren
        Object.keys(articleFiles).forEach(articleNumber => {
            const files = articleFiles[articleNumber];
            processedArticles[articleNumber] = ['', '', '', '', '', '', ''];
            
            if (hasFrontBack[articleNumber]) {
                // Mit front/back Sortierung
                files.forEach(fileObj => {
                    const fileName = fileObj.fileName.toLowerCase();
                    
                    if (fileName.includes('back')) {
                        processedArticles[articleNumber][0] = fileObj;
                    } else if (fileName.includes('front')) {
                        processedArticles[articleNumber][1] = fileObj;
                    } else {
                        // Freie Position finden
                        for (let i = 2; i < 7; i++) {
                            if (processedArticles[articleNumber][i] === '') {
                                processedArticles[articleNumber][i] = fileObj;
                                break;
                            }
                        }
                    }
                });
            } else {
                // Einfach nach Reihenfolge sortieren
                files.forEach((fileObj, index) => {
                    if (index < 7) {
                        processedArticles[articleNumber][index] = fileObj;
                    }
                });
            }
        });
        
        // Statistiken aktualisieren
        imageCount.textContent = uploadedFiles.length;
        articleCount.textContent = Object.keys(processedArticles).length;
        
        // Tabelle befüllen
        populateTable();
        
        // Ergebnisse anzeigen
        resultsSection.classList.remove('hidden');
    }
    
    // Datei zum Server hochladen
    async function uploadFileToServer(file, fileName) {
        const maxRetries = 3;
        let lastError;
        
        for (let attempt = 1; attempt <= maxRetries; attempt++) {
            try {
                const formData = new FormData();
                formData.append('image', file, fileName);
                
                // Verwende das einfache Upload-Script mit verbesserter Komprimierung und Größenänderung
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 60000); // 60 second timeout
                
                const response = await fetch(serverUrl + 'simple_upload.php', {
                    method: 'POST',
                    body: formData,
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                if (!response.ok) {
                    const errorText = await response.text().catch(() => 'No response text');
                    console.error(`HTTP error (attempt ${attempt}):`, response.status, response.statusText, errorText);
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                let result;
                try {
                    result = await response.json();
                    console.log(`Upload successful (attempt ${attempt}):`, result);
                } catch (e) {
                    console.error(`JSON parse error (attempt ${attempt}):`, e);
                    throw new Error('Invalid server response format');
                }
                
                if (!result.success) {
                    throw new Error(result.message || 'Server reported upload failure');
                }
                
                return result;
                
            } catch (error) {
                lastError = error;
                console.warn(`Upload attempt ${attempt} failed for ${fileName}:`, error.message);
                
                if (attempt < maxRetries) {
                    // Wait before retrying (exponential backoff)
                    await new Promise(resolve => setTimeout(resolve, Math.pow(2, attempt) * 1000));
                }
            }
        }
        
        // If all retries failed
        console.error(`All upload attempts failed for ${fileName}:`, lastError);
        throw lastError;
    }
    
    // Drag & Drop Funktionen für die Tabellenzellen
    function handleDragStart(e) {
        this.style.opacity = '0.4';
        this.classList.add('dragging');
        dragSrcEl = this;
        
        // Speichere Informationen über das gezogene Element
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
        
        // Speichere Artikel- und Zelleninformationen
        const row = this.parentNode;
        const articleNumber = row.firstChild.textContent;
        const cellIndex = Array.from(row.children).indexOf(this) - 1; // -1 wegen der Artikelnummer-Zelle
        
        e.dataTransfer.setData('application/json', JSON.stringify({
            articleNumber: articleNumber,
            cellIndex: cellIndex
        }));
    }

    function handleDragOver(e) {
        if (e.preventDefault) {
            e.preventDefault(); // Erlaubt das Dropping
        }
        e.dataTransfer.dropEffect = 'move';
        return false;
    }

    function handleDragEnter(e) {
        this.classList.add('over');
    }

    function handleDragLeave(e) {
        this.classList.remove('over');
    }

    function handleDrop(e) {
        if (e.stopPropagation) {
            e.stopPropagation(); // Verhindert Browser-Redirect
        }
        
        if (dragSrcEl != this) {
            // Hole die Daten des gezogenen Elements
            const sourceData = JSON.parse(e.dataTransfer.getData('application/json'));
            const sourceArticle = sourceData.articleNumber;
            const sourceIndex = sourceData.cellIndex;
            
            // Bestimme Zielinformationen
            const row = this.parentNode;
            const targetArticle = row.firstChild.textContent;
            const targetIndex = Array.from(row.children).indexOf(this) - 1; // -1 wegen der Artikelnummer-Zelle
            
            // Tausche die Bilder im Datenmodell
            const tempImg = processedArticles[sourceArticle][sourceIndex];
            processedArticles[sourceArticle][sourceIndex] = processedArticles[targetArticle][targetIndex];
            processedArticles[targetArticle][targetIndex] = tempImg;
            
            // Aktualisiere die Tabelle
            populateTable();
        }
        
        return false;
    }

    function handleDragEnd(e) {
        // Entferne alle visuellen Markierungen
        document.querySelectorAll('td').forEach(function(td) {
            td.classList.remove('over');
            td.classList.remove('dragging');
            td.style.opacity = '1';
        });
    }
    
    // Tabelle mit Bildern befüllen
    function populateTable() {
        imageTable.innerHTML = '';
        
        // Artikel nach Nummer sortieren
        const sortedArticles = Object.keys(processedArticles).sort();
        
        sortedArticles.forEach(articleNumber => {
            const tr = document.createElement('tr');
            const tdArticle = document.createElement('td');
            tdArticle.textContent = articleNumber;
            tr.appendChild(tdArticle);
            
            // Bilder für diesen Artikel hinzufügen
            processedArticles[articleNumber].forEach((fileObj, index) => {
                const td = document.createElement('td');
                
                // Mache die Zelle draggable
                td.setAttribute('draggable', 'true');
                td.classList.add('droppable-cell');
                td.dataset.position = index;
                td.dataset.article = articleNumber;
                
                // Füge Drag & Drop Event Listener hinzu
                td.addEventListener('dragstart', handleDragStart, false);
                td.addEventListener('dragenter', handleDragEnter, false);
                td.addEventListener('dragover', handleDragOver, false);
                td.addEventListener('dragleave', handleDragLeave, false);
                td.addEventListener('drop', handleDrop, false);
                td.addEventListener('dragend', handleDragEnd, false);
                
                if (fileObj && fileObj.url) {
                    const img = document.createElement('img');
                    img.src = fileObj.url;
                    img.alt = fileObj.fileName;
                    img.title = fileObj.fileName;
                    img.addEventListener('click', (e) => {
                        // Verhindere das Öffnen des Bildes beim Drag & Drop
                        if (!td.classList.contains('dragging')) {
                            window.open(fileObj.url, '_blank');
                        }
                    });
                    td.appendChild(img);
                }
                
                tr.appendChild(td);
            });
            
            imageTable.appendChild(tr);
        });
    }
    
    // CSV herunterladen
    downloadCsvButton.addEventListener('click', downloadCSV);
    
    function downloadCSV() {
        if (Object.keys(processedArticles).length === 0) {
            showNotification('No processed images available for CSV export.', 'warning');
            return;
        }
        
        showNotification('Generating CSV export...', 'info');
        
        let csvContent = 'Artikelnummer;Image1;Image2;Image3;Image4;Image5;Image6;Image7\n';
        
        // Artikel nach Nummer sortieren
        const sortedArticles = Object.keys(processedArticles).sort();
        
        // Protokoll und Host für absolute URL-Erstellung
        const protocol = window.location.protocol;
        const host = window.location.host;
        
        // Zur Überprüfung der URLs in der Konsole anzeigen
        console.log('Generating CSV with image URLs, base:', protocol + '//' + host);
        
        sortedArticles.forEach(articleNumber => {
            let row = articleNumber;
            
            processedArticles[articleNumber].forEach(fileObj => {
                // Verwende die Server-URL für die CSV-Datei, wenn verfügbar
                if (fileObj && fileObj.serverUrl) {
                    console.log('Server URL verfügbar:', fileObj.serverUrl);
                    row += ';' + fileObj.serverUrl;
                } else if (fileObj && fileObj.fileName) {
                    // Fallback: Generiere eine URL aus dem Dateinamen
                    const imageUrl = protocol + '//' + host + '/images/' + fileObj.fileName;
                    console.log('Generierte URL:', imageUrl);
                    row += ';' + imageUrl;
                } else {
                    row += ';';
                }
            });
            
            csvContent += row + '\n';
        });
        
        console.log('CSV Vorschau:', csvContent.substring(0, 500) + '...');
        
        // CSV-Datei erstellen und herunterladen
        const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
        const filename = `product-images-${timestamp}.csv`;
        
        const blob = new Blob([csvContent], { type: 'text/csv;charset=latin-1;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.setAttribute('href', url);
        a.setAttribute('download', filename);
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        
        showNotification(`CSV export completed: ${filename}`, 'success');
    }
    
    // Artikelnummer aus Dateinamen extrahieren (6-8 stellige Zahl)
    function extractArticleNumber(fileName) {
        // Versuche zuerst eine 8-stellige Zahl zu finden
        let match = fileName.match(/(\d{8})/);
        if (match) return match[1];
        
        // Falls keine 8-stellige Zahl gefunden wurde, suche nach 6-stelligen Zahlen
        match = fileName.match(/(\d{6})/);
        return match ? match[1] : null;
    }
    
    // Zurücksetzen der Ergebnisse
    function resetResults() {
        processedArticles = {};
        hasFrontBack = {};
        imageTable.innerHTML = '';
    }
});
