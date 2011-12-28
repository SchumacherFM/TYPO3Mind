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

########################################################################
# Extension Manager/Repository config file for ext: "typo3mind"
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'TYPO3Mind',
	'description' => 'TYPO3Mind is an extension for generating mind mapping files from your whole TYPO3 installation. Mind maps helps you to understand how your TYPO3 project has been setup and what the current running status is. Currently you can only export .mm files which can be imported by FreeMind (strongly recommended), Freeplane, XMind, Mindjet, MindManager, etc. TYPO3Mind uses the cool icon from FreeMind. This extension hooks into the tree click menu and in the left pane. The mind map includes many icons and pictures with URIs to your webserver. You have a lot of configuration options. Needs TYPO3 4.5 or later, SimpleXML, PHP5.2 or later. Export via eID is also possible.',
	'category' => 'be',
	'author' => 'Cyrill Schumacher',
	'author_email' => 'Cyrill@Schumacher.fm',
	'author_company' => '',
	'shy' => 0,
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'version' => '0.0.13',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'extbase' => '',
			'fluid' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);
