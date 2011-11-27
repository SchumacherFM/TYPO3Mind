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
 * @package freemind2
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 */
class Tx_Freemind2_Export_mmExportCommon {

	/**
	 * pageUid of the current page
	 *
	 * @var int
	 */
	protected $pageUid;


	/**
	 * @var string
	 */
	public $mmVersion = '0.9.0';

	/**
	 * fmConfigRepository
	 *
	 * @var Tx_Freemind2_Domain_Repository_FmConfigRepository
	 */
	protected $fmConfigRepository;

	/**
	 * initializeAction
	 *
	 * @return void
	 */
	public function __construct() {
	//	$this->fmConfigRepository = t3lib_div::makeInstance('Tx_Freemind2_Domain_Repository_FmConfigRepository');
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

		$fileName = 'fm2_'.preg_replace('~[^a-z0-9]+~i','',$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']).'.mm';
		// that's quite a hack!
		file_put_contents(PATH_site.'typo3temp/'.$fileName, str_replace( array('|lt|','|gt|'), array('<','>'), $xml->asXML() ) );
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
	 * Creates a rich content node
	 *
	 * @param	SimpleXMLElement $xml
	 * @param	array $attributes  key is the name and value the value
	 * @param	string $htmlContent
	 * @return	SimpleXMLElement
	 */
	protected function addRichContentNode(SimpleXMLElement $xml,$attributes,$htmlContent) {

		$htmlContent = str_replace( array('<','>'), array('|lt|','|gt|'), $htmlContent );

		$node = $xml->addChild('node','');
		$this->CheckAttributes($attributes);

		foreach($attributes as $k=>$v){
			$node->addAttribute($k,$v);
		}

		$rc = $node->addChild('richcontent','');
		$rc->addAttribute('TYPE','NODE');
		$html = $rc->addChild('html','');
				$html->addChild('head','');
		$body = $html->addChild('body',$htmlContent);

		return $node;
	}

	/**
	 * Creates the attributes from a page record
	 *
	 * @param	array $pageRecord
	 * @param	Tx_Freemind2_Domain_Model_FmConfig $FmConfig
	 * @param	array $additionalAttributes  key is the name and value the value
	 * @return	SimpleXMLElement
	 */
	protected function getAttrFromPage(&$pageRecord,$FmConfig = NULL,$additionalAttributes = array() ) {

		/* now we have here to the the special options from the column tx_freemind2_data from table pages */

		$attr = array(
			'FOLDED'=>'false',
			'ID'=>'page_'.$pageRecord['uid'],
			'POSITION'=>'right',
			'TEXT'=>$pageRecord['title'],
		);

/*		if( !empty( $FmConfig->getNodeColor() ) ){
			$attr['COLOR'] = $FmConfig->getNodeColor();
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
		if( !isset($attributes['FOLDED']) ){
			$attributes['FOLDED'] = 'false';
		}
	}

	/**
	 * Translate key from locallang.xml.
	 *
	 * @param string $key Key to translate
	 * @param array $arguments Array of arguments to be used with vsprintf on the translated item.
	 * @return string Translation output.
	 */
	protected function translate($key, $arguments = null) {
		return Tx_Extbase_Utility_Localization::translate($key, 'Freemind2', $arguments);
	}

	/**
	 * gets microtime string.
	 *
	 * @return float microtime.
	 */
	protected function getMicrotime(){
		$m = explode(' ',microtime());
		return $m[1] .''. ( (string)$m[0] );
	}

}
