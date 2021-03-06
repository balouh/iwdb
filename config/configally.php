<?php
/*****************************************************************************/
/* configally.php                                                            */
/*****************************************************************************/
/* Iw DB: Icewars geoscan and sitter database                                */
/* Open-Source Project started by Robert Riess (robert@riess.net)            */
/* Software Version: Iw DB 1.00                                              */
/* ========================================================================= */
/* Software Distributed by:    http://lauscher.riess.net/iwdb/               */
/* Support, News, Updates at:  http://lauscher.riess.net/iwdb/               */
/* ========================================================================= */
/* Copyright (c) 2004 Robert Riess - All Rights Reserved                     */
/*****************************************************************************/
/* This program is free software; you can redistribute it and/or modify it   */
/* under the terms of the GNU General Public License as published by the     */
/* Free Software Foundation; either version 2 of the License, or (at your    */
/* option) any later version.                                                */
/*                                                                           */
/* This program is distributed in the hope that it will be useful, but       */
/* WITHOUT ANY WARRANTY; without even the implied warranty of                */
/* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General */
/* Public License for more details.                                          */
/*                                                                           */
/* The GNU GPL can be found in LICENSE in this directory                     */
/*****************************************************************************/

/*****************************************************************************/
/* Diese Erweiterung der ursprünglichen DB ist ein Gemeinschafftsprojekt von */
/* IW-Spielern.                                                              */
/* Bei Problemen kannst du dich an das eigens dafur eingerichtete            */
/* Entwicklerforum wenden:                                                   */
/*                                                                           */
/*        httpd://handels-gilde.org/?www/forum/index.php;board=1099.0        */
/*                                                                           */
/*****************************************************************************/

if (!defined('IRA'))
	die('Hacking attempt...');

// *************************************************************************** 
//  Wichtige Angaben die überprüft werden und für die eigene Allianz 
//  eingestellt werden sollten!
//

// Mailadresse (z.B. wenn sich ein User falsch eingeloggt hat)
$config_mailto = "iwdb@iwdb.de";

// angezeigter Name in Mail
$config_mailto_id = "IWDB Admin";

// Mail von Adresse/Name
$config_mailname = "IWDB-Server";
$config_mailfrom = "iwdb@iw-allianz.de";
$config_server   = "localhost";
$config_url      = "http://iwdb.de";
// Titel der AllianzDatenbank
$config_allytitle = "Kilrathy Datenbank";

// Tag der Allianz - wird für die Anzeige der Mitglieder auf der Karte benoetigt
$config_allytag = "Kilrathy";

// Aktuelle Spielversion (wird auch für den Techtree benötigt).
$config_gameversion = "XII";

// Default Galaxy
$config_map_default_galaxy = 3;

// maximale Anzahl der Galaxien
$config_map_galaxy_count = 20;

// Spaltenanzahl der Karte
$config_map_cols = 20;

// ab welchem Alter des Universumsscans in Sekunden soll keine Farbabstufung mehr stattfinden
$config_map_timeout = 14 * (24 * 60 * 60);

// Anzahl der Tage, ab der ein Geoscan als veraltet gilt und erneuert werden müsste 
$config_geoscan_yellow = 14;
$config_geoscan_red    = 30;

// wie lange vor Eintreten (in Sekunden) soll ein Sitterauftrag 
// als "aktiv" (einloggen/erledigt anwählbar) geschaltet werden?
$sitter_wie_lange_vorher_zeigen = 30;

// Breite und Höhe des Member-Statistikgraphen
$config_xsize = 950;
$config_ysize = 500;

// Seite, die standardmässig (z.B. nach Login) geladen wird
$config_default_action = "showhighscore";

$config_banner = "bilder/logo.png";
$config_banner_width = "500px";

?>