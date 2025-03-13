# Bilder-Upload mit Server-Integration

Diese Webanwendung ermöglicht das Hochladen von Bildern, deren automatische Verarbeitung und Speicherung auf dem Server sowie das Erstellen einer CSV-Datei mit den Bildzuordnungen und vollständigen URLs.

## Funktionen

- Drag & Drop-Upload von Bildern
- Automatische Extraktion von Artikelnummern aus Dateinamen (8-stellige Zahlen)
- Automatische Umbenennung von Dateien (kleinschreiben, Leerzeichen und Unterstriche durch Bindestriche ersetzen)
- Intelligente Sortierung:
  - Bei Artikeln mit "front" oder "back" im Dateinamen: Spezielle Zuordnung zu Image1 (back) und Image2 (front)
  - Bei Artikeln ohne "front" oder "back": Reihenfolge nach Upload
- **NEU: Automatisches Cropping auf 1500px x 1500px und Komprimierung (60% Qualität)**
- **NEU: Speicherung der Bilder auf dem Server im Ordner /images/**
- **NEU: Vollständige URLs in der CSV-Datei** (Format: "Artikelnummer;URL1;URL2;URL3;URL4;URL5;URL6;URL7")
- Tabellarische Darstellung der Bildzuordnungen

## Serverstruktur

Die Bilder werden auf dem Server unter folgender Struktur gespeichert:
```
https://upload.karlknoop.com/upload/images/[Dateiname]
```

## Bildverarbeitung

Die Bilder werden vor dem Speichern auf dem Server automatisch verarbeitet:

1. **Cropping**: Alle Bilder werden zu einem Quadrat (1500px x 1500px) zugeschnitten
2. **Komprimierung**: Die Bilder werden mit 60% Qualität komprimiert, um die Dateigröße zu reduzieren

## Verwendung

1. **Bilder hochladen**:
   - Ziehen Sie Bilder in den Drop-Bereich oder klicken Sie auf "Bilder auswählen"
   - Die Bilder sollten 8-stellige Artikelnummern im Dateinamen enthalten (z.B. "10012345_Produktname.jpg")
   - Optional: Deaktivieren Sie das Kontrollkästchen, wenn Sie die Dateinamen nicht automatisch anpassen möchten

2. **Bilder verarbeiten**:
   - Klicken Sie auf "Bilder verarbeiten"
   - Die Bilder werden auf den Server hochgeladen und verarbeitet
   - Eine Fortschrittsanzeige zeigt den Status des Uploads an
   - Nach dem Upload werden die Bilder in der Tabelle angezeigt

3. **CSV herunterladen**:
   - Überprüfen Sie die Tabelle mit den zugeordneten Bildern
   - Klicken Sie auf "CSV-Datei herunterladen", um die Zuordnungen als CSV-Datei zu speichern
   - Die CSV-Datei enthält vollständige URLs zu den hochgeladenen Bildern

## Technische Details

- **Frontend**: HTML, CSS, JavaScript (ohne externe Bibliotheken)
- **Backend**: PHP für die Bildverarbeitung und -speicherung
- **Server-Konfiguration**: .htaccess mit Einstellungen für Speicherplatz und Verarbeitungszeit

## Voraussetzungen (Server)

- PHP 7.0+ mit GD-Bibliothek für die Bildverarbeitung
- Apache-Server mit mod_rewrite und AllowOverride All
- Schreibrechte für den /images/ Ordner
