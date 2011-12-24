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
	 * initializeAction
	 *
	 * @return void
	 */
	public function __construct($settings) {
		parent::__construct($settings);

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
			'TEXT'=>'fileadmin'
		));

		$scandirLevel1 = PATH_site.'fileadmin/';
		$dirLevel1 = scandir($scandirLevel1);
		foreach($dirLevel1 as $k=>$vL1){

			/* is dir and avoid .svn or .git or ... folders file starting with a . */
			if( is_dir($scandirLevel1.$vL1) && preg_match('~^\..*~',$vL1)==false ){

				$faLevel1 = $this->addNode($nodeFileadmin,array(
					'TEXT'=>$vL1
				));

				$dirLevel2 = scandir($scandirLevel1.$vL1);
				foreach($dirLevel2 as $k2=>$vL2){
					$Level2Dir = $scandirLevel1.$vL1.'/'.$vL2;
					/* is dir and avoid .svn or .git or ... folders file starting with a . */
					if( is_dir($Level2Dir) && preg_match('~^\..*~',$vL2)==false ){

						$size = $this->formatBytes( $this->getDirSize($Level2Dir) );

						$faLevel2 = $this->addNode($faLevel1,array(
							'TEXT'=>xmlentities($vL2.' '.$size)
						));
					}
				}/*endforeach*/


			}
		} /*endforeach*/
	}

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
			'FOLDED'=>'true',
			'TEXT'=>$this->translate('tree.typo3users'),
		), 'typo3/sysext/t3skin/icons/gfx/i/be_users__x.gif', 'height="16"' );


		/*<show all admins>*/
		$UserAdminNode = $this->addNode($UsersNode,array(
			'FOLDED'=>'true',
			'TEXT'=>$this->translate('tree.typo3.useradmin'),
		));
		$this->addIcon($UserAdminNode,'penguin');

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( 'username,email,realname,lastlogin,disable,deleted', 'be_users', 'admin=1', '', 'username', '10' );
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc ($result)) {
			$this->BeUsersHandleRow($UserAdminNode,$row);
		}
		/*</show all admins>*/

		/*<show all non admins>*/
		$UserUserNode = $this->addNode($UsersNode,array(
			'FOLDED'=>'true',
			'TEXT'=>$this->translate('tree.typo3.users'),
		));
		$this->addIcon($UserUserNode,'male1');

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( 'username,email,realname,lastlogin,disable,deleted', 'be_users', 'admin=0', '', 'username', (int)$this->settings['numberOfLogRows'] );
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc ($result)) {
			$this->BeUsersHandleRow($UserUserNode,$row);
		}
		/*</show all non admins>*/


		/*<show all groups>*/
		$UserGroupNode = $this->addNode($UsersNode,array(
			'FOLDED'=>'true',
			'TEXT'=>$this->translate('tree.typo3.groups'),
		));
		$this->addIcon($UserGroupNode,'group');

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( 'title,hidden,deleted,crdate,tables_select,tables_modify', 'be_groups', '', '', 'title', (int)$this->settings['numberOfLogRows'] );
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc ($result)) {
			$this->BeGroupsHandleRow($UserGroupNode,$row);
		}
		/*</show all groups>*/

	}/*</getTYPONodeBackendUsers>*/

	/**
	 * handles a row returned from mysql with the backend user values
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @param	array $row
	 * @return	SimpleXMLElement
	 */
	private function BeUsersHandleRow(SimpleXMLElement $xmlNode,$row){

		// TODO: test user passwords, IF extension salted passwords is not loaded!
		// http://www.stottmeister.com/blog/2009/04/14/how-to-crack-md5-passwords/
		// http://netmd5crack.com/cgi-bin/Crack.py?InputHash=[md5string]
	
		$aUserNode = $this->addNode($xmlNode,array(
				'FOLDED'=>'true',
				'TEXT'=>$row['username']
			));
			if( $row['deleted'] == 1 ) {	$this->addIcon($aUserNode,'button_cancel'); }
			elseif( $row['disable'] == 1 ) {	$this->addIcon($aUserNode,'encrypted'); }
			if( ($row['lastlogin']+(3600*24*9)) < time() ) {	$this->addIcon($aUserNode,'hourglass'); }

			if( !empty($row['realname']) ){ $this->addNode($aUserNode,array('TEXT'=>$row['realname'])); }
			if( !empty($row['email']) ){ $this->addNode($aUserNode,array('TEXT'=>$row['email']));	}

			$this->addNode($aUserNode,array('TEXT'=>'ToDO: Idea Show SysLog last 10 enries...'));

	} /*</BeUsersHandleRow>*/

	/**
	 * handles a row returned from mysql with the backend groups values
	 * TODO:  list all settings for a group ... it seems complicated ...
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @param	array $row
	 * @return	SimpleXMLElement
	 */
	private function BeGroupsHandleRow(SimpleXMLElement $xmlNode,$row){
		
		$aUserNode = $this->addNode($xmlNode,array(
			//	'FOLDED'=>'true',
				'TEXT'=>$row['title']
			));
			if( $row['deleted'] == 1 ) {	$this->addIcon($aUserNode,'button_cancel'); }
			elseif( $row['hidden'] == 1 ) {	$this->addIcon($aUserNode,'closed'); }
			/* if( ($row['lastlogin']+(3600*24*9)) < time() ) {	$this->addIcon($aUserNode,'hourglass'); } */

			$this->BeGroupsHandleTableSelMod($aUserNode,$row['tables_select'],'tree.typo3.groups.tables_select');
			$this->BeGroupsHandleTableSelMod($aUserNode,$row['tables_modify'],'tree.typo3.groups.tables_modify');

	} /*</BeGroupsHandleRow>*/

	
	/**
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @param	string $tables
	 * @param	string $translateKey
	 * @return	SimpleXMLElement
	 */
	private function BeGroupsHandleTableSelMod(SimpleXMLElement $xmlNode,$tables,$translateKey){
		GLOBAL $TCA;

		if( !empty($tables) ){ 
			$nodeTables = $this->addNode($xmlNode,array('TEXT'=>$this->translate($translateKey)));	
			$exploded = t3lib_div::trimExplode(',', $tables ,1 );
			foreach($exploded as $k=>$table){
				$this->addNode($nodeTables,array('TEXT'=>$this->SYSLANG->sL( $TCA[$table]['ctrl']['title'] ) ));	
			}
//  echo '<pre>';	var_dump($exploded); exit;
		}

	} /*</BeGroupsHandleRow>*/
	
	/**
	 * shows all TYPO3_CONF_VARS
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	SimpleXMLElement
	 */
	private function getTYPONodeConfVars(SimpleXMLElement $xmlNode) {

		$t3ConfVarNode = $this->addNode($xmlNode,array(
			'FOLDED'=>'false',
			'TEXT'=>'TYPO3_CONF_VARS', // $this->translate('tree.typo3.typo3_conf_vars'),
		));

		

	}/*</getTYPONodeConfVars>*/
	
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

	}/*endmethod*/


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
//	echo '<pre>';   var_dump( $groupedTables ); exit;

		foreach ($groupedTables as $group => $tables){

			$tGroup = $this->translate('tree.database.'.$group);

			$GroupTableNode = $this->addNode($MainNode,array(
				'FOLDED'=>'true',
				'TEXT'=> $tGroup == '' ? $group : $tGroup,
			));

			foreach ($tables as $tkey => $tinfo){

				if( !empty($tinfo['Rows']) ){
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
				}else{
					$ATableNode = $this->addNode($GroupTableNode,array(
						'TEXT'=>$tkey,
					));

				}
			}

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

/*		$ChildFirst_Extensions = $this->addNode($xmlNode,array(
			'POSITION'=>'left',
			'TEXT'=>$this->translate('tree.extensions'),
		)); */

		$ChildFirst_Extensions = $this->addImgNode($xmlNode,array(
			'POSITION'=>'left',
			'TEXT'=>$this->translate('tree.extensions'),
		), 'typo3/sysext/t3skin/icons/module_tools_em.png' );


		/* frontend plugins which you can choose in the backend */
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




		$installedExt = $this->getInstalledExtensions();
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


		// echo '<pre>';   var_dump( $installedExt ); exit;

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

						$color = $extI%2==0 ? '#ececec' : '#ffffff';
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
								'LINK'=>'http://typo3.org/extensions/repository/view/'.$extKey.'/current/',
							) );
							// $this->settings['mapMode'] maybe ... if frontend then no TER link ...
						}


						// displaying the rest of the config
						$constraints = $this->humanizeConstraints($extArray['EM_CONF']['constraints']);
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

/* **************************************************************
*  Copyright notice
*
*  (c) webservices.nl
*  (c) 2006-2010 Karsten Dambekalns <karsten@typo3.org>
*  All rights reserved
*
* **************************************************************/


	/**
	 * Returns the list of available (installed) extensions
	 *
	 * @param	boolean		if set, function will return a flat list only
	 * @return	array		Array with two arrays, list array (all extensions with info) and category index
	 * @see getInstExtList()
	 */
	function getInstalledExtensions( ) {
		$list = array();

		$cat = tx_em_Tools::getDefaultCategory();

		$path = PATH_typo3 . 'sysext/';
		$this->getInstExtList($path, $list, $cat, 'S');

		$path = PATH_typo3 . 'ext/';
		$this->getInstExtList($path, $list, $cat, 'G');

		$path = PATH_typo3conf . 'ext/';
		$this->getInstExtList($path, $list, $cat, 'L');

		return array($list, $cat);
	}

	/**
	 * Gathers all extensions in $path
	 *
	 * @param	string		Absolute path to local, global or system extensions
	 * @param	array		Array with information for each extension key found. Notice: passed by reference
	 * @param	array		Categories index: Contains extension titles grouped by various criteria.
	 * @param	string		Path-type: L, G or S
	 * @return	void		'Returns' content by reference
	 * @see getInstalledExtensions()
	 */
	function getInstExtList($path, &$list, &$cat, $type) {

		if (@is_dir($path)) {
			$extList = t3lib_div::get_dirs($path);
			if (is_array($extList)) {
				foreach ($extList as $extKey) {
					if (@is_file($path . $extKey . '/ext_emconf.php')) {
						$emConf = tx_em_Tools::includeEMCONF($path . $extKey . '/ext_emconf.php', $extKey);
						if (is_array($emConf)) {
							if (is_array($list[$extKey])) {
								$list[$extKey] = array('doubleInstall' => $list[$extKey]['doubleInstall']);
							}
							$list[$extKey]['extkey'] = $extKey;
							$list[$extKey]['doubleInstall'] .= $type;
							$list[$extKey]['type'] = $type;
							$list[$extKey]['installed'] = t3lib_extMgm::isLoaded($extKey);
							$list[$extKey]['EM_CONF'] = $emConf;
							$list[$extKey]['files'] = t3lib_div::getFilesInDir($path . $extKey, '', 0, '', $this->excludeForPackaging);

							tx_em_Tools::setCat($cat, $list[$extKey], $extKey);
						}
					}
				}
			}
		}
	}


	/**
	 * Make constraints readable
	 *
	 * @param  array $constraints
	 * @return array
	 */
	public function humanizeConstraints($constraints) {
		$depends = $conflicts = $suggests = array();
		$result = array(
			'depends' => '',
			'conflicts' => '',
			'suggests' => ''
		);

		if (is_array($constraints) && count($constraints)) {
			if (is_array($constraints['depends']) && count($constraints['depends'])) {
				foreach ($constraints['depends'] as $key => $value) {
					if ($value) {
						$tmp = t3lib_div::trimExplode('-', $value, TRUE);
						if (trim($tmp[1]) && trim($tmp[1]) !== '0.0.0') {
							$value = $tmp[0] . ' - ' . $tmp[1];
						} else {
							$value = $tmp[0];
						}
					}
					$depends[] = $key . ($value ? ' (' . $value . ')' : '');
				}
			}
			if (is_array($constraints['conflicts']) && count($constraints['conflicts'])) {
				foreach ($constraints['conflicts'] as $key => $value) {
					if ($value) {
						$tmp = t3lib_div::trimExplode('-', $value, TRUE);
						if (trim($tmp[1]) && trim($tmp[1]) !== '0.0.0') {
							$value = $tmp[0] . ' - ' . $tmp[1];
						} else {
							$value = $tmp[0];
						}
					}
					$conflicts[] = $key . ($value ? ' (' . $value . ')' : '');
				}
			}
			if (is_array($constraints['suggests']) && count($constraints['suggests'])) {
				foreach ($constraints['suggests'] as $key => $value) {
					if ($value) {
						$tmp = t3lib_div::trimExplode('-', $value, TRUE);
						if (trim($tmp[1]) && trim($tmp[1]) !== '0.0.0') {
							$value = $tmp[0] . ' - ' . $tmp[1];
						} else {
							$value = $tmp[0];
						}
					}
					$suggests[] = $key . ($value ? ' (' . $value . ')' : '');
				}
			}
			if (count($depends)) {
				$result['depends'] = htmlspecialchars(implode(', ', $depends));
			}
			if (count($conflicts)) {
				$result['conflicts'] = htmlspecialchars(implode(', ', $conflicts));
			}
			if (count($suggests)) {
				$result['suggests'] = htmlspecialchars(implode(', ', $suggests));
			}
		}
		return $result;
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
