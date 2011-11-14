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
class Tx_Freemind2_Export_mmExport implements Tx_Freemind2_Export_mmExportInterface {

	/**
	 * pageUid of the current page
	 *
	 * @var int
	 */
	protected $pageUid;

	/**
	 * initializeAction
	 *
	 * @param integer $pageUid
	 * @return void
	 */
	public function __construct($pageUid) {
		$this->pageUid = $pageUid;
	
	}

	/**
	 * main method to get the content
	 *
	 * @return void
	 */
	public function getContent() {

	
		tslib_eidtools::initTCA();

//		t3lib_div::loadTCA('pages');
		// General Includes
//		require_once(PATH_t3lib.'class.t3lib_pagetree.php');

		// Initialize starting point of page tree:
		$treeStartingPoint = $this->pageUid;
/*		$treeStartingRecord = t3lib_BEfunc::getRecord('pages', $treeStartingPoint);
*/
		$tree = t3lib_div::makeInstance('Tx_Freemind2_Utility_PageTree');
		$tree->init('');

		// Creating top icon; the current page
		// $HTML = t3lib_iconWorks::getIconImage('pages', $treeStartingRecord, $GLOBALS['BACK_PATH'],'align="top"');
//		$tree->tree[] = array('row' => $treeStartingRecord,'HTML'=>'$HTML' );

		// Create the tree from starting point:
		$tree->getTree($treeStartingPoint, 999, '');

echo '<pre>'; 
var_dump($tree->buffer_idH);
var_dump($tree->recs);
// var_dump($tree->tree);
// echo htmlspecialchars(var_export($tree->tree,1)); 
echo '</pre><hr>';
	
		// $this->getTree( array('id'=>$this->pageUid) );
	
	}


	/**
	 * Clear branch cache action
	 * 
	 * @param	stdClass $nodeData
	 * @return	string Error message for the BE user
	 */
	public function getTree($nodeData) {

		$nodeUids = array();
		$childNodeUids = array();

		$nodeLimit = ($GLOBALS['TYPO3_CONF_VARS']['BE']['pageTree']['preloadLimit']) ? $GLOBALS['TYPO3_CONF_VARS']['BE']['pageTree']['preloadLimit'] : 999;

			/* @var $node t3lib_tree_pagetree_Node */
		$node = t3lib_div::makeInstance('t3lib_tree_pagetree_Node', (array) $nodeData);
echo '<pre>';
var_dump($node);
			// Get uid of actual page
		$nodeUids[] = $node->getId();

			// Get uids of subpages
			/* @var t3lib_tree_pagetree_DataProvider */
	/*	$dataProvider = t3lib_div::makeInstance('t3lib_tree_pagetree_DataProvider', $nodeLimit);
		$nodeCollection = $dataProvider->getNodes($node);
		$childNodeUids = $this->transformTreeStructureIntoFlatArray($nodeCollection);

			// Marge actual and child nodes
		$nodeUids = array_merge($nodeUids, $childNodeUids);
*/

	}

	/**
	 * Recursively transform the node collection from tree structure into a flat array
	 * 
	 * @param	t3lib_tree_NodeCollection $nodeCollection A tree of node
	 * @param	integer $level Recursion counter, used internaly
	 * @return	array Node uids of all child nodes
	 */
	protected function transformTreeStructureIntoFlatArray($nodeCollection, $level = 0) {
		$nodeUids = array();

		if ($level > 99) {
			return array();
		}

		foreach ($nodeCollection as $childNode) {
			$nodeUids[] = $childNode->getId();
			if ($childNode->hasChildNodes()) {
				$nodeUids = array_merge($nodeUids, $this->transformTreeStructureIntoFlatArray($childNode->getChildNodes(), $level + 1));
			} else {
				$nodeUids[] = $childNode->getId();
			}
		}
		return $nodeUids;
	}
	

}
