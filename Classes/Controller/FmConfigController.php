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
class Tx_Freemind2_Controller_FmConfigController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * fmConfigRepository
	 *
	 * @var Tx_Freemind2_Domain_Repository_FmConfigRepository
	 */
	protected $fmConfigRepository;

	/**
	 * injectFmConfigRepository
	 *
	 * @param Tx_Freemind2_Domain_Repository_FmConfigRepository $fmConfigRepository
	 * @return void
	 */
	public function injectFmConfigRepository(Tx_Freemind2_Domain_Repository_FmConfigRepository $fmConfigRepository) {
		$this->fmConfigRepository = $fmConfigRepository;
	}

	/**
	 * extConfSettings from the localconf.php
	 *
	 * @var array
	 */
	private $extConfSettings;

	/**
	 * pageUid of the current page
	 *
	 * @var int
	 */
	protected $pageUid;

	/**
	 * apikey the key to authenticate with an eID request
	 *
	 * @var string
	 */
	protected $apikey;

	/**
	 * helpers object
	 *
	 * @var Tx_Freemind2_Utility_Helpers
	 */
	protected $helpers;

	/**
	 * initializeAction
	 *
	 * @return void
	 */
	public function initializeAction() {
		$this->pageUid = (int)t3lib_div::_GET('id');
		$this->apikey = md5(t3lib_div::_GET('apikey'));
		$this->helpers = new Tx_Freemind2_Utility_Helpers;
        $this->extConfSettings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['freemind2']);

		// todo: better error handling
		if( !isset($this->extConfSettings['apikey']) || trim($this->extConfSettings['apikey'])=='' ){
			die('Please set an API Key in the Extension Manager');
		}

	}

	/**
	 * Checks if an entry exists and redirects to the corresponding data row
	 *
	 * @return void
	 */
	public function dispatchAction() {
		if( $this->pageUid == 0 ){
			// todo better error messages .... 8-)
			die('no page uid specified');
		}
	
			$FmConfig = $this->fmConfigRepository->findOneBypageUid( $this->pageUid );

			if( $FmConfig == NULL ){
				$FmConfig = t3lib_div::makeInstance('Tx_Freemind2_Domain_Model_FmConfig');
				$FmConfig->setpageUid( $this->pageUid );
				$this->fmConfigRepository->add($FmConfig);
				$persistenceManager = Tx_Extbase_Dispatcher::getPersistenceManager();
				$persistenceManager->persistAll();
				$FmConfig = $this->fmConfigRepository->findOneBypageUid( $this->pageUid );
			}
			
			
		$T3_THIS_LOCATION = urlencode('mod.php?M=web_list&id='.$this->pageUid);
		$this->view->assign('redirect','alt_doc.php?returnUrl='.$T3_THIS_LOCATION.'&edit[tx_freemind2_domain_model_fmconfig]['.$FmConfig->getUid().']=edit');
		$this->view->assign('page', t3lib_BEfunc::getRecord('pages', $this->pageUid, 'uid,title' ) );
	}
	
	/**
	 * shows the flash browser and load the current tree
	 *
	 * @return void
	 */
	public function browserAction() {

	}

	
	/**
	 * action editPages
	 *
	 * @param $FmConfig
	 * @dontvalidate $FmConfig
	 * @return void
	 */
	public function editPagesAction(Tx_Freemind2_Domain_Model_FmConfig $FmConfig = NULL) {

		if ($FmConfig == NULL) {

			$FmConfig = $this->fmConfigRepository->findOneBypageUid( $this->pageUid );

			if( $FmConfig == NULL ){
				$FmConfig = t3lib_div::makeInstance('Tx_Freemind2_Domain_Model_FmConfig');
				$FmConfig->setpageUid( $this->pageUid );
				$this->fmConfigRepository->add($FmConfig);
			}
		}

/*
echo '<pre>';
var_dump($FmConfig);
die( '</pre>');

*/


		$this->view->assign('FmConfig', $FmConfig );
		$this->view->assign('page', t3lib_BEfunc::getRecord('pages', $this->pageUid, 'uid,title' ) );
		$this->view->assign('icons', $this->fmConfigRepository->getIcons( $this->settings ) );
		$this->view->assign('userIcons', $this->fmConfigRepository->getUserIcons( $this->settings ) );
		$this->view->assign('nodePositions', $this->helpers->trimExplodeVK(',', $this->settings['nodePositions'] ) );
		$this->view->assign('nodeStyles', $this->helpers->trimExplodeVK(',', $this->settings['nodeStyles'] ) );
		$this->view->assign('edgeStyles', $this->helpers->trimExplodeVK(',', $this->settings['edgeStyles'] ) );
		$this->view->assign('edgeWidths', $this->helpers->trimExplodeVK(',', $this->settings['edgeWidths'] ) );

	}

	/**
	 * action editPagesSave
	 *
	 * @param $FmConfig
	 * @param array $options
	 * @return void
	 */
	public function editPagesSaveAction(Tx_Freemind2_Domain_Model_FmConfig $FmConfig, $options ) {

		foreach($options as $k=>$v){
			$options[$k] = (int)$v;
		}

		$this->pageUid = isset($options['pageUid']) ? $options['pageUid'] : 0;

		echo '<pre>';
		var_dump($options);
		echo '<hr/>';
		var_dump($FmConfig);
		echo '</pre>';


		if( $this->pageUid == 0 ){
			$this->redirect('editPages');
		}

		$this->fmConfigRepository->update($FmConfig);

		$this->view->assign('options', $options );
		$this->view->assign('page', t3lib_BEfunc::getRecord('pages', $this->pageUid, 'title' ) );

	}

	/**
	 * action export
	 *
	 * @return void
	 */
	public function exportAction() {

	
		$expObj = t3lib_div::makeInstance('Tx_Freemind2_Export_mmExport');
		$typo3tempFilename = $expObj->getContent();
		
		$this->view->assign('downloadURL', '/typo3temp/'.$typo3tempFilename);
		$this->view->assign('filename', $typo3tempFilename);
	}

	/**
	 * action export via eID
	 http://turmhof/index.php?eID=freemind2&id=6&apikey=tYeAvJ4rgxWSU9C!.mf5k:-dMQq_
	 *
	 * $apikey string the key
	 * @return void
	 */
	public function exportEIDAction() {
		// todo better error handling
		if( $this->apikey <> md5($this->extConfSettings['apikey']) ){
			die('API Keys does not match!');
		}


		// xml header ...

echo '<pre>';
// echo( htmlspecialchars($xml) );
echo 'wrote file to typo3temp';
echo '</pre>';

		exit;


	}

}
