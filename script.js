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
        } else {
            alert('Bitte wählen Sie gültige Bilddateien aus (JPEG, JPG, PNG).');
        }
    }
    
    // Event-Listener für Verarbeiten-Button
    processButton.addEventListener('click', processImages);
    
    // Bilder verarbeiten
    function processImages() {
        resetResults();
        
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
        
        // Dann die Bilder verarbeiten
        uploadedFiles.forEach(file => {
            let fileName = file.name;
            const articleNumber = extractArticleNumber(fileName);
            
            if (articleNumber) {
                // Dateiname anpassen, wenn Option aktiviert
                if (autoRename) {
                    fileName = fileName.toLowerCase().replace(/[ _]/g, '-');
                }
                
                if (!articleFiles[articleNumber]) {
                    articleFiles[articleNumber] = [];
                }
                
                articleFiles[articleNumber].push({
                    file: file,
                    fileName: fileName,
                    url: URL.createObjectURL(file)
                });
            }
        });
        
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
        let csvContent = 'Artikelnummer;Image1;Image2;Image3;Image4;Image5;Image6;Image7\n';
        
        // Artikel nach Nummer sortieren
        const sortedArticles = Object.keys(processedArticles).sort();
        
        sortedArticles.forEach(articleNumber => {
            let row = articleNumber;
            
            processedArticles[articleNumber].forEach(fileObj => {
                row += ';' + (fileObj && fileObj.fileName ? fileObj.fileName : '');
            });
            
            csvContent += row + '\n';
        });
        
        // CSV-Datei erstellen und herunterladen
        const blob = new Blob([csvContent], { type: 'text/csv;charset=latin-1;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.setAttribute('href', url);
        a.setAttribute('download', 'artikelbilder.csv');
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
    
    // Artikelnummer aus Dateinamen extrahieren (8-stellige Zahl)
    function extractArticleNumber(fileName) {
        const match = fileName.match(/(\d{8})/);
        return match ? match[1] : null;
    }
    
    // Zurücksetzen der Ergebnisse
    function resetResults() {
        processedArticles = {};
        hasFrontBack = {};
        imageTable.innerHTML = '';
    }
});