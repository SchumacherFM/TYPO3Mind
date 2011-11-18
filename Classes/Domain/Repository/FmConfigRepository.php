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
class Tx_Freemind2_Domain_Repository_FmConfigRepository extends Tx_Extbase_Persistence_Repository {

	/**
	 *	@param array $settingsIcons see setup.txt
	 */
	public function getIcons($settingsIcons){

		$path = preg_replace('~^ext:~i','typo3conf/ext/',$settingsIcons['path']);
	
		$icons = t3lib_div::trimExplode(';',$settingsIcons['list'],1);
		
		echo '<pre>';
		var_dump($icons);
		die( '</pre>');
	
	}

}
