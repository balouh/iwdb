<?php
/*****************************************************************************/
/* m_frachtkapa.php                                                          */
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
$modulname  = "m_frachtkapa";

//****************************************************************************
//
// -> Menütitel des Moduls der in der Navigation dargestellt werden soll.
//
$modultitle = "Frachtkapazitäten";

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
$moduldesc = 
  "Das Frachtkapazitäten-Modul dient zur Berechnung der notwendigen" .
  " Transporteranzahl für eine gegebene Menge Ressourcen";

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

?>
<div class='doc_title'>Frachtkapazitätenberechnung</div>
<form name='transcalc' action=''>
     
<table border='0' cellpadding='4' cellspacing='1' class='bordercolor' style='width: 100%;'>
<tr>
    <td colspan='2' class='titlebg'><b>Eingabe:</b></td>
</tr>
<tr>
    <td class='windowbg2' style='width: 200px;'>Eisen:</td>
    <td class='windowbg1'><input type='text' size='17' id='eisen' value='0' pattern="\d*" style='width: 100px; text-align:right'></td>
</tr><tr>
    <td class='windowbg2' style='width: 200px;'>Stahl:</td>
    <td class='windowbg1'><input type='text' size='17' id='stahl' value='0' pattern="\d*" style='width: 100px; text-align:right'></td>
</tr><tr>
    <td class='windowbg2' style='width: 200px;'>chem. Elemente:</td>
    <td class='windowbg1'><input type='text' size='17' id='chemie' value='0' pattern="\d*" style='width: 100px; text-align:right'></td>
</tr><tr>
    <td class='windowbg2' style='width: 200px;'>VV4A:</td>
    <td class='windowbg1'><input type='text' size='17' id='vv4a' value='0' pattern="\d*" style='width: 100px; text-align:right'></td>
</tr><tr>
    <td class='windowbg2' style='width: 200px;'>Eis:</td>
    <td class='windowbg1'><input type='text' size='17' id='eis' value='0' pattern="\d*" style='width: 100px; text-align:right'></td>
</tr><tr>
    <td class='windowbg2' style='width: 200px;'>Wasser:</td>
    <td class='windowbg1'><input type='text' size='17' id='wasser' value='0' pattern="\d*" style='width: 100px; text-align:right'></td>
</tr><tr>
    <td class='windowbg2' style='width: 200px;'>Energie:</td>
    <td class='windowbg1'><input type='text' size='17' id='energie' value='0' pattern="\d*" style='width: 100px; text-align:right'></td>
</tr>
<tr>
    <td colspan='2' class='titlebg'><b>Vorhandene Transen für Klasse 1</b></td>
</tr>
<tr>
    <td class='windowbg2' style='width: 200px;'>Systransen:</td>
    <td class='windowbg1'><input type='text' size='17' id='systransen_vorhanden' value='0' pattern="\d*" style='width: 100px; text-align:right'></td>
</tr><tr>
    <td class='windowbg2' style='width: 200px;'>Gorgols:</td>
    <td class='windowbg1'><input type='text' size='17' id='gorgols_vorhanden' value='0' pattern="\d*" style='width: 100px; text-align:right'></td>
</tr><tr>
    <td class='windowbg2' style='width: 200px;'>Kamele:</td>
    <td class='windowbg1'><input type='text' size='17' id='kamele_vorhanden' value='0' pattern="\d*" style='width: 100px; text-align:right'></td>
</tr><tr>
    <td class='windowbg2' style='width: 200px;'>Flughunde:</td>
    <td class='windowbg1'><input type='text' size='17' id='flughunde_vorhanden' value='0' pattern="\d*" style='width: 100px; text-align:right'></td>
</tr>
<tr>
    <td colspan='2' class='titlebg'><b>Vorhandene Transen für Klasse 2</b></td>
</tr>
<tr>
    <td class='windowbg2' style='width: 200px;'>Lurche:</td>
    <td class='windowbg1'><input type='text' size='17' id='luche_vorhanden' value='0' pattern="\d*" style='width: 100px; text-align:right'></td>
</tr><tr>
    <td class='windowbg2' style='width: 200px;'>Eisbären:</td>
    <td class='windowbg1'><input type='text' size='17' id='eisbaeren_vorhanden' value='0' pattern="\d*" style='width: 100px; text-align:right'></td>
</tr><tr>
    <td class='windowbg2' style='width: 200px;'>Waschbären:</td>
    <td class='windowbg1'><input type='text' size='17' id='waschbaeren_vorhanden' value='0' pattern="\d*" style='width: 100px; text-align:right'></td>
</tr><tr>
    <td class='windowbg2' style='width: 200px;'>Seepferdchen:</td>
    <td class='windowbg1'><input type='text' size='17' id='seepferdchen_vorhanden' value='0' pattern="\d*" style='width: 100px; text-align:right'></td>
</tr>
</table>
</form>
<br>
<table border='0' cellpadding='4' cellspacing='1' class='bordercolor' style='width: 100%;'>
<tr>
    <td colspan='3' class='titlebg'><b>Benötigte Frachtkapazität</b></td>
</tr><tr>
    <td class='windowbg2'>Klasse 1:</td>
    <td class='windowbg1' id='class1kappatext' style='width: 100px; text-align:right'>&nbsp;</td>
    <td class='windowbg1'></td>
</tr><tr>
    <td class='windowbg2'>Klasse 2:</td>
    <td class='windowbg1' id='class2kappatext' style='width: 100px; text-align:right'>&nbsp;</td>
    <td class='windowbg1'></td>
</tr>

<tr>
    <td colspan='3' class='titlebg'><b>Benötigte Transen für Klasse 1</b></td>
</tr><tr>
    <td class='windowbg2'>entweder</td>
    <td class='windowbg1' id='systranstext' style='width: 100px; text-align:right'>&nbsp;</td>
    <td class='windowbg2'>Systrans(en)</td>
</tr><tr>
    <td class='windowbg2'>oder</td>
    <td class='windowbg1' id='gorgoltext' style=' width: 100px; text-align:right'>&nbsp;</td>
    <td class='windowbg2'>Gorgol(s)</td>
</tr><tr>
    <td class='windowbg2'>oder</td>
    <td class='windowbg1' id='kameltext' style='width: 100px; text-align:right'>&nbsp;</td>
    <td class='windowbg2'>Kamel(e)</td>
</tr><tr>
    <td class='windowbg2'>oder</td>
    <td class='windowbg1' id='flughundtext' style='width: 100px; text-align:right'>&nbsp;</td>
    <td class='windowbg2'>Flughund(e)</td>
</tr>

<tr>
    <td colspan='3' class='titlebg'><b>Benötigte Transen für Klasse 2</b></td>
</tr><tr>
    <td class='windowbg2'>entweder</td>
    <td class='windowbg1' id='lurchtext' style='width: 100px; text-align:right'>&nbsp;</td>
    <td class='windowbg2'>Lurch(e)</td>
</tr><tr>
    <td class='windowbg2'>oder</td>
    <td class='windowbg1' id='eisbaertext' style='width: 100px; text-align:right'>&nbsp;</td>
    <td class='windowbg2'>Eisbär(en)</td>
</tr><tr>
    <td class='windowbg2'>oder</td>
    <td class='windowbg1' id='waschbaertext' style='width: 100px; text-align:right'>&nbsp;</td>
    <td class='windowbg2'>Waschbär(en)</td>
</tr><tr>
    <td class='windowbg2'>oder</td>
    <td class='windowbg1' id='seepferdchentext' style='width: 100px; text-align:right'>&nbsp;</td>
    <td class='windowbg2'>Seepferdchen</td>
</tr>
</table>

<script type="text/javascript">

    var aktiv = window.setInterval("Rechnen()", 500);

    function number_format(number, decimals, dec_point, thousands_sep) {
        "use strict";
        //javascript equivalent to php number_format
        //from http://phpjs.org/functions/number_format:481
        //License GPLv2 and MIT
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (thousands_sep === undefined) ? ',' : thousands_sep,
            dec = (dec_point === undefined) ? '.' : dec_point,
            s = '',
            toFixedFix = function (n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
        // Fix for IE parseFloat(0.55).toFixed(0) = 0;
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += [prec - s[1].length + 1].join('0');
        }
        return s.join(dec);
    }

    function Rechnen() {
        "use strict";
        var class1kappa_benoetigt, class1kappa_vorhanden, class1kappa_nochbenoetigt, class2kappa_benoetigt, class2kappa_vorhanden, class2kappa_nochbenoetigt;
      
        class1kappa_benoetigt = (document.getElementById('eisen').value * 1)
            + (document.getElementById('stahl').value * 2)
            + (document.getElementById('chemie').value * 3)
            + (document.getElementById('vv4a').value * 4);

        class1kappa_vorhanden = (document.getElementById('systransen_vorhanden').value * 5000)
            + (document.getElementById('gorgols_vorhanden').value * 20000)
            + (document.getElementById('kamele_vorhanden').value * 75000)
            + (document.getElementById('flughunde_vorhanden').value * 400000);

        class2kappa_benoetigt = (document.getElementById('eis').value * 2)
            + (document.getElementById('wasser').value * 2)
            + (document.getElementById('energie').value * 1);

        class2kappa_vorhanden = (document.getElementById('luche_vorhanden').value * 2000)
            + (document.getElementById('eisbaeren_vorhanden').value * 10000)
            + (document.getElementById('waschbaeren_vorhanden').value * 50000)
            + (document.getElementById('seepferdchen_vorhanden').value * 250000);

        class1kappa_nochbenoetigt = class1kappa_benoetigt - class1kappa_vorhanden;
        class2kappa_nochbenoetigt = class2kappa_benoetigt - class2kappa_vorhanden;
        
        document.getElementById('class1kappatext').firstChild.data = number_format(class1kappa_benoetigt, 0, ',', '.');
        document.getElementById('class2kappatext').firstChild.data = number_format(class2kappa_benoetigt, 0, ',', '.');

        document.getElementById('systranstext').firstChild.data = number_format(Math.ceil(class1kappa_nochbenoetigt / 5000), 0, ',', '.');
        document.getElementById('gorgoltext').firstChild.data = number_format(Math.ceil(class1kappa_nochbenoetigt / 20000), 0, ',', '.');
        document.getElementById('kameltext').firstChild.data = number_format(Math.ceil(class1kappa_nochbenoetigt / 75000), 0, ',', '.');
        document.getElementById('flughundtext').firstChild.data = number_format(Math.ceil(class1kappa_nochbenoetigt / 400000), 0, ',', '.');

        document.getElementById('lurchtext').firstChild.data = number_format(Math.ceil(class2kappa_nochbenoetigt / 2000), 0, ',', '.');
        document.getElementById('eisbaertext').firstChild.data = number_format(Math.ceil(class2kappa_nochbenoetigt / 10000), 0, ',', '.');
        document.getElementById('waschbaertext').firstChild.data = number_format(Math.ceil(class2kappa_nochbenoetigt / 50000), 0, ',', '.');
        document.getElementById('seepferdchentext').firstChild.data = number_format(Math.ceil(class2kappa_nochbenoetigt / 250000), 0, ',', '.');
    }
</script>