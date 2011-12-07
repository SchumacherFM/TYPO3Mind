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
class Tx_Typo3mind_Export_mmExportRightSide extends Tx_Typo3mind_Export_mmExportCommon {

	/**
	 * @var SimpleXMLElement
	 */
	protected $xmlParentNode;

	/**
	 * @var object
	 */
	protected $SYSLANG;

	/**
	 * the whole tree
	 * @var object
	 */
	protected $tree;

	/**
	 * icons by doktype
	 * @var array
	 */
	protected $dokTypeIcon;



	/**
	 * initializeAction
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
		$this->SYSLANG = t3lib_div::makeInstance('language');
		$this->SYSLANG->init('default');	// initalize language-object with actual language
/*		$this->categories = array(
			'be' => $this->SYSLANG->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_BE'),
			'module' => $this->SYSLANG->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_BE_modules'),
			'fe' => $this->SYSLANG->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_FE'),
			'plugin' => $this->SYSLANG->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_FE_plugins'),
			'misc' => $this->SYSLANG->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_miscellanous'),
			'services' => $this->SYSLANG->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_services'),
			'templates' => $this->SYSLANG->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_templates'),
			'example' => $this->SYSLANG->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_examples'),
			'doc' => $this->SYSLANG->sL('LLL:EXT:lang/locallang_mod_tools_em.xml:category_documentation'),
			'' => 'none'
		);
*/
		$this->tree = t3lib_div::makeInstance('Tx_Typo3mind_Utility_PageTree');
		$this->tree->init('');
		$this->tree->getTree(0, 999, '');

		$this->dokTypeIcon = array();
		$this->dokTypeIcon[254] = 'typo3/sysext/t3skin/images/icons/apps/pagetree-folder-default.png';
		$this->dokTypeIcon[1] = 'typo3/sysext/t3skin/images/icons/apps/pagetree-page-default.png';
		// 3 URL
		$this->dokTypeIcon[3] = 'typo3/sysext/t3skin/images/icons/apps/pagetree-page-shortcut-external.png';
		// 4 shortcut
		$this->dokTypeIcon[4] = 'typo3/sysext/t3skin/images/icons/apps/pagetree-page-shortcut.png';
		$this->dokTypeIcon[199] = 'typo3/sysext/t3skin/images/icons/apps/pagetree-spacer.png';
		$this->dokTypeIcon[254] = 'typo3/sysext/t3skin/images/icons/apps/pagetree-page-default.png';
		$this->dokTypeIcon[255] = 'typo3/sysext/t3skin/images/icons/apps/pagetree-page-recycler.png';

	} /* endconstruct */



	/**
	 * gets the whole typo3 tree
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	SimpleXMLElement
	 */
	public function getTree(SimpleXMLElement &$xmlNode) {

/*		$pageTreeRoot = $this->addNode($xmlNode,array(
			'TEXT'=>'Page Tree',
			// 'FOLDED'=>'true',
		));	*/
	/*
echo "<pre>\n\n";
 var_dump($this->tree->recs);
echo "\n\n</pre><hr>"; exit;
*/
		$this->getTreeRecursive($xmlNode,$this->tree->buffer_idH,0);
		
/*
		// Initialize starting point of page tree:
		$treeStartingPoint = $this->pageUid;
		$treeStartingPoint = 0;


		// Create the tree from starting point:
		$tree->recs[$treeStartingPoint] = $treeStartingRecord;

		if( $treeStartingPoint > 0 ){
			$treeStartingRecord = t3lib_BEfunc::getRecord('pages', $treeStartingPoint, implode(',',$tree->fieldArray) );
		}else{
		}




		$T3mind = $this->t3MindRepository->findOneByPageUid( $treeStartingRecord['uid'] );


		foreach($tree->buffer_idH as $uid=>$childUids){

			$T3mind = $this->t3MindRepository->findOneByPageUid($uid);

			$childs = $this->addNode($firstChild, $this->getAttrFromPage( $tree->recs[$uid] , $T3mind ) );
		}
	*/		


	}

	/**
	 * recursive tree printing - first time is for ... nothing
	 *
	 * @param	SimpleXMLElement 	$xmlNode
	 * @param	array				$subTree
	 * @param	integer				$depth
	 * @return	SimpleXMLElement
	 */
	private function getTreeRecursive(SimpleXMLElement &$xmlNode,$subTree,$depth = 0) {
	
		foreach($subTree as $uid=>$childUids){

			$record = $this->tree->recs[$childUids['uid']];
		
			$attr = array(
				'TEXT'=>'('.$childUids['uid'].') '.$record['title'],
				'LINK'=>$this->httpHost.'index.php?id='.$childUids['uid'],
			);
			
				// todo to opt the icon ... due to overlays ...
			$iconDokType = $record['hidden'] == 1 ? 'typo3/sysext/t3skin/icons/gfx/hidden_page.gif' : $this->dokTypeIcon[$record['doktype']];
/* echo "<pre>\n\n";
 var_dump($depth);
 var_dump($attr);
echo "\n\n</pre><hr>"; exit; */
			

			if( $depth == 1 ){
				$attr['FOLDED'] = 'true';
			}
			$pageParent = $this->addImgNode($xmlNode,$attr,$iconDokType);
			
			if( isset($childUids['subrow']) ){ 
				$depth++;
				$this->getTreeRecursive($pageParent,$childUids['subrow'],$depth); 
			}
		} /*endforeach*/

	}
	
	
}
