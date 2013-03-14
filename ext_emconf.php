<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "typo3mind".
 *
 * Auto generated 10-11-2012 12:27
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'TYPO3Mind',
	'description' => 'TYPO3Mind V6 is an extension for generating mind mapping files from your whole TYPO3 installation. Mind maps helps you to understand how
	your TYPO3 project has been setup and what the current running status is. Currently you can only export .mm files which can be imported by FreeMind
	(strongly recommended), Freeplane, XMind, Mindjet, MindManager, etc. TYPO3Mind uses the cool icon from FreeMind. This extension hooks into the tree
	click menu and in the left pane. The mind map includes many icons and pictures with URIs to your webserver. You have a lot of configuration options.
	Needs TYPO3 6.0 or later, SimpleXML, PHP5.3 or later.',
	'category' => 'be',
	'author' => 'Cyrill Schumacher',
	'author_email' => 'Cyrill@Schumacher.fm',
	'author_company' => '',
	'shy' => 0,
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'version' => '2.0.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.0.0-0.0.0',
			'cms' => '',
			'extbase' => '',
			'fluid' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => '',
);
