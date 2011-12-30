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

/**
 *
 *
 * @package typo3mind
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 */
class Tx_Typo3mind_Export_mmExportCommon extends Tx_Typo3mind_Export_mmExportFreeMind /* extends SimpleXMLElement */ {

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
	 * TS settings
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * t3MindRepository
	 *
	 * @var Tx_Typo3mind_Domain_Repository_T3mindRepository
	 */
	protected $t3MindRepository;


	/**
	 * Lists all valid SysDomains for viewing pages... will overwrite httpHost ...
	 *
	 * @var array
	 */
	protected $sysDomains;

	/**
	 * Check what type your are running ...
	 *
	 * @var array
	 */
	public $mapMode;

	/**
	 * Constructor
	 *
	 * @param array $settings
	 * @param Tx_Typo3mind_Domain_Repository_T3mindRepository $t3MindRepository
	 * @return void
	 */
	public function __construct(array $settings,Tx_Typo3mind_Domain_Repository_T3mindRepository $t3MindRepository) {
		$this->settings = $settings;
		$this->t3MindRepository = $t3MindRepository;
		$this->setmapMode();
		$this->initSysDomains();
		$this->setHttpHosts();
	}

	/**
	 * sets a array what kind of map will be generated
	 *
	 * @param	none
	 * @return	string
	 */
	private function setmapMode(){

		$this->mapMode = array(
			'befe'=>	$this->settings['mapMode'], /* bad content from outside ... so use properly */
			'isbe'=>	stristr($this->settings['mapMode'],'backend') !== false,
		);
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
	public function getBEHttpHost() {
		return $this->httpHosts['backend'];
	}
	/**
	 * returns the http_host
	 *
	 * @param	none
	 * @return	string
	 */
	public function getFEHttpHost( $page_Uid ) {
		if( isset($this->sysDomains[$page_Uid]) ){
			$this->httpHosts['frontend'] = 'http://'.$this->sysDomains[$page_Uid].'/';
		}
		return $this->httpHosts['frontend'];
	}



	/**
	 * Creates the attributes from a page record   MAYBE DEPRECATED
	 *
	 * @param	array $pageRecord
	 * @param	Tx_Typo3mind_Domain_Model_T3mind $T3mind
	 * @param	array $additionalAttributes  key is the name and value the value
	 * @return	SimpleXMLElement
	 */
	protected function XXXgetAttrFromPage($pageRecord,$T3mind = NULL,$additionalAttributes = array() ) {

		/* now we have here to the the special options from the column tx_typo3mind_data from table pages */

		$attr = array(
			'FOLDED'=>'false',
			'ID'=>'page_'.$pageRecord['uid'],
			'POSITION'=>'right',
			'TEXT'=>$pageRecord['title'],
		);


		// mvc webrequest -> base uri to build in!
		if( in_array($pageRecord['doktype'], array(1,4) ) && $pageRecord['uid'] > 0 ){
			// $this->mapMode['befe']
			$attr['LINK'] = 'http://'.$_SERVER['HTTP_HOST'].'/index.php?id='.$pageRecord['uid'];
		}

		return array_merge($attr,$additionalAttributes);
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


	/**
	 * format the size by bytes ... outputs human readable...
	 *
	 * @return integer bytes
	 */
	protected function formatBytes($bytes){
		$return = '';
		if( $bytes < 1024 ){ $return = sprintf('%.2f',$bytes).'  B'; }
		elseif( $bytes < 1024*1000 ){ $return = sprintf('%.2f',$bytes/1024).' KB'; }
		elseif( $bytes < 1024*1000*1000 ){ $return = sprintf('%.2f',$bytes/1024/1024).' MB'; }
		elseif( $bytes < 1024*1000*1000*1000 ){ $return = sprintf('%.2f',$bytes/1024/1024/1024).' GB'; }
		elseif( $bytes < 1024*1000*1000*1000*1000 ){ $return = sprintf('%.2f',$bytes/1024/1024/1024/1024).' TB'; }

		return str_pad($return,15,' ', STR_PAD_LEFT);
	}
	/**
	 * gets all recurSive Directory size / func could be improved with scandir()
	 *
	 * @return integer bytes
	 */
	protected function getDirSize($dir_name){
		$dir_size =0;
		if (is_dir($dir_name)) {
			if ($dh = opendir($dir_name)) {
				while (($file = readdir($dh)) !== false) {
					if($file !='.' && $file != '..'){
						if(is_file($dir_name.'/'.$file)){
							$dir_size += filesize($dir_name.'/'.$file);
						 }
						 // check for any new directory inside this directory
						 if(is_dir($dir_name.'/'.$file)){
							$dir_size +=$this->getDirSize($dir_name.'/'.$file);
						}
					}
				}
			 }
			closedir($dh);
		}
		return $dir_size;
	}

	/**
	 * gets the URL to the detailed ext description on the TER
	 *
	 * @param string $extName
	 * @return integer bytes
	 */
	protected function getTerURL($extName){
		return str_replace('###extname###',$extName,$this->settings['TerURL2Ext']);
	}

	/**
	 * tries to get the plaintext password from an md5 string... returns false on failure
	 *
	 * @param string $md5
	 * @return string
	 */
	protected function getPlainTextPasswordFromMD5($md5){
		return Tx_Typo3mind_Utility_UnsecurePasswords::getPlainPW($md5);
	}
	
	/**
	 * if defined in the settings TS it returned the color count for alternating colors
	 *
	 * @param string $methodName
	 * @param integer $rowCounter incremental
	 * @param string $type what kind of color ...
	 * @return string
	 */
	protected function getDesignAlternatingColor($methodName,$rowCounter,$type='BACKGROUND_COLOR'){
		if( !isset($this->settings['design'][$methodName]) ){ 
			$this->settings['design'][$methodName] = array($type=>array() ); 
		}
		elseif( !isset($this->settings['design'][$methodName][$type]) ){ 
			$this->settings['design'][$methodName][$type] = array(); 
		}
		
		$count = count($this->settings['design'][$methodName][$type]);
		$count = $count == 0 ? 1 : $count;
		
		$mod = $rowCounter % $count;
		return isset($this->settings['design'][$methodName][$type][$mod]) ? $this->settings['design'][$methodName][$type][$mod] : '';
		
	}/*</getDesignAlternatingColor>*/
	
	/**
	 * if defined in the settings TS it returned the edge width
	 *
	 * @param string $methodName
	 * @return string
	 */
	protected function getDesignEdgeWidth($methodName){
		if( !isset($this->settings['design'][$methodName]) ){ 
			$this->settings['design'][$methodName] = array('EDGE_WIDTH'=>0 ); /*no edge width*/
		}
		elseif( !isset($this->settings['design'][$methodName]['EDGE_WIDTH']) ){ 
			$this->settings['design'][$methodName]['EDGE_WIDTH'] = 0; 
		}
		
		return (int)$this->settings['design'][$methodName]['EDGE_WIDTH'];
		
	}/*</getDesignEdgeWidth>*/
	
	/**
	 * if defined in the settings TS it returned the edge width
	 *
	 * @param SimpleXMLElement $xmlNode
	 * @return string
	 */
	protected function RssFeeds2Node(SimpleXMLElement $xmlNode){
		
		if( count($this->settings['TYPO3SecurityRssFeeds'])==0 ){
			return false;
		}
		
		foreach($this->settings['TYPO3SecurityRssFeeds'] as $index=>$feedURL){
		
			$rssContent = simplexml_load_string($this->getURLcache($feedURL));
			if( $rssContent ){
			
				$rssHeadNode = $this->addNode($xmlNode,array('FOLDED'=>'true','TEXT'=>$rssContent->channel->title,'LINK'=>$rssContent->channel->link));
				foreach($rssContent->channel->item as $index=>$item){
				
					$htmlContent = array();
					$htmlContent[] = '<p>'.$item->author.'</p>';
					$htmlContent[] = '<p>'.$item->pubDate.'</p>';
					$htmlContent[] = '<p>'.$item->description.'</p>';

					$rssItemNode = $this->addRichContentNote($rssHeadNode,array('TEXT'=>$item->title,'LINK'=>$item->link),implode('',$htmlContent));
				
				}/*endforeach*/
			}/*endif $rssContent*/
		}/*endforeach*/
		return true;
	}/*</RssFeeds2Node>*/
	
	/**
	 * gets the enc key with a specific length
	 *
	 * @param integer $length
	 * @return string
	 */
	protected function getencryptionKey($length){
		$encK = str_repeat('X3Pk{2b#+eG5d}vi\?47RJ'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'],$length);
		return substr($encK,0,$length);
	}
	/**
	 * fetches a URL and saves it in a cache for a lifetime of 3 days
	 *
	 * @param string $url
	 * @return string
	 */
	protected function getURLcache($url){
	
		$urlContent = false;
		$cacheFile = PATH_site.'typo3temp/t3m_'.md5($_SERVER['HTTP_HOST'] . $url);
		if( !file_exists($cacheFile) || filemtime($cacheFile) < (time()-(3600*24*3)) ){
			$urlContent = trim(t3lib_div::getURL($url));
			if( !empty($urlContent) ){
				$encK = $this->getencryptionKey(strlen($urlContent));
				/* echo "Wrote Cache $cacheFile<br>\n"; */
				t3lib_div::writeFile($cacheFile,($urlContent ^ $encK) ); /* hihihi ;-) */
				
			}else{ return false; }
		}else{
			/* echo "Read Cache $cacheFile<br>\n"; */
			$urlContent = implode('',file($cacheFile));
			$encK = $this->getencryptionKey(strlen($urlContent));
			$urlContent = ($urlContent ^ $encK);
		}
		return $urlContent;
	}
	
	/**
	 * returns formated date, used the global T3 config
	 *
	 * @param integer $unixTimeStamp
	 * @return string
	 */
	public function getDateTime($unixTimeStamp){
		$unixTimeStamp = (int)$unixTimeStamp;
		$dateFormat = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'];
		$timeFormat = $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'];
		return date($dateFormat . ', ' . $timeFormat,$unixTimeStamp);
	}
	
	/**
	 * gets the note content from a DB row
	 *
	 * @param array $row from the database table
	 * @param string $tableName if defined then ALL rows specified in the TCA will be printed...
	 * @return	void
	 */
	public function getNoteContentFromRow($row,$tableName=''){
		global $TCA;
	
		$htmlContent = array('<table>');
		$htmlContent[] = '<tr><td>UID</td><td>'.$row['uid'].'</td></tr>'; unset($row['uid']);
		$htmlContent[] = '<tr><td>Created</td><td>'.$this->getDateTime($row['crdate']).'</td></tr>'; unset($row['crdate']);
		$htmlContent[] = '<tr><td>Created by</td><td>'.$this->getUserById($row['cruser_id']).'</td></tr>'; unset($row['cruser_id']);
		$htmlContent[] = '<tr><td>Last update</td><td>'.$this->getDateTime($row['tstamp']).'</td></tr>'; unset($row['tstamp']);
		
		if( isset($row['sys_language_uid']) ){ 
			$htmlContent[] = '<tr><td>Language ID</td><td>'.$row['sys_language_uid'].'</td></tr>';  unset($row['sys_language_uid']); 
		}
		
		if( isset($row['starttime']) && $row['starttime'] > 0 ){ 
			$htmlContent[] = '<tr><td>Starttime</td><td>'.$this->getDateTime($row['starttime']).'</td></tr>';  unset($row['starttime']);
		}
		if( isset($row['endtime']) && $row['endtime'] > 0 ){ 
			$htmlContent[] = '<tr><td>Endtime</td><td>'.$this->getDateTime($row['endtime']).'</td></tr>';  unset($row['endtime']);
		}
		
		/* maybe not needed .. we have to defined via TS if you want to list more ... */
		if( $tableName <> '' && count($row)>0 ){
			foreach($row as $colName=>$colVal){
			
echo '<pre>';
var_dump($TCA[$tableName]['columns'][$colName]['label']);
die('</pre>');
			
		//		$col = $this->SYSLANG->sL( $TCA[$tableName]['columns'][$colName]['label'] )
			}
		
		}/*endif $tableName*/
		
		$htmlContent[] = '</table>';
		return implode('',$htmlContent);
	}
	
}
