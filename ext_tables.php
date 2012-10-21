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
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

if (TYPO3_MODE === 'BE') {

    /**
     * Registers a Backend Module
     */
    Tx_Extbase_Utility_Extension::registerModule(
        $_EXTKEY,
        'web', // Make module a submodule of 'user'
        'fm2be', // Submodule key
        '', // Position
        array(
            'T3mind' => 'export, dispatch, editPages, editPagesSave',
        ),
        array(
            'access' => 'user,group',
            'icon' => 'EXT:typo3mind/ext_icon.gif',
            'labels' => 'LLL:EXT:typo3mind/Resources/Private/Language/locallang_fm2be.xml',
        )
    );

    $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultUserTSconfig'] .= "\n" . 'options.contextMenu.table.pages.items {
			755 = ITEM
			755 {
				name = typo3mindClickMenu
				label = LLL:EXT:typo3mind/Resources/Private/Language/locallang_fm2be:contextMenu
				icon = ' . t3lib_div::locationHeaderUrl(t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif') . '
				spriteIcon =
				displayCondition =
				callbackAction = openCustomUrlInContentFrame
				customAttributes.contentUrl = mod.php?M=web_Typo3mindFm2be&tx_typo3mind_web_typo3mindfm2be%5Baction%5D=dispatch&tx_typo3mind_web_typo3mindfm2be%5Bcontroller%5D=T3mind&id=###ID###
			}
		';
} /*endif TYPO3_MODE === 'BE'*/

t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'TYPO3Mind');

include_once(t3lib_extMgm::extPath($_EXTKEY) . 'Classes/Utility/class.tx_fmItemsProcFunc.php');


t3lib_extMgm::addLLrefForTCAdescr('tx_typo3mind_domain_model_t3mind', 'EXT:typo3mind/Resources/Private/Language/locallang_csh_tx_typo3mind_domain_model_t3mind.xml');
t3lib_extMgm::allowTableOnStandardPages('tx_typo3mind_domain_model_t3mind');
$TCA['tx_typo3mind_domain_model_t3mind'] = array(
    'ctrl' => array(
        'title' => 'LLL:EXT:typo3mind/Resources/Private/Language/locallang_db.xml:tx_typo3mind_domain_model_t3mind',
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
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/T3mind.php',
        'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_typo3mind_domain_model_t3mind.gif'
    ),
);
