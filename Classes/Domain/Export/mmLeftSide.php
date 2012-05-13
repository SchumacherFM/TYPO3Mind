<?php
/* * *************************************************************
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
 * ************************************************************* */

/**
 * @package typo3mind
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Tx_Typo3mind_Domain_Export_mmLeftSide extends Tx_Typo3mind_Domain_Export_mmCommon
{

	/**
	 * @var SimpleXMLElement
	 */
	protected $xmlParentNode;

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
	 * @var t3lib_loadModules
	 */
	private $_loadModules;

	/**
	 * @var t3lib_TCEforms
	 */
	private $_TCEforms;

	/**
	 * Just to set an flag IF an extension update is available
	 * @var array
	 */
	private $_isExtUpdateAvailable = array();

	/**
	 * __constructor
	 *
	 * @param array $settings
	 * @param Tx_Typo3mind_Domain_Repository_T3mindRepository $t3MindRepository
	 * @return void
	 */
	public function __construct(array $settings, Tx_Typo3mind_Domain_Repository_T3mindRepository $t3MindRepository)
	{
		parent::__construct($settings, $t3MindRepository);

		$this->_loadModules = t3lib_div::makeInstance('t3lib_loadModules');
		$this->_loadModules->load($GLOBALS['TBE_MODULES']);
		$this->_TCEforms = t3lib_div::makeInstance('t3lib_TCEforms');

		$this->categories = array(
			'be' => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_BE'),
			'module' => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_BE_modules'),
			'fe' => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_FE'),
			'plugin' => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_FE_plugins'),
			'misc' => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_miscellanous'),
			'services' => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_services'),
			'templates' => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_templates'),
			'example' => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_examples'),
			'doc' => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_documentation'),
			'' => 'none'
		);
		$this->types = array(
			'S' => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:type_system'),
			'G' => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:type_global'),
			'L' => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:type_local'),
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
	private function _getTYPONodeFiles(SimpleXMLElement $xmlNode)
	{

		$MainNode = $this->mmFormat->addImgNode($xmlNode, array(
			'FOLDED' => 'true',
			'TEXT' => $this->translate('tree.typo3filesandfolders'),
				), 'typo3/sysext/t3skin/images/icons/apps/pagetree-folder-default.png', 'height="16"');

		$nodeFileadmin = $this->mmFormat->addNode($MainNode, array(
			'TEXT' => 'fileadmin',
			'FOLDED' => 'true',
				));
		$this->_getTYPONodeFilesScandir($nodeFileadmin, PATH_site . 'fileadmin/');



		$nodeUpload = $this->mmFormat->addNode($MainNode, array(
			'TEXT' => 'uploads',
			'FOLDED' => 'true',
				));
		$this->_getTYPONodeFilesScandir($nodeUpload, PATH_site . 'uploads/');
	} /* </_getTYPONodeFiles> */

	/**
	 * scans the dir ;-)
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @param	string $fullPath
	 * @return	void
	 */
	private function _getTYPONodeFilesScandir(SimpleXMLElement $xmlNode, $fullPath)
	{

		$dirLevel1 = scandir($fullPath);
		foreach ($dirLevel1 as $k => $vL1) {

			$BACKGROUND_COLOR = $this->getDesignAlternatingColor('getTYPONodeFiles', $k);

			/* is dir and avoid .svn or .git or ... folders file starting with a . */
			if (is_dir($fullPath . $vL1) && preg_match('~^\..*~', $vL1) == false) {

				$size = $this->formatBytes($this->getDirSize($fullPath . $vL1));

				$faLevel1 = $this->mmFormat->addNode($xmlNode, array(
					'TEXT' => $vL1 . ' ' . $size,
					'BACKGROUND_COLOR' => $BACKGROUND_COLOR,
						));
				$this->mmFormat->addEdge($faLevel1, array('WIDTH' => $this->getDesignEdgeWidth('getTYPONodeFiles'), 'COLOR' => $BACKGROUND_COLOR));

				$dirLevel2 = @scandir($fullPath . $vL1);
				if (is_array($dirLevel2)) {
					foreach ($dirLevel2 as $k2 => $vL2) {
						$Level2Dir = $fullPath . $vL1 . '/' . $vL2;
						/* is dir and avoid .svn or .git or ... folders file starting with a . */
						if (is_dir($Level2Dir) && preg_match('~^\..*~', $vL2) == false) {

							$size = $this->formatBytes($this->getDirSize($Level2Dir));

							$faLevel2 = $this->mmFormat->addNode($faLevel1, array(
								'TEXT' => $this->xmlentities($vL2 . ' ' . $size),
								'BACKGROUND_COLOR' => $BACKGROUND_COLOR,
									));
						}
					}/* endforeach */
				} /* endif is array $dirLevel2 */
			}/* endif */
		} /* endforeach */
	} /* </_getTYPONodeFilesScandir> */

	/**
	 * gets some T3 logins and error logs
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	void
	 */
	private function _getTYPONodeLogs(SimpleXMLElement $xmlNode)
	{
		// logs
		$LogsNode = $this->mmFormat->addImgNode($xmlNode, array(
			'FOLDED' => 'true',
			'TEXT' => $this->translate('tree.typo3logs'),
				), 'typo3/sysext/t3skin/icons/module_tools_log.gif', 'height="16"');

		/* <LOGS successfull backend logins> */
		$nodeHTML = array();
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('from_unixtime(tstamp,\'%Y-%m-%d %H:%i:%s\') as LoggedDate,log_data', 'sys_log', 'error=0 AND type=255', '', 'tstamp DESC', (int) $this->settings['numberOfLogRows']);
		while ($r = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$nodeHTML[$r['LoggedDate']] = implode(' / ', unserialize($r['log_data']));
		}

		$this->mmFormat->addRichContentNote($LogsNode, array('TEXT' => $this->translate('tree.typo3.SuccessfullBackendLogins')), '<h3>' . $this->translate('tree.typo3.SuccessfullBackendLogins') . '</h3>' . $this->array2Html2ColTable($nodeHTML));
		/* </LOGS successfull backend logins> */


		/* <LOGS failed backend logins> */
		$nodeHTML = array();
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('from_unixtime(tstamp,\'%Y-%m-%d %H:%i:%s\') as LoggedDate,log_data', 'sys_log', 'error=3', '', 'tstamp DESC', (int) $this->settings['numberOfLogRows']);
		while ($r = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$nodeHTML[$r['LoggedDate']] = implode(' / ', unserialize($r['log_data']));
		}
		$this->mmFormat->addRichContentNote($LogsNode, array('TEXT' => $this->translate('tree.typo3.FailedBackendLogins')), '<h3>' . $this->translate('tree.typo3.FailedBackendLogins') . '</h3>' . $this->array2Html2ColTable($nodeHTML));
		/* </LOGS failed backend logins> */


		/* <LOGS error logs> */
		$DBresult = $GLOBALS['TYPO3_DB']->exec_SELECTquery('from_unixtime(tstamp,\'%Y-%m-%d %H:%i:%s\') as LoggedDate,details', 'sys_log', 'error=1', '', 'tstamp DESC', (int) $this->settings['numberOfLogRows']);
		$nodeHTML = array();
		while ($r = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($DBresult)) {
			$nodeHTML[$r['LoggedDate']] = strip_tags(str_replace('|', ' ', $r['details']));
		}

		$this->mmFormat->addRichContentNote($LogsNode, array('TEXT' => $this->translate('tree.typo3.ErrorLog')), '<h3>' . $this->translate('tree.typo3.ErrorLog') . '</h3>' . $this->array2Html2ColTable($nodeHTML));
		/* </LOGS error logs> */
	}

	/**
	 * gets all backend users
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	void
	 */
	private function _getTYPONodeBackendUsers(SimpleXMLElement $xmlNode)
	{


		$UsersNode = $this->mmFormat->addImgNode($xmlNode, array(
			'LINK' => $this->mapMode['isbe'] ? $this->getBEHttpHost() . 'typo3/mod.php?M=tools_beuser' : '',
			'FOLDED' => 'true',
			'TEXT' => $this->translate('tree.typo3users'),
				), 'typo3/sysext/t3skin/icons/gfx/i/be_users__x.gif', 'height="16"');


		/* <show all admins> */
		$UserAdminNode = $this->mmFormat->addNode($UsersNode, array(
			'FOLDED' => 'true',
			'TEXT' => $this->translate('tree.typo3.useradmin'),
				));
		$this->mmFormat->addIcon($UserAdminNode, 'penguin');

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,username,password,email,realname,lastlogin,disable,deleted', 'be_users', 'admin=1', '', 'username');
		$i = 0;
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$this->_BeUsersHandleRow($UserAdminNode, $row, $i);
			$i++;
		}
		/* </show all admins> */


		// @TODO: to which group belongs a user?
		/* <show all non admins> */
		$UserUserNode = $this->mmFormat->addNode($UsersNode, array(
			'FOLDED' => 'true',
			'TEXT' => $this->translate('tree.typo3.users'),
				));
		$this->mmFormat->addIcon($UserUserNode, 'male1');


		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,username,password,email,realname,lastlogin,disable,deleted,userMods', 'be_users', 'admin=0', '', 'username' /* , (int)$this->settings['numberOfLogRows'] */);
		$i = 0;
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$this->_BeUsersHandleRow($UserUserNode, $row, $i);
			$i++;
		}
		/* </show all non admins> */


		/* <show all groups> */
		$UserGroupNode = $this->mmFormat->addNode($UsersNode, array(
			'FOLDED' => 'true',
			'TEXT' => $this->translate('tree.typo3.groups'),
				));
		$this->mmFormat->addIcon($UserGroupNode, 'group');

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title,hidden,deleted,crdate,tables_select,tables_modify,groupMods', 'be_groups', '', '', 'title' /* , (int)$this->settings['numberOfLogRows'] */);
		$i = 0;
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$this->_BeGroupsHandleRow($UserGroupNode, $row, $i);
			$i++;
		}
		/* </show all groups> */
	} /* </_getTYPONodeBackendUsers> */

	/**
	 * handles a row returned from mysql with the backend user values
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @param	array $row
	 * @param	integer $rowCounter
	 * @return	void
	 */
	private function _BeUsersHandleRow(SimpleXMLElement $xmlNode, $row, $rowCounter)
	{


		$BACKGROUND_COLOR = $this->getDesignAlternatingColor('BeUsersHandleRow', $rowCounter);
		$aUserNode = $this->mmFormat->addNode($xmlNode, array(
			// not possible due to the ampersand returnUrl=%2Ftypo3%2Fmod.php%3FM%3Dtools_beuser&
			'LINK' => $this->mapMode['isbe'] ? $this->getBEHttpHost() . 'typo3/alt_doc.php?edit[be_users][' . $row['uid'] . ']=edit' : '',
			// 'FOLDED'=>'true',
			'TEXT' => $row['username'],
			'BACKGROUND_COLOR' => $BACKGROUND_COLOR,
				));
		$this->mmFormat->addEdge($aUserNode, array('WIDTH' => $this->getDesignEdgeWidth('BeUsersHandleRow'), 'COLOR' => $BACKGROUND_COLOR));

		if ($row['deleted'] == 1) {
			$this->mmFormat->addIcon($aUserNode, 'button_cancel');
		} elseif ($row['disable'] == 1) {
			$this->mmFormat->addIcon($aUserNode, 'encrypted');
		}
		if (($row['lastlogin'] + (3600 * 24 * 9)) < time()) {
			$this->mmFormat->addIcon($aUserNode, 'hourglass');
		}

		if (!empty($row['realname'])) {
			$this->mmFormat->addNode($aUserNode, array('BACKGROUND_COLOR' => $BACKGROUND_COLOR, 'TEXT' => $row['realname']));
		}
		if (!empty($row['email'])) {
			$this->mmFormat->addNode($aUserNode, array('BACKGROUND_COLOR' => $BACKGROUND_COLOR, 'TEXT' => $row['email']));
		}
		if (!empty($row['password'])) {
			/* <check for unsecure installtool password!> */
			// http://www.stottmeister.com/blog/2009/04/14/how-to-crack-md5-passwords/
			// http://netmd5crack.com/cgi-bin/Crack.py?InputHash=[md5string]
			$plainPassword = $this->getPlainTextPasswordFromMD5($row['password']);

			if ($plainPassword !== false) {
				$attrPW = array(
					'BACKGROUND_COLOR' => $BACKGROUND_COLOR, 'TEXT' => 'Decrypted your unsecure password: ' . $plainPassword,
				);
				$attrPW['COLOR'] = '#D60035';
				$attrPW['LINK'] = 'http://www.tmto.org/pages/passwordtools/hashcracker/';
				$nodePW = $this->mmFormat->addNode($aUserNode, $attrPW);
				$this->mmFormat->addIcon($nodePW, 'messagebox_warning');
			}

			/* </check for unsecure installtool password!> */
		}
		if (isset($row['userMods']) && !empty($row['userMods'])) {
			$nodeUserMods = $this->mmFormat->addNode($aUserNode, array('BACKGROUND_COLOR' => $BACKGROUND_COLOR, 'TEXT' => $this->translate('tree.typo3.groups.groupMods')));
			$this->_BeUserGroupsGetModList($nodeUserMods, 'modListUser', $row['userMods'], array('BACKGROUND_COLOR' => $BACKGROUND_COLOR));
		}/* endif */


		/* <LOGS user logs> */
		$DBresult = $GLOBALS['TYPO3_DB']->exec_SELECTquery('from_unixtime(tstamp,\'%Y-%m-%d %H:%i:%s\') as LoggedDate,tablename,details,log_data,ip', 'sys_log', 'userid=' . $row['uid'] . ' and error=0', '', 'tstamp DESC', (int) $this->settings['numberOfLogRows']);
		$nodeHTML = array();
		while ($r = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($DBresult)) {
			if (!empty($r['log_data'])) {
				$data = unserialize($r['log_data']);
				for ($o = 0; $o < 5; $o++) {
					if (!isset($data[$o])) {
						$data[$o] = '';
					}
				}
				$r['details'] = sprintf($r['details'], htmlspecialchars($data[0]), htmlspecialchars($data[1]), htmlspecialchars($data[2]), htmlspecialchars($data[3]), htmlspecialchars($data[4]));
			}
			unset($r['log_data']);
			if (empty($r['tablename'])) {
				unset($r['tablename']);
			}
			$nodeHTML[$r['LoggedDate']] = implode(' / ', ($r));
		}

		if (count($nodeHTML) > 0) {
			$this->mmFormat->addRichContentNote($aUserNode, array('BACKGROUND_COLOR' => $BACKGROUND_COLOR, 'TEXT' => $this->translate('tree.typo3.SysLog')), '<h3>' . $this->translate('tree.typo3.SysLog') . '</h3>' . $this->array2Html2ColTable($nodeHTML));
		}
		/* </LOGS user logs> */
	} /* </_BeUsersHandleRow> */

	/**
	 * handles a row returned from mysql with the backend groups values
	 * @TODO:  list all settings for a group ... it seems complicated ...
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @param	array $row
	 * @param	integer $rowCounter
	 * @return	void
	 */
	private function _BeGroupsHandleRow(SimpleXMLElement $xmlNode, $row, $rowCounter)
	{

		$BACKGROUND_COLOR = $this->getDesignAlternatingColor('BeGroupsHandleRow', $rowCounter);
		$aGroupNode = $this->mmFormat->addNode($xmlNode, array(
			'BACKGROUND_COLOR' => $BACKGROUND_COLOR,
			'TEXT' => $row['title'],
			'LINK' => $this->mapMode['isbe'] ? $this->getBEHttpHost() . 'typo3/alt_doc.php?edit[be_groups][' . $row['uid'] . ']=edit' : ''
				));

		$this->mmFormat->addEdge($aGroupNode, array('WIDTH' => $this->getDesignEdgeWidth('BeGroupsHandleRow'), 'COLOR' => $BACKGROUND_COLOR));

		if ($row['deleted'] == 1) {
			$this->mmFormat->addIcon($aGroupNode, 'button_cancel');
		} elseif ($row['hidden'] == 1) {
			$this->mmFormat->addIcon($aGroupNode, 'closed');
		}

		if (!empty($row['groupMods'])) {
			$nodeGroupMods = $this->mmFormat->addNode($aGroupNode, array('BACKGROUND_COLOR' => $BACKGROUND_COLOR, 'TEXT' => $this->translate('tree.typo3.groups.groupMods')));
			$this->_BeUserGroupsGetModList($nodeGroupMods, 'modListGroup', $row['groupMods'], array('BACKGROUND_COLOR' => $BACKGROUND_COLOR));
		}/* endif */

		$this->_BeGroupsHandleTableSelectModify($aGroupNode, $row['tables_select'], 'tree.typo3.groups.tables_select', array('BACKGROUND_COLOR' => $BACKGROUND_COLOR));
		$this->_BeGroupsHandleTableSelectModify($aGroupNode, $row['tables_modify'], 'tree.typo3.groups.tables_modify', array('BACKGROUND_COLOR' => $BACKGROUND_COLOR));
	}

/* </_BeGroupsHandleRow> */

	/**
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @param	string $tables
	 * @param	string $translateKey
	 * @param	array $attr
	 * @return	void
	 */
	private function _BeGroupsHandleTableSelectModify(SimpleXMLElement $xmlNode, $tables, $translateKey, $attr)
	{
		GLOBAL $TCA;

		if (!empty($tables)) {
			$nodeTables = $this->mmFormat->addNode($xmlNode, array_merge($attr, array('FOLDED' => 'true', 'TEXT' => $this->translate($translateKey))));
			$exploded = t3lib_div::trimExplode(',', $tables, 1);
			ksort($exploded);
			foreach ($exploded as $k => $table) {
				$text = isset($TCA[$table]) ? $GLOBALS['LANG']->sL($TCA[$table]['ctrl']['title']) : $table;
				$this->mmFormat->addNode($nodeTables, array_merge($attr, array('TEXT' => '[' . $table . '] ' . $text)));
			}
		}
	} /* </_BeGroupsHandleTableSelectModify> */

	/**
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @param	string $modListType
	 * @param	string $groupMods
	 * @param	array $attr
	 * @return	void
	 */
	private function _BeUserGroupsGetModList(SimpleXMLElement $xmlNode, $modListType, $groupMods, $attr = array())
	{


		$modList = $modListType == 'modListUser' ? $this->_loadModules->modListUser : $this->_loadModules->modListGroup;

		$groupModsExploded = Tx_Typo3mind_Utility_Helpers::trimExplodeVK(',', $groupMods);

		if (is_array($modList)) {
			sort($modList);
			foreach ($modList as $theMod) {
				if (isset($groupModsExploded[$theMod])) {
					/* 	// Icon:	maybe one day ... we'll add an icon
					  $icon = $GLOBALS['LANG']->moduleLabels['tabs_images'][$theMod . '_tab'];
					  if ($icon) {
					  $icon = '../' . substr($icon, strlen(PATH_site));
					  } */

					$modLabel = $this->_TCEforms->addSelectOptionsToItemArray_makeModuleData($theMod);
					$attri = array_merge(array('TEXT' => $modLabel), $attr);
					$this->mmFormat->addNode($xmlNode, $attri);
				}/* endif isset $groupModsExploded */
			}
		}/* endif is array */
	} /* </_BeUserGroupsGetModList> */

	/**
	 * shows all TYPO3_CONF_VARS
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	void
	 */
	private function _getTYPONodeConfVars(SimpleXMLElement $xmlNode)
	{

		$t3ConfVarNode = $this->mmFormat->addNode($xmlNode, array(
			'FOLDED' => 'true',
			'TEXT' => $this->translate('tree.typo3.typo3_conf_vars'),
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


		foreach ($tcv as $section => $seccfg) {
			foreach ($seccfg as $k => $v) {
				if (stristr($k, 'TypoScript') !== false || stristr($k, 'TSconfig') !== false) {
					unset($tcv[$section][$k]);
				}
			}
		}

		$T3ConfCheck = new Tx_Typo3mind_Utility_T3ConfCheck();
		$commentArr = $T3ConfCheck->getDefaultConfigArrayComments();

		$seccfglistDetails = array(
			'extConf' => 1,
			'XCLASS' => 1,
			'XLLfile' => 1,
			'defaultPermissions' => 1,
			'defaultUC' => 1,
			'fileExtensions' => 1,
			'loginNews' => 1,
		);
		$installToolPlainPassword = false;
		foreach ($tcv as $section => $seccfg) {
			$NodeSection = $this->mmFormat->addNode($t3ConfVarNode, array(
				'FOLDED' => count($seccfg) > 0 ? 'true' : 'false',
				'TEXT' => $this->translate('tree.typo3.typo3_conf_vars.' . $section),
					));

			ksort($seccfg);
			foreach ($seccfg as $confName => $v) {

				if (is_array($v) && count($v) > 0) {
					$attr = array(
						'TEXT' => $confName,
						'FOLDED' => 'true',
					);
					$NodeSectionValue = $this->mmFormat->addNode($NodeSection, $attr);

					if ($confName == 'eID_include') {
						$this->mmFormat->addIcon($NodeSectionValue, 'messagebox_warning');
					}

					foreach ($v as $extName => $extConf) {

						$extConf = (is_array($extConf) || stristr($extConf, '}') === false) ? $extConf : unserialize($extConf);

						$htmlContent = array();
						if (is_array($extConf)) {
							$htmlContent[] = '<pre>' . htmlspecialchars(var_export($extConf, 1)) . '</pre>';
						} else {
							$htmlContent[] = htmlspecialchars($extConf);
						}
						$NodeExtName = $this->mmFormat->addRichContentNote($NodeSectionValue, array(
							//	'FOLDED'=>count($seccfg) > 0 ? 'true' : 'false',
							'TEXT' => $extName,
							'LINK' => $confName == 'extConf' ? $this->getTerURL($extName) : '',
								), implode("<br/>\n", $htmlContent));
					} /* endforeach */
				} else {
					$htmlContent = array();
					$attr = array(
						'TEXT' => '[' . $confName . '] = ' . (!is_string($v) ? 'no-string given' : htmlspecialchars($v) ),
					);

					/* <check for unsecure installtool password!> */
					if ($confName == 'installToolPassword') {
						$installToolPlainPassword = $this->getPlainTextPasswordFromMD5($v);
						if ($installToolPlainPassword !== false) {

							$attr['COLOR'] = '#D60035';
							$attr['LINK'] = 'http://www.tmto.org/pages/passwordtools/hashcracker/';
							$htmlContent[] = '<h3>Decrypted your unsecure password: <i>' . $installToolPlainPassword . '</i></h3>';
						}
					}
					/* </check for unsecure installtool password!> */

					$htmlContent[] = '<b>' . $this->translate('tree.typo3.typo3_conf_vars.value') . ': <i>' . $v . '</i></b>';
					if (isset($commentArr[1][$section]) && isset($commentArr[1][$section][$confName])) {
						$htmlContent[] = strip_tags($commentArr[1][$section][$confName], '<a>,<dl>,<dt>,<dd>');
					}
					/* bug in typo3 default config file, improper closed html @see getDefaultConfigArrayComments */
					$htmlContent = str_replace('<dd><dt>', '</dd><dt>', implode('<br/>', $htmlContent));

					$NodeSectionValue = $this->mmFormat->addRichContentNote($NodeSection, $attr, $htmlContent /* ,$addEdgeAttr = array(),$addFontAttr = array(), $type = 'NOTE' */);

					if ($installToolPlainPassword !== false) {
						$this->mmFormat->addFont($NodeSectionValue, array('SIZE' => 14, 'BOLD' => 'true', 'COLOR' => '#fff'));
						$this->mmFormat->addIcon($NodeSectionValue, 'messagebox_warning');
						$installToolPlainPassword = false;
					}
				}/* endelse default */
			}/* endforeach */
		}/* endforeach */
	} /* </_getTYPONodeConfVars> */

	/**
	 * checks the directories
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	void
	 */
	private function _getTYPONodeCheckDirs(SimpleXMLElement $xmlNode)
	{

		$checkDirNode = $this->mmFormat->addNode($xmlNode, array(
			'FOLDED' => 'true',
			'TEXT' => $this->translate('tree.typo3.checkDirs'),
				));

		$T3ConfCheck = new Tx_Typo3mind_Utility_T3ConfCheck();
		$T3ConfCheck->checkDirs();

		foreach ($T3ConfCheck->messages as $k => $message) {

			$attr = array(
				'TEXT' => htmlspecialchars($message['short']),
				'BACKGROUND_COLOR' => $this->getDesignAlternatingColor('getTYPONodeCheckDirs', $k),
			);

			if (!empty($message['long'])) {
				$htmlContent = $message['long'];
				$messageNode = $this->mmFormat->addRichContentNote($checkDirNode, $attr, $htmlContent /* ,$addEdgeAttr = array(),$addFontAttr = array(), $type = 'NOTE' */);
			} else {
				$messageNode = $this->mmFormat->addNode($checkDirNode, $attr);
			}
			$this->mmFormat->addIcon($messageNode, $message['icon']);
		}/* endforeach */
	} /* </_getTYPONodeCheckDirs> */

	/**
	 * gets some T3 specific informations / main method
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	void
	 */
	public function getTYPONode(SimpleXMLElement $xmlNode)
	{
		if( $this->mainNodeIsDisabled(__FUNCTION__) ){ return false; }

		$MainNode = $this->mmFormat->addImgNode($xmlNode, array(
			'POSITION' => 'left',
			//		'FOLDED'=>'true',
			'TEXT' => $this->translate('tree.typo3'),
				), 'typo3/sysext/t3skin/images/icons/apps/pagetree-root.png', 'height="16"');



		$this->_getTYPONodeFiles($MainNode);

		$this->_getTYPONodeLogs($MainNode);


		$this->_getTYPONodeBackendUsers($MainNode);

		$this->_getTYPONodeConfVars($MainNode);

		$this->_getTYPONodeCheckDirs($MainNode);
	} /* endmethod */

	/**
	 * gets security node
	 * http://www.iconarchive.com/show/refresh-cl-icons-by-tpdkdesign.net/System-Security-Warning-icon.html
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	void
	 */
	public function getSecurityNode(SimpleXMLElement $xmlNode)
	{
		if( $this->mainNodeIsDisabled(__FUNCTION__) ){ return false; }

		$secMainNode = $this->mmFormat->addImgNode($xmlNode, array(
			'POSITION' => 'left',
			'FOLDED' => 'true',
			'TEXT' => $this->translate('tree.security'),
				), 'typo3conf/ext/typo3mind/Resources/Public/Icons/System-Security-Warning-icon.png', 'height="16"');

		$this->mmFormat->addNode($secMainNode, array('TEXT' => 'TYPO3 Security Guide ', 'LINK' => $this->settings['TYPO3SecurityGuideURL']));


		$this->RssFeeds2Node($secMainNode);


		$this->mmFormat->addNode($secMainNode, array('TEXT' => 'more to follow', 'LINK' => 'https://github.com/SchumacherFM/TYPO3Mind/issues/12'));
	} /* </getSecurityNode> */

	/**
	 * gets some server informations
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	void
	 */
	public function getServerNode(SimpleXMLElement $xmlNode)
	{
		if( $this->mainNodeIsDisabled(__FUNCTION__) ){ return false; }

		$MainNode = $this->mmFormat->addImgNode($xmlNode, array(
			'POSITION' => 'left',
			'FOLDED' => 'true',
			'TEXT' => $this->translate('tree.server'),
				), 'typo3/sysext/t3skin/images/icons/apps/filetree-root.png', 'height="16"');


		$_SERVER['PHP_VERSION'] = phpversion();
		$this->mmFormat->addRichContentNote($MainNode, array('TEXT' => $this->translate('tree.server._server')), $this->array2Html2ColTable($_SERVER));

		$this->mmFormat->addRichContentNote($MainNode, array('TEXT' => $this->translate('tree.server._env')), $this->array2Html2ColTable($_ENV));

		/* nice, but needs deep reformatting ...
		  ob_start() ;
		  phpinfo(INFO_MODULES) ;
		  $pinfo = ob_get_contents () ;
		  ob_end_clean () ;
		  $this->mmFormat->addRichContentNote($MainNode, array('TEXT'=>$this->translate('tree.server.phpModules')),  $pinfo );
		 */
	}

	/**
	 * gets some database informations
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	SimpleXMLElement the main node
	 */
	public function getDatabaseNode(SimpleXMLElement $xmlNode)
	{
		if( $this->mainNodeIsDisabled(__FUNCTION__) ){ return false; }

		$MainNode = $this->mmFormat->addImgNode($xmlNode, array(
			'POSITION' => 'left',
			'FOLDED' => 'true',
			'TEXT' => $this->translate('tree.database'),
				), 'typo3/sysext/t3skin/icons/module_tools_dbint.gif', 'height="16"');

		// general mysql infos
		$mysqlNode = $this->mmFormat->addNode($MainNode, array(
			'FOLDED' => 'true',
			'TEXT' => $this->translate('tree.mysql'),
				));

		$nodeHTML = array();
		$DBresult = $GLOBALS['TYPO3_DB']->sql_query('SHOW VARIABLES LIKE  \'version%\'');
		while ($r = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($DBresult)) {
			$nodeHTML[$r['Variable_name']] = $r['Value'];
		}

		$this->mmFormat->addRichContentNode($mysqlNode, array(), $this->array2Html2ColTable($nodeHTML));



		$agt = $GLOBALS['TYPO3_DB']->admin_get_tables();

		$groupedTables = array();
		foreach ($agt as $table => $tinfo) {
			$te = explode('_', $table);
			$groupedTables[$te[0]][$table] = $tinfo;
		}
		unset($agt);
		ksort($groupedTables);

		$i = 0;
		foreach ($groupedTables as $group => $tables) {

			$tGroup = $this->translate('tree.database.' . $group);
			$BACKGROUND_COLOR = $this->getDesignAlternatingColor('getDatabaseNode', $i);

			$GroupTableNode = $this->mmFormat->addNode($MainNode, array(
				'FOLDED' => 'true',
				'BACKGROUND_COLOR' => $BACKGROUND_COLOR,
				'TEXT' => $tGroup == '' ? $group : $tGroup,
					));
			$this->mmFormat->addEdge($GroupTableNode, array('WIDTH' => $this->getDesignEdgeWidth('getDatabaseNode'), 'COLOR' => $BACKGROUND_COLOR));

			foreach ($tables as $tkey => $tinfo) {

				$size = sprintf('%.2f', ($tinfo['Data_length'] + $tinfo['Index_length']) / 1024);

				$ATableNode = $this->mmFormat->addNode($GroupTableNode, array(
					//	'FOLDED'=>'true',
					'BACKGROUND_COLOR' => $BACKGROUND_COLOR,
					'TEXT' => $tkey . ' (' . $tinfo['Rows'] . ') ' . $size . ' KB',
						));

				/*
				  $ATableNode = $this->mmFormat->addNode($GroupTableNode,array(
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
				  $tinfoNode = $this->mmFormat->addRichContentNode($ATableNode, array(),implode('',$nodeHTML) );
				 */
			}
			$i++;
		}/* endforeach */


		return $MainNode;
	} /* </getDatabaseNode> */

	/**
	 *
	 * @param string $extKey
	 * @param string $extType
	 * @return array
	 */
	private function _getExtensionNodeIcon($extKey, $extType)
	{

		switch ($extType) {
			case 'S':
				$preURI = TYPO3_mainDir . 'sysext/';
				$addTERLink = 0;
				break;
			case 'G':
				$preURI = TYPO3_mainDir . 'ext/';
				$addTERLink = 1;
				break;
			case 'L':
				$preURI = 'typo3conf/ext/';
				$addTERLink = 1;
				break;
		}

		// ext icon
		$extIcon = $preURI . $extKey . '/ext_icon.gif';

		return array('addTERLink' => $addTERLink, 'extIcon' => $extIcon);
	} /* </_ExtensionNodeIcon> */

	/**
	 * gets the extension nodes
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	SimpleXMLElement $ChildFirst_Extensions
	 */
	public function getExtensionNode(SimpleXMLElement $xmlNode)
	{
		global $TCA;
		
		if( $this->mainNodeIsDisabled(__FUNCTION__) ){ return false; }

		$extensionManager = new Tx_Typo3mind_Utility_ExtensionManager();


		$ChildFirst_Extensions = $this->mmFormat->addImgNode($xmlNode, array(
			'POSITION' => 'left',
			'TEXT' => $this->translate('tree.extensions'),
				), 'typo3/sysext/t3skin/icons/module_tools_em.png');


		/* <frontend plugins which you can choose in the backend> */
		$selectableExtensions = $this->mmFormat->addNode($ChildFirst_Extensions, array(
			'TEXT' => $this->translate('tree.extensions.selectable'),
			'FOLDED' => 'true',
				));


		foreach ($TCA['tt_content']['columns']['list_type']['config']['items'] as $ei => $extA) {
			$extA[0] = $GLOBALS['LANG']->sL($extA[0]);
			if (!empty($extA[0])) {

				$extName = array();
				preg_match('~/([\w]+)/ext_icon\.gif~i', $extA[2], $extName);

				$extKey = isset($extName[1]) ? $extName[1] : '';

				$this->mmFormat->addImgNode($selectableExtensions, array(
					'TEXT' => '(' . $extA[1] . ') ' . $extA[0],
					'LINK' => '#LSext' . $extKey,
						), $extA[2]);
			}
		}/* endforeach */
		/* </frontend plugins which you can choose in the backend> */




		/* <check for extension updates!> */
		$updateExtensions = $this->mmFormat->addNode($ChildFirst_Extensions, array(
			'TEXT' => $this->translate('tree.extensions.updates'),
			'FOLDED' => 'true',
				));
		if (is_file(PATH_site . 'typo3temp/extensions.xml.gz')) {
			$dateFormat = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'];
			$timeFormat = $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'];
			$content = $this->translate('tree.extensions.updates.last') .
					date(
							$dateFormat . ', ' . $timeFormat, filemtime(PATH_site . 'typo3temp/extensions.xml.gz')
					) . '<br/>' . $this->translate('tree.extensions.updates.number') .
					tx_em_Database::getExtensionCountFromRepository();
			$this->mmFormat->addRichContentNode($updateExtensions, array(), $content);
		}
		$showExtensionsToUpdate = $extensionManager->showExtensionsToUpdate();

		foreach ($showExtensionsToUpdate as $extName => $extData) {
			// ext icon
			$extIcon = $this->getBEHttpHost() . str_replace('../', '', $extData['icon']);

			$htmlContent = array(
				'NODE' => '<img src="' . $extIcon . '"/>@#160;@#160;' . $extData['nicename'],
				'NOTE' => '<p>Version local: ' . $extData['version_local'] . '</p>' .
				'<p>Version remote: ' . $extData['version_remote'] . '</p>' .
				'<p>' . htmlspecialchars($this->mmFormat->convertLTGT($extData['comment'])) . '</p>',
			);
			$this->_isExtUpdateAvailable[$extName] = 1;
			$attr = array('ID' => 'LSupdate' . $extName, 'LINK' => '#LSext' . $extName);

			$extRCNode = $this->mmFormat->addRichContentNote($updateExtensions, $attr, $htmlContent, array(), array(), 'BOTH');

			$this->mmFormat->addArrowlink($extRCNode, array('DESTINATION' => 'LSext' . $extName));
		}/* endforeach */
		/* </check for extension updates!> */


		$installedExt = $extensionManager->getInstalledExtensions();

		//	echo '<pre>'; var_dump($installedExt[0]); die('</pre>');

		/* <Simple list all extensions and link them> */
		$ListAllExtensionsNode = $this->mmFormat->addNode($ChildFirst_Extensions, array(
			'TEXT' => $this->translate('tree.extensions.allext') . ' (' . count($installedExt[0]) . ')',
			'FOLDED' => 'true',
				));

		foreach ($installedExt[0] as $extKey => $extArray) {

			// ext icon
			$ico = $this->_getExtensionNodeIcon($extKey, $extArray['type']);

			$extNode = $this->mmFormat->addImgNode($ListAllExtensionsNode, array(
				'TEXT' => $extArray['EM_CONF']['title'],
				'LINK' => '#LSext' . $extKey,
					), $ico['extIcon']);

			// installed or not icon
			$icon = $extArray['installed'] ? 'button_ok' : 'button_cancel';
			$this->mmFormat->addIcon($extNode, $icon);
		}
		/* </Simple list all extensions and link them> */


		/* extension by modul state */

		/* rebuilding the array by cat->state->name */
		$installExt2 = array();
		foreach ($installedExt[1]['cat'] as $catName => $extArray) {
			ksort($extArray);
			foreach ($extArray as $extKey => $extNiceName) {
				foreach ($this->states as $statek => $stateName) {

					if ($installedExt[0][$extKey]['EM_CONF']['state'] == $statek) {
						$installExt2[$catName][$statek][$extKey] = $installedExt[0][$extKey];
					}
				}
			}
		}
		$installedExt = $installExt2;


		/* extension by category = normal view */
		foreach ($installedExt as $catName => $catArray) {

			$catNode = $this->mmFormat->addNode($ChildFirst_Extensions, array(
				'FOLDED' => 'true',
				'TEXT' => $this->categories[$catName],
					));

			foreach ($catArray as $statek => $stateArray) {


				$attr = array(
					'FOLDED' => 'true',
					'TEXT' => $this->states[$statek],
					'BACKGROUND_COLOR' => $this->stateColors[$statek],
					'COLOR' => '#ffffff'
				);
				$aStateNode = $this->mmFormat->addNode($catNode, $attr);
				$this->mmFormat->addFont($aStateNode, array('BOLD' => 'true', 'SIZE' => 14));

				$this->mmFormat->addCloud($aStateNode, array('COLOR' => $this->stateColors[$statek]));

				$extI = 0;
				foreach ($stateArray as $extKey => $extArray) {

					$ico = $this->_getExtensionNodeIcon($extKey, $extArray['type']);

					$extNode = $this->mmFormat->addImgNode($aStateNode, array(
						'ID' => 'LSext' . $extKey,
						'FOLDED' => 'true',
						'TEXT' => $extArray['EM_CONF']['title'],
						/* if there is an extension update, then link back to the update! */
						'LINK' => isset($this->_isExtUpdateAvailable[$extKey]) ? '#LSupdate' . $extKey : '',
							), $ico['extIcon']);

					$color = $this->getDesignAlternatingColor('getExtensionNode', $extI, 'CLOUD_COLOR');
					$this->mmFormat->addCloud($extNode, array('COLOR' => $color));


					// installed or not icon
					$icon = $extArray['installed'] ? 'button_ok' : 'button_cancel';
					$this->mmFormat->addIcon($extNode, $icon);

					// node for system global or local ext
					$this->mmFormat->addNode($extNode, array(
						'TEXT' => $this->types[$extArray['type']],
					));

					$this->mmFormat->addNode($extNode, array(
						'TEXT' => 'Key: ' . $extKey,
					));

					// link to TER
					if ($ico['addTERLink'] == 1) {
						$this->mmFormat->addNode($extNode, array(
							'TEXT' => $this->translate('tree.linkName2TER'),
							'LINK' => $this->getTerURL($extKey),
						));
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


					foreach ($extArray['EM_CONF'] as $ek => $ev) {
						if (!empty($ev)) {

							$attr = array(
								'TEXT' => ucfirst($ek) . ': ' . $ev,
							);

							if ($ek == 'state') {
								$attr['BACKGROUND_COLOR'] = $this->stateColors[$ev];
								$attr['COLOR'] = '#ffffff';
								$attr['TEXT'] = $this->states[$ev];
							}

							$this->mmFormat->addNode($extNode, $attr);
						}
					}
					$extI++;
				} /* endforeach $installedExt[1]['state'][$statek] */
			}/* endforeach $this->states */
		}/* endforeach $installedExt[1]['cat'] */
		return $ChildFirst_Extensions;
	}

	/**
	*
	* @param string $string
	* @return string
	*/
	private function xmlentities($string)
	{
		$not_in_list = '';
		return preg_replace_callback('/[^A-Z0-9a-z_-]/', 'get_xml_entity_at_index_0', $string);
	}

} /*endclass*/

	/**
	* outside class due to preg_replace_callback
	*
	* @param string $CHAR
	* @return string
    * @see xmlentities()
	*/
	function get_xml_entity_at_index_0($CHAR)
	{
		if (!is_string($CHAR[0]) || ( strlen($CHAR[0]) > 1 )) {
			die("function: 'get_xml_entity_at_index_0' requires data type: 'char' (single character). '{$CHAR[0]}' does not match this type.");
		}
		switch ($CHAR[0]) {
			case '\'': case '"': case '&': case '<': case '>':
				return htmlspecialchars($CHAR[0], ENT_QUOTES);
				break;
			default:
				return numeric_entity_4_char($CHAR[0]);
				break;
		}
	}

	/**
	* outside class due to preg_replace_callback
	*
	* @param string $char
	* @return string
    * @see xmlentities()
	*/
	function numeric_entity_4_char($char)
	{
		return '@#' . str_pad(ord($char), 3, '0', STR_PAD_LEFT) . ';';
	}
