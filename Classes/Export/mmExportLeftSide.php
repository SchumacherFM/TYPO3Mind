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
 * @package freemind2
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 */
class Tx_Freemind2_Export_mmExportLeftSide extends Tx_Freemind2_Export_mmExportCommon {


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
	public function __construct() {

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
	 * gets some database informations
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	SimpleXMLElement
	 */
	public function getDatabaseNode(SimpleXMLElement &$xmlNode) {
		$MainNode = $this->addNode($xmlNode,array(
			'POSITION'=>'left',
			'TEXT'=>$this->translate('tree.database'),
		));

		$agt = $GLOBALS['TYPO3_DB']->admin_get_tables();

	//	echo '<pre>';   var_dump( $agt ); exit;

		foreach ($agt as $table => $tinfo){

			$TableNode = $this->addNode($MainNode,array(
				'FOLDED'=>'true',
				'TEXT'=>$table,
			));

			$nodeHTML = array('<table>');
			foreach($tinfo as $tk=>$tv){
				if( !empty($tv) ){
					$nodeHTML[] = '<tr><td>'.$tk.'</td><td>'.$tv.'</td></tr>';
				}
			}
			$nodeHTML[] = '</table>';

			$tinfoNode = $this->addRichContentNode($TableNode, array(),implode('',$nodeHTML) );

		}/*endforeach*/


		return $MainNode;
	}

	/**
	 * gets the extension nodes
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	SimpleXMLElement
	 */
	public function getExtensionNode(SimpleXMLElement &$xmlNode) {

		$ChildFirst_Extensions = $this->addNode($xmlNode,array(
			'POSITION'=>'left',
			'TEXT'=>$this->translate('tree.extensions'),
		));



		$installedExt = $this->getInstalledExtensions();

		foreach( $installedExt[1]['cat'] as $catName => $ext ){

			$catNode = $this->addNode($ChildFirst_Extensions, array(
				// 'FOLDED'=>'true',
				'TEXT'=>$this->categories[$catName],
			) );
			ksort($ext);
			foreach($ext as $extKey => $niceName ){

				switch($installedExt[0][$extKey]['type']){
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

				if( file_exists(PATH_site.$extIcon) ){
					$img = '<img src="http://'.t3lib_div::getIndpEnv('HTTP_HOST').'/'.$extIcon.'"/>';
					$nodeHTML = $img.'@#160;@#160;'.htmlspecialchars($niceName);
					$extNode = $this->addRichContentNode($catNode, array(
						'FOLDED'=>'true',
					),$nodeHTML);
				}else{
					$extNode = $this->addNode($catNode, array(
						'FOLDED'=>'true',
						'TEXT'=>htmlspecialchars($niceName),
					) );
				}


				// installed or not icon
				$icon = $installedExt[0][$extKey]['installed'] ? 'button_ok' : 'button_cancel';
				$this->addIcon($extNode,$icon);

				// node for system global or local ext
				$this->addNode($extNode, array(
					// 'FOLDED'=>'true',
					'TEXT'=>$this->types[ $installedExt[0][$extKey]['type'] ],
				) );

				// link to TER
				if( $addTERLink == 1 ){
					$this->addNode($extNode, array(
						// 'FOLDED'=>'true',
						'TEXT'=>$this->translate('tree.linkName2TER'),
						'LINK'=>'http://typo3.org/extensions/repository/view/'.$extKey.'/current/',
					) );
				}


				// displaying the rest of the config
				$emconf = $installedExt[0][$extKey]['EM_CONF'];
				$constraints = $this->humanizeConstraints($emconf['constraints']);
				$emconf['depends'] = $constraints['depends'];
				$emconf['conflicts'] = $constraints['conflicts'];
				$emconf['suggests'] = $constraints['suggests'];
				unset($emconf['title']);
				unset($emconf['constraints']);
				unset($emconf['category']);
				unset($emconf['_md5_values_when_last_written']);


				foreach($emconf as $ek=>$ev){
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

			}/*endforeach2*/

		}/*endforeach*/

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
	 * @return	void		"Returns" content by reference
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
