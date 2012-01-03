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
	 * the whole tree
	 * @var Tx_Typo3mind_Utility_PageTree
	 */
	protected $tree;

	/**
	 * fetches all tables with 10 rows for a sysfolder ID
	 * @var Tx_Typo3mind_Utility_DbList
	 */
	protected $dbList;


	/**
	 * icons by doktype
	 * @var array
	 */
	protected $dokTypeIcon;

	/**
	 * T3Mind Config for each page ...
	 * @var array
	 */
	protected $t3mind;

	/**
	 * Interpolation colors from RGB to HSL to RGB with an interpolation factor
	 * @var Tx_Typo3mind_Utility_RGBinterpolate
	 */
	protected $RGBinterpolate;

	
	/**
	 * __constructor
	 *
	 * @param array $settings
	 * @param Tx_Typo3mind_Domain_Repository_T3mindRepository $t3MindRepository
	 * @return void
	 */
	public function __construct(array $settings,Tx_Typo3mind_Domain_Repository_T3mindRepository $t3MindRepository) {
		parent::__construct($settings,$t3MindRepository);

		$this->RGBinterpolate = t3lib_div::makeInstance('Tx_Typo3mind_Utility_RGBinterpolate');

		
		$this->tree = t3lib_div::makeInstance('Tx_Typo3mind_Utility_PageTree');
		$this->tree->init('');
		$this->tree->getTree(0, 999, '');

		$this->dbList = t3lib_div::makeInstance('Tx_Typo3mind_Utility_DbList',$this);


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
	 * sets the t3mind array, keys are the pageUid, for performance reasons
	 *
	 * @param	array $t3MindRepositoryFindAll
	 * @return	nothing
	 */
	public function sett3mind($t3MindRepositoryFindAll) {

		foreach($t3MindRepositoryFindAll as $k=>$v){
			unset($v['l10n_parent']);
			unset($v['l10n_diffsource']);
			$v['node_folded'] = ($v['node_folded']==1 ? 'true' : 'false'); /*must be a string*/
			$v['font_bold'] = ($v['font_bold']==1 ? 'true' : 'false'); /*must be a string*/
			$v['font_italic'] = ($v['font_italic']==1 ? 'true' : 'false'); /*must be a string*/
			$this->t3mind[ $v['page_uid'] ] = $v;
		}
		unset($t3MindRepositoryFindAll);
	}

	/**
	 * gets sys languages
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	SimpleXMLElement
	 */
	public function getSysLanguages(SimpleXMLElement $xmlNode) {

		$MainNode = $this->addImgNode($xmlNode,array(
			'FOLDED'=>'true',
			'TEXT'=>$this->translate('tree.syslanguage'),
		), 'typo3/sysext/t3skin/images/icons/mimetypes/x-sys_language.gif'  );

		// todo find out what the default language is ... currently it is 0 but how to access config.language
		// $GLOBALS['TSFE']->config['config']['language'];
		// sure DE is not default ...
		$domainNode = $this->addImgNode($MainNode,	$this->createTLFattr('(0) '.$this->getSysLanguageDetails(0,'title'),''),
			$this->getSysLanguageDetails(0,'flag')
		);


		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( 'uid,title,flag,hidden',
			'sys_language', '', '', 'title' );
		while ($r = $GLOBALS['TYPO3_DB']->sql_fetch_assoc ($result)) {

			$link = $this->mapMode['isbe'] ? $this->getBEHttpHost().'typo3/alt_doc.php?edit[sys_language]['.$r['uid'].']=edit' : '';

			$domainNode = $this->addImgNode($MainNode,	$this->createTLFattr('('.$r['uid'].') '.$r['title'],$link),
				'typo3/sysext/t3skin/images/flags/'.$r['flag'].'.png'
			);

			if( $r['hidden'] == 1 ){
				$this->addIcon($domainNode, 'closed' );
			}
		}

	}

	/**
	 * gets sys domains
	 *
	 * @param	SimpleXMLElement $xmlNode
	 * @return	nothing
	 */
	public function getSysDomains(SimpleXMLElement $xmlNode) {

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

			foreach($pageDomains as $kt=>$domains){

				$ktEx = explode('~#',$kt);
				$ktlink = $this->mapMode['isbe'] ? $this->getBEHttpHost().'typo3/mod.php?&M=web_list&id='.$ktEx[1].'&table=sys_domain' : '';

				$titleNode = $this->addNode($MainNode,$this->createTLFattr($ktEx[0],$ktlink) );
				foreach($domains as $kd=>$vd){

					$link = $this->mapMode['isbe'] ? $this->getBEHttpHost().'typo3/alt_doc.php?edit[sys_domain]['.$kd.']=edit' : 'http://'.$vd['domainName'];

					$domainNode = $this->addNode($titleNode,$this->createTLFattr($vd['domainName'],$link) );

					$this->addIcon($domainNode, $vd['hidden'] == 1 ? 'closed' : 'button_ok' );

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
	public function getTree(SimpleXMLElement $xmlNode) {

		/* COLOR TESTER
		$c1 = '#996600';
		echo '<div style="width:500px;height:50px; background-color:'.$c1.';">'.$c1.'</div>';
		for($i=0;$i<15;$i++){
			$c2 = $this->RGBinterpolate->interpolate( $c1, '#ffffff', 0.075 ); // last value depends on the depth of the tree
			echo '<div style="margin:3px;padding:2px;width:50px;height:50px; background-color:'.$c2.'; float:left;">'.$c2.'</div>';
			$c1 = $c2;
		}
		exit;
		*/

		$this->getTreeRecursive($xmlNode, $this->tree->buffer_idH, -1, NULL);

	}

	/**
	 * recursive tree printing - first time is for ... nothing, therefore we start with -1
	 *
	 * @param	SimpleXMLElement 	$xmlNode
	 * @param	array				$subTree
	 * @param	integer				$depth
	 * @param	array				$t3mind		for recursive mode!
	 * @return	SimpleXMLElement
	 */
	private function getTreeRecursive(SimpleXMLElement $xmlNode,$subTree,$depth = 0,$t3mind = NULL) {
		$depth++;

		$alternatingColors = array();
		if( is_array($t3mind) ){ /* only for recurisve mode use previous color ... */
			$alternatingColors['cloud'] = $t3mind['cloud_color'];
			$alternatingColors['node'] = $t3mind['node_color'];
			$alternatingColors['font'] = $t3mind['font_color'];
			$alternatingColors['edge'] = $t3mind['edge_color'];
		}

		foreach($subTree as $uid=>$childUids){

			$record = $this->tree->recs[$childUids['uid']];

			/* setting properties */
			$useConfigUID = $configUID == 0 ? $uid : $configUID;

			$t3mindCurrent = isset($t3mind) ? $t3mind : (isset($this->t3mind[$uid]) ? $this->t3mind[$uid] : NULL);

			$hasMoreThanOneChild = ( isset($childUids['subrow']) && count($childUids['subrow']) > 1 ) ? true : false;
			$isRecursive = (int)$t3mindCurrent['recursive'] == 1 ? true : false;


			$attr = array(
				'TEXT'=>'('.$childUids['uid'].') '.$record['title'],
				'LINK'=>$this->getFEHttpHost($uid).'index.php?id='.$childUids['uid'],
			);

			if( $this->mapMode['befe'] == 'backend_tv' ){
				$attr['LINK'] = $this->getBEHttpHost().'typo3conf/ext/templavoila/mod1/index.php?id='.$childUids['uid'];
			}
			if( $this->mapMode['befe'] == 'backend_list' ){
				$attr['LINK'] = $this->getBEHttpHost().'typo3/mod.php?M=web_list&id='.$childUids['uid'];
			}


				// todo to opt the icon ... due to overlays ...
			$iconDokType = !isset($this->dokTypeIcon[$record['doktype']]) ? $this->dokTypeIcon['notFound'] : $this->dokTypeIcon[$record['doktype']];

			// $this->dokTypeIcon

			if( $depth == 0 ){ /* is root */
				$doktypeRoot = $record['doktype']*1000;
				$iconDokType = isset($this->dokTypeIcon[$doktypeRoot]) ? $this->dokTypeIcon[$doktypeRoot] : $this->dokTypeIcon[$record['doktype']];
			}


			/* module icon overwrites all */
			if( !empty($record['module']) ){
				$iconDokType = $this->dokTypeIcon[ $record['module'] ];
			}
			// build internal link to show in the backend the folders, trashcans ,etc
			if( $record['doktype'] > 100 && $this->mapMode['isbe'] ) {
				$attr['LINK'] = $this->getBEHttpHost().'typo3/mod.php?M=web_list&id='.$childUids['uid'];
			}


			/*if we have a frontend map and the page is hidden ... then disable the LINK */
			if( $record['hidden'] == 1 && $this->mapMode['befe'] == 'frontend' ){
				unset($attr['LINK']);
			}

			// isRecursive, the color of the node name, not the bg color.
			$attr = $this->setAttr($t3mindCurrent,'font_color',$attr,'COLOR');

			/*
				TESTING:
				IF we have a cloud with a color, then the node itself has the opposide color!

			if( $hasMoreThanOneChild && $isRecursive && $t3mindCurrent['cloud_is']==1 && !empty($t3mindCurrent['cloud_color']) ){
				$attr['COLOR'] = $this->RGBinterpolate->inverse($t3mindCurrent['cloud_color']);
			} */

			if( isset($t3mindCurrent['node_color']) && $t3mindCurrent['node_color'] <> '' ){

				$attr = $this->setAttr($t3mindCurrent,'node_color',$attr,'BACKGROUND_COLOR');

				if($isRecursive && !empty($alternatingColors['node'])){
					$attr['BACKGROUND_COLOR'] = $alternatingColors['node'] = $this->RGBinterpolate->interpolate(
						$alternatingColors['node'], '#ffffff', 0.075
					);

				}
			}


			if( isset($childUids['subrow']) ){ 
				$attr = $this->setAttr($t3mindCurrent,'node_folded',$attr,'FOLDED');
			}
			$attr = $this->setAttr($t3mindCurrent,'node_style',$attr,'STYLE');

			/*first 3 levels are folded */
			if( $this->settings['nodeAutoFold'] == 1 && $depth < 3 && isset($childUids['subrow']) ){ 
				$attr['FOLDED'] = 'true'; 
			}


			// if user assigns multiple images then use: addImagesNode
			if( isset($this->t3mind[$uid]) && !empty($this->t3mind[$uid]['node_user_icon']) ){

				$ui = $this->t3mind[$uid]['node_user_icon'];
				$iconArray = array( array('path'=>$iconDokType) );
				$uicons = t3lib_div::trimExplode(',',$ui,1);
				foreach($uicons as $k=>$name){
					$iconArray[] = array('path'=>$this->settings['userIconsPath'].$name);
				}
				/* @TODO implement function addImageNote() */
				$pageParent = $this->addImagesNode($xmlNode,$attr,$iconArray,1);
			} else {
			
				/* @TODO defined in the table model config to show details for a page OR not! */
				if( (int)$this->settings['ShowExtendedDetailsInPageTree'] == 1 ){
					$htmlContent = $this->getNoteContentFromRow('pages',$record);
					$pageParent = $this->addImgNote($xmlNode,$attr,$iconDokType,'',$htmlContent);
				}else{
					$pageParent = $this->addImgNode($xmlNode,$attr,$iconDokType);
				}
				
			}

			if( is_array($t3mindCurrent) ){

				/*<add cloud>*/
				if( $hasMoreThanOneChild && $t3mindCurrent['cloud_is']==1 ){

					$color = $t3mindCurrent['cloud_color'];
					if($isRecursive && !empty($color) && !empty($alternatingColors['cloud']) ){
						/* last value depends on the depth of the tree */
						$color = $alternatingColors['cloud'] = $this->RGBinterpolate->interpolate( $alternatingColors['cloud'], '#ffffff', 0.075 );
						// $this->RGBinterpolate->getColor();
					}

					$this->addCloud($pageParent,array('COLOR'=>$color));
				}
				/*</add cloud>*/


				/*<add Edge>*/
				$subNodeAttr = array();
				// array('#FFFF00','STYLE'=>'sharp_bezier', 'WIDTH'=>'thin')
				$subNodeAttr = $this->setAttr($t3mindCurrent,'edge_color',$subNodeAttr,'COLOR');
				$subNodeAttr = $this->setAttr($t3mindCurrent,'edge_style',$subNodeAttr,'STYLE');
				$subNodeAttr = $this->setAttr($t3mindCurrent,'edge_width',$subNodeAttr,'WIDTH');

				if($isRecursive &&  !empty($subNodeAttr['COLOR']) && !empty($alternatingColors['edge']) ){
					$subNodeAttr['COLOR'] = $alternatingColors['edge'] = $this->RGBinterpolate->interpolate( $alternatingColors['edge'], '#ffffff', 0.075 );
					// $this->RGBinterpolate->getColor();
				}

				if( count($subNodeAttr)>0 ){
					$this->addEdge($pageParent,$subNodeAttr);
				}
				/*</add Edge>*/


				/*<add font>*/

/*				if($isRecursive && !empty($subNodeAttr['COLOR']) && !empty($alternatingColors['font']) ){
					$subNodeAttr['COLOR'] = $alternatingColors['font'] = $this->RGBinterpolate->interpolate( $alternatingColors['font'], '#ffffff', 0.075 );
					// $this->RGBinterpolate->getColor();
				} */

				$this->setNodeFont($pageParent,$t3mindCurrent);
				/*</add font>*/

				/*<add node icon> not recursive*/
				if( isset($this->t3mind[$uid]) && !empty($this->t3mind[$uid]['node_icon']) ){
					$this->addIcon($pageParent,$t3mindCurrent['node_icon']);
				}

				/*</add node icon>*/


			}/* endif isset $t3mindCurrent */

			// add hidden icon
			if( $record['hidden'] == 1 ){
				$this->addIcon($pageParent,'closed');
			}


			if( isset($childUids['subrow']) && count($childUids['subrow']) > 0 ){

				/* start recursive mode */
				$subT3mindCurrent = NULL;
				if( is_array($t3mindCurrent) && $t3mindCurrent['recursive']==1 ){ // is always recursive!
					$subT3mindCurrent = $t3mindCurrent;

					if( is_array($t3mind) /*from the recursion*/ ){
		$subT3mindCurrent['cloud_color'] = empty($alternatingColors['cloud']) ? $subT3mindCurrent['cloud_color'] : $alternatingColors['cloud'];
		$subT3mindCurrent['edge_color'] = empty($alternatingColors['edge']) ? $subT3mindCurrent['edge_color'] : $alternatingColors['edge'];
		$subT3mindCurrent['node_color'] = empty($alternatingColors['node']) ? $subT3mindCurrent['node_color'] : $alternatingColors['node'];
					}
				}

				$this->getTreeRecursive($pageParent,$childUids['subrow'],$depth,$subT3mindCurrent);

			}
			/* IF we have a sysfolder .. then list it's content */
			if( $record['doktype'] == 254 ){
				$this->dbList->getTRsysFolderContent($pageParent,$uid,$depth,$t3mindCurrent);
			}
		} /*endforeach*/

	}


}
