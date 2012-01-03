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
	public $settings;

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
	 * Lists all Sys Languages
	 *
	 * @var array
	 */
	protected $sysLanguages;

	/**
	 * Check what type your are running ...
	 *
	 * @var array
	 */
	public $mapMode;
	
	/**
	 * assoc array containing the ID and the name of the user
	 * @var array
	*/
	public $cruser_id;
	
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
		$this->initSysLanguages();
		$this->setHttpHosts();
		$this->setCruserId();
	}
	/**
	 * sets the CruserId
	 *
	 * @param	none
	 * @return	nothing
	 */
	private function setCruserId() {
		$this->cruser_id = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,concat(realName,\'(\',username,\')\') as user','be_users','','','','','uid');
	}
	
	/**
	 * gets the User by Id
	 *
	 * @param	integer $uid
	 * @return	nothing
	 */
	public function getUserById($uid) {
		return isset($this->cruser_id[$uid]) ? $this->cruser_id[$uid]['user'] : $this->translate('UserNotFound').' ('.$uid.')';
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
	 * gets all Sys Languages
	 *
	 * @return string
	 */
	private function initSysLanguages(){
/*
	mod.SHARED {
		defaultLanguageFlag = de.gif
		defaultLanguageLabel = german
	}	
*/	
		$modSharedTSconfig = t3lib_BEfunc::getModTSconfig( $this->settings['pageUid'], 'mod.SHARED');

		// fallback non sprite-configuration
		if (preg_match('/\.gif$/', $modSharedTSconfig['properties']['defaultLanguageFlag'])) {
			$modSharedTSconfig['properties']['defaultLanguageFlag'] = str_replace('.gif', '', $modSharedTSconfig['properties']['defaultLanguageFlag']);
		}


		$this->sysLanguages = array(
			0=>array(
				'title' => strlen($modSharedTSconfig['properties']['defaultLanguageLabel']) ? $modSharedTSconfig['properties']['defaultLanguageLabel'].' ('.$GLOBALS['LANG']->sl('LLL:EXT:lang/locallang_mod_web_list.xml:defaultLanguage').')' : $GLOBALS['LANG']->sl('LLL:EXT:lang/locallang_mod_web_list.xml:defaultLanguage'),
				'flag' => $modSharedTSconfig['properties']['defaultLanguageFlag'],
			)
		);
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( 'uid,title,flag',
			'sys_language', '', '', '' );
		while ($r = $GLOBALS['TYPO3_DB']->sql_fetch_assoc ($result)) {
			$this->sysLanguages[ $r['uid'] ] = $r;
		}
	}

	/**
	 * gets details for a Sys Languages
	 *
	 * @param integer $language_id
	 * @param string $column (title or flag)
	 * @return string
	 */
	public function getSysLanguageDetails($language_id,$column){
		if( $column == 'flag' ){
			$return = 'typo3/gfx/flags/'.$this->sysLanguages[$language_id][$column].'.gif';
		}else{
			$return = $this->sysLanguages[$language_id][$column];
		
		}
		return $return;
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

				$rssHeadNode = $this->addNode($xmlNode,array(
					'FOLDED'=>'true',
					'TEXT'=>htmlspecialchars($rssContent->channel->title),
					'LINK'=>$rssContent->channel->link
				));
				foreach($rssContent->channel->item as $index=>$item){

					$htmlContent = array();
					$htmlContent[] = '<p>'.htmlspecialchars($item->author).'</p>';
					$htmlContent[] = '<p>'.htmlspecialchars($item->pubDate).'</p>';
					$htmlContent[] = '<p>'.$item->description.'</p>';

					$rssItemNode = $this->addRichContentNote($rssHeadNode,array('TEXT'=>htmlspecialchars($item->title),'LINK'=>$item->link),implode('',$htmlContent));

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
	 * @param string $tableName if defined then ALL rows specified in the TCA will be printed...
	 * @param array $row from the database table
	 * @return	void
	 */
	public function getNoteContentFromRow($tableName,$row){
		global $TCA;

		$htmlContent = array('<table>');
		$htmlContent[] = $this->getNoteTableRow('UID',$row['uid']); unset($row['uid']);

		$col = 'crdate';
		$label = $this->getNoteTableRowLabel($tableName,$col,'Created');
		$htmlContent[] = $this->getNoteTableRow($label,$this->getDateTime($row[$col]) ); unset($row[$col]);

		$col = 'cruser_id';
		$label = $this->getNoteTableRowLabel($tableName,$col,'Created by');
		$htmlContent[] = $this->getNoteTableRow($label,$this->getUserById($row[$col]) ); unset($row[$col]);

		$col = 'tstamp';
		$label = $this->getNoteTableRowLabel($tableName,$col,'Last update');
		$htmlContent[] = $this->getNoteTableRow($label,$this->getDateTime($row[$col]) ); unset($row[$col]);


		if( isset($row['sys_language_uid']) ){
			$col = 'sys_language_uid';
			$label = $this->getNoteTableRowLabel($tableName,$col,'Language ID');
			$htmlContent[] = $this->getNoteTableRow($label,'('.$row[$col].') '.$this->getSysLanguageDetails($row[$col],'title').' <img src="'.$this->getBEHttpHost().$this->getSysLanguageDetails($row[$col],'flag').'"/>' );
		} unset($row[$col]);

		if( isset($row['starttime']) && $row['starttime'] > 0 ){
			$col = 'starttime';
			$label = $this->getNoteTableRowLabel($tableName,$col,'Starttime');
			$htmlContent[] = $this->getNoteTableRow($label,$this->getDateTime($row[$col]) );
		}
		unset($row['starttime']);
		if( isset($row['endtime']) && $row['endtime'] > 0 ){
			$col = 'endtime';
			$label = $this->getNoteTableRowLabel($tableName,$col,'Endtime');
			$htmlContent[] = $this->getNoteTableRow($label,$this->getDateTime($row[$col]) );
		}
		unset($row['endtime']);
		unset($row['uid']);
		unset($row['pid']);
		unset($row['titInt0']);
		unset($row['title']);
		unset($row['doktype']);
		unset($row['shortcut_mode']);
		unset($row['module']);
		unset($row['deleted']);
		unset($row['hidden']);
		unset($row['disable']);

		/* list user defined columsn for a table listet in a sysfolder */
		if( $tableName <> '' && count($row)>0 ){
		
			$bodyLength = isset($this->settings['SysFolderContentListTextMaxLength']) ? (int)$this->settings['SysFolderContentListTextMaxLength'] : 250;

			foreach($row as $colName=>$colVal){
				$noLtGtReplace = 0;
			
				if( $colVal <> '' ){
					$label = $this->getNoteTableRowLabel($tableName,$colName,$colName);
					$tcaEval = isset($TCA[$tableName]['columns'][$colName]) ? $TCA[$tableName]['columns'][$colName]['config']['eval'] : '';
					$tcaType = isset($TCA[$tableName]['columns'][$colName]) ? strtolower($TCA[$tableName]['columns'][$colName]['config']['type']) : 'text';
					
					/*<TemplaVoila>*/
					if( $tableName == 'tx_templavoila_tmplobj' && in_array($colName,array('fileref_md5','fileref','datastructure')) ){
					
						if( $colName=='fileref_md5' && isset($row['fileref_md5']) && 
							isset($row['fileref']) && file_exists(PATH_site.$row['fileref']) ){
							
							$currentMD5 = @md5_file(PATH_site.$row['fileref']);
							if($currentMD5 <> $row['fileref_md5'] ){
								$label .= ' <img src="'.$this->getBEHttpHost().'typo3/sysext/t3skin/icons/gfx/icon_warning.gif"/>';
								$colVal = ' New: '.$currentMD5.'<br />Old: '.$colVal;
							}
						}elseif($colName=='datastructure' || $colName=='fileref'){
							$colVal = $this->value2ATag($colVal);
						}
					}/*</TemplaVoila>*/
					
					/*<Templates>*/
					elseif( $tableName == 'sys_template' && in_array($colName,array('include_static_file','constants','config')) ){
						if( $colName == 'include_static_file' ){
							$colVal = implode('<br />',explode(',',$colVal));
						}
						if( $colName == 'constants' || $colName == 'config' ){
							$noLtGtReplace = 1;

							/* @todo link external TS files via eID ... still to think about it ...
							$colVal = Tx_Typo3mind_Utility_Helpers::TSReplaceFileLinkWithHref($colVal); 
							*/
							$colVal = '|lt|pre|gt|'.trim($colVal).'|lt|/pre|gt|';
						}
					}/*</Templates>*/
					
					/*<default values>*/
					elseif( stristr($tcaEval,'date')!==false ){ $colVal = $this->getDateTime($colVal); }
					elseif( $tcaType == 'text' || $tcaType == 'input' ){
						/* we can't relay on the user, that all HTML is XML valid ... */
						$colVal = strip_tags($colVal);
						if( strlen($colVal) > $bodyLength ){ $colVal = preg_replace('/^(.{'.$bodyLength.'}\S*).*$/s','\\1 ...',$colVal); }
						$colVal = preg_replace('~[\r\n]+~',"\n",$colVal);
						$colVal = nl2br($colVal);
					}
					/*</default values>*/
					 
					$htmlContent[] = $this->getNoteTableRow($label,$colVal,$noLtGtReplace );
				}/*endif*/
			}/*endforeach*/

		}/*endif $tableName*/

		$htmlContent[] = '</table>';
		return implode('',$htmlContent);
	}/*</getNoteContentFromRow>*/

	
	private function value2ATag($string){
		return '<a href="'.$this->getBEHttpHost().$string.'">'.$string.'</a> '.$this->translate('value2ATag');
	}
	
	/**
	 * generates a HTML table row
	 *
	 * @param string $label
	 * @param string $value
	 * @return	void
	 * @see getNoteContentFromRow
	 */
	private function getNoteTableRow($label,$value,$noLtGtReplace=0){	
		$value = htmlspecialchars($value);
		if( $noLtGtReplace == 0 ){ $value = str_replace(array('&lt;','&gt;'),array('|lt|','|gt|'),$value); }
		return '<tr valign="top"><td>'.htmlspecialchars($label).'</td><td>'.$value.'</td></tr>';
	}
	
	/**
	 * generates a label for a HTML table row
	 *
	 * @param string $tableName
	 * @param string $col the column
	 * @param string $alt alternative text
	 * @return	void
	 * @see getNoteContentFromRow
	 */
	private function getNoteTableRowLabel($tableName,$col,$alt){
		global $TCA;
		$label = isset($TCA[$tableName]['columns'][$col]) ? $GLOBALS['LANG']->sL( $TCA[$tableName]['columns'][$col]['label']) : $alt;
		if( empty($label) ){ $label = $alt; }
		return $label;
	}
}
