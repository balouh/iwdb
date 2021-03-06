<?php
/*
 * ----------------------------------------------------------------------------
 * "THE BEER-WARE LICENSE" (Revision 42):
 * <MacXY@herr-der-mails.de> wrote this file. As long as you retain
 * this notice you can do whatever you want with this stuff. If we meet some
 * day, and you think this stuff is worth it, you can buy me a beer in return.
 * Mac
 * ----------------------------------------------------------------------------
 */
/**
 * @author Mac <MacXY@herr-der-mails.de>
 * @package libIwParsers
 * @subpackage parsers_de
 */

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

/**
 * Parses a Defence Information
 *
 * This parser is responsible for parsing the information of a orb|plan defence
 *
 * Its identifier: de_info_defence
 */
class ParserInfoDefenceC extends ParserBaseC implements ParserI
{

  /////////////////////////////////////////////////////////////////////////////

  public function __construct()
  {
    parent::__construct();

    $this->setIdentifier('de_info_defence');
    $this->setName('Verteidigungsinfo');
    $this->setRegExpCanParseText('/Verteidigungseinrichtungen\s-\sInfo\s+Verteidigungseinrichtungen\s-\sInfo|Verteidigungseinrichtungen\s-\sInfo.+Kampfdaten/s');
    $this->setRegExpBeginData( '' );
    $this->setRegExpEndData( '' );
  }

  /////////////////////////////////////////////////////////////////////////////

  /**
   * @see ParserI::parseText()
   */
  public function parseText( DTOParserResultC $parserResult )
  {
    $parserResult->objResultData = new DTOParserInfoDefenceResultC();
    $retVal =& $parserResult->objResultData;
    $fRetVal = 0;

    $this->stripTextToData();

    $regExp             = $this->getRegularExpression();
    $regExpRess         = $this->getRegularExpressionRess();
    $regExpEffective    = $this->getRegularExpressionEffective();

    $aResult = array();
    $fRetVal = preg_match( $regExp, $this->getText(), $aResult );

    if( $fRetVal !== false && $fRetVal > 0 )
    {
      	$parserResult->bSuccessfullyParsed = true;
	
	$retVal->strDefenceName = $aResult['strDefenceName'];
	$retVal->iProductionTime = HelperC::convertMixedTimeToTimestamp( $aResult['strTime'] );
	$retVal->aResearchs = HelperC::convertBracketStringToArray($aResult['strResearchs']);

    $retVal->strAreaName = PropertyValueC::ensureString($aResult['strTyp']);

	$retVal->iVerbrauchBrause = PropertyValueC::ensureInteger($aResult['iVerbrauchBrause']);
	$retVal->iVerbrauchEnergie = PropertyValueC::ensureInteger($aResult['iVerbrauchEnergie']);
	
	$retVal->strWeaponClass = PropertyValueC::ensureString($aResult['strWeaponClass']);
	$retVal->iAttack = PropertyValueC::ensureInteger($aResult['iAngriff']);
	$retVal->iDefence = PropertyValueC::ensureInteger($aResult['iDefence']);
//	$retVal->iArmour_kin = PropertyValueC::ensureInteger($aResult['iPanzkin']);
//	$retVal->iArmour_grav = PropertyValueC::ensureInteger($aResult['iPanzgrav']);
//	$retVal->iArmour_electr = PropertyValueC::ensureInteger($aResult['iPanzelektr']);
	$retVal->iShields = PropertyValueC::ensureInteger($aResult['iSchilde']);
	$retVal->iAccuracy = PropertyValueC::ensureFloat($aResult['iZielgenauigkeit']);

        $treffer = array();
        preg_match_all ($regExpRess, $aResult['kosten'], $treffer, PREG_SET_ORDER );
        foreach ($treffer as $teff)
        {
            $retVal->aCosts[] = array('strResourceName' => PropertyValueC::ensureResource( $teff['resource_name'] ), 'iResourceCount' => PropertyValueC::ensureInteger($teff['resource_count']));
        }

        //! Mac: effektivitat der Deffanlagen gegen alle Schiffstypen betraegt 100%!! (abweichend von den Datenblaettern)
//        $treffer = array();
//        preg_match_all ($regExpEffective, $aResult['effective'], $treffer, PREG_SET_ORDER );
//        foreach ($treffer as $teff)
//        {
//            $retVal->aEffectivity[] = array('strAreaName' => PropertyValueC::ensureString( $teff['area_name'] ), 'fEffective' => PropertyValueC::ensureFloat($teff['effective_count']/100));
//        }
    }
    else
    {
      $parserResult->bSuccessfullyParsed = false;
      $parserResult->aErrors[] = 'Unable to match the pattern.';
    }
  }

  /////////////////////////////////////////////////////////////////////////////

  private function getRegularExpressionRess()
  {

    $reResource                = $this->getRegExpResource();

    $regExpRess  = '/';
    $regExpRess  .= '(?P<resource_name>'.$reResource.')\:\s(?P<resource_count>'.$this->getRegExpDecimalNumber().')';
    $regExpRess  .= '/mx';

    return $regExpRess;
  }

  private function getRegularExpressionEffective()
  {
 
    $reResource                = $this->getRegExpAreas();

    $regExpRess  = '/';
    $regExpRess  .= '(?P<area_name>'.$reResource.')\s+(?P<effective_count>'.'\d+(?:\%|\\\%)'.')';
    $regExpRess  .= '/mx';
    
    return $regExpRess;
  }

  private function getRegularExpression()
  {
 
  $reDefenceName       = $this->getRegExpSingleLineText3();
  $reResearchName       = $this->getRegExpBracketString();
  $reMixedTime     = $this->getRegExpMixedTime();
  $reResource         = $this->getRegExpResource();
  $reCosts        = $this->getRegExpDecimalNumber();
  $reBonus        = $this->getRegExpFloatingDouble();

  $regExp  = '/';
  $regExp  .= 'Verteidigungseinrichtungen\s-\sInfo\:\s';
  $regExp  .= '(?P<strDefenceName>'.$reDefenceName.')\s*?';
  $regExp  .= '[\n\r]+';
  $regExp  .= 'Typ\s+?';
  $regExp  .= '(?P<strTyp>'.$reDefenceName.')\s*?';
  $regExp  .= '[\s\S]+?';
  $regExp  .= 'Kosten\s+?(?P<kosten>(\s?'.$reResource.'\:\s'.$reCosts.')*)';
  $regExp  .= '[\n\r]+';
  $regExp  .= 'Dauer\s+?';
  $regExp  .= '(?P<strTime>'.$reMixedTime.')\s*?';
  $regExp  .= '[\n\r]+';

  $regExp  .= 'Verbrauch\s(?:Brause|chem\.\sElemente)\s+?';
  $regExp  .= '(?P<iVerbrauchBrause>'.'\d+'.')\s*?';
  $regExp  .= '[\n\r]+';
  $regExp  .= 'Energieverbrauch\s+?';
  $regExp  .= '(?P<iVerbrauchEnergie>'.'\d+'.')\s*?';
  $regExp  .= '[\n\r]+';

  $regExp  .= 'Voraussetzungen\sForschungen\s+?';
  $regExp  .= '(?P<strResearchs>'.$reResearchName.'){0,1}';
  $regExp  .= '[\n\r]+';
 
  $regExp  .= 'Kampfdaten';
  $regExp  .= '[\n\r]+';
  $regExp  .= 'Angriff\s+?';
  $regExp  .= '(?P<iAngriff>'.'\d+'.')\s*?';
  $regExp  .= '[\n\r]+';
  $regExp  .= 'Waffenklasse\s+?';
  $regExp  .= '(?P<strWeaponClass>'.'(?:keine|elektrisch|gravimetrisch|kinetisch)'.')\s*?';
  $regExp  .= '[\n\r]+';
  $regExp  .= 'Verteidigung\s+?';
  $regExp  .= '(?P<iDefence>'.'\d+'.')\s*?';
  $regExp  .= '[\n\r]+';
//  $regExp  .= 'Panzerung\s\(kinetisch\)\s+?';
//  $regExp  .= '(?P<iPanzkin>'.'\d+'.')\s*?';
//  $regExp  .= '[\n\r]+';
//  $regExp  .= 'Panzerung\s\(elektrisch\)\s+?';
//  $regExp  .= '(?P<iPanzelektr>'.'\d+'.')\s*?';
//  $regExp  .= '[\n\r]+';
//  $regExp  .= 'Panzerung\s\(gravimetrisch\)\s+?';
//  $regExp  .= '(?P<iPanzgrav>'.'\d+'.')\s*?';
//  $regExp  .= '[\n\r]+';
  $regExp  .= 'Schilde\s+?';
  $regExp  .= '(?P<iSchilde>'.'\d+'.')\s*?';
  $regExp  .= '[\n\r]+';
////  $regExp  .= 'Wendigkeit\s+?';
////  $regExp  .= '(?P<iWendigkeit>'.'\d+'.')\s*?';
////  $regExp  .= '[\n\r]+';
  $regExp  .= 'Zielgenauigkeit\s+?';
  $regExp  .= '(?P<iZielgenauigkeit>'.$reBonus.')\s*?';
  $regExp  .= '[\n\r]+';
  $regExp  .= 'Effektivit.t\sgegen\s*[\n\r](?P<effective>(?:^'.$reDefenceName.'\s*\d+(?:\%|\\\%)[\n\r])+)';

//  $regExp  .= 'Besonderheiten';
//  $regExp  .= '[\n\r]+';
  $regExp  .= '/mxu';

    return $regExp;
  }  

  /////////////////////////////////////////////////////////////////////////////  

}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////