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
	public function __construct($settings) {
		parent::__construct($settings);

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


		$this->dokTypeIcon['notFound'] = 'typo3/sysext/t3skin/images/icons/apps/toolbar-menu-cache.png';

		$this->dokTypeIcon['news'] = 'typo3/sysext/t3skin/images/icons/apps/pagetree-folder-contains-news.png';
		$this->dokTypeIcon['fe_users'] = 'typo3/sysext/t3skin/images/icons/apps/pagetree-folder-contains-fe_users.png';
		$this->dokTypeIcon['approve'] = 'typo3/sysext/t3skin/images/icons/apps/pagetree-folder-contains-approve.png';
		$this->dokTypeIcon['board'] = 'typo3/sysext/t3skin/images/icons/apps/pagetree-folder-contains-board.png';
		$this->dokTypeIcon['shop'] = 'typo3/sysext/t3skin/images/icons/apps/pagetree-folder-contains-shop.png';

		$this->dokTypeIcon[254] = 'typo3/sysext/t3skin/images/icons/apps/pagetree-folder-default.png';
		$this->dokTypeIcon[1] = 'typo3/sysext/t3skin/images/icons/apps/pagetree-page-default.png';
		// 3 URL
		$this->dokTypeIcon[3] = 'typo3/sysext/t3skin/images/icons/apps/pagetree-page-shortcut-external.png';
		// 4 shortcut
		$this->dokTypeIcon[4] = 'typo3/sysext/t3skin/images/icons/apps/pagetree-page-shortcut.png';
		$this->dokTypeIcon[4000] = 'typo3/sysext/t3skin/images/icons/apps/pagetree-page-shortcut-root.png';

		$this->dokTypeIcon[199] = 'typo3/sysext/t3skin/images/icons/apps/pagetree-spacer.png';

		$this->dokTypeIcon[255] = 'typo3/sysext/t3skin/images/icons/apps/pagetree-page-recycler.png';



	} /* endconstruct */


	/**
	 * gets sys languages
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	SimpleXMLElement
	 */
	public function getSysLanguages(SimpleXMLElement &$xmlNode) {

		// todo isBe reuild as property
		$isBE = stristr($this->settings['mapMode'],'backend') !== false;

		$MainNode = $this->addImgNode($xmlNode,array(
			'FOLDED'=>'true',
			'TEXT'=>$this->translate('tree.syslanguage'),
		), 'typo3/sysext/t3skin/images/icons/mimetypes/x-sys_language.gif'  );

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( 'uid,title,flag,hidden',
			'sys_language', '', '', 'title' );
		while ($r = $GLOBALS['TYPO3_DB']->sql_fetch_assoc ($result)) {
		
			$link = $isBE ? $this->getBEHttpHost().'typo3/alt_doc.php?edit[sys_language]['.$r['uid'].']=edit' : '';
		
			$domainNode = $this->addImgNode($MainNode,	$this->createTLFattr($r['title'],$link), 
				'typo3/sysext/t3skin/images/flags/'.$r['flag'].'.png' 
			);

			if( $r['hidden'] == 1 ){
				$this->addIcon($domainNode, 'button_cancel' );
			}
		}
	
	}
	
	/**
	 * gets sys domains
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	nothing
	 */
	public function getSysDomains(SimpleXMLElement &$xmlNode) {
	
		$pageDomains = array();
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( 'p.title,p.uid as puid,sd.uid,sd.domainName,sd.hidden',
			'sys_domain sd join pages p on sd.pid=p.uid', '', '', 'p.pid,p.sorting,sd.sorting' );
		while ($r = $GLOBALS['TYPO3_DB']->sql_fetch_assoc ($result)) {
			$k = $r['title'].'~#'.$r['puid'];
			if( !isset($pageDomains[$k]) ){ 
				$pageDomains[ $k ] = array(); 
			}
			$pageDomains[ $k ][] = $r;
		}
		
		if( count($pageDomains) > 0 ){
			$MainNode = $this->addImgNode($xmlNode,array(
				'FOLDED'=>'true',
				'TEXT'=>$this->translate('tree.sysdomains'),
			), 'typo3/sysext/t3skin/images/icons/mimetypes/x-content-domain.png'  );
			
			$isBE = stristr($this->settings['mapMode'],'backend') !== false;
			
			foreach($pageDomains as $kt=>$domains){
			
				$ktEx = explode('~#',$kt);
				$ktlink = $isBE ? $this->getBEHttpHost().'typo3/mod.php?&M=web_list&id='.$ktEx[1].'&table=sys_domain' : '';
			
				$titleNode = $this->addNode($MainNode,$this->createTLFattr($ktEx[0],$ktlink) );
				foreach($domains as $kd=>$vd){
				
					$link = $isBE ? $this->getBEHttpHost().'typo3/alt_doc.php?edit[sys_domain]['.$kd.']=edit' : 'http://'.$vd['domainName'];
				
					$domainNode = $this->addNode($titleNode,$this->createTLFattr($vd['domainName'],$link) );

					$this->addIcon($domainNode, $vd['hidden'] == 1 ? 'button_cancel' : 'button_ok' );
	
				}
			}
		} /*endif count*/
		
	}/*endfnc*/


	/**
	 * gets the whole typo3 tree
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	SimpleXMLElement
	 */
	public function getTree(SimpleXMLElement &$xmlNode) {

 
	/* 
echo "<pre>\n\n";
 var_dump( $this->settings );
echo "\n\n</pre><hr>"; exit;
 */
		$this->getTreeRecursive($xmlNode,$this->tree->buffer_idH,-1);

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

			$childs = $this->addNode($firstChild, $this->getAttr___FromPage( $tree->recs[$uid] , $T3mind ) );
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
		$depth++;

		// todo get customization by user from database

		foreach($subTree as $uid=>$childUids){

			$record = $this->tree->recs[$childUids['uid']];

			$attr = array(
				'TEXT'=>'('.$childUids['uid'].') '.$record['title'],
				'LINK'=>$this->getFEHttpHost($uid).'index.php?id='.$childUids['uid'],
			);

			if( $this->settings['mapMode'] == 'backend_tv' ){
				$attr['LINK'] = $this->getBEHttpHost().'typo3conf/ext/templavoila/mod1/index.php?id='.$childUids['uid'];
			}
			if( $this->settings['mapMode'] == 'backend_list' ){
				$attr['LINK'] = $this->getBEHttpHost().'typo3/mod.php?M=web_list&id='.$childUids['uid'];
			}


				// todo to opt the icon ... due to overlays ...
			$iconDokType = !isset($this->dokTypeIcon[$record['doktype']]) ? $this->dokTypeIcon['notFound'] : $this->dokTypeIcon[$record['doktype']];

			// $this->dokTypeIcon

			if( $depth == 0 ){ /* is root */
				$doktypeRoot = $record['doktype']*1000;
				$iconDokType = isset($this->dokTypeIcon[$doktypeRoot]) ? $this->dokTypeIcon[$doktypeRoot] : $this->dokTypeIcon[$record['doktype']];
			}

			/*first 3 levels are folded*/
			if( $depth < 3 && isset($childUids['subrow']) ){ $attr['FOLDED'] = 'true'; }


			/* module icon overwrites all */
			if( !empty($record['module']) ){
				$iconDokType = $this->dokTypeIcon[ $record['module'] ];
			}
			// build internal link to show in the backend the folders, trashcans ,etc
			if( $record['doktype'] > 100 && stristr($this->settings['mapMode'],'backend') !== false ) {
				$attr['LINK'] = $this->getBEHttpHost().'typo3/mod.php?M=web_list&id='.$childUids['uid'];
			}


/*
 echo '<pre>';
 var_dump($depth);
 var_dump($attr);
 var_dump($record);
  echo '</pre><hr/>'; */

			/*if we have a frontend map and the page is hidden ... then disable the LINK */
			if( $record['hidden'] == 1 && $this->settings['mapMode'] == 'frontend' ){
				unset($attr['LINK']);
			}
			
			// if user assigns multiple images then use: addImagesNode
			$pageParent = $this->addImgNode($xmlNode,$attr,$iconDokType);

			// add hidden icon
			if( $record['hidden'] == 1 ){
				$this->addIcon($pageParent,'button_cancel');
			}


			if( isset($childUids['subrow']) ){
				$this->getTreeRecursive($pageParent,$childUids['subrow'],$depth);
			}
		} /*endforeach*/

	}


}
