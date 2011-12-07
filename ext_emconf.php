<?php

########################################################################
# Extension Manager/Repository config file for ext: "typo3mind"
#
# Auto generated by Extension Builder 2011-11-12
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'TYPO3Mind',
	'description' => 'TYPO3Mind is an extension for generating mind mapping files. Mind maps helps you to understand how your whole TYPO3 installation has been setup and what the current running status is. Currently you can only export .mm files which can be imported by FreeMind, Freeplane, XMind, Mindjet, MindManager, etc. TYPO3Mind uses the cool icon from FreeMind. This extension hooks into the tree click menu and in the left pane. The mind map includes many icons and pictures with URIs to your webserver. Each page of the page tree can be configured for itself. Needs TYPO3 4.5 or later, SimpleXML, PHP5. Export via eID is also possible.',
	'category' => 'be',
	'author' => 'Cyrill Schumacher',
	'author_email' => 'Cyrill@Schumacher.fm',
	'author_company' => '',
	'shy' => 0,
	'priority' => '',
	'module' => '',
	'state' => 'experimental',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'version' => '0.0.6',
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
