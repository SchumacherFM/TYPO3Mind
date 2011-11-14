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
	 * @var string
	 */
	public $mmVersion = '0.9.0';


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

		$tree = t3lib_div::makeInstance('Tx_Freemind2_Utility_PageTree');
		$tree->init('');

		$treeStartingRecord = t3lib_BEfunc::getRecord('pages', $treeStartingPoint, implode(',',$tree->fieldArray) );

		// Create the tree from starting point:
		$tree->getTree($treeStartingPoint, 999, '');
		$tree->recs[$treeStartingPoint] = $treeStartingRecord;

		$mmXML = new SimpleXMLElement('<map></map>');
		$mmXML->addAttribute('version',$this->mmVersion);

		$firstChild = $this->addNode($mmXML, $this->getAttrFromPage($treeStartingRecord)  );

		foreach($tree->buffer_idH as $uid=>$childUids){
				
			$childs = $this->addNode($firstChild, $this->getAttrFromPage( $tree->recs[$uid] ) );
		}
	/*
echo '<pre>';  
// echo htmlspecialchars(var_export($mmXML->asXML(),1));
// echo '<hr>'; var_dump($tree->buffer_idH); 
echo '<hr>'; var_dump($tree->recs); 
echo '</pre><hr>';
*/
	
		return $mmXML->asXML();
	} /* end fnc getContent */

	/*
http://freemind.sourceforge.net/wiki/index.php/Asked_questions
how to insert new lines
	Insert &#xa; instead of plain newline. Example of a map with three newlines:


	*/

	/**
	 * Creates a node
	 *
	 * @param	SimpleXMLElement $xml
	 * @param	array $attributes  key is the name and value the value
	 * @return	SimpleXMLElement
	 */
	private function addNode(SimpleXMLElement $xml,$attributes) {

		$child = $xml->addChild('node','');

		foreach($attributes as $k=>$v){
			$child->addAttribute($k,$v);
		}
		
		// add icon if ...
		
		return $child;
	}

	/**
	 * Creates the attributes from a page record
	 *
	 * @param	array $pageRecord
	 * @param	array $additionalAttributes  key is the name and value the value
	 * @return	SimpleXMLElement
	 */
	private function getAttrFromPage(&$pageRecord,$additionalAttributes = array() ) {

		/* now we have here to the the special options from the column tx_freemind2_data from table pages */

		$attr = array(
			/* optional, time is not a real timestamp
			'CREATED'=>'1124560950701',
			'MODIFIED'=>'1225908650678',
			*/
			'COLOR'=>'#407000',
			'FOLDED'=>'false',
			'ID'=>'page_'.$pageRecord['uid'],
			'POSITION'=>'right',
			'TEXT'=>$pageRecord['title'],
		);

		// mvc webrequest -> base uri to build in!
		if( in_array($pageRecord['doktype'], array(1,4) ) ){
			$attr['LINK'] = 'http://'.$_SERVER['HTTP_HOST'].'/index.php?id='.$uid;
		}
		
		return array_merge($attr,$additionalAttributes);
	}


}
