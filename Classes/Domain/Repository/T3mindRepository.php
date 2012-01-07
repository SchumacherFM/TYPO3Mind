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
 *	http://typo3.org/fileadmin/typo3api-4.0.0/d9/de0/classt3lib__BEfunc.html
 *
 * @package typo3mind
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 */
class Tx_Typo3mind_Domain_Repository_T3mindRepository extends Tx_Extbase_Persistence_Repository {

	/**
	 * Constructor of the repository.
	 * Sets the respect storage page to false.
	 * @param Tx_Extbase_Object_ObjectManagerInterface $objectManager
	 */
	public function __construct(Tx_Extbase_Object_ObjectManagerInterface $objectManager = NULL) {
		parent::__construct($objectManager);
		$this->defaultQuerySettings = new Tx_Extbase_Persistence_Typo3QuerySettings();
		$this->defaultQuerySettings->setRespectStoragePage(FALSE);
	}

	/**
	 * Finds all and returns all them as an array
	 * @param none
	 * @return array
	 */
	public function findAll(){
			$query = $this->createQuery();
			$query->getQuerySettings()->setReturnRawQueryResult(TRUE);
			return $query->execute();
	}
	/**
	 * Gets the icons as an array
	 * @param array $settingsIcons see setup.txt
	 * @return array
	 */
	public function getIcons($settingsIcons){

		$path = t3lib_extMgm::extPath('typo3mind').'Resources/Public/'.$settingsIcons['iconsPath'];

		$icons = scanDir($path,0);

		$icons2 = array();
		foreach($icons as $k=>$v){
			if( preg_match('~(.+)\.(png)$~i',$v,$filename) ){
				$icons2[ $filename[1] ] = $settingsIcons['iconsPath'].$v;
			}
		}
		return $icons2;

	}

	/**
	 * Gets the icons as an array from the user defined folder
	 * @param array $settingsIcons see setup.txt
	 * @return array
	 */
	public function getUserIcons($settingsIcons){

		$path = t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT').'/'.$settingsIcons['userIconsPath'];

		$icons = scanDir($path,0);

		$icons2 = array();
		foreach($icons as $k=>$v){
			if( preg_match('~(.+)\.(png|jpg|gif)$~i',$v,$filename) ){
				$icons2[ $filename[1] ] = $settingsIcons['userIconsPath'].$v;
			}
		}
		return $icons2;
	}

}
