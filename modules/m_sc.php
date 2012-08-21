<?php
/*****************************************************************************/
/* m_sc.php                                                          */
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
/* Dieses Modul dient als Vorlage zum Erstellen von eigenen Zusatzmodulen    */
/* für die Iw DB: Icewars geoscan and sitter database                        */
/*---------------------------------------------------------------------------*/
/* Diese Erweiterung der ursprünglichen DB ist ein Gemeinschaftsprojekt von  */
/* IW-Spielern.                                                              */
/* Bei Problemen kannst du dich an das eigens dafür eingerichtete            */
/* Entwicklerforum wenden:                                                   */
/*                                                                           */
/*        httpd://handels-gilde.org/?www/forum/index.php;board=1099.0        */
/*                                                                           */
/*****************************************************************************/

// -> Abfrage ob dieses Modul über die index.php aufgerufen wurde.
//    Kann unberechtigte Systemzugriffe verhindern.
if (basename($_SERVER['PHP_SELF']) != "index.php") {
	echo "Hacking attempt...!!"; 
	exit; 
}

//****************************************************************************
//
// -> Name des Moduls, ist notwendig für die Benennung der zugehörigen 
//    Config.cfg.php
// -> Das m_ als Beginn des Datreinamens des Moduls ist Bedingung für 
//    eine Installation über das Menü
//
$modulname  = "m_sc";

//****************************************************************************
//
// -> Menütitel des Moduls der in der Navigation dargestellt werden soll.
//
$modultitle = "Sondenkalkulator";

//****************************************************************************
//
// -> Status des Moduls, bestimmt wer dieses Modul über die Navigation 
//    ausführen darf. Mögliche Werte: 
//    - ""      <- nix = jeder, 
//    - "admin" <- na wer wohl
//
$modulstatus = "";

//****************************************************************************
//
// -> Beschreibung des Moduls, wie es in der Menü-Übersicht angezeigt wird.
//
$moduldesc = "Berechnet die benötigten Sonden anhand gegebener Sondendeff";

//****************************************************************************
//
// Function workInstallDatabase is creating all database entries needed for
// installing this module. 
//
function workInstallDatabase() {
  echo "<div class='system_notification'>Installation: Datenbankänderungen = <b>OK</b></div>";
}

//****************************************************************************
//
// Function workUninstallDatabase is creating all menu entries needed for
// installing this module. This function is called by the installation method
// in the included file includes/menu_fn.php
//
function workInstallMenu() {
    global $modultitle, $modulstatus, $_POST;

    $actionparamters = "";
  	insertMenuItem( $_POST['menu'], $_POST['submenu'], $modultitle, $modulstatus, $actionparamters );
}

//****************************************************************************
//
// Function workInstallConfigString will return all the other contents needed 
// for the configuration file.
//
function workInstallConfigString() {
  return "";
}

//****************************************************************************
//
// Function workUninstallDatabase is creating all database entries needed for
// removing this module. 
//
function workUninstallDatabase() {
    echo "<div class='system_notification'>Deinstallation: Datenbankänderungen = <b>OK</b></div>";
}

//****************************************************************************
//
// Installationsroutine
//
// Dieser Abschnitt wird nur ausgeführt wenn das Modul mit dem Parameter 
// "install" aufgerufen wurde. Beispiel des Aufrufs: 
//
//      http://Mein.server/iwdb/index.php?action=default&was=install
//
// Anstatt "Mein.Server" natürlich deinen Server angeben und default 
// durch den Dateinamen des Moduls ersetzen.
//
if( !empty($_REQUEST['was'])) {
  //  -> Nur der Admin darf Module installieren. (Meistens weiss er was er tut)
  if ( $user_status != "admin" ) 
		die('Hacking attempt...');

  echo "<div class='system_notification'>Installationsarbeiten am Modul " . $modulname . 
	     " ("  . $_REQUEST['was'] . ")</div>\n";

  if (!@include("./includes/menu_fn.php")) 
	  die( "Cannot load menu functions" );
    
  // Wenn ein Modul administriert wird, soll der Rest nicht mehr 
  // ausgeführt werden. 
  return;
}

if (!@include("./config/".$modulname.".cfg.php")) { 
	die( "Error:<br><b>Cannot load ".$modulname." - configuration!</b>");
}

//****************************************************************************
//
// -> Und hier beginnt das eigentliche Modul

$sondendeff = array( 
  "sd01" => "SD01 Gatling", 
  "sd02" => "SD02 Pulslaser"
);

foreach( $sondendeff as $key => $value) {
  $temp   = getVar($key);
  ${$key} = empty($temp) ? 0 : $temp;
}

$anz1 = ceil($sd01+($sd02*2.5)+20);
$anz2 = ceil(($sd01/1.2)+($sd02*2.5/1.2)+10);
$anz3 = ceil(($sd01/2)+($sd02*2.5/2)+8);

$sonden = array( 
  "X11" => $anz1, 
  "Terminus" => $anz2, 
  "X13" => $anz3
); 

echo "<div class='doc_title'>Sondenkalkulator</div>\n";
echo "<form method=\"POST\" action=\"index.php?action=" . $modulname .
     "&sid=" . $sid . "\" enctype=\"multipart/form-data\">\n";
     
echo " <table border=\"0\" cellpadding=\"4\" cellspacing=\"1\" class=\"bordercolor\" style=\"width: 40%;\">\n";
echo "  <tr>\n";
echo "   <td colspan=\"2\" class=\"titlebg\"><b>Eingabe:</b></td>\n";
echo "  </tr>\n"; 

foreach( $sondendeff as $key => $title) {
  echo "  <tr>\n";
  echo "   <td class=\"windowbg2\" style=\"width: 200px;\">" . $title . ":</td>\n";
  echo "   <td class=\"windowbg1\"><input type=\"text\" size=\"17\" name=\"" . $key . 
       "\" value=\"" . ${$key} . "\"></td>\n";
  echo "  </tr>\n";
}

echo "  <tr>\n";
echo "   <td colspan=\"2\" class=\"windowbg2\" align=\"center\"><input type=\"submit\" style=\"width: 120px;\" value=\"Berechnen\"></td>\n";
echo "  </tr>\n";
echo " </table>\n";
echo "</form>\n";
echo "<br>\n";


echo "<table border=\"0\" cellpadding=\"4\" cellspacing=\"1\" class=\"bordercolor\" style=\"width: 40%;\">\n";
echo " <tr>\n";
echo "  <td colspan=\"3\" class=\"titlebg\"><b>Benötigte Anzahl Sonden</b></td>\n";
echo " </tr>\n"; 

$t1 = "Entweder";
foreach($sonden as $name => $divisor) {
  echo " <tr>\n";
  echo "  <td class=\"windowbg2\">" . $t1 . "</td>\n";
  echo "  <td class=\"windowbg1\">" . ceil($divisor) . "</td>\n";
  echo "  <td class=\"windowbg2\">" . $name . "</td>\n";  
  echo " </tr>\n";
  $t1 = "Oder";
} 

echo "</table>\n";
?>