<?php
/*****************************************************************************/
/* newscan.php                                                               */
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
/*                                                                           */
/* Autor: Mac (MacXY@herr-der-mails.de)                                      */
/* Datum: April 2012                                                         */
/*                                                                           */
/* Bei Problemen kannst du dich an das eigens dafür eingerichtete            */
/* Entwicklerforum wenden:                                                   */
/*         http://handels-gilde.org/?www/forum/index.php;board=1099.0        */
/*                   https://github.com/iwdb/iwdb                            */
/*                                                                           */
/*****************************************************************************/

if (basename($_SERVER['PHP_SELF']) != "index.php")
  die('Hacking attempt...!!');

if (!defined('IRA'))
	die('Hacking attempt...');

$debugdir="debug";
$debugfile_keeptime = 7*24*3600;        //Debugfiles 1 Woche behalten

ob_start();  
	
$start = microtime(true);

//$debug = TRUE;

/*
	Autoload system for plib parser
*/
global $plibfiles;

$plibfiles = array();
function __autoload($class) {
	global $plibfiles;
	if (empty($plibfiles)) {
		function ReadTheDir ($base) {
			global $plibfiles;
			$base = realpath($base) . DIRECTORY_SEPARATOR;
			$dir = opendir($base);
			while($file = readdir($dir)) {
		        if (is_file($base.$file)) {
		        	if (substr($file,-4) == ".php")	{
		        		$plibfiles[md5($file)] = $base.$file; //add php-file to hashtable
		        	}
		        } else if (is_dir($base.$file) && $file != "." && $file != ".." && substr($file,0,1) != ".") {//! keine versteckten Verzeichnisse
					ReadTheDir($base.$file.DIRECTORY_SEPARATOR);
		        }
      		}
			closedir($dir);
		}
		ReadTheDir ('plib'.DIRECTORY_SEPARATOR);
	}
	if (isset($plibfiles[md5($class.".php")]) && file_exists($plibfiles[md5($class.".php")])) {
		require_once ($plibfiles[md5($class.".php")]);
	}
}

// parser werden nur noch fier die abwaertskompatibilitaet gebraucht. 
// libIWParser bestimmen automatisch, den korrekten Parser
$parser = array();
$sql = "SELECT modulename, recognizer, message FROM " . 
        $db_tb_parser . " ORDER BY message ASC";
$result = $db->db_query($sql)
	or error(GENERAL_ERROR, 
        'Could not query config information.', '', 
        __FILE__, __LINE__, $sql);
           
while( $row = $db->db_fetch_array($result)) {
	$parser[$row['modulename']] = array($row['recognizer'], 0, $row['message']);
}

function plural($singular) {
  if( preg_match( '/.*sicht$/i', $singular ))
	return ($singular . "en");  

  if( preg_match( '/.*bericht$/i', $singular ))
    return ($singular . "e");  

  if( preg_match( '/.*liste$/i', $singular ))
    return ($singular . "n");  

  if( preg_match( '/.*scan$/i', $singular ))
    return ($singular . "s");  
} 

$selectedusername = getVar('seluser');	
if( empty($selectedusername)) 
  $selectedusername = $user_sitterlogin;

echo "<div class='doc_title'>Neuer Bericht</div>\n";
echo "<form method='POST' action='index.php?action=newscan&sid=" . $sid. "' enctype='multipart/form-data'>\n";
echo "<table border='0' cellpadding='4' cellspacing='1' class='bordercolor' style='width: 90%;'>\n";
echo " <tr>\n";
echo "  <td class='windowbg2' align='center'>\n";

global $user_status, $user_sitten;

$sqlP = "SELECT value FROM ".$db_prefix."params WHERE name = 'bericht_fuer_rang' ";
$resultP = $db->db_query($sqlP)
    or error(GENERAL_ERROR, 'Could not query config information.', '', __FILE__, __LINE__, $sqlP);
$rowP = $db->db_fetch_array($resultP);  

$allow1 = FALSE;

if ( $rowP['value'] == 'hc' AND ( strtolower($user_status) == 'hc' ) ) $allow1 = TRUE;
if ( $rowP['value'] == 'mv' AND ( strtolower($user_status) == 'hc' OR strtolower($user_status) == 'mv' ) ) $allow1 = TRUE;
if ( $rowP['value'] == 'all' AND ( strtolower($user_status) != 'guest' ) ) $allow1 = TRUE;

$sqlP = "SELECT value FROM ".$db_prefix."params WHERE name = 'bericht_fuer_sitter' ";
$resultP = $db->db_query($sqlP)
    or error(GENERAL_ERROR, 'Could not query config information.', '', __FILE__, __LINE__, $sqlP);
$rowP = $db->db_fetch_array($resultP);  

$allow2 = FALSE;

if ($rowP['value'] == 0 AND ( $user_sitten == 0 OR $user_sitten == 1 ) ) $allow2 = TRUE;
if ($rowP['value'] == 1 AND ( $user_sitten == 1 ) ) $allow2 = TRUE;
if ($rowP['value'] == 3 AND ( $user_sitten == 0 OR $user_sitten == 1 ) ) $allow2 = TRUE;
if ($rowP['value'] == 2 ) $allow2 = TRUE;

if( $user_status == "admin" ) {
	$allow1 = TRUE;
	$allow2 = TRUE;
}

if( $allow1 AND $allow2 ) { 
	echo "   Bericht einfügen für\n";
	echo "	 <select name='seluser' style='width: 200px;'>\n";

	$sql = "SELECT sitterlogin FROM " . $db_tb_user . " ORDER BY id ASC";
	$result = $db->db_query($sql)
		or error(GENERAL_ERROR, 
        'Could not query config information.', '', 
        __FILE__, __LINE__, $sql);
             
	while( $row = $row = $db->db_fetch_array($result)) {
		echo "      <option value='" . $row['sitterlogin'] . "'" . ($selectedusername == $row['sitterlogin'] ? " selected" : "") . ">" . $row['sitterlogin'] . "</option>";
	}
	echo " 	 </select><br />\n";
}

echo "   <textarea name='text' rows='14' cols='70'></textarea><br />\n";
echo " 	 <br />\n";
echo "<font color='red'>Wichtig : Beim Einlesen der Ressübersicht darauf achten, dass das Lager ausgeklappt ist!</font><br />";
echo "<font color='red'>Wichtig : Beim Einlesen der Startseite darauf achten, dass die Fluginformationen ausgeklappt sind!</font><br />";
echo " 	 <br />\n";
echo "   Für Hilfe bitte oben auf den \"Hilfe\" Button drücken.\n";
echo "  </td>\n";
echo " </tr>\n";
echo " <tr>\n";
echo "  <td class='titlebg' align='center'>\n";
echo "   <input type='submit' value='abspeichern' name='B1' class='submit'>\n";
echo "  </td>\n";
echo " </tr>\n";
echo "</table>\n";
echo "</form>\n";

$textinput = getVar('text',true);        //! ungefilterten Bericht holen
if ( ! empty($textinput) ) {
    
	//logging der neuen Berichte für Debug
    $debugfile = $debugdir.DIRECTORY_SEPARATOR.'newscan_'.$user_sitterlogin."_".date("Y-m-d_H-i-s",$config_date).".txt";
    $debugfilehandle = fopen($debugfile, "w");
    if ($debugfilehandle) {
        chmod($debugfile, 0700);
        fwrite ($debugfilehandle, '#################### input ######################'."\n\n");
        fwrite ($debugfilehandle, 'db-user: '.var_export($user_sitterlogin, true)."\n");
        fwrite ($debugfilehandle, 'selected username: '.var_export(getVar('seluser',true), true)."\n");
        fwrite ($debugfilehandle, 'date: '.var_export(date("Y.m.d H:i:s",$config_date), true)."\n");
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            fwrite ($debugfilehandle, 'HTTP_USER_AGENT: '.$_SERVER['HTTP_USER_AGENT']."\n");
        }
        if (!empty($_SERVER['SERVER_SOFTWARE'])) {
            fwrite ($debugfilehandle, 'SERVER_SOFTWARE: '.$_SERVER['SERVER_SOFTWARE']."\n");
        }
        fwrite ($debugfilehandle, 'php-Version: '.phpversion()."\n");
        fwrite ($debugfilehandle, 'input text: '.var_export(getVar('text',true), true)."\n");
        fclose ($debugfilehandle);
    }
	
	$count = 0;
 
    require_once ('plib/ParserFactoryConfigC.php');
    $availParsers = new ParserFactoryConfigC();   
    $aParserIds = $availParsers->getParserIdsFor( $textinput );
    $newparserfound = false;

    if (count($aParserIds)) {
         foreach ($aParserIds as $selectedParserId)
         {
            $parserObj = $availParsers->getParser( $textinput, $selectedParserId );
            if( $parserObj instanceof ParserI )
            {
                $key = $parserObj->getIdentifier();
                if (!isset($parser[$key])) {
                    $parser[$key] = array("/deprecated/", 1, $parserObj->getName());
                }
                if($parser[$key][1] == 1) {
                    echo "<div class='system_notification'>" . $parser[$key][2] . 
                        " erkannt. Parse ...</div>\n";
                } else {
                    echo "<div class='system_notification'>Weiteren " . $parser[$key][2] . 
                        " erkannt. Parse ...</div>\n";
                }

                $parserResult = new DTOParserResultC ($parserObj);
                $parserObj->parseText ($parserResult);

                if ($parserResult->bSuccessfullyParsed) {
                    if (!empty($parserResult->aErrors) && count($parserResult->aErrors) > 0) {
                        echo "info:<br />";
                        foreach ($parserResult->aErrors as $t) {
                            echo "...$t <br />";
                        }
                    } else {
                        $lparser = $parserResult->strIdentifier;
                        if (file_exists('parser/'.$lparser.'.php')) {
                            require_once ('parser/'.$lparser.'.php');

                            if (function_exists('parse_'.$lparser)) {
                            
                                $newparserfound = true;
                                
                                if(isset($debug)) {
                                    echo "<div class='system_debug_blue'>";
                                    echo "Rufe Parserfunktion parse_" . $lparser . " mit folgendem Parameter:<br />\n";
                                    echo "<br /><pre>";
                                    print_r($parserResult);
                                    echo "</pre><br />";
                                    echo "</div>";  
                                }

                                call_user_func ('parse_'.$lparser, $parserResult);

                                if (function_exists('done_'.$lparser)) {
                                    call_user_func ('done_'.$lparser, $parserResult);
                                }	
                                $parser[$key][1]++;
                                $count++;
                                
                            } else {
                                echo "Input erfolgreich erkannt (" . $parserObj->getName() . "). Passende Verarbeitung ist aber bisher nicht vorhanden.<br />Suche alten Parser...<br />";
                                if(isset($debug)) {
                                   echo "<div class='system_debug_blue'>parse_{$lparser}() in parser/{$lparser}.php nicht gefunden!</div>";   
                                }
                            }

                        } else {
                            echo "Input erfolgreich erkannt (" . $parserObj->getName() . "). Passende Verarbeitung ist aber bisher nicht vorhanden.<br />Suche alten Parser...<br />";
                            if(isset($debug)) {
                                echo "<div class='system_debug_blue'>parser/{$lparser}.php nicht gefunden!</div>";   
                            }                        
                        }
                    }
                } else {
                    echo "Input (" . $parserObj->getName() . ") wurde erkannt, konnte aber nicht fehlerfrei geparsed werden!<br />";               
                    if (!empty($parserResult->aErrors) && count($parserResult->aErrors) > 0) {
                        echo "error:<br />";
                        foreach ($parserResult->aErrors as $t) {
							echo "...$t <br />";
                        }
                    }
                }
            }
        }    
    }

    if (!$newparserfound) {
    
        // Es konnte kein passender Parser gefunden werden - suche alten Parser
        $textinput = getVar('text');        //! gefilterten Bericht holen       
        $textinput = str_replace(" \t", " ", $textinput);
        $textinput = str_replace("\t", " ", $textinput);

        $text = str_replace("%", "\\%", $textinput);
        $text = str_replace(" (HC)", "", $text);
        $text = str_replace(" (iHC)", "", $text);
        $text = str_replace("\r", "\n ", $text);
        $text = str_replace("\n \n", "\n", $text);

        $text = str_replace("Erdbeeren", "Eisen", $text);
        $text = str_replace("Erdbeermarmelade", "Stahl", $text);
        $text = str_replace("Erdbeerkonfit&uuml;re", "VV4A", $text);
        $text = str_replace("Brause", "chem. Elemente", $text);
        $text = str_replace("Vanilleeis", "Eis", $text);
        $text = str_replace("Eismatsch", "Wasser", $text);
        $text = str_replace("Traubenzucker", "Energie", $text);

    /* Bereich nur aktivieren, wenn im Spiel Ressourcennamen mit Keks vorkommen
        $text = str_replace("weicher Keks", "Eis", $text);
        $text = str_replace("Keksmatsch", "Wasser", $text);
        $text = str_replace("Doppelkeks mit Cremef&uuml;llung", "VV4A", $text);
        $text = str_replace("Doppelkeks", "Stahl", $text);
        $text = str_replace("Cremef&uuml;llung", "chem. Elemente", $text);
        $text = str_replace("Powerkeks", "Energie", $text);
        $text = str_replace("Sandtaler", "Credits", $text);
        $text = str_replace("Keksvernichter", "Bev&ouml;lkerung", $text);
        $text = str_replace("Keks", "Eisen", $text);
    */

        $text = explode("\n", $text);

        $scan_type = '';
        $cat = '';
        $update_users = array();
        $scanlines = array();

        $ignoremenu = FALSE;
        $ignorekoloinfo = -1;

        foreach ($text as $scan) {

            //Wirtshcaftsmenu auslassen, da das sonst zu Fehlern mi9t der Koloinfo f&uuml;hrt
            if( strpos( $scan, 'Wirtschaft - Men&uuml;' ) !== FALSE ) {
                $ignoremenu = TRUE;
            }
            if( strpos( $scan, 'Artefakt&uuml;bersicht' ) !== FALSE ) {
                $ignoremenu = FALSE; 
                $ignorekoloinfo = 1;
            }
            if( $ignoremenu ) {
                $scan = '';
            }

            foreach( $parser as $key => $value ) {
            // Nach der Umstellung in der IWDB.sql ist das Halten der html-Entities 
            // fuer $value[0] nicht mehr noetig ...
               
                //da die Koloinfo den gleichen Recognizer wie als Titel hat muss hier Bugcatching betrieben werden
                if ( ($value[0] == 'Kolonieinfo') AND ($ignorekoloinfo == 1) AND ( strpos( $scan, $value[0] ) !== FALSE ) ) {
                    $scan = 'ignored';
                    $ignorekoloinfo = 0;
                }

                if( strpos( $scan, $value[0] ) !== FALSE ) {
                    if( !empty( $scan_type )) {
                        if($parser[$scan_type][1] == 1) {
                            echo "<div class='system_notification'>" . $parser[$scan_type][2] . 
                                " erkannt. Parse ...</div>\n";
                            include("./parser/s_" . $scan_type . ".php");
                        } else {
                            echo "<div class='system_notification'>Weiteren " . $parser[$scan_type][2] . 
                                " erkannt. Parse ...</div>\n";
                        }

                        $func = "parse_" . $scan_type;

                        if(isset($debug)) {
                            echo "<div class='system_debug_blue'>";
                            echo "Rufe Parserfunktion " . $func . " mit folgendem Parameter:<br />\n";
                            echo "<br /><pre>";
                            print_r($scanlines);
                            echo "</pre><br />";
                            echo "</div>";  
                        }

                        $func($scanlines);
                        $count++;
                    }

                    // den ganzen Mist vor dem Schluessel ignorieren. Uns interessiert
                    // wirklich nur, was nach dem Schluessel kommt.
                    unset($scanlines);
                    $scanlines = array();

                    $parser[$key][1]++;
                    $scan_type = $key;
                }
            }
            $scanlines[] = $scan;
        }

        if( !empty( $scan_type )) {
            if($parser[$scan_type][1] == 1) {
            echo "<div class='system_notification'>" . $parser[$scan_type][2] . 
                " erkannt. Parse ...</div>\n";
            include("./parser/s_" . $scan_type . ".php");
            } else {
            echo "<div class='system_notification'>Weiteren " . $parser[$scan_type][2] . 
                " erkannt. Parse ...</div>\n";
            }
            $func = "parse_" . $scan_type;
            if(isset($debug)) {
                echo "<div class='system_debug_blue'>";
                echo "Rufe Parserfunktion " . $func . " mit folgendem Parameter:<br />\n";
                echo "<br /><pre>";
                print_r($scanlines);
                echo "</pre><br />";
                echo "</div>";  
            }
            $func($scanlines);
            $count++;

            echo "<br />\n";
        } else {
            echo "<div class='system_notification'>es wurde kein passender Parser erkannt.</div>\n";
        }
    }
    
    //! Anzeige fuer den Spieler ...
    if($count > 0) {
        if($count > 1) {
            echo "<table border='0' cellpadding='4' cellspacing='1' class='bordercolor' style='width: 90%;'>\n";
            echo "  <tr><td colspan='2' class='windowbg2' style='font-size: 18px;'>Zusammenfassung</td></tr>\n";
        }
        foreach( $parser as $key => $value ) {
            if( $parser[$key][1] > 0 ) {
                if($count > 1) {
                    echo "  <tr>\n";
                    echo "    <td class='windowbg1' align='right' width='30px'>" . $parser[$key][1] . "</td>\n";
                    echo "    <td class='windowbg1' align='left'>" . (($parser[$key][1] > 1) ? (plural($parser[$key][2])) : $parser[$key][2]) . "</td>\n";
                    echo "  </tr>\n";
                }
                // Closure hook for module after all needed things were inserted.
                // E.g. recalculating research levels after new researches were added. 
                if(function_exists("finish_".$key)) {
                    $func = "finish_" . $key;
                    $func();
                }

                // Display hook for displaying the result of the insertation. 
                if(function_exists("display_".$key)) {
                    $func = "display_" . $key;
                    $func();
                }
            }
        }
        if($count > 1) {
            echo "</table><br />\n";
        }
    } 
    
 	// Eigenkreation Start
    //! Mac: erstmal rausgenommen, da es $ausgabe im Moment eh nicht gibt
//	if (isset($ausgabe['KBs'])) {
//	
//		sort($ausgabe['KBs']); // sortieren nach Zeit
//	
//		echo '
//			<table border="0" cellpadding="4" cellspacing="1" class="bordercolor" style="width: 90%;">
//				<tr>
//					<td colspan="2" class="windowbg2" style="font-size: 18px;">BBCode der Kampfberichte</td>
//				</tr>
//				<tr>
//					<td class="windowbg1">';
//		foreach($ausgabe['KBs'] as $key => $value) {
//			if ($key != 0)
//				echo '
//					<br />_______________________________________________________<br /><br />';
//			echo htmlentities($value['Bericht']);
//		}
//		echo '
//				</tr>
//			</table><br />';
// 	} 
	
	$stop = microtime(true);
	$dauer = $stop - $start;
	echo '
			Dauer: '.round($dauer,4).' sec<br />';

	// Eigenkreation Ende
  
	//Debugfile nochmal öffnene und php ausgabeoutput anfügen
    $debugfilehandle = fopen($debugfile, "a");
    if ($debugfilehandle) {
        fwrite ($debugfilehandle, "\n".'#################### output ######################'."\n\n");
        fwrite ($debugfilehandle, ob_get_contents());       //php ausgabe in debugfile schreiben
        fclose ($debugfilehandle);
        echo "<div class='system_notification'>gespeichert in '" . basename($debugfile) . "'</div>";
    }
    ob_end_flush();                 //php Ausgabe am Bildschirm ausgeben, Buffer leeren und deaktivieren

    $debugdirhandle = opendir($debugdir.DIRECTORY_SEPARATOR );                           //Debugfiles älter als 7 Tage löschen
    if ($debugdirhandle) {
        while(false!==($file = readdir($debugdirhandle))) {
            if (is_file($debugdir.DIRECTORY_SEPARATOR.$file) AND substr($file,0,1) != ".") {
                if((!empty($debugfile_keeptime)) AND (filemtime($debugdir.'/'.$file) < $config_date-($debugfile_keeptime))) {
                    unlink($debugdir.DIRECTORY_SEPARATOR.$file);
                }
            }
        }
        closedir($debugdirhandle);
    }
  
	return;
}
?>
