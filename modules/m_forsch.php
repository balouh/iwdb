<?php
/*****************************************************************************/
/* m_forsch.php                                                             */
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
/* Diese Erweiterung der ursprünglichen DB ist ein Gemeinschaftsprojekt von */
/* IW-Spielern.                                                              */
/* Bei Problemen kannst du dich an das eigens dafür eingerichtete           */
/* Entwicklerforum wenden:                                                   */
/*                                                                           */
/*                   http://www.iwdb.de.vu                                   */
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
$modulname  = "m_forsch";

//****************************************************************************
//
// -> Menütitel des Moduls der in der Navigation dargestellt werden soll.
//
$modultitle = "Forschungsübersicht";

//****************************************************************************
//
// -> Status des Moduls, bestimmt wer dieses Modul über die Navigation 
//    ausfuehren darf. Mögliche Werte:
//    - ""      <- nix = jeder, 
//    - "admin" <- na wer wohl
//
$modulstatus = "admin";

//****************************************************************************
//
// -> Beschreibung des Moduls, wie es in der Menü-Übersicht angezeigt wird.
//
$moduldesc = 
  "Die Forschungsübersicht zeigt die aktuell laufenden Forschungen";

//****************************************************************************
//
// Function workInstallDatabase is creating all database entries needed for
// installing this module. 
//
function workInstallDatabase() {
/*	global $db, $db_prefix, $db_tb_iwdbtabellen;

  $sqlscript = array(
    "CREATE TABLE " . $db_prefix . "neuername
    (
		);",

    "INSERT INTO " . $db_tb_iwdbtabellen . "(`name`)" .
    " VALUES('neuername')"
  );
  foreach($sqlscript as $sql) {
    $result = $db->db_query($sql)
  	  or error(GENERAL_ERROR,
               'Could not query config information.', '',
               __FILE__, __LINE__, $sql);
  }
  echo "<div class='system_notification'>Installation: Datenbankänderungen = <b>OK</b></div>";
*/}

//****************************************************************************
//
// Function workUninstallDatabase is creating all menu entries needed for
// installing this module. This function is called by the installation method
// in the included file includes/menu_fn.php
//
function workInstallMenu() {
    global $modultitle, $modulstatus;

    $menu    = getVar('menu');
    $submenu = getVar('submenu');

	$actionparamters = "";
  	insertMenuItem( $menu, $submenu, $modultitle, $modulstatus, $actionparamters );
	  //
	  // Weitere Wiederholungen für weitere Menü-Einträge, z.B.
	  //
	  // 	insertMenuItem( $menu+1, ($submenu+1), "Titel2", "hc", "&weissichnichtwas=1" );
	  //
}

//****************************************************************************
//
// Function workInstallConfigString will return all the other contents needed 
// for the configuration file
//
function workInstallConfigString() {
/*  global $config_gameversion;
  return
    "\$v04 = \" <div class=\\\"doc_lightred\\\">(V " . $config_gameversion . ")</div>\";";
*/}

//****************************************************************************
//
// Function workUninstallDatabase is creating all database entries needed for
// removing this module. 
//
function workUninstallDatabase() {
/*  global $db, $db_tb_iwdbtabellen, $db_tb_neuername;

  $sqlscript = array(
    "DROP TABLE " . $db_tb_neuername . ";",
    "DELETE FROM " . $db_tb_iwdbtabellen . " WHERE name='neuername';"
  );

  foreach($sqlscript as $sql) {
    $result = $db->db_query($sql)
  	  or error(GENERAL_ERROR,
               'Could not query config information.', '',
               __FILE__, __LINE__, $sql);
  }
  echo "<div class='system_notification'>Deinstallation: Datenbankänderungen = <b>OK</b></div>";
*/}

//****************************************************************************
//
// Installationsroutine
//
// Dieser Abschnitt wird nur ausgefuehrt wenn das Modul mit dem Parameter
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

$sql = "SELECT * FROM " . $db_tb_user_research . " ORDER BY date ASC";
$result = $db->db_query($sql)
	or error(GENERAL_ERROR, 
	'Could not query config information.', '', 
	__FILE__, __LINE__, $sql);

$data = array();
//$akt=strftime("%d.%m.%y %H:%M:%S", time());
$akt=time();

start_table();
	start_row("titlebg", "nowrap style=\"width:0%\" align=\"center\" ");
		echo "<b>User</b>";
		next_cell("titlebg", "nowrap style=\"width:0%\" align=\"center\"");
		echo "<b>laufende Forschung</b>";
		next_cell("titlebg", "nowrap style=\"width:0%\" align=\"center\"");
		echo "<b>Forschung endet</b>";
		next_cell("titlebg", "nowrap style=\"width:0%\" align=\"center\"");
		echo "<b>Einlesezeitpunkt</b>";
	
	while ($row = $db->db_fetch_array($result)) {
		
		$sql = "SELECT name FROM " .$db_tb_research . " WHERE id ='" . $row['rId'] . "'";
		$result_forsch = $db->db_query($sql)
			or error(GENERAL_ERROR, 
            'Could not query config information.', '', 
            __FILE__, __LINE__, $sql);
		$row1 = $db->db_fetch_array($result_forsch);
		
		if ($row['date'] !='0') {
			if (($row['date']>$row['time']) && ($row['date']>$akt)) {
				$color = "#00FF00";
			}
			else {
				$color = "#FFA500";
			}
		$row['date'] = strftime("%d.%m.%y %H:%M:%S", $row['date']);	
		}
		else {
			$row['date'] = '';
			$color = "#FF0000";
			}
		
		next_row("windowbg1", "style=\"background-color:" . $color . "\" nowrap=\"nowrap\" \"width:0%\" align=\"left\"");
		echo $row['user'];
		next_cell("windowbg1", "nowrap style=\"width:0%\" align=\"left\"");
		echo $row1['name'];
		next_cell("windowbg1", "nowrap style=\"width:0%\" align=\"left\"");
		echo $row['date'];
		next_cell("windowbg1", "nowrap style=\"width:0%\" align=\"left\"");
		echo strftime("%d.%m.%y %H:%M:%S", $row['time']);
		}
	end_row();
end_table();
// Legende ausgeben
echo '<br><table border="0" cellpadding="4" cellspacing="1" class="bordercolor" style="">';
echo '<tr nowrap>';
echo '<td style="width: 30; background-color: #00FF00;"></td>';
echo '<td class="windowbg1">Status aktuell</td>';
echo '<td style="width: 30; background-color: #FF0000;"></td>';
echo '<td class="windowbg1">es wird nicht geforscht</td>';
echo '<td style="width: 30; background-color: #FFA500;"></td>';
echo '<td class="windowbg1">Startseite muss neu eingelesen werden</td>';
echo '</tr>';
echo '</table>';
?>