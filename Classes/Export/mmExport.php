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
class Tx_Typo3mind_Export_mmExport extends Tx_Typo3mind_Export_mmExportCommon implements Tx_Typo3mind_Export_mmExportInterface {

	/**
	 * TS settings
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * t3MindRepository
	 *
	 * @var Tx_Typo3mind_Domain_Repository_T3mindRepository
	 */
	protected $t3MindRepository;

	/**
	 * initializeAction
	 *
	 * @return void
	 */
	public function __construct($settings) {
		parent::__construct($settings);
		$this->settings = $settings;
		$this->t3MindRepository = t3lib_div::makeInstance('Tx_Typo3mind_Domain_Repository_T3mindRepository');
	}

	/**
	 * main method to get the content
	 *
	 * @return void
	 */
	public function getContent() {

//		tslib_eidtools::initTCA();
//		t3lib_div::loadTCA('pages');
		// General Includes
//		require_once(PATH_t3lib.'class.t3lib_pagetree.php');
/*
	Structure of the tree in FM:
	Left side system informations like installed extensions, etc
	right side same as the typo3 backend tree
*/


		$mmXML = $this->getMap();

		$attributes = array(
			'COLOR'=>'#993300',
		);
// $temp_TTclassName = t3lib_div::makeInstanceClassName(‘t3lib_timeTrack’);

		$html = '<center><img src="'.$this->getBEHttpHost().'typo3/sysext/t3skin/icons/gfx/loginlogo_transp.gif" alt="TYPO3 Logo" />
		<h2>'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'].'</h2>
		<p style="font-size:10px;">TYPO3: '.TYPO3_version.'</p></center>';
		$rootNode = $this->addRichContentNode($mmXML,$attributes,$html);

		$ThisFileInfoNode = $this->addImgNode($rootNode,array(
			'POSITION'=>'left',
//			'FOLDED'=>'false',
			'TEXT'=>$this->translate('tree.fileInfo'),
		), 'typo3/sysext/about/ext_icon.gif' );



		$this->addNode($ThisFileInfoNode,array(
			'TEXT'=>'Backend HTTP Address: '.$this->getBEHttpHost(),
		));
		$this->addNode($ThisFileInfoNode,array(
			'TEXT'=>'Created: '.date('Y-m-d H:i:s'),
		));
		$this->addNode($ThisFileInfoNode,array(
			'TEXT'=>'MD5 Hash: ###MD5_FILE_HASH####',
		));
		$this->addNode($ThisFileInfoNode,array(
			'TEXT'=>'Map Mode: '.$this->settings['mapMode'],
		));


		$mmExportLeftSide = t3lib_div::makeInstance('Tx_Typo3mind_Export_mmExportLeftSide',$this->settings);
		$mmExportLeftSide->getTYPONode($rootNode);
		$mmExportLeftSide->getExtensionNode($rootNode);
		$mmExportLeftSide->getDatabaseNode($rootNode);
		$mmExportLeftSide->getServerNode($rootNode);

		$mmExportRightSide = t3lib_div::makeInstance('Tx_Typo3mind_Export_mmExportRightSide',$this->settings);
		$mmExportRightSide->getSysLanguages($rootNode);
		$mmExportRightSide->getSysDomains($rootNode);
		
		$mmExportRightSide->sett3mind( $this->t3MindRepository->findAll() );
		
		$mmExportRightSide->getTree($rootNode);

		return $this->finalOutputFile($mmXML);

	} /* end fnc getContent */



}