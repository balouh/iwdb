<?php
/*
 * ----------------------------------------------------------------------------
 * "THE BEER-WARE LICENSE" (Revision 42):
 * <martin@martimeo.de> wrote this file. As long as you retain
 * this notice you can do whatever you want with this stuff. If we meet some
 * day, and you think this stuff is worth it, you can buy me a beer in return.
 * Martin Martimeo
 * ----------------------------------------------------------------------------
 */
/**
 * @author Martin Martimeo <martin@martimeo.de>
 * @package libIwParsers
 * @subpackage parsers_de
 */

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

/**
 * Parses a Sol Information
 *
 * This parser is responsible for parsing the information of a solsystem
 *
 * It uses two child parsers: ParserUniversumPlainTextC and ParserUniversumXmlC.
 *
 * Its identifier: de_universum
 */
class ParserUniversumC extends ParserBaseC implements ParserI
{
  const ID_PARSER_PLAIN_TEXT = 'plainText';
  const ID_PARSER_XML = 'xml';

  private $_parserXml;
  private $_parserPlainText;
  private $_strParserToUse = '';

  /////////////////////////////////////////////////////////////////////////////

  public function __construct()
  {
    parent::__construct();

    $this->_parserPlainText = new ParserUniversumPlainTextC();
    $this->_parserXml = new ParserUniversumXmlC();

    $this->setIdentifier('de_universum');
    $this->setName('Universum');
    $this->setRegExpCanParseText( '' );
    $this->setRegExpBeginData( '' );
    $this->setRegExpEndData( '' );
  }

  /////////////////////////////////////////////////////////////////////////////

  private function getParserToUse()
  {
    $retVal = NULL;

    switch( $this->_strParserToUse )
    {
    case self::ID_PARSER_XML:
        $retVal = $this->_parserXml;
        break;
    case self::ID_PARSER_PLAIN_TEXT:
    default:
        $retVal = $this->_parserPlainText;
        break;
    }

    return $retVal;
  }

  /////////////////////////////////////////////////////////////////////////////

  /**
   * @see ParserI::canParseText()
   *
   * This function checks if one parser (xml or plainText) can
   * parse the provided text.
   */
  public function canParseText( $text )
  {
    $retVal = false;

    if( $this->_parserXml->canParseText($text) )
    {
      $retVal = true;
      $this->_strParserToUse = self::ID_PARSER_XML;
    }
    elseif( $this->_parserPlainText->canParseText($text) )
    {
      $retVal = true;
      $this->_strParserToUse = self::ID_PARSER_PLAIN_TEXT;
    }

    return $retVal;
  }

  /////////////////////////////////////////////////////////////////////////////

  /**
   * @see ParserI::parseText()
   */
  public function parseText( DTOParserResultC $parserResult )
  {
    $parser = $this->getParserToUse();
    return $parser->parseText($parserResult);
  }

  /////////////////////////////////////////////////////////////////////////////

}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

/**
 * Parses a Sol Information
 *
 * This parser is responsible for parsing the information of a solsystem
 *
 * Its identifier: de_universum
 *
 * This parser is the former ParserUniversumC. It currently parses the xml
 * data of the universe view, but based on regular expressions.
 * The new parser ParserUniversumXmlC will do the same job, but is based
 * on SimpleXML.
 */
class ParserUniversumPlainTextC extends ParserBaseC implements ParserI
{
  private $_stringCanParseText;

  /////////////////////////////////////////////////////////////////////////////

  public function __construct()
  {
    parent::__construct();

    $this->setIdentifier('de_universum');
    $this->setName('Universum (Text)');
    //because the parser currently only can parse xml, we include the <?xml
    $this->setStringCanParseText('Das\sUniversum\s\-\sunendliche\sWeiten.+<?xml', 's');
    $this->setRegExpBeginData( '' );
    $this->setRegExpEndData( '' );
  }

  /////////////////////////////////////////////////////////////////////////////

  public function getStringCanParseText()
  {
    return $this->_stringCanParseText;
  }

  /////////////////////////////////////////////////////////////////////////////

  private function setStringCanParseText( $value, $modifier )
  {
    $value = PropertyValueC::ensureString($value);
    $modifier = PropertyValueC::ensureString($modifier);

    $this->setRegExpCanParseText( "/$value/$modifier" );
    $this->_stringCanParseText = $value;
  }

  /////////////////////////////////////////////////////////////////////////////

  /**
   * @see ParserI::parseText()
   */
  public function parseText( DTOParserResultC $parserResult )
  {
    $parserResult->objResultData = new DTOParserUniversumResultC();
    $retVal =& $parserResult->objResultData;
    $fRetVal = 0;
    $this->stripTextToData();

    $regText  = $this->getText();
    $regExp   = $this->getRegularExpression();
    $aResult  = array();  
    $fRetVal  = preg_match_all ( $regExp, $regText, $aResult, PREG_SET_ORDER );

    if( $fRetVal !== false && $fRetVal > 0 )
    {
    $parserResult->bSuccessfullyParsed = true;

    $iCoordsGal = 0;
    $iCoordsSol = 0;
    foreach ($aResult as $result)
    {
      $planet = new DTOParserUniversumPlanetResultC();
      $objCoordinates = new DTOCoordinatesC();

      //! Gal & Sol gelten jeweils fuer alle Planeten
      if (!empty($result['iCoordsGal']))
        $iCoordsGal = PropertyValueC::ensureInteger($result['iCoordsGal']);

      if (!empty($result['iCoordsSol']))
        $iCoordsSol = PropertyValueC::ensureInteger($result['iCoordsSol']);

      $iCoordsPla = PropertyValueC::ensureInteger($result['iCoordsPla']);


      $aCoords = array('coords_gal' => $iCoordsGal, 'coords_sol' => $iCoordsSol, 'coords_pla' => $iCoordsPla);
      $strCoords = $iCoordsGal.':'.$iCoordsSol.':'.$iCoordsPla;

      $objCoordinates->iGalaxy = $iCoordsGal;
      $objCoordinates->iPlanet = $iCoordsPla;
      $objCoordinates->iSystem = $iCoordsSol;

      $planet->aCoords = $aCoords;
      $planet->objCoordinates = $objCoordinates;
      $planet->strCoords = $strCoords;

      $planet->strUserName = PropertyValueC::ensureString($result['strUserName']);
      $planet->strUserAlliance = PropertyValueC::ensureString($result['strAlliance']);
      $planet->strPlanetName = trim(PropertyValueC::ensureString($result['strPlanetName']));
      if ($planet->strPlanetName == "-" && empty($planet->strUserName))
        $planet->strPlanetName = "";

      $planet->strObjectType = PropertyValueC::ensureString($result['strObjectType']);
      if (empty($planet->strObjectType))        //! damit das ensureEnum korrekt funktioniert
                $planet->strObjectType = "---";

      $planet->strPlanetType = PropertyValueC::ensureString($result['strPlanetType']);
      if ($iCoordsPla == 0 && empty($planet->strPlanetType))    //! damit das ensureEnum korrekt funktioniert
            $planet->strPlanetType = "Sonne";

      //! Mac: Problem Opera liefert keine Informationen ueber den Planetentyp!
      $planet->eObjectType = PropertyValueC::ensureEnum ($planet->strObjectType, "eObjectTypes") ;
      $planet->ePlanetType = PropertyValueC::ensureEnum ($planet->strPlanetType, "ePlanetTypes") ;

//      if (isset($result['strNebel']) && !empty($result['strNebel']))
//      {
//        $planet->strNebula = PropertyValueC::ensureString($result['strNebel']);
//        $planet->bHasNebula = true;
//        $planet->eNebula = PropertyValueC::ensureEnum( $result['strNebel'], 'ePlanetSpecials' );
//      }

//      if ($result['strObjectType'] == "Raumstation")
//      {
//        $retVal->aPlanets[$iCoordsGal.':'.$iCoordsSol.':0'] = new DTOParserUniversumPlanetResultC();
//        $retVal->aPlanets[$iCoordsGal.':'.$iCoordsSol.':0']->strPlanetType = 'Raumstation';
//      }
//      else {
        $retVal->aPlanets[$strCoords] = $planet;
//      }
    }
      
    }
    else
    {
      $parserResult->bSuccessfullyParsed = false;
      $parserResult->aErrors[] = 'Unable to match the pattern.';
    }

  }

 /////////////////////////////////////////////////////////////////////////////

  /**
   * For debugging with "The Regex Coach" which doesn't support named groups
   */
  private function getRegularExpressionWithoutNamedGroups()
  {
    $retValLine = $this->getRegularExpression();

    $retValLine = preg_replace( '/\?P<\w+>/', '', $retValLine );

    return $retValLine;
  }

  /////////////////////////////////////////////////////////////////////////////

  private function getRegularExpression()
  {
    /**
    */
  $reCoordsGal        = '\d+';
  $reCoordsSol        = '\d+';

  $rePlanetType       = $this->getRegExpPlanetTypes();
  $reObjectType       = $this->getRegExpKoloTypes();
  $reName           = $this->getRegExpUserName();
  $reAlliance         = $this->getRegExpSingleLineText();
  $rePlanetName       = $this->getRegExpSingleLineText();
  $reText           = $this->getRegExpSingleLineText();
  $reCoordsPla        = '\d+';
//  $rePlanetPoints     = '\d+';
//  $reNebel       = $this->getRegExpSingleLineText();
  $reRank            = $this->getRegExpUserRank_de();

  $regExp  = '/';
  //! Header
  $regExp  .= '(?:';
  $regExp  .= 'Galaxy\s(?P<iCoordsGal>' . $reCoordsGal . '),\sSonnensystem\s(?P<iCoordsSol>' . $reCoordsSol . ')';
  $regExp  .= '\s*';
  $regExp  .= $reAlliance;
  $regExp  .= '\s*';
  $regExp  .= '\s+Name\s+Allianztag\s+Planetenname\s+Aktionen\s*';
  $regExp  .= ')?';
  //! Planetlines
  $regExp  .= '(?:';
  $regExp  .= '^\s*(?P<iCoordsPla>' . $reCoordsPla . ')';       // bei Opera gibts ein zus. Leerzeichen nach Zeilenanfang
  $regExp  .= '\s*';
  $regExp  .= '^(?P<strObjectType>'.$reObjectType.'|)';
  $regExp  .= '\s*';
  $regExp  .= '^(?P<strPlanetType>'.$rePlanetType.'|schwarzes\sLoch|Sonne|)';
  $regExp  .= '\s*';
  $regExp  .= '(?:^(?:'.$rePlanetType.'|schwarzes\sLoch|Sonne)\s*){0,3}';
  $regExp  .= '^\s(?P<strUserName>'.$reName.'|)';
  $regExp  .= '\s*';
  $regExp  .= '(?:\[(?P<strAlliance>'.$reAlliance.')\])?';
  $regExp  .= '\s*';
  $regExp  .= '(?:\((?P<strAllianceRank>'.$reRank.')\))?';
  $regExp  .= '\s*';
  $regExp  .= '(?P<strPlanetName>'.$rePlanetName.'|-|)';
  $regExp  .= '\s*';
  $regExp  .= '(?:Flottenlink\sanlegen';    //! Header Link
  $regExp  .= '|';                          //! oder Versendelinks
  $regExp  .= 'Flotte\sversenden';
  $regExp  .=     '(?:(?:\s+' . $reText . '){1,5}(?=\s+^\s*' .$reCoordsPla. '\s*))?';     //! User abh. Flottenlinks (max. 5 Stck)
  $regExp  .= ')';
  $regExp  .= '\s+';
  $regExp  .= ')';

  $regExp  .= '/mx';

    return $regExp;
  }

  /////////////////////////////////////////////////////////////////////////////

  
}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

/**
 * Parses a Sol Information (uses the xml data)
 *
 * This parser is responsible for parsing the (xml-)information of a solsystem.
 * The xml will be validated against the relaxNG Schema that can be found at
 * lib/xml/relaxng/universe.rng
 *
 * The parser can handle both complete page input (<Ctrl> + <a>, <Ctrl> + <c>)
 * and also xml-only input (for cases when s.o. clicks the xml-Data and then
 * copies it).
 *
 * Its identifier: de_universum
 */


class ParserUniversumXmlC extends ParserBaseC implements ParserI
{

  /////////////////////////////////////////////////////////////////////////////

  public function __construct()
  {
    parent::__construct();

    $this->setIdentifier('de_universum');
    $this->setName('Universum (XML)');
    $this->setRegExpBeginData( '/(?=<\?xml)/s' );
    $this->setRegExpEndData( '/(?<=<\/planeten_data>)/' );
  }

  /////////////////////////////////////////////////////////////////////////////

  protected function getRngFileUniverse()
  {
    return ConfigC::get( 'path.rng' ) . DIRECTORY_SEPARATOR . 'universe.rng';
  }

  /////////////////////////////////////////////////////////////////////////////

  /**
   * @see ParserI::canParseText()
   *
   * This function checks if the parser can handle the text that is provided.
   * To do so, it tries to load the text as xml and to validate it against
   * the universe schema.
   */
  public function canParseText( $text )
  {
    $retVal = false;
    $fRetVal = false;
    $dom = new DOMDocument();
    $xmlString = '';
    $rngFileUniverse = $this->getRngFileUniverse();

    //dont modify the text that is provided!
    $textCopy = $text;

    $this->setText($textCopy);
    $this->stripTextToData();

    $xmlString = $this->getText();

    //supress errors, otherwise this parser may crash the
    //application if it is provided non-xml data!
    $fRetVal = @$dom->loadXml( $xmlString, LIBXML_NOERROR );

    if( $fRetVal === true )
    {
      $retVal = $dom->relaxNGValidate( $rngFileUniverse );
    }

    return $retVal;
  }

  /////////////////////////////////////////////////////////////////////////////

  /**
   * @see ParserI::parseText()
   */
  public function parseText( DTOParserResultC $parserResult )
  {
    $parserResult->objResultData = new DTOParserUniversumResultC();
    $retVal =& $parserResult->objResultData;
    $fRetVal = 0;
    $rngFileUniverse = $this->getRngFileUniverse();

    $this->stripTextToData();

    $xmlString = $this->getText();
/*
  // Create a XSLT Processor
  // "$xsltproc" is the XSLT processor resource.

  $xsltproc = xslt_create();
 
  // Processing the two files
  $result = xslt_process( $xsltproc, $xmlString, 'example.xslt' );

  // If we have a result then display the contents?
  // If not then die and display the error information

  if( $result ) {
    echo "$result";
  } else {
    die( sprintf(
      "The XSLT processor failed [%d]: %s",
      xslt_errno( $xsltproc ),
      xslt_error( $xsltproc ) )
    );
  }

  // Free up the XSLT Processor
  xslt_free( $xsltproc );

*/

/*
  // Create a DomDocument object from an xml file.
  if( !$domXmlObj = domxml_open_file( "example.xml" ) ) {
    die( "Cannot parse the xml file." );
  }

  // Create a DomDocument object from the xslt file.
  $domXsltObj = domxml_xslt_stylesheet_file( "example.xslt" );

  // Create a DomDocument object from the xslt transformation
  // of the xml and xslt file.
  $domTranObj = $domXsltObj->process( $domXmlObj );

  // Display the output of the DomDocument object
  // from the xslt transformation.
  echo $domXsltObj->result_dump_mem( $domTranObj );
print_die("bla"); 
*/
 

    //load the xml and replace nebula names
    $dom = $this->xmlInjectNebulaNames(utf8_decode($xmlString));

    //I prefer to use simpleXml...
    $xml = simplexml_import_dom( $dom );

    $retVal->iTimestamp = PropertyValueC::ensureInteger( $xml->informationen->aktualisierungszeit );

    foreach( $xml->planet as $xmlPlanet )
    {
 
      $planet = new DTOParserUniversumPlanetResultC();
      $objCoordinates = new DTOCoordinatesC();

      $iCoordsPla = PropertyValueC::ensureInteger($xmlPlanet->koordinaten->pla);
      $iCoordsGal = PropertyValueC::ensureInteger($xmlPlanet->koordinaten->gal);
      $iCoordsSol = PropertyValueC::ensureInteger($xmlPlanet->koordinaten->sol);
      $strCoords = PropertyValueC::ensureString($xmlPlanet->koordinaten->string);
      $aCoords = array('coords_gal' => $iCoordsGal, 'coords_sol' => $iCoordsSol, 'coords_pla' => $iCoordsPla);

      $objCoordinates->iGalaxy = $iCoordsGal;
      $objCoordinates->iPlanet = $iCoordsPla;
      $objCoordinates->iSystem = $iCoordsSol;

      $planet->aCoords = $aCoords;
      $planet->bHasNebula = count($xmlPlanet->xpath('nebel')) === 0 ? false : true;
      $planet->eObjectType = PropertyValueC::ensureEnum( $xmlPlanet->objekt_typ, 'eObjectTypes' );
      $planet->ePlanetType = PropertyValueC::ensureEnum( $xmlPlanet->planet_typ, 'ePlanetTypes' );
      $planet->iIngamePlaiid = PropertyValueC::ensureInteger( $xmlPlanet->id );
      $planet->objCoordinates = $objCoordinates;
      $planet->strCoords = $strCoords;
      $planet->strObjectType = PropertyValueC::ensureString( $xmlPlanet->objekt_typ );
      $planet->strPlanetName = PropertyValueC::ensureString( $xmlPlanet->name );
      $planet->strPlanetType = PropertyValueC::ensureString( $xmlPlanet->planet_typ );
      $planet->strUserAlliance = PropertyValueC::ensureString( $xmlPlanet->user->allianz_tag );
      $planet->strUserName = PropertyValueC::ensureString( $xmlPlanet->user->name );

      if( $planet->bHasNebula === true )
      {
        $planet->eNebula = PropertyValueC::ensureEnum( $xmlPlanet->injectedNebulaName, 'ePlanetSpecials' );
        $planet->strNebula = PropertyValueC::ensureString( $xmlPlanet->nebel );
      }

      $retVal->aPlanets[$strCoords] = $planet;
    }

    $parserResult->bSuccessfullyParsed = true;
  }

  /////////////////////////////////////////////////////////////////////////////

  protected function xmlInjectNebulaNames( $xmlString )
  {
    $retVal = NULL;
    $xsltProcessor = new XSLTProcessor();
    $docInjectionXsl = new DOMDocument();
   
    $docSourceXml = new DOMDocument();
    $docSourceXml->loadXml( $xmlString );
   
    $filenameInjectionXsl = ConfigC::get( 'path.xslt' ) . DIRECTORY_SEPARATOR . 'universeInjectNebulaNames.xsl';

    $docInjectionXsl->load( $filenameInjectionXsl );
    $xsltProcessor->importStyleSheet( $docInjectionXsl );

    if( $docSourceXml instanceof DOMDocument )
    {
      $retVal = $xsltProcessor->transformToDoc( $docSourceXml );

      //TODO: error processing
      if( $retVal === false )
      {
        $retVal = NULL;
      }
    }

    return $retVal;
  }

  /////////////////////////////////////////////////////////////////////////////

}


///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////