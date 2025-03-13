# Bilder-Upload und CSV-Generator

Diese Webanwendung ermöglicht das Hochladen von Bildern, deren automatische Umbenennung und Sortierung nach Artikelnummern, sowie das Erstellen einer CSV-Datei mit den Bildzuordnungen.

## Funktionen

- Drag & Drop-Upload von Bildern
- Automatische Extraktion von Artikelnummern aus Dateinamen (8-stellige Zahlen)
- Automatische Umbenennung von Dateien (kleinschreiben, Leerzeichen und Unterstriche durch Bindestriche ersetzen)
- Intelligente Sortierung:
  - Bei Artikeln mit "front" oder "back" im Dateinamen: Spezielle Zuordnung zu Image1 (back) und Image2 (front)
  - Bei Artikeln ohne "front" oder "back": Reihenfolge nach Upload
- Tabellarische Darstellung der Bildzuordnungen
- Export als CSV-Datei im Format "Artikelnummer;Image1;Image2;Image3;Image4;Image5;Image6;Image7"

## Deployment auf Hostinger

### Voraussetzungen
- Ein Hostinger-Konto mit Cloud-Hosting-Plan
- FTP-Zugang zu Ihrem Hosting (z.B. über FileZilla)

### Schritte

1. **Dateien vorbereiten**: Stellen Sie sicher, dass Sie die folgenden Dateien haben:
   - `index.html`
   - `style.css`
   - `script.js`

2. **Mit FTP verbinden**:
   - Öffnen Sie FileZilla oder einen anderen FTP-Client
   - Stellen Sie eine Verbindung zu Ihrem Hostinger-Server her (nutzen Sie die von Hostinger bereitgestellten FTP-Daten)
   - Navigieren Sie zu dem Verzeichnis, in dem Sie die Anwendung hosten möchten (in der Regel `public_html` oder ein Unterverzeichnis)

3. **Dateien hochladen**:
   - Laden Sie alle drei Dateien (`index.html`, `style.css`, `script.js`) in das gewählte Verzeichnis hoch

4. **Zugriffsrechte prüfen** (falls nötig):
   - Stellen Sie sicher, dass die Dateien die richtigen Zugriffsrechte haben (in der Regel 644 für HTML, CSS und JS-Dateien)

5. **Anwendung testen**:
   - Rufen Sie Ihre Website im Browser auf: `https://ihre-domain.com/` oder `https://ihre-domain.com/unterverzeichnis/`

## Deployment über GitHub Pages

### Voraussetzungen
- Ein GitHub-Konto
- Git auf Ihrem lokalen Computer (optional, wenn Sie direkt über die GitHub-Weboberfläche arbeiten)

### Schritte

1. **Repository erstellen**:
   - Loggen Sie sich in GitHub ein
   - Klicken Sie auf "New repository"
   - Geben Sie einen Namen für das Repository ein (z.B. "bilder-upload")
   - Wählen Sie "Public" aus
   - Klicken Sie auf "Create repository"

2. **Dateien hochladen**:
   - Klicken Sie im Repository auf "Add file" > "Upload files"
   - Ziehen Sie die drei Dateien (`index.html`, `style.css`, `script.js`) in den Upload-Bereich
   - Geben Sie eine Commit-Nachricht ein (z.B. "Initial commit")
   - Klicken Sie auf "Commit changes"

3. **GitHub Pages aktivieren**:
   - Gehen Sie zu den Repository-Einstellungen (Tab "Settings")
   - Scrollen Sie nach unten zum Abschnitt "GitHub Pages"
   - Unter "Source" wählen Sie "main" (oder "master") als Branch aus
   - Klicken Sie auf "Save"
   - GitHub gibt Ihnen dann die URL, unter der Ihre Seite verfügbar ist (normalerweise `https://ihr-username.github.io/repository-name/`)

4. **Anwendung testen**:
   - Warten Sie einige Minuten, bis GitHub Pages Ihre Seite bereitgestellt hat
   - Besuchen Sie die URL, die GitHub Ihnen angezeigt hat

## Anwendung verwenden

1. **Bilder hochladen**:
   - Ziehen Sie Bilder in den Drop-Bereich oder klicken Sie auf "Bilder auswählen"
   - Die Bilder sollten 8-stellige Artikelnummern im Dateinamen enthalten (z.B. "10012345_Produktname.jpg")
   - Optional: Deaktivieren Sie das Kontrollkästchen, wenn Sie die Dateinamen nicht automatisch anpassen möchten

2. **Bilder verarbeiten**:
   - Klicken Sie auf "Bilder verarbeiten"
   - Die Anwendung erkennt Artikelnummern und sortiert die Bilder entsprechend
   - Bilder mit "front" oder "back" im Namen werden speziell zugeordnet

3. **CSV herunterladen**:
   - Überprüfen Sie die Tabelle mit den zugeordneten Bildern
   - Klicken Sie auf "CSV-Datei herunterladen", um die Zuordnungen als CSV-Datei zu speichern

## Hinweise

- Die Anwendung läuft vollständig im Browser (Client-seitig), es werden keine Daten an einen Server gesendet
- Die Bilder werden nicht auf den Server hochgeladen, sondern nur temporär im Browser verarbeitet
- Die CSV-Datei wird mit dem Zeichensatz Latin-1 (ISO-8859-1) erstellt, um Umlaute korrekt darstellen zu können