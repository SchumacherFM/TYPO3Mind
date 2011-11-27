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



class Tx_Freemind2_Utility_Helpers {


	/**
	 * Getting the tree data: frees data handle
	 *
	 * @param	array	$array
	 * @return	array
	 */
	public static function arrayKeysEqualValues($array) {
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
	public static function trimExplodeVK($d,$s) {
	
		return $this->arrayKeysEqualValues ( t3lib_div::trimExplode($d, $s ,1 ) );
	}
	
	/**
	 * trimExplode VK = value also in keys
	 *
	 * @param	string	$d delimiter
	 * @param	string	$s from the TypoScript settings
	 * @return	array
	 */
	public static function getFromTS($settingsName) {
		return array('3331','333');

	}
}
