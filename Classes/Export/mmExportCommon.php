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
class Tx_Typo3mind_Export_mmExportCommon /* extends Tx_Typo3mind_Export_mmExportSimpleXML */ {

	/**
	 * counts the nodes and uses this value as an id in the .mm file
	 *
	 * @var int
	 */
	protected $nodeIDcounter;

	/**
	 * pageUid of the current page
	 *
	 * @var int
	 */
	protected $pageUid;

	/**
	 * the http host with http prefix
	 *
	 * @var array
	 */
	protected $httpHosts;


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
	 * TS settings
	 *
	 * @var array
	 */
	protected $settings;
	
	/**
	 * Lists all valid SysDomains for viewing pages... will overwrite httpHost ...
	 *
	 * @var array
	 */
	protected $sysDomains;
	
	/**
	 * initializeAction
	 *
	 * @return void
	 */
	public function __construct($settings) {
		$this->settings = $settings;
	//	$this->t3MindRepository = t3lib_div::makeInstance('Tx_Typo3mind_Domain_Repository_T3mindRepository');
		$this->initSysDomains();
		$this->setHttpHosts();
		$this->nodeIDcounter = 1;		
	}
	
	/**
	 * sets the http_host for frontend and backend
	 *
	 * @param	none
	 * @return	string
	 */
	private function setHttpHosts() {
	
		$this->httpHosts = array(
			'frontend'=>'http://'.t3lib_div::getIndpEnv('HTTP_HOST').'/',
			'backend'=>'http://'.t3lib_div::getIndpEnv('HTTP_HOST').'/',
		);
	}
	
	/**
	 * returns the http_host
	 *
	 * @param	none
	 * @return	string
	 */
	protected function getBEHttpHost( $page_Uid ) {
		return $this->httpHosts['backend'];
	}
	/**
	 * returns the http_host
	 *
	 * @param	none
	 * @return	string
	 */
	protected function getFEHttpHost( $page_Uid ) {
		if( isset($this->sysDomains[$page_Uid]) ){
			$this->httpHosts['frontend'] = 'http://'.$this->sysDomains[$page_Uid].'/';
		}
		return $this->httpHosts['frontend'];
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

		// todo add datetime here  .'-'.date('Y-m-d_His')
		$fileName = 'TYPO3Mind_'.preg_replace('~[^a-z0-9]+~i','',$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']).'.mm';
		// that's quite a hack!



		$xml = str_replace(
			array('|lt|','|gt|','@#'),
			array('<','>','&#'),
			$xml->asXML()
		);

		$md5 = md5($xml);
		$xml = str_replace(
			array('###MD5_FILE_HASH####'),
			array($md5),
			$xml
		).'<!--HiddenMD5:'.$md5.'-->';

		file_put_contents(PATH_site.'typo3temp/'.$fileName, $xml );
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
	 * adds a cloud
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @param	array $attributes
	 * @return	nothing
	 */
	protected function addCloud(SimpleXMLElement $xmlNode,$attributes) {
		$cloud = $xmlNode->addChild('cloud','');

		foreach($attributes as $k=>$v){
			$cloud->addAttribute($k,$v);
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
	 * adds one image to a node
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

			$nodeHTML = '<img '.$imgHTML.' src="'.$this->getBEHttpHost().$iconLocal.'"/>'.
						'@#160;@#160;'.htmlspecialchars( $attributes['TEXT'] );
			$childNode = $this->addRichContentNode($xmlNode, $attributes ,$nodeHTML);

		}else {
			$childNode = $this->addNode($xmlNode,$attributes);
		}

		return $childNode;
	}

	/**
	 * adds multiple images with links to a node - hyperlinks are not supported!
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @param	array $attributes
	 * @param	array $images [] = array(path=>,html=>,link=>) relativ image path like ../typo3conf/ext/..../ext_icon.gif 
	 * @return	nothing
	 */
	protected function addImagesNode(SimpleXMLElement $xmlNode,$attributes,$images) {

		$html = array();
		
		foreach($images as $k=>$img){
			$iconLocal = str_replace('../','',$img['path']);
			if( is_file(PATH_site.$iconLocal)  ){
				
				if( isset($img['link']) ){
					$img['link'] = str_replace('&','&amp;',$img['link']);
					$html[] = '<a href="'.$img['link'].'"><img border="0" '.$img['html'].' src="'.$this->getBEHttpHost().$iconLocal.'"/></a>';
				}else{
					$html[] = '<img '.$img['html'].' src="'.$this->getBEHttpHost().$iconLocal.'"/>';
				}

			}
		}

		if( count($html) > 0  ){
 
			$nodeHTML = implode('@#160;@#160;',$html).'@#160;@#160;'.htmlspecialchars( $attributes['TEXT'] );
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
			// $this->settings['mapMode']
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
			$attributes['ID'] = 't3m'.mt_rand().'.'.$this->nodeIDcounter;
		}

		$attributes['TEXT'] = $this->strip_tags( str_replace('"','',$attributes['TEXT']) );
		$this->nodeIDcounter++;
	}
	
	/**
	 * Creates the TLF attributes array (text, link, folded)
	 *
	 * @param	array $attributes
	 * @return	nothing
	 */
	protected function createTLFattr($text,$link='',$folded='') {
		$a = array();
		if( !empty($text) ){ $a['TEXT'] = $text; }
		if( !empty($link) ){ $a['LINK'] = $link; }
		if( !empty($folded) ){ $a['FOLDED'] = $folded; }

		return $a;

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
	 * strip tags with preg_replace whitespaces
	 *
	 * @param string $string
	 * @return string
	 */
	protected function strip_tags($string ){
		return preg_replace('~\s+~',' ',strip_tags($string));
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

	/**
	 * gets all SysDomains
	 *
	 * @return string
	 */
	private function initSysDomains(){
		$this->sysDomains = array();
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( 'pid,domainName',
			'sys_domain', 'hidden=0', '', 'pid, sorting DESC' );
		while ($r = $GLOBALS['TYPO3_DB']->sql_fetch_assoc ($result)) {
			$this->sysDomains[ $r['pid'] ] = $r['domainName'];
		}
	}
	
}
