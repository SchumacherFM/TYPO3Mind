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
class tx_fmItemsProcFunc {


	/**
	 * Getting the tree data: frees data handle
	 *
	 * @param	array	$array
	 * @return	array
	 */
	public function arrayKeysEqualValues($array) {
		$a = array();
		foreach($array as $v){
			$a[ $v ] = $v;
		}
		return $a;
	}

	/**
	 * trimExplode VK = value also in keys
	 *
	 * @param	string	$d delimiter
	 * @param	string	$s from the TypoScript settings
	 * @return	array
	 */
	public function trimExplodeVK($d,$s) {

		return $this->arrayKeysEqualValues ( t3lib_div::trimExplode($d, $s ,0 ) );
	}

	/**
	 * trimExplode VK = value also in keys
	 *
	 * @param	array	$params
	 * @param	array	$pObj
	 * @return	array
	 */
	public function getFromTS(&$params, &$pObj) {
		// global $TCA, $LANG;
		// http://typo3.toaster-schwerin.de/typo3_dev/2011_11/msg00089.html
		$typoscriptInclude = '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:typo3mind/Configuration/TypoScript/setup.txt">';

		$TSparserObject = t3lib_div::makeInstance('t3lib_tsparser');
		$configTS = $TSparserObject->checkIncludeLines($typoscriptInclude);
		$TSparserObject->parse($configTS, $matchObj);

		$tsKey = $params['config']['itemsProcFunc_config']['tsKey'];
  		$tsValue = $TSparserObject->setup['module.']['tx_typo3mind.']['settings.'] [$tsKey];



		$params['items'] = array();
		if( isset($params['config']['itemsProcFunc_config']['type']) &&
			$params['config']['itemsProcFunc_config']['type']=='folder' &&
			$tsValue <> '' &&
			is_dir(PATH_site.$tsValue)
		){

			$params['items'] = $this->_getFiles( $tsValue );

		}else{


			$tsValarray = $this->trimExplodeVK(',',$tsValue);

			foreach($tsValarray as $v){
				$vk = $v;
				$vv = $v;

				if( strtolower($vk) == 'default' ){
					switch($params['config']['eval']){
						case 'int':
							$vv = 0;
						break;
						case 'trim':
						case 'trim,nospace':
						case 'alpha':
						case 'alphanum':
						case 'alphanum_x':
							$vv = ' ';
						break;
					}
				}

				$params['items'][] = array($vk,$vv);
			}

		}
	}/*endfnc*/

	/**
	 * recursive read files
	 *
	 * @param	string 	path
	 * @return	array
	 */
	private function _getFiles( $path ) {
			$subA = $fileArray = array();

			if( preg_match('~(\.svn|\.git)~i',$path) ){
				return array();
			}

			$pics = scandir(PATH_site.$path);

			foreach($pics as $v){
				$subPath = $path.$v.'/';
				if( $v!='.' && $v != '..' && is_dir(PATH_site.$subPath) ){
					$subA = $this->_getFiles( $subPath );
					$fileArray = array_merge($fileArray,$subA);
				}
				if( preg_match('~\.(png|jpg|gif|jpeg|webp)$~i',$v) ){
					$fileArray[] = array($v,$v,'../'.$path.$v );
				}
			}

			if( count($fileArray) == 0 ){
					$fileArray[] = array('No files found! Bad permissions? '.$path,false,'bad permissions!' );
			}
		return $fileArray;
	}

}
