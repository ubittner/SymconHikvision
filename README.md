# Hikvision People Counting

[![Version](https://img.shields.io/badge/Symcon_Version-5.0>-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
![Version](https://img.shields.io/badge/Modul_Version-1.00-blue.svg)
![Version](https://img.shields.io/badge/Modul_Build-1-blue.svg)
![Version](https://img.shields.io/badge/Code-PHP-blue.svg)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![StyleCI](https://github.styleci.io/repos/169845075/shield?branch=master&style=flat)](https://github.styleci.io/repos/169845075)


![Logo](imgs/logo.png)

Hikvision People Counting - Ein Gemeinschaftsprojekt von Normen Thiel und Ulrich Bittner

Dieses Modul integriert eine [Hikvision Kamera zur Personenzählung](https://www.hikvision.com/de/Products/Deep-Learning/DeepinView-Camera) in [IP-Symcon](https://www.symcon.de). 

Somit können Sie die Anzahl der Personen in einem Raum überwachen.
Wird die maximale Anzahl der Personen überschritten, so kann eine Benachrichtigung und/oder Alarmierung erfolgen.
Bei einer Alarmierung können Sie weitere Variablen schalten, Skripte ausführen lassen und/oder den Alarmausgang der Kamera schalten.

Für dieses Modul besteht kein Anspruch auf Fehlerfreiheit, Weiterentwicklung, sonstige Unterstützung oder Support.

Bevor das Modul installiert wird, sollte unbedingt ein Backup von IP-Symcon durchgeführt werden.

Der Entwickler haftet nicht für eventuell auftretende Datenverluste.

Der Nutzer stimmt den o.a. Bedingungen, sowie den Lizenzbedingungen ausdrücklich zu.

Unterstütze Modelle:

[IDS-2CD6810F/C](https://www.hikvision.com/de/Products/Deep-Learning/DeepinView-Camera/iDS-2CD6810F/C)

## Dokumentation

**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang)  
2. [Voraussetzungen](#2-voraussetzungen)  
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)
8. [GUIDs und Datenaustausch](#8-guids-und-datenaustausch)
9. [Changelog](#9-changelog)
10. [Lizenz](#10-lizenz)
11. [Author](#11-author)


### 1. Funktionsumfang

Die von der Kamera in IP-Symcon über einen Server Socket empfangenen Daten werden immer verarbeitet und im WebFront aktualisiert.

Wird die Überwachung eingeschaltet, so erfolgt auch eine Benachrichtigung / Alarmierung bei einer Zustandsänderung.

Für weitere Funktionen wenden Sie sich bitte an den Entwickler.

#### Funktionen:  

 - Ein- / Ausschalten der Überwachung
 - Ändern des Wertes für den kritischen Zustand
 - Zurücksetzten des Personenzählers
	  
### 2. Voraussetzungen

 - IP-Symcon ab Version 5.0, Web-Console

### 3. Software-Installation

Bei kommerzieller Nutzung (z.B. als Einrichter oder Integrator) wenden Sie sich bitte zunächst an den Entwickler.

Bei privater Nutzung:

Nachfolgend wird die Installation des Moduls anhand der neuen Web-Console ab der Version 5.0 beschrieben. Die Verwendung der (legacy) Verwaltungskonsole wird vom Entwickler nicht mehr berücksichtigt.

Folgende Instanzen stehen dann in IP-Symcon zur Verfügung:

- PeopleCounting (Geräte Instanz)
- ServerSocket (I/O Instanz)

#### 3a. Modul hinzufügen

Im Objektbaum von IP-Symcon die Kern Instanzen aufrufen. 

Danach die [Modulverwaltung](https://www.symcon.de/service/dokumentation/modulreferenz/module-control/) aufrufen.

Sie sehen nun die bereits installierten Module.

Fügen Sie über das `+` Symbol (unten rechts) ein neues Modul hinzu.

Wählen Sie als URL:

`https://github.com/ubittner/SymconHikvision.git`  

Anschließend klicken Sie auf `OK`, um das Modul zu installieren.

#### 3b. Instanz hinzufügen

Jede Kamera benötigt eine Instanz, bzw. jede Instanz kann eine Kamera verwalten.

Klicken Sie in der Objektbaumansicht unten links auf das `+` Symbol. 

Wählen Sie anschließend `Instanz` aus. 

Geben Sie im Schnellfiler das Wort "PeopleCounting" ein oder wählen den Hersteller "Hikvision" aus. 
Wählen Sie aus der Ihnen angezeigten Liste "PeopleCounting" aus und klicken Sie anschließend auf `OK`, um die Instanz zu installieren.

Die Instanz für den ServerSocket zum Empfang der Kameradaten wird automatisch erstellt.

### 4. Einrichten der Instanzen in IP-Symcon

#### Konfiguration:

#### PeopleCounting:

| Eigenschaft                       | Typ     | Standardwert          | Funktion                                                |
| :-------------------------------: | :-----: | :-------------------: | :-----------------------------------------------------: |
| (1) Allgemeine Einstellungen      |         |                       |                                                         |
| Benutze Überwachung               | boolean | false                 | Generelle Überwachung aus-/ einschalten                 |
| Raumbezeichnung                   | string  |                       | Bezeichnung des zu überwachenden Raumes                 |
| (2) Kamera                        |         |                       |                                                         |
| IP-Adresse                        | string  |                       | IP-Adresse der Kamera                                   |
| Timeout                           | integer | 2000                  | Netzwerk Timeout                                        |
| Benutzer                          | string  |                       | Benutzername zur Anmeldung an der Kamera                |
| Kennwort                          | string  |                       | Kenwwort des Benutzers                                  |
| Kanal                             | integer | 1                     | Verwendeter Kanal der Kamera                            |
| (3) Grenzwerte                    |         |                       |                                                         |
| Maximale Anzahl der Personen      | integer | 200                   | Maximale Anzahl der Personen                            |
| Kritischer Zustand                | integer | 180                   | Anzahl der Personen für den kritischen Zustand          |
| (4) Benachrichtigung              |         |                       |                                                         |
| Titelbezeichnung                  | string  |                       | Titlebezeichnung                                        |
| Benachrichtigungsvarianten        | string  |                       | Auswahl und Bezeichnung der Benachrichtigungsvarianten  |               
| WebFront Benachrichtigung         | string  |                       | Benachrichtigung im WebFront de- / aktivieren           |
| Push Benachrichtigung             | string  |                       | Push Benachrichtigung de- / aktivieren                  |
| E-Mail Benachrichtigung           | string  |                       | E-Mail Benachrichtigung de- / aktivieren                |
| (5) Alarmierung                   |         |                       |                                                         |
| Alarmausgang Kamera               | bool    | false                 | Alarmausgang der Kamera de-/ aktivieren                 |
| Variablen                         | string  |                       | Variablen, die bei Alarmierung geschaltet werden sollen |
| Skripte                           | string  |                       | Skripte, die bei Alarmierung ausgeführt werden sollen   |

__Schaltfläche__:

| Bezeichnung                       | Beschreibung
| :-------------------------------: | :-------------------------------------------------------------------: |
| Anleitung                         | Ruft die Dokumentation auf Github auf                                 |
| Kamerainformationen anzeigen      | Zeigt Informationen der Kamera an                                     |
| Personenzähler zurücksetzen       | Setzt den Personenzähler zurück                                       |
| Nachrichtenliste anzeigen         | Zeigt die für MessageSink registrierten Variablen an                  |
| Alarmausgang 1 aus                | Schaltet den Alarmausgang 1 an der Kamera aus                         |
| Alarmausgang 1 an                 | Schaltet den Alarmausgang 1 an der Kamera ein                         |

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen:

| Name                                          | Typ       | Beschreibung                                          |
| :-------------------------------------------: | :-------: | :---------------------------------------------------: |
| Überwachung                                   | Boolean   | Schaltet die Überwachung ein/aus.                     |
| Aktueller Status                              | Integer   | Zeigt den aktuellen Status an                         |
| Aktuelle Anzahl der Personen                  | Integer   | Anzahl der Personen die sich aktuell im Raum befinden |
| Betreten                                      | Integer   | Anzahl der Perosnen die den Raum betreten haben       |
| Verlassen                                     | Integer   | Anzahl der Perosnen die den Raum verlassen haben      |
| Durchlauf                                     | Integer   | Anzahl der Durchläufe                                 |
| Maximale Anzahl der Perosnen                  | Integer   | Maximal Anzahl der Perosnen                           |
| Grenzwert für den kritischen Zustand          | Integer   | Grenzwert für den kritischen Zustand                  |
| Grenzwert für den kritischen Zustand ändern   | Integer   | Grenzwert für den kritischen Zustand ändern           |
| Zähler                                        | Intger    | Personenzähler zurücksetzen                           |      

##### Profile:

Nachfolgende Profile werden automatisch angelegt und sofern die Instanz gelöscht wird auch wieder automatisch gelöscht.

| Name                                          | Typ       | Beschreibung                                      |
| :-------------------------------------------: | :-------: | :-----------------------------------------------: |
| HV.InstanceID.Monitoring                      | Boolean   | Überwachung                                       |
| HV.InstanceID.ActualState                     | Integer   | Aktueller Status                                  |
| HV.InstanceID.PeopleInRoom                    | Integer   | Anzahl der Perosnen im Raum                       |
| HV.InstanceID.PeopleEnter                     | Integer   | Betreten                                          |
| HV.InstanceID.PeopleExit                      | Integer   | Verlassen                                         |
| HV.InstanceID.PeoplePass                      | Integer   | Durchlauf                                         |
| HV.InstanceID.LimitPeopleMaximum              | Integer   | Maximale Anzahl der Personen                      |
| HV.InstanceID.ThresholdCriticalState          | Integer   | Anzahl der Perosnen für den kritischen Zustand    |
| HV.InstanceID.ChangeThresholdCriticalState    | Integer   | Wert für den kritischen Zustand ändern            |
| HV.InstanceID.ResetCounter                    | Integer   | Personenzähler zurücksetzten                      |

### 6. WebFront

Über das WebFront kann die Überwachung ein- und ausgeschaltet werden.
Ebenfalls kann der Wert für den kritischen Zustand geändert werden und der Personenzähler zurückgesetzt werden.

### 7. PHP-Befehlsreferenz

#### Präfix: HV

#### PeopleCounting:

`boolean HV_ToggleMonitoring(bool $State);`  
Schaltet die Überwachung ein / aus. 
(true = Ein, false = Aus).  
Die Funktion liefert keinerlei Rückgabewert.  
`HV_ToggleMonitoring(12345, true);` 

`integer HV_ChangeThresholdCriticalState(int $Value);`  
Ändert den Wert für den kritischen Zustand. 
(0 = - 10 Personen, 1 = Standardwert, 2 = + 10 Perosnen).  
Die Funktion liefert keinerlei Rückgabewert.  
`HV_ChangeThresholdCriticalState(12345, 1);`  

`HV_ResetPeopleCounter();`  
Setzt den Personenzähler zurück.  
Die Funktion liefert keinerlei Rückgabewert.   
`HV_ResetPeopleCounter(12345);`  

`HV_CheckCameraUser();`  
Prüft die Anmeldedaten an der Kamera.   
Die Funktion liefert das Ergebnis zurück.    
`HV_CheckCameraUserr(12345);` 

`HV_GetCameraInformation();`  
Liefert Informationen über die Kamera.  
Die Funktion liefert das Ergebnis zurück.    
`HV_GetCameraInformation(12345);`  

`HV_ResetCameraPeopleCounter();`  
Setzt den Personenzähler der Kamera zurück.  
Die Funktion liefert das Ergebnis zurück.  
`HV_ResetCameraPeopleCounter(12345);`   

`variant HV_TriggerCameraAlarmOutput(int $Output, bool $State);`  
Schaltet die Überwachung ein / aus.  
(Output:ID des Ausgangs, State: false = Aus, true = Ein).    
Die Funktion liefert das Ergebnis zurück.   
`HV_TriggerCameraAlarmOutput(12345, 1, true);`   

###  8. GUIDs und Datenaustausch

#### PeopleCounting:

| Beschreibung                  | GUID                                   |
| :---------------------------: | :------------------------------------: |
| Bibliothek                    | {78F1F259-B1ED-2723-B27E-4F35A0EABF86} |
| PeopleCounting                | {05986539-30D5-6B74-CD44-2573FD73BAE8} |

Der Datenempfang erfolgt automatisch über den Server Socket.

### 9. Changelog

| Version     | Build | Datum      | Beschreibung                   |
|:----------: | :---: | :--------: | :----------------------------: |
| 1.00        | 1     | 11.01.2019 | Version 1.00 für IP-Symcon 5.0 |

### 10. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)

### 11. Author

Ulrich Bittner
