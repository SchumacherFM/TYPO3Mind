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
			'doc' => $this->SYSLANG->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_documentation')
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

//		echo '<pre>'; var_dump( $categories ); exit;

		foreach( $installedExt[1]['cat'] as $catName => $ext ){

			$catNode = $this->addNode($ChildFirst_Extensions, array(
				// 'FOLDED'=>'true',
				'TEXT'=>$categories[$catName],
			) );

		}/*endforeach*/

		/*
		$extList = t3lib_div::trimExplode(',',$GLOBALS['TYPO3_CONF_VARS']['EXT']['extList'],1);
		sort($extList);
		foreach($extList as $k=>$extKey){

			$isLoaded = t3lib_extMgm::isLoaded($extKey);

			// include local ext_emconf
			$extDetails = $this->setEmconf(PATH_typo3conf . 'ext/' . $extKey . '/ext_emconf.php', $extKey);
			// if not then if could be a sysext
			if (!$extDetails) {
				$extDetails = $this->setEmconf(PATH_typo3 . 'sysext/' . $extKey . '/ext_emconf.php', $extKey);
			}
	//		echo '<pre>'; var_dump($extDetails); exit;


			$icon = $isLoaded ? 'button_ok' : 'button_cancel';
			$this->addIcon($extNode,$icon);

			unset( $extDetails['title'] );
			unset( $extDetails['constraints'] );
			unset( $extDetails['suggests'] );
			unset( $extDetails['_md5_values_when_last_written'] );


			foreach($extDetails as $edk=>$edv){
				if( !empty($edv) ){
					$this->addNode($extNode,array(
						'TEXT'=>ucfirst($edk).': '.htmlentities($edv, ENT_QUOTES | ENT_IGNORE, "UTF-8"),

					));
				}
			}


		} *endforeach*/

		return $ChildFirst_Extensions;
	}



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








}
