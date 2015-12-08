### IP-Symcon Modul // Bundesliga Tabelle
---

## Dokumentation

**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang) 
2. [Systemanforderungen](#2-systemanforderungen)
3. [Installation](#3-installation)
4. [Befehlsreferenz](#4-befehlsreferenz)
5. [Changelog](#5-changelog) 


## 1. Funktionsumfang
Dieses Modul liest zyklisch die aktuelle Fussball-Tabelle bei "www.dfb.de"
aus und speichert diese in einer String-Variable (HTMLBox) ab. Die Instanz kann
mehrmals angelegt werden, wenn ihr z.B. alle 3 Ligen einbinden möchtet.

Einstellungsmöglichkeiten in der Instanz:
- Liga (1. Bundesliga, 2. Bundesliga, 3. Liga)
- Update Intervall der Tabelle (in Sekunden)
- Farbige Darstellung der Tabelle (aktiv / inaktiv)


## 2. Systemanforderungen
- IP-Symcon ab Version 4.x


## 3. Installation
Über die Kern-Instanz "Module Control" folgende URL hinzufügen:

`git://github.com/BayaroX/BY_BundesligaTabelle.git`

Die neue Instanz findet ihr dort, wo ihr sie angelegt habt.
Danach muss nur noch die Variable "Tabelle - ..." in euer WebFront verlinkt werden.


## 4. Befehlsreferenz
```php
  BLT_Update($InstanzID);
```
Aktualisiert die Fussball-Tabelle in der Variable.


## 5. Changelog
Version 1.0:
  - Erster Release
