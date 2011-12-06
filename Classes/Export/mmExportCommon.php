<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Cyrill Schumacher <Cyrill@Schumacher.fm>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

	/*	some hints ..
	http://freemind.sourceforge.net/wiki/index.php/Asked_questions
	how to insert new lines
	Insert &#xa; instead of plain newline. Example of a map with three newlines:
	*/

/**
 *
 *
 * @package typo3mind
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 */
class Tx_Typo3mind_Export_mmExportCommon {

	/**
	 * pageUid of the current page
	 *
	 * @var int
	 */
	protected $pageUid;

	/**
	 * the http host with http prefix
	 *
	 * @var string
	 */
	protected $httpHost;


	/**
	 * @var string
	 */
	public $mmVersion = '0.9.0';

	/**
	 * t3MindRepository
	 *
	 * @var Tx_Typo3mind_Domain_Repository_T3mindRepository
	 */
	protected $t3MindRepository;

	/**
	 * initializeAction
	 *
	 * @return void
	 */
	public function __construct() {
	//	$this->t3MindRepository = t3lib_div::makeInstance('Tx_Typo3mind_Domain_Repository_T3mindRepository');
		$this->httpHost = 'http://'.t3lib_div::getIndpEnv('HTTP_HOST').'/';
	}

	/**
	 * gets the root map element and creates the map
	 *
	 * @param	none
	 * @return	SimpleXMLElement
	 */
	protected function getMap() {
		$mmXML = new SimpleXMLElement('<map></map>');
		$mmXML->addAttribute('version',$this->mmVersion);
		return $mmXML;
	}

	/**
	 * Saves the SimpleXMLElement as a xml file in the typo3temp dir
	 *
	 * @param	SimpleXMLElement $xml
	 * @param	array $attributes  key is the name and value the value
	 * @return	string the filename
	 */
	protected function finalOutputFile(SimpleXMLElement &$xml) {

		$fileName = 'TYPO3Mind_'.preg_replace('~[^a-z0-9]+~i','',$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']).'.mm';
		// that's quite a hack!
		file_put_contents(PATH_site.'typo3temp/'.$fileName, str_replace(
			array('|lt|','|gt|','@#'),
			array('<','>','&#'),
			$xml->asXML() ) );
		return $fileName;
	}




	/**
	 * adds an builtin icon
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @param	string $iconName
	 * @return	nothing
	 */
	protected function addIcon(SimpleXMLElement $xmlNode,$iconName) {
		$icon = $xmlNode->addChild('icon','');
		$icon->addAttribute('BUILTIN',$iconName);
	}

	/**
	 * adds a node
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @param	array $attributes  key is the name and value the value
	 * @return	SimpleXMLElement
	 */
	protected function addNode(SimpleXMLElement $xmlNode,$attributes) {

		$child = $xmlNode->addChild('node','');

		$this->CheckAttributes($attributes);

		foreach($attributes as $k=>$v){
			$child->addAttribute($k,$v);
		}

		// add icon if ...

		return $child;
	}

	/**
	 * adds an edge
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @param	array $attributes
	 * @return	nothing
	 */
	protected function addEdge(SimpleXMLElement $xmlNode,$attributes) {
		$edge = $xmlNode->addChild('edge','');

		if( !isset($attributes['STYLE']) ){
			$attributes['STYLE'] = 'bezier';
		}
		if( !isset($attributes['WIDTH']) ){
			$attributes['WIDTH'] = 'thin';
		}

		foreach($attributes as $k=>$v){
			$edge->addAttribute($k,$v);
		}
	}
	
	/**
	 * adds a font
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @param	array $attributes
	 * @return	nothing
	 */
	protected function addFont(SimpleXMLElement $xmlNode,$attributes) {
		$font = $xmlNode->addChild('font','');

		if( !isset($attributes['NAME']) ){
			$attributes['NAME'] = 'SansSerif';
		}
		if( !isset($attributes['SIZE']) ){
			$attributes['SIZE'] = 12;
		}

		foreach($attributes as $k=>$v){
			$font->addAttribute($k,$v);
		}
	}

	/**
	 * adds a font
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @param	array $attributes
	 * @param	string $imgRelPath relativ image path like ../typo3conf/ext/..../ext_icon.gif
	 * @param	string $imgHTML additionl html for the img tag
	 * @return	nothing
	 */
	protected function addImgNode(SimpleXMLElement $xmlNode,$attributes,$imgRelPath,$imgHTML='') {

		$iconLocal = str_replace('../','',$imgRelPath);
		
		if( is_file(PATH_site.$iconLocal)  ){
		
			$nodeHTML = '<img '.$imgHTML.' src="'.$this->httpHost.$iconLocal.'"/>'.
						'@#160;@#160;'.htmlspecialchars( $attributes['TEXT'] );
			$childNode = $this->addRichContentNode($xmlNode, $attributes ,$nodeHTML);
		
		}else {
			$childNode = $this->addNode($xmlNode,$attributes);
		}
		
		return $childNode;
	}
	
	/**
	 * Creates a rich content node
	 *
	 * @param	SimpleXMLElement $xml
	 * @param	array $attributes  key is the name and value the value
	 * @param	string $htmlContent
	 * @param	array $addEdgeAttr
	 * @param	array $addFontAttr
	 * @return	SimpleXMLElement
	 */
	protected function addRichContentNode(SimpleXMLElement $xml,$attributes,$htmlContent,$addEdgeAttr = array(),$addFontAttr = array()  ) {

		return $this->addRichContentNote($xml,$attributes,$htmlContent,$addEdgeAttr,$addFontAttr, 'NODE' );

	}
	
	/**
	 * Creates a rich content note
	 *
	 * @param	SimpleXMLElement $xml
	 * @param	array $attributes  key is the name and value the value
	 * @param	string $htmlContent
	 * @param	array $addEdgeAttr
	 * @param	array $addFontAttr
	 * @param	string $type defined how this rich content will look... like a node or a note!
	 * @return	SimpleXMLElement
	 */
	protected function addRichContentNote(SimpleXMLElement $xml,$attributes,$htmlContent,$addEdgeAttr = array(),$addFontAttr = array(), $type = 'NOTE' ) {

		$htmlContent = str_replace( array('<','>'), array('|lt|','|gt|'), $htmlContent );

		$css = '';
		
		$node = $xml->addChild('node','');
		$this->CheckAttributes($attributes);

		foreach($attributes as $k=>$v){
			$node->addAttribute($k,$v);
		}

		$rc = $node->addChild('richcontent','');
		$rc->addAttribute('TYPE',$type);
		$html = $rc->addChild('html','');
				$html->addChild('head',$css);
		$body = $html->addChild('body',$htmlContent);

		if( count($addEdgeAttr)>0 ){
			$this->addEdge($node, $addEdgeAttr );
		}
		if( count($addFontAttr)>0 ){
			$this->addFont($node, $addFontAttr );
		}
				
		return $node;
	}

		
	/**
	 * Creates the attributes from a page record
	 *
	 * @param	array $pageRecord
	 * @param	Tx_Typo3mind_Domain_Model_T3mind $T3mind
	 * @param	array $additionalAttributes  key is the name and value the value
	 * @return	SimpleXMLElement
	 */
	protected function getAttrFromPage(&$pageRecord,$T3mind = NULL,$additionalAttributes = array() ) {

		/* now we have here to the the special options from the column tx_typo3mind_data from table pages */

		$attr = array(
			'FOLDED'=>'false',
			'ID'=>'page_'.$pageRecord['uid'],
			'POSITION'=>'right',
			'TEXT'=>$pageRecord['title'],
		);

/*		if( !empty( $T3mind->getNodeColor() ) ){
			$attr['COLOR'] = $T3mind->getNodeColor();
		} */

		// mvc webrequest -> base uri to build in!
		if( in_array($pageRecord['doktype'], array(1,4) ) && $pageRecord['uid'] > 0 ){
			$attr['LINK'] = 'http://'.$_SERVER['HTTP_HOST'].'/index.php?id='.$pageRecord['uid'];
		}

		return array_merge($attr,$additionalAttributes);
	}

	/**
	 * Checks if neccessary attributes are set
	 *
	 * @param	array $attributes
	 * @return	nothing
	 */
	protected function CheckAttributes(&$attributes,$defaultNodeIdConfigFromTSorDB = '') {

		if( !isset($attributes['ID']) ){
			$attributes['ID'] = 'node_'.$this->getMicrotime();
		}

		$attributes['TEXT'] = str_replace('"','',$attributes['TEXT']);
		 
	}

	/**
	 * Translate key from locallang.xml.
	 *
	 * @param string $key Key to translate
	 * @param array $arguments Array of arguments to be used with vsprintf on the translated item.
	 * @return string Translation output.
	 */
	protected function translate($key, $arguments = null) {
		return Tx_Extbase_Utility_Localization::translate($key, 'Typo3mind', $arguments);
	}

	/**
	 * gets microtime string.
	 *
	 * @return float microtime.
	 */
	protected function getMicrotime(){
		$m = explode(' ',microtime());
		return mt_rand() .'' . ( (string)$m[0] );
	}

	/**
	 * returns an array as an html table
	 *
	 * @return string
	 */
	protected function array2Html2ColTable($array,$width=300  ){
		$nodeHTML = array('<table width="'.$width.'" border="0" cellpadding="3" cellspacing="0">');
		$i=0;
		foreach( $array as $k=>$v ){

			$nodeHTML[] = '<tr style="background-color:'.(
				$i % 2 == 0 ? '#ffffff' : '#ececec'
			).';" valign="top"><td>'.$k.'</td><td>'.htmlspecialchars($v).'</td></tr>';
			$i++;
		}
		$nodeHTML[] = '</table>';

		return implode('',$nodeHTML);
	}

}
