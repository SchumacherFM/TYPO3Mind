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
class Tx_Freemind2_Export_mmExport extends Tx_Freemind2_Export_mmExportCommon implements Tx_Freemind2_Export_mmExportInterface {

	/**
	 * fmConfigRepository
	 *
	 * @var Tx_Freemind2_Domain_Repository_FmConfigRepository
	 */
	protected $fmConfigRepository;

	/**
	 * initializeAction
	 *
	 * @return void
	 */
	public function __construct() {
		$this->fmConfigRepository = t3lib_div::makeInstance('Tx_Freemind2_Domain_Repository_FmConfigRepository');
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

		
		$html = '<h2>'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'].'</h2><p>TYPO3: '.TYPO3_version.'</p>';
		$rootNode = $this->addRichContentNode($mmXML,$attributes,$html);

		
		$mmExportLeftSide = t3lib_div::makeInstance('Tx_Freemind2_Export_mmExportLeftSide');
		$mmExportLeftSide->getTYPONode($rootNode);
		$mmExportLeftSide->getExtensionNode($rootNode);
		$mmExportLeftSide->getDatabaseNode($rootNode);
		$mmExportLeftSide->getServerNode($rootNode);

		
/*
		// Initialize starting point of page tree:
		$treeStartingPoint = $this->pageUid;
		$treeStartingPoint = 0;

		$tree = t3lib_div::makeInstance('Tx_Freemind2_Utility_PageTree');
		$tree->init('');

		// Create the tree from starting point:
		$tree->getTree($treeStartingPoint, 999, '');
		$tree->recs[$treeStartingPoint] = $treeStartingRecord;

		if( $treeStartingPoint > 0 ){
			$treeStartingRecord = t3lib_BEfunc::getRecord('pages', $treeStartingPoint, implode(',',$tree->fieldArray) );
		}else{
		}




		$FmConfig = $this->fmConfigRepository->findOneByPageUid( $treeStartingRecord['uid'] );


		foreach($tree->buffer_idH as $uid=>$childUids){

			$FmConfig = $this->fmConfigRepository->findOneByPageUid($uid);

			$childs = $this->addNode($firstChild, $this->getAttrFromPage( $tree->recs[$uid] , $FmConfig ) );
		}
	*/


/*
echo "<pre>\n\n";
 var_dump($treeStartingRecord);
 echo '<hr>'; var_dump($tree->buffer_idH);
echo '<hr>'; var_dump($tree->recs);
echo "\n\n</pre><hr>";  */



		return $this->finalOutputFile($mmXML);

	} /* end fnc getContent */
 
	

}
