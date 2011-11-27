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



class tx_fmItemsProcFunc {


	/**
	 * Getting the tree data: frees data handle
	 *
	 * @param	array	$array
	 * @return	array
	 */
	public function arrayKeysEqualValues($array) {
		$a = array();
		foreach($array as $k=>$v){
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
	
		return $this->arrayKeysEqualValues ( t3lib_div::trimExplode($d, $s ,1 ) );
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

		$typoscriptInclude = '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:freemind2/Configuration/TypoScript/setup.txt">';

		$TSparserObject = t3lib_div::makeInstance('t3lib_tsparser');
		$configTS = $TSparserObject->checkIncludeLines($typoscriptInclude);
		$TSparserObject->parse($configTS, $matchObj);
  
		$tsKey = $params['config']['itemsProcFunc_config']['tsKey'];
  		$tsValue = $TSparserObject->setup['module.']['tx_freemind2.']['settings.'] [$tsKey];
  
    
		$params['items'] = array();
		if( isset($params['config']['itemsProcFunc_config']['type']) &&
			$params['config']['itemsProcFunc_config']['type']=='folder' && 
			is_dir(PATH_site.$tsValue) 
		){
			$pics = scandir(PATH_site.$tsValue);
			foreach($pics as $k=>$v){
				if( preg_match('~\.(png|jpg|gif|jpeg|webp)$~i',$v) ){
					$params['items'][] = array($v,$v,'../'.$tsValue.$v);
				
				}
			}
		}else{

		
			$tsValarray = $this->trimExplodeVK(',',$tsValue);

			foreach($tsValarray as $k=>$v){
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
		/*	
	echo '<pre>';
  var_dump($tsValarray);
  exit;	*/
		}
		
		

	}
}
