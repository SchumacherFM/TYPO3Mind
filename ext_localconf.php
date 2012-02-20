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
if (!defined ('TYPO3_MODE')){ 	die ('Access denied.');	}
/*
still not supported by freemind to include files from URLs ... but with a tricky of adjusting the .bat startup file and using wget ...
I think relying on the eID generation of the map with an API key
There are plenty of problems to get the eID feature running ... 
$TYPO3_CONF_VARS['FE']['eID_include']['typo3mind'] = t3lib_extMgm::extPath('typo3mind').'Classes/Utility/eIDDispatcher.php';
*/
