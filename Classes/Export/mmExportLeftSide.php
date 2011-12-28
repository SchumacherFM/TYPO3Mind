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
class Tx_Typo3mind_Export_mmExportLeftSide extends Tx_Typo3mind_Export_mmExportCommon {


	/**
	 * @var SimpleXMLElement
	 */
	protected $xmlParentNode;

	/**
	 * @var object
	 */
	protected $SYSLANG;

	/**
	 * @var array
	 */
	protected $categories;

	/**
	 * @var array
	 */
	protected $types;

	/**
	 * @var array
	 */
	protected $states;

	/**
	 * @var array
	 */
	protected $stateColors;


	/**
	 * __constructor
	 *
	 * @param array $settings
	 * @param Tx_Typo3mind_Domain_Repository_T3mindRepository $t3MindRepository
	 * @return void
	 */
	public function __construct(array $settings,Tx_Typo3mind_Domain_Repository_T3mindRepository $t3MindRepository) {
		parent::__construct($settings,$t3MindRepository);

		$this->SYSLANG = t3lib_div::makeInstance('language');
		$this->SYSLANG->init('default');	// initalize language-object with actual language
		$this->categories = array(
			'be' => $this->SYSLANG->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_BE'),
			'module' => $this->SYSLANG->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_BE_modules'),
			'fe' => $this->SYSLANG->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_FE'),
			'plugin' => $this->SYSLANG->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_FE_plugins'),
			'misc' => $this->SYSLANG->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_miscellanous'),
			'services' => $this->SYSLANG->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_services'),
			'templates' => $this->SYSLANG->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_templates'),
			'example' => $this->SYSLANG->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_examples'),
			'doc' => $this->SYSLANG->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_documentation'),
			'' => 'none'
		);
		$this->types = array(
			'S' => $this->SYSLANG->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:type_system'),
			'G' => $this->SYSLANG->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:type_global'),
			'L' => $this->SYSLANG->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:type_local'),
		);


		/**
		 * Extension States
		 * Content must be redundant with the same internal variable as in class.tx_extrep.php!
		 */
		$this->states = tx_em_Tools::getStates();
		$this->stateColors = tx_em_Tools::getStateColors();

	}




	/**
	 * gets some T3 specific informations about fileadmin and uploads folder ...
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	SimpleXMLElement
	 */
	private function getTYPONodeFiles(SimpleXMLElement $xmlNode) {

		$MainNode = $this->addImgNode($xmlNode,array(
			'FOLDED'=>'true',
			'TEXT'=>$this->translate('tree.typo3filesandfolders'),
		), 'typo3/sysext/t3skin/images/icons/apps/pagetree-folder-default.png', 'height="16"' );

		$nodeFileadmin = $this->addNode($MainNode,array(
			'TEXT'=>'fileadmin',
			'FOLDED'=>'true',
		));
		$this->getTYPONodeFilesScandir($nodeFileadmin,PATH_site.'fileadmin/');



		$nodeUpload = $this->addNode($MainNode,array(
			'TEXT'=>'uploads',
			'FOLDED'=>'true',
		));
		$this->getTYPONodeFilesScandir($nodeUpload,PATH_site.'uploads/');

	}/*</getTYPONodeFiles>*/


	/**
	 * scans the dir ;-)
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @param	string $fullPath
	 * @return	SimpleXMLElement
	 */
	private function getTYPONodeFilesScandir(SimpleXMLElement $xmlNode,$fullPath) {

		$dirLevel1 = scandir($fullPath);
		foreach($dirLevel1 as $k=>$vL1){

			$BACKGROUND_COLOR = $this->getDesignAlternatingColor('getTYPONodeFiles',$k);

			/* is dir and avoid .svn or .git or ... folders file starting with a . */
			if( is_dir($fullPath.$vL1) && preg_match('~^\..*~',$vL1)==false ){

				$size = $this->formatBytes( $this->getDirSize($fullPath.$vL1) );

				$faLevel1 = $this->addNode($xmlNode,array(
					'TEXT'=>$vL1.' '.$size,
					'BACKGROUND_COLOR'=>$BACKGROUND_COLOR,
				));
				$this->addEdge($faLevel1,array('WIDTH'=>$this->getDesignEdgeWidth('getTYPONodeFiles'),'COLOR'=>$BACKGROUND_COLOR));

				$dirLevel2 = scandir($fullPath.$vL1);
				foreach($dirLevel2 as $k2=>$vL2){
					$Level2Dir = $fullPath.$vL1.'/'.$vL2;
					/* is dir and avoid .svn or .git or ... folders file starting with a . */
					if( is_dir($Level2Dir) && preg_match('~^\..*~',$vL2)==false ){

						$size = $this->formatBytes( $this->getDirSize($Level2Dir) );

						$faLevel2 = $this->addNode($faLevel1,array(
							'TEXT'=>xmlentities($vL2.' '.$size),
							'BACKGROUND_COLOR'=>$BACKGROUND_COLOR,
						));
					}
				}/*endforeach*/


			}/*endif*/
		} /*endforeach*/
	}/*</getTYPONodeFilesScandir>*/

	/**
	 * gets some T3 logins and error logs
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	SimpleXMLElement
	 */
	private function getTYPONodeLogs(SimpleXMLElement $xmlNode) {
		// logs
		$LogsNode = $this->addImgNode($xmlNode,array(
			'FOLDED'=>'true',
			'TEXT'=>$this->translate('tree.typo3logs'),
		), 'typo3/sysext/t3skin/icons/module_tools_log.gif', 'height="16"' );

		/*<LOGS successfull backend logins>*/
		$nodeHTML = array();
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( 'from_unixtime(tstamp,\'%Y-%m-%d %H:%i:%s\') as LoggedDate,log_data',
			'sys_log', 'error=0 AND type=255', '', 'tstamp DESC', (int)$this->settings['numberOfLogRows'] );
		while ($r = $GLOBALS['TYPO3_DB']->sql_fetch_assoc ($result)) {
			$nodeHTML[ $r['LoggedDate'] ] = implode(' / ',unserialize($r['log_data']));
		}

		$this->addRichContentNote($LogsNode, array('TEXT'=>$this->translate('tree.typo3.SuccessfullBackendLogins') ),
			'<h3>'.$this->translate('tree.typo3.SuccessfullBackendLogins').'</h3>'. $this->array2Html2ColTable($nodeHTML) );
		/*</LOGS successfull backend logins>*/


		/*<LOGS failed backend logins>*/
		$nodeHTML = array();
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( 'from_unixtime(tstamp,\'%Y-%m-%d %H:%i:%s\') as LoggedDate,log_data',
			'sys_log', 'error=3', '', 'tstamp DESC', (int)$this->settings['numberOfLogRows'] );
		while ($r = $GLOBALS['TYPO3_DB']->sql_fetch_assoc ($result)) {
			$nodeHTML[ $r['LoggedDate'] ] = implode(' / ',unserialize($r['log_data']));
		}
		$this->addRichContentNote($LogsNode, array('TEXT'=>$this->translate('tree.typo3.FailedBackendLogins')),
			'<h3>'.$this->translate('tree.typo3.FailedBackendLogins').'</h3>'. $this->array2Html2ColTable($nodeHTML) );
		/*</LOGS failed backend logins>*/


		/*<LOGS error logs>*/
		$DBresult = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( 'from_unixtime(tstamp,\'%Y-%m-%d %H:%i:%s\') as LoggedDate,details',
			'sys_log', 'error=1', '', 'tstamp DESC', (int)$this->settings['numberOfLogRows'] );
		$nodeHTML = array();
		while($r = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($DBresult) ){
			$nodeHTML[ $r['LoggedDate'] ] = strip_tags(str_replace('|',' ',$r['details']));
		}

		$this->addRichContentNote($LogsNode, array('TEXT'=>$this->translate('tree.typo3.ErrorLog')),
			'<h3>'.$this->translate('tree.typo3.ErrorLog').'</h3>'. $this->array2Html2ColTable($nodeHTML) );
		/*</LOGS error logs>*/

	}

	/**
	 * gets all backend users
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	SimpleXMLElement
	 */
	private function getTYPONodeBackendUsers(SimpleXMLElement $xmlNode) {

		// backend users groups

		$UsersNode = $this->addImgNode($xmlNode,array(
			'LINK'=>$this->mapMode['isbe'] ? $this->getBEHttpHost().'typo3/mod.php?M=tools_beuser' : '',
			'FOLDED'=>'true',
			'TEXT'=>$this->translate('tree.typo3users'),
		), 'typo3/sysext/t3skin/icons/gfx/i/be_users__x.gif', 'height="16"' );


		/*<show all admins>*/
		$UserAdminNode = $this->addNode($UsersNode,array(
			'FOLDED'=>'true',
			'TEXT'=>$this->translate('tree.typo3.useradmin'),
		));
		$this->addIcon($UserAdminNode,'penguin');

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( 'uid,username,password,email,realname,lastlogin,disable,deleted', 'be_users', 'admin=1', '', 'username'  );
		$i=0;
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc ($result)) {
			$this->BeUsersHandleRow($UserAdminNode,$row,$i);
			$i++;
		}
		/*</show all admins>*/



		/*<show all non admins>*/
		$UserUserNode = $this->addNode($UsersNode,array(
			'FOLDED'=>'true',
			'TEXT'=>$this->translate('tree.typo3.users'),
		));
		$this->addIcon($UserUserNode,'male1');


		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( 'uid,username,password,email,realname,lastlogin,disable,deleted,userMods', 'be_users', 'admin=0', '', 'username' /* , (int)$this->settings['numberOfLogRows'] */ );
		$i=0;
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc ($result)) {
			$this->BeUsersHandleRow($UserUserNode,$row,$i);
			$i++;
		}
		/*</show all non admins>*/


		/*<show all groups>*/
		$UserGroupNode = $this->addNode($UsersNode,array(
			'FOLDED'=>'true',
			'TEXT'=>$this->translate('tree.typo3.groups'),
		));
		$this->addIcon($UserGroupNode,'group');

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( 'uid,title,hidden,deleted,crdate,tables_select,tables_modify,groupMods', 'be_groups', '', '', 'title' /* , (int)$this->settings['numberOfLogRows'] */ );
		$i=0;
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc ($result)) {
			$this->BeGroupsHandleRow($UserGroupNode,$row,$i);
			$i++;
		}
		/*</show all groups>*/

	}/*</getTYPONodeBackendUsers>*/

	/**
	 * handles a row returned from mysql with the backend user values
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @param	array $row
	 * @param	integer $rowCounter
	 * @return	SimpleXMLElement
	 */
	private function BeUsersHandleRow(SimpleXMLElement $xmlNode,$row,$rowCounter){


		$BACKGROUND_COLOR = $this->getDesignAlternatingColor('BeUsersHandleRow',$rowCounter);
		$aUserNode = $this->addNode($xmlNode,array(
				// not possible due to the ampersand returnUrl=%2Ftypo3%2Fmod.php%3FM%3Dtools_beuser&
				'LINK'=>$this->mapMode['isbe'] ? $this->getBEHttpHost().'typo3/alt_doc.php?edit[be_users]['.$row['uid'].']=edit' : '',
				// 'FOLDED'=>'true',
				'TEXT'=>$row['username'],
				'BACKGROUND_COLOR'=> $BACKGROUND_COLOR,
			));
			$this->addEdge($aUserNode,array('WIDTH'=>$this->getDesignEdgeWidth('BeUsersHandleRow'),'COLOR'=>$BACKGROUND_COLOR));

			if( $row['deleted'] == 1 ) {	$this->addIcon($aUserNode,'button_cancel'); }
			elseif( $row['disable'] == 1 ) {	$this->addIcon($aUserNode,'encrypted'); }
			if( ($row['lastlogin']+(3600*24*9)) < time() ) {	$this->addIcon($aUserNode,'hourglass'); }

			if( !empty($row['realname']) ){
				$this->addNode($aUserNode,array('BACKGROUND_COLOR'=> $BACKGROUND_COLOR,'TEXT'=>$row['realname']));
			}
			if( !empty($row['email']) ){
				$this->addNode($aUserNode,array('BACKGROUND_COLOR'=> $BACKGROUND_COLOR,'TEXT'=>$row['email']));
			}
			if( !empty($row['password']) ){
					/*<check for unsecure installtool password!>*/
					// http://www.stottmeister.com/blog/2009/04/14/how-to-crack-md5-passwords/
					// http://netmd5crack.com/cgi-bin/Crack.py?InputHash=[md5string]
					$plainPassword = $this->getPlainTextPasswordFromMD5($row['password']);

					if( $plainPassword !== false ){
						$attrPW = array(
							'BACKGROUND_COLOR'=> $BACKGROUND_COLOR,'TEXT'=>'Decrypted your unsecure password: '.$plainPassword,
						);
						$attrPW['COLOR'] = '#D60035';
						$attrPW['LINK'] = 'http://www.tmto.org/pages/passwordtools/hashcracker/';
						$nodePW = $this->addNode($aUserNode,$attrPW);
						$this->addIcon($nodePW,'messagebox_warning');

					}

					/*</check for unsecure installtool password!>*/
			}
			if( isset($row['userMods']) && !empty($row['userMods']) ){
				$nodeUserMods = $this->addNode($aUserNode,array('BACKGROUND_COLOR'=> $BACKGROUND_COLOR,'TEXT'=>$this->translate('tree.typo3.groups.groupMods')));
				$this->BeUserGroupsGetModList($nodeUserMods,'modListUser',$row['userMods'],array('BACKGROUND_COLOR'=> $BACKGROUND_COLOR));
			}/*endif*/


			/*<LOGS user logs>*/
			$DBresult = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( 'from_unixtime(tstamp,\'%Y-%m-%d %H:%i:%s\') as LoggedDate,tablename,details,log_data,ip',
				'sys_log', 'userid='.$row['uid'].' and error=0', '', 'tstamp DESC', (int)$this->settings['numberOfLogRows'] );
			$nodeHTML = array();
			while($r = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($DBresult) ){
				if( !empty($r['log_data']) ){
					$data = unserialize($r['log_data']);

					$r['details'] = sprintf($r['details'], htmlspecialchars($data[0]), htmlspecialchars($data[1]), htmlspecialchars($data[2]), htmlspecialchars($data[3]), htmlspecialchars($data[4]));
				}
				unset($r['log_data']);
				if( empty($r['tablename']) ){ unset($r['tablename']); }
				$nodeHTML[ $r['LoggedDate'] ] = implode(' / ',($r));
			}

			if( count($nodeHTML) > 0 ){
				$this->addRichContentNote($aUserNode, array('BACKGROUND_COLOR'=> $BACKGROUND_COLOR,'TEXT'=>$this->translate('tree.typo3.SysLog')),
					'<h3>'.$this->translate('tree.typo3.SysLog').'</h3>'. $this->array2Html2ColTable($nodeHTML) );
			}
			/*</LOGS user logs>*/

	} /*</BeUsersHandleRow>*/

	/**
	 * handles a row returned from mysql with the backend groups values
	 * TODO:  list all settings for a group ... it seems complicated ...
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @param	array $row
	 * @param	integer $rowCounter
	 * @return	SimpleXMLElement
	 */
	private function BeGroupsHandleRow(SimpleXMLElement $xmlNode,$row,$rowCounter){

		$BACKGROUND_COLOR = $this->getDesignAlternatingColor('BeGroupsHandleRow',$rowCounter);
		$aGroupNode = $this->addNode($xmlNode,array(
			'BACKGROUND_COLOR'=>$BACKGROUND_COLOR,
			'TEXT'=>$row['title'],
			'LINK' => $this->mapMode['isbe'] ? $this->getBEHttpHost().'typo3/alt_doc.php?edit[be_groups]['.$row['uid'].']=edit' : ''
		));

		$this->addEdge($aGroupNode,array('WIDTH'=>$this->getDesignEdgeWidth('BeGroupsHandleRow'),'COLOR'=> $BACKGROUND_COLOR ));

			if( $row['deleted'] == 1 ) {	$this->addIcon($aGroupNode,'button_cancel'); }
			elseif( $row['hidden'] == 1 ) {	$this->addIcon($aGroupNode,'closed'); }

			if( !empty($row['groupMods']) ){
				$nodeGroupMods = $this->addNode($aGroupNode,array('BACKGROUND_COLOR'=>$BACKGROUND_COLOR,'TEXT'=>$this->translate('tree.typo3.groups.groupMods')));
				$this->BeUserGroupsGetModList($nodeGroupMods,'modListGroup',$row['groupMods'],array('BACKGROUND_COLOR'=>$BACKGROUND_COLOR));
			}/*endif*/

			$this->BeGroupsHandleTableSelectModify($aGroupNode,$row['tables_select'],'tree.typo3.groups.tables_select',array('BACKGROUND_COLOR'=>$BACKGROUND_COLOR));
			$this->BeGroupsHandleTableSelectModify($aGroupNode,$row['tables_modify'],'tree.typo3.groups.tables_modify',array('BACKGROUND_COLOR'=>$BACKGROUND_COLOR));

	} /*</BeGroupsHandleRow>*/


	/**
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @param	string $tables
	 * @param	string $translateKey
	 * @param	array $attr
	 * @return	SimpleXMLElement
	 */
	private function BeGroupsHandleTableSelectModify(SimpleXMLElement $xmlNode,$tables,$translateKey,$attr){
		GLOBAL $TCA;

		if( !empty($tables) ){
			$nodeTables = $this->addNode($xmlNode,array_merge($attr,array('TEXT'=>$this->translate($translateKey))));
			$exploded = t3lib_div::trimExplode(',', $tables ,1 );
			foreach($exploded as $k=>$table){
				$this->addNode($nodeTables,array_merge($attr,array('TEXT'=>$this->SYSLANG->sL( $TCA[$table]['ctrl']['title'] )) ));
			}
		}

	} /*</BeGroupsHandleTableSelectModify>*/


	/**
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @param	string $modListType
	 * @param	string $groupMods
	 * @param	array $attr
	 * @return	SimpleXMLElement
	 */
	private function BeUserGroupsGetModList(SimpleXMLElement $xmlNode,$modListType,$groupMods,$attr = array()){

			$loadModules = t3lib_div::makeInstance('t3lib_loadModules');
			$loadModules->load($GLOBALS['TBE_MODULES']);

			$modList = $modListType == 'modListUser' ? $loadModules->modListUser : $loadModules->modListGroup;

			$groupModsExploded = Tx_Typo3mind_Utility_Helpers::trimExplodeVK(',', $groupMods );

			if (is_array($modList)) {
				foreach ($modList as $theMod) {
					if( isset($groupModsExploded[$theMod]) ){
						/*	// Icon:	maybe one day ... we'll add an icon
						$icon = $GLOBALS['LANG']->moduleLabels['tabs_images'][$theMod . '_tab'];
						if ($icon) {
							$icon = '../' . substr($icon, strlen(PATH_site));
						} */

						$modLabel = t3lib_TCEforms::addSelectOptionsToItemArray_makeModuleData($theMod);
						$attri = array_merge(array('TEXT'=>$modLabel),$attr);
						$this->addNode($xmlNode,$attri);
					}/*endif isset $groupModsExploded*/
				}
			}/*endif is array*/
	}/*</BeUserGroupsGetModList>*/



	/**
	 * shows all TYPO3_CONF_VARS
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	SimpleXMLElement
	 */
	private function getTYPONodeConfVars(SimpleXMLElement $xmlNode) {

		$t3ConfVarNode = $this->addNode($xmlNode,array(
			'FOLDED'=>'true',
			'TEXT'=>$this->translate('tree.typo3.typo3_conf_vars'),
		));
		$tcv = $GLOBALS['TYPO3_CONF_VARS'];

		unset($tcv['SYS']['caching']);
		unset($tcv['SYS']['encryptionKey']);
		unset($tcv['SYS']['locallangXMLOverride']);

		unset($tcv['EXT']['extList']);
		unset($tcv['EXT']['extList']);
		unset($tcv['EXT']['extList_FE']);

		unset($tcv['BE']['AJAX']);
		unset($tcv['BE']['RTE_reg']);
		unset($tcv['SC_OPTIONS']);
		unset($tcv['EXT']['extConf']['typo3mind']);
		unset($tcv['typo3/backend.php']);
		unset($tcv['INSTALL']);


		foreach($tcv as $section=>$seccfg){
			foreach($seccfg as $k=>$v){
				if( stristr($k,'TypoScript')!==false || stristr($k,'TSconfig')!==false ){
					unset($tcv[$section][$k]);
				}
			}
		}

		$T3ConfCheck = new Tx_Typo3mind_Utility_T3ConfCheck();
		$commentArr = $T3ConfCheck->getDefaultConfigArrayComments();

		$seccfglistDetails = array(
			'extConf'=>1,
			'XCLASS'=>1,
			'XLLfile'=>1,
			'defaultPermissions'=>1,
			'defaultUC'=>1,
			'fileExtensions'=>1,
			'loginNews'=>1,
		);
		$installToolPlainPassword = false;
		foreach($tcv as $section=>$seccfg){
			$NodeSection = $this->addNode($t3ConfVarNode,array(
				'FOLDED'=>count($seccfg) > 0 ? 'true' : 'false',
				'TEXT'=>$section,
			));
			ksort($seccfg);
			foreach($seccfg as $confName=>$v){

				if( is_array($v) && count($v)>0 ){
					$attr = array(
						'TEXT'=>$confName,
						'FOLDED'=>'true',
					);
					$NodeSectionValue = $this->addNode($NodeSection,$attr);

					if( $confName == 'eID_include' ){
						$this->addIcon($NodeSectionValue,'messagebox_warning');
					}

					foreach($v as $extName=>$extConf){

						$extConf = (is_array($extConf) || stristr($extConf,'}') === false) ? $extConf : unserialize($extConf);

						$htmlContent = array();
						if( is_array($extConf) ){
							$htmlContent[] = '<pre>'.htmlspecialchars(var_export($extConf,1)).'</pre>';
						}else{
							$htmlContent[] = htmlspecialchars($extConf);
						}
						$NodeExtName = $this->addRichContentNote($NodeSectionValue,array(
						//	'FOLDED'=>count($seccfg) > 0 ? 'true' : 'false',
							'TEXT'=>$extName,
							'LINK'=> $confName=='extConf' ? $this->getTerURL($extName) : '',
						),implode("<br/>\n",$htmlContent));



					} /*endforeach*/

				}else{
					$htmlContent = array();
					$attr = array(
						'TEXT'=>'['.$confName.'] = '.htmlspecialchars($v),
					);

					/*<check for unsecure installtool password!>*/
					if( $confName=='installToolPassword' ){
						$installToolPlainPassword = $this->getPlainTextPasswordFromMD5($v);
						if( $installToolPlainPassword !== false ){

							$attr['COLOR'] = '#D60035';
							$attr['LINK'] = 'http://www.tmto.org/pages/passwordtools/hashcracker/';
							$htmlContent[] = '<h3>Decrypted your unsecure password: <i>'.$installToolPlainPassword.'</i></h3>';
						}
					}
					/*</check for unsecure installtool password!>*/

					$htmlContent[] = '<b>'.$this->translate('tree.typo3.typo3_conf_vars.value').': <i>'.$v.'</i></b>';
					if( isset($commentArr[1][$section]) && isset($commentArr[1][$section][$confName]) ){
						$htmlContent[] = strip_tags($commentArr[1][$section][$confName],'<a>,<dl>,<dt>,<dd>');
					}
					/*bug in typo3 default config file, improper closed html @see getDefaultConfigArrayComments */
					$htmlContent = str_replace('<dd><dt>','</dd><dt>',implode('<br/>',$htmlContent));

					$NodeSectionValue = $this->addRichContentNote($NodeSection,$attr,$htmlContent /*,$addEdgeAttr = array(),$addFontAttr = array(), $type = 'NOTE' */ );

					if( $installToolPlainPassword !== false ){
							$this->addFont($NodeSectionValue,array('SIZE'=>14,'BOLD'=>'true','COLOR'=>'#fff'));
							$this->addIcon($NodeSectionValue,'messagebox_warning');
							$installToolPlainPassword = false;
					}


				}/*endelse default*/

			}/*endforeach*/
		}/*endforeach*/

	}/*</getTYPONodeConfVars>*/

	/**
	 * checks the directories
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	SimpleXMLElement
	 */
	private function getTYPONodeCheckDirs(SimpleXMLElement $xmlNode) {

		$checkDirNode = $this->addNode($xmlNode,array(
			'FOLDED'=>'true',
			'TEXT'=>$this->translate('tree.typo3.checkDirs'),
		));

		$T3ConfCheck = new Tx_Typo3mind_Utility_T3ConfCheck();
		$T3ConfCheck->checkDirs();

		foreach($T3ConfCheck->messages as $k=>$message){

			$attr = array(
					'TEXT'=>htmlspecialchars($message['short']),
					'BACKGROUND_COLOR'=>$this->getDesignAlternatingColor('getTYPONodeCheckDirs',$k),
				);

			if( !empty($message['long']) ){
				$htmlContent = $message['long'];
				$messageNode = $this->addRichContentNote($checkDirNode,$attr,$htmlContent /*,$addEdgeAttr = array(),$addFontAttr = array(), $type = 'NOTE' */ );

			}else{
				$messageNode = $this->addNode($checkDirNode,$attr);
			}
			$this->addIcon($messageNode,$message['icon']);
		}/*endforeach*/

	}/*</getTYPONodeCheckDirs>*/

	/**
	 * gets some T3 specific informations
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	SimpleXMLElement
	 */
	public function getTYPONode(SimpleXMLElement $xmlNode) {

		$MainNode = $this->addImgNode($xmlNode,array(
			'POSITION'=>'left',
	//		'FOLDED'=>'true',
			'TEXT'=>$this->translate('tree.typo3'),
		), 'typo3/sysext/t3skin/images/icons/apps/pagetree-root.png', 'height="16"' );



		$this->getTYPONodeFiles($MainNode);

		$this->getTYPONodeLogs($MainNode);


		$this->getTYPONodeBackendUsers($MainNode);

		$this->getTYPONodeConfVars($MainNode);

		$this->getTYPONodeCheckDirs($MainNode);




	}/*endmethod*/

	
	/**
	 * gets secutiry node
	 * http://www.iconarchive.com/show/refresh-cl-icons-by-tpdkdesign.net/System-Security-Warning-icon.html
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	SimpleXMLElement
	 */
	public function getSecurityNode(SimpleXMLElement $xmlNode) {
	
		$secMainNode = $this->addImgNode($xmlNode,array(
			'POSITION'=>'left',
			// 'FOLDED'=>'true',
			'TEXT'=>$this->translate('tree.security'),
		), 'typo3conf/ext/typo3mind/Resources/Public/Icons/System-Security-Warning-icon.png', 'height="16"' );

		$this->addNode($secMainNode,array('TEXT'=>'TYPO3 Security Guide ','LINK'=>'http://typo3.org/documentation/document-library/extension-manuals/doc_guide_security/current/'));

		
		$rss = 'http://news.typo3.org/news/teams/security/rss.xml';
		// caching and a global add rss feed class
		$rssContent = simplexml_load_string(t3lib_div::getURL($rss));
	
		$rssHeadNode = $this->addNode($secMainNode,array('TEXT'=>$rssContent->channel->title,'LINK'=>$rssContent->channel->link));
		
// echo '<pre>';  var_dump($rssContent->channel->item); exit;
		
		foreach($rssContent->channel->item as $index=>$item){
		
			$htmlContent = array();
			$htmlContent[] = '<p>'.$item->author.'</p>';
			$htmlContent[] = '<p>'.$item->pubDate.'</p>';
			$htmlContent[] = '<p>'.$item->description.'</p>';

			$rssItemNode = $this->addRichContentNote($rssHeadNode,array('TEXT'=>$item->title,'LINK'=>$item->link),implode('',$htmlContent));
		
		}/*endforeach*/
	
	
	
		$this->addNode($secMainNode,array('TEXT'=>'more to follow','LINK'=>'https://github.com/SchumacherFM/TYPO3Mind/issues/12'));
	
	}/*</getSecurityNode>*/
	
	
	/**
	 * gets some server informations
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	SimpleXMLElement
	 */
	public function getServerNode(SimpleXMLElement $xmlNode) {

		$MainNode = $this->addImgNode($xmlNode,array(
			'POSITION'=>'left',
			'FOLDED'=>'true',
			'TEXT'=>$this->translate('tree.server'),
		), 'typo3/sysext/t3skin/images/icons/apps/filetree-root.png', 'height="16"' );


		$_SERVER['PHP_VERSION'] = phpversion();
		$this->addRichContentNote($MainNode, array('TEXT'=>$this->translate('tree.server._server')),  $this->array2Html2ColTable($_SERVER) );

		$this->addRichContentNote($MainNode, array('TEXT'=>$this->translate('tree.server._env')),  $this->array2Html2ColTable($_ENV) );

		/* nice, but needs deep reformatting ...
		ob_start() ;
		phpinfo(INFO_MODULES) ;
		$pinfo = ob_get_contents () ;
		ob_end_clean () ;
		$this->addRichContentNote($MainNode, array('TEXT'=>$this->translate('tree.server.phpModules')),  $pinfo );
		*/

	}
	/**
	 * gets some database informations
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	SimpleXMLElement
	 */
	public function getDatabaseNode(SimpleXMLElement $xmlNode) {

		$MainNode = $this->addImgNode($xmlNode,array(
			'POSITION'=>'left',
			'FOLDED'=>'true',
			'TEXT'=>$this->translate('tree.database'),
		), 'typo3/sysext/t3skin/icons/module_tools_dbint.gif', 'height="16"' );

		// general mysql infos
		$mysqlNode = $this->addNode($MainNode,array(
			'FOLDED'=>'true',
			'TEXT'=>$this->translate('tree.mysql'),
		));

		$nodeHTML = array();
		$DBresult = $GLOBALS['TYPO3_DB']->sql_query('SHOW VARIABLES LIKE  \'version%\'');
		while($r = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($DBresult) ){
			$nodeHTML[$r['Variable_name']] = $r['Value'];
		}

		$this->addRichContentNode($mysqlNode, array(),$this->array2Html2ColTable($nodeHTML) );



		$agt = $GLOBALS['TYPO3_DB']->admin_get_tables();

		$groupedTables = array();
		foreach ($agt as $table => $tinfo){
			$te = explode('_',$table);
			$groupedTables[ $te[0] ][$table] = $tinfo;
		}
		unset($agt);
		ksort($groupedTables);

		$i=0;
		foreach ($groupedTables as $group => $tables){

			$tGroup = $this->translate('tree.database.'.$group);
			$BACKGROUND_COLOR = $this->getDesignAlternatingColor('getDatabaseNode',$i);

			$GroupTableNode = $this->addNode($MainNode,array(
				'FOLDED'=>'true',
				'BACKGROUND_COLOR'=>$BACKGROUND_COLOR,
				'TEXT'=> $tGroup == '' ? $group : $tGroup,
			));
			$this->addEdge($GroupTableNode,array('WIDTH'=>$this->getDesignEdgeWidth('getDatabaseNode'),'COLOR'=>$BACKGROUND_COLOR));

			foreach ($tables as $tkey => $tinfo){

				$size = sprintf('%.2f',($tinfo['Data_length']+$tinfo['Index_length'])/1024);

				$ATableNode = $this->addNode($GroupTableNode,array(
				//	'FOLDED'=>'true',
					'BACKGROUND_COLOR'=>$BACKGROUND_COLOR,
					'TEXT'=>$tkey.' ('.$tinfo['Rows'].') '.$size.' KB',
				));

				/*
				$ATableNode = $this->addNode($GroupTableNode,array(
					'FOLDED'=>'true',
					'TEXT'=>$tkey,
				));

				$nodeHTML = array('<table border="0" cellpadding="3" cellspacing="0">');
				// $nodeHTML[] = '<tr><th colspan="2">'.$tkey.'</th></tr>';
				$nodeHTML[] = '<tr><td>Rows</td><td style="text-align: right">'.$tinfo['Rows'].'</td></tr>';
				$nodeHTML[] = '<tr><td>Data</td><td style="text-align: right">'.sprintf('%.2f',$tinfo['Data_length']/1024).'KiB</td></tr>';
				$nodeHTML[] = '<tr><td>Index</td><td style="text-align: right">'.sprintf('%.2f',$tinfo['Index_length']/1024).'KiB</td></tr>';
				if( $tinfo['Data_free']>0 ){ $nodeHTML[] = '<tr><td>Overhead</td><td style="text-align: right">'.sprintf('%.2f',$tinfo['Data_free']/1024).'KiB</td></tr>'; }
				$nodeHTML[] = '</table>';
				$tinfoNode = $this->addRichContentNode($ATableNode, array(),implode('',$nodeHTML) );
				*/
			}
		$i++;
		}/*endforeach*/


		return $MainNode;
	}

	/**
	 * gets the extension nodes
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	SimpleXMLElement
	 */
	public function getExtensionNode(SimpleXMLElement $xmlNode) {
		global $TCA;

		$extensionManager = new Tx_Typo3mind_Utility_ExtensionManager();


		$ChildFirst_Extensions = $this->addImgNode($xmlNode,array(
			'POSITION'=>'left',
			'TEXT'=>$this->translate('tree.extensions'),
		), 'typo3/sysext/t3skin/icons/module_tools_em.png' );


		/*<frontend plugins which you can choose in the backend>*/
		$selectableExtensions = $this->addNode($ChildFirst_Extensions,array(
			'TEXT'=>$this->translate('tree.extensions.selectable'),
			'FOLDED'=>'true',
		));

		foreach( $TCA['tt_content']['columns']['list_type']['config']['items'] as $ei=>$extA ){
			$extA[0] = $this->SYSLANG->sL($extA[0]);
			if( !empty($extA[0]) ){
				$this->addImgNode($selectableExtensions,array(
					'TEXT'=> '('.$extA[1].') '.$extA[0],
				),$extA[2] );
			}
		}/*endforeach*/
		/*</frontend plugins which you can choose in the backend>*/




		/*<check for extension updates!>*/
		$updateExtensions = $this->addNode($ChildFirst_Extensions,array(
			'TEXT'=>$this->translate('tree.extensions.updates'),
			'FOLDED'=>'true',
		));
				if (is_file(PATH_site . 'typo3temp/extensions.xml.gz')) {
					$dateFormat = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'];
					$timeFormat = $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'];
					$content = $this->translate('tree.extensions.updates.last').
						date(
							$dateFormat . ', ' . $timeFormat,
							filemtime(PATH_site . 'typo3temp/extensions.xml.gz')
						).'<br/>'.$this->translate('tree.extensions.updates.number').
						tx_em_Database::getExtensionCountFromRepository();
					$this->addRichContentNode($updateExtensions,array(), $content );
				}
			$showExtensionsToUpdate = $extensionManager->showExtensionsToUpdate();

			foreach($showExtensionsToUpdate as $extName=>$extData){
						// ext icon
						$extIcon = $this->getBEHttpHost().str_replace('../','',$extData['icon']);

						$htmlContent = array(
							'NODE' => '<img src="'.$extIcon.'"/>@#160;@#160;'.$extData['nicename'],
							'NOTE'=> '<p>Version local: '.$extData['version_local'].'</p>'.
								'<p>Version remote: '.$extData['version_remote'].'</p>'.
								'<p>'.htmlspecialchars($extData['comment']).'</p>',
						);
						$extRCNode = $this->addRichContentNote($updateExtensions,array(), $htmlContent,array(),array(), 'BOTH' );

			}/*endforeach*/
		/*</check for extension updates!>*/




		$installedExt = $extensionManager->getInstalledExtensions();
		/* extension by modul state */

		/* rebuilding the array by cat->state->name */
		$installExt2 = array();
		foreach( $installedExt[1]['cat'] as $catName => $extArray ){
			ksort($extArray);
			foreach( $extArray as $extKey=>$extNiceName ){
				foreach($this->states as $statek=>$stateName){

					if( $installedExt[0][ $extKey ]['EM_CONF']['state'] == $statek) {
						$installExt2[$catName][$statek][$extKey] = $installedExt[0][ $extKey ];
					}
				}
			}
		}
		$installedExt = $installExt2;


		/* extension by category = normal view */
		foreach( $installedExt as $catName => $catArray ){

			$catNode = $this->addNode($ChildFirst_Extensions, array(
				'FOLDED'=>'true',
				'TEXT'=>$this->categories[$catName],
			) );

			foreach($catArray as $statek=>$stateArray){


					$attr = array(
						'FOLDED'=>'true',
						'TEXT'=>$this->states[$statek],
						'BACKGROUND_COLOR' => $this->stateColors[ $statek ],
						'COLOR' => '#ffffff'
					);
					$aStateNode = $this->addNode($catNode,$attr);
					$this->addFont($aStateNode,array('BOLD'=>'true','SIZE'=>14));

					$this->addCloud($aStateNode,array('COLOR'=>$this->stateColors[ $statek ]));

					$extI = 0;
					foreach($stateArray as $extKey=>$extArray ){

						switch($extArray['type']){
							case 'S':
								$preURI = TYPO3_mainDir.'sysext/';
								$addTERLink = 0;
							break;
							case 'G':
								$preURI = TYPO3_mainDir.'ext/';
								$addTERLink = 1;
							break;
							case 'L':
								$preURI = 'typo3conf/ext/';
								$addTERLink = 1;
							break;
						}

						// ext icon
						$extIcon = $preURI . $extKey . '/ext_icon.gif';

						$extNode = $this->addImgNode($aStateNode,array(
							'FOLDED'=>'true',
							'TEXT'=> $extArray['EM_CONF']['title'],
						), $extIcon );

						$color = $this->getDesignAlternatingColor('getExtensionNode',$extI,'CLOUD_COLOR');
						$this->addCloud($extNode,array('COLOR'=>$color ));


						// installed or not icon
						$icon = $extArray['installed'] ? 'button_ok' : 'button_cancel';
						$this->addIcon($extNode,$icon);

						// node for system global or local ext
						$this->addNode($extNode, array(
							// 'FOLDED'=>'true',
							'TEXT'=>$this->types[ $extArray['type'] ],
						) );

						// link to TER
						if( $addTERLink == 1 ){
							$this->addNode($extNode, array(
								// 'FOLDED'=>'true',
								'TEXT'=>$this->translate('tree.linkName2TER'),
								'LINK'=>$this->getTerURL($extKey),
							) );
							// $this->settings['mapMode'] maybe ... if frontend then no TER link ...
						}


						// displaying the rest of the config
						$constraints = $extensionManager->humanizeConstraints($extArray['EM_CONF']['constraints']);
						$extArray['EM_CONF']['depends'] = $constraints['depends'];
						$extArray['EM_CONF']['conflicts'] = $constraints['conflicts'];
						$extArray['EM_CONF']['suggests'] = $constraints['suggests'];
						unset($extArray['EM_CONF']['title']);
						unset($extArray['EM_CONF']['constraints']);
						unset($extArray['EM_CONF']['category']);
						unset($extArray['EM_CONF']['_md5_values_when_last_written']);


						foreach($extArray['EM_CONF'] as $ek=>$ev){
							if( !empty($ev) ){

								$attr = array(
									'TEXT'=>ucfirst($ek).': '.$ev,
								);

								if( $ek == 'state' ){
									$attr['BACKGROUND_COLOR'] = $this->stateColors[ $ev ];
									$attr['COLOR'] = '#ffffff';
									$attr['TEXT']  = $this->states[$ev];
								}

								$this->addNode($extNode, $attr );
							}
						}
						$extI++;
					} /*endforeach $installedExt[1]['state'][$statek]*/


			}/*endforeach $this->states*/
		}/*endforeach $installedExt[1]['cat']*/
		return $ChildFirst_Extensions;
	}




}
/*
				$size = xmlentities($size);
				$size = str_replace('&#','@#',$size);
*/
	 function xmlentities( $string ) {
		$not_in_list = '';
		return preg_replace_callback( '/[^A-Z0-9a-z_-]/' , 'get_xml_entity_at_index_0' , $string );
	}
	 function get_xml_entity_at_index_0( $CHAR ) {
		if( !is_string( $CHAR[0] ) || ( strlen( $CHAR[0] ) > 1 ) ) {
			die( "function: 'get_xml_entity_at_index_0' requires data type: 'char' (single character). '{$CHAR[0]}' does not match this type." );
		}
		switch( $CHAR[0] ) {
			case '\'':	case '"':	case '&':	case '<':	case '>':
				return htmlspecialchars( $CHAR[0], ENT_QUOTES );	break;
			default:
				return numeric_entity_4_char($CHAR[0]);				break;
		}
	}
	 function numeric_entity_4_char( $char ) {
		return '@#'.str_pad(ord($char), 3, '0', STR_PAD_LEFT).';';
	}
