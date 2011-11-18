<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

if(TYPO3_MODE === 'BE') {

	/**
	 * Registers a Backend Module
	 */
	Tx_Extbase_Utility_Extension::registerModule(
		$_EXTKEY,
		'web',	 		// Make module a submodule of 'user'
		'fm2be',		// Submodule key
		'',				// Position
		array(
			'FmConfig' => 'export, exportEID, editPages, editPagesSave',
		),
		array(
			'access' => 'user,group',
			'icon'   => 'EXT:freemind2/ext_icon.gif',
			'labels' => 'LLL:EXT:freemind2/Resources/Private/Language/locallang_fm2be.xml',
		)
	);
	
	$GLOBALS['TYPO3_CONF_VARS']['BE']['defaultUserTSconfig'] .= "\n".'options.contextMenu.table.pages.items {
			# 754 = DIVIDER

			755 = ITEM
			755 {
				name = freemind2ClickMenu
				label = LLL:EXT:freemind2/Resources/Private/Language/locallang_fm2be:contextMenu
				icon = ' . t3lib_div::locationHeaderUrl(t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif') . '
				spriteIcon =
				displayCondition =
				callbackAction = openCustomUrlInContentFrame
				customAttributes.contentUrl = mod.php?M=web_Freemind2Fm2be&tx_freemind2_web_freemind2fm2be%5Baction%5D=editPages&tx_freemind2_web_freemind2fm2be%5Bcontroller%5D=FmConfig&id=###ID###

			}
		';
} /*endif TYPO3_MODE === 'BE'*/

t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'FreeMind2');



t3lib_extMgm::addLLrefForTCAdescr('tx_freemind2_domain_model_fmconfig', 'EXT:freemind2/Resources/Private/Language/locallang_csh_tx_freemind2_domain_model_fmconfig.xml');
t3lib_extMgm::allowTableOnStandardPages('tx_freemind2_domain_model_fmconfig');
$TCA['tx_freemind2_domain_model_fmconfig'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:freemind2/Resources/Private/Language/locallang_db.xml:tx_freemind2_domain_model_fmconfig',
		'label' => 'page_uid',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/FmConfig.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_freemind2_domain_model_fmconfig.gif'
	),
);
