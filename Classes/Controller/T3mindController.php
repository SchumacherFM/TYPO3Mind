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
class Tx_Typo3mind_Controller_T3mindController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * t3MindRepository
	 *
	 * @var Tx_Typo3mind_Domain_Repository_T3mindRepository
	 */
	protected $t3MindRepository;

	/**
	 * injectT3mindRepository
	 *
	 * @param Tx_Typo3mind_Domain_Repository_T3mindRepository $t3MindRepository
	 * @return void
	 */
	public function injectT3mindRepository(Tx_Typo3mind_Domain_Repository_T3mindRepository $t3MindRepository) {
		$this->t3MindRepository = $t3MindRepository;
	}

	/**
	 * @var Tx_Typo3mind_Domain_Export_mm $t3mmExport
	 */
	protected $t3mmExport;

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
	 * @var Tx_Typo3mind_Utility_Helpers
	 */
	protected $helpers;

	/**
	 * time track
	 *
	 * @var t3lib_timetrack
	 */
	protected $tt;

	/**
	 * Checks if the pageUid has been set, if not throws an error
	 * @param string $position
	 */
	private function checkPageUid($position){
		if( $this->pageUid == 0 ){
			throw new Exception( $this->translate('error.missingPageUid') .' / Position: '.$position );
		}
	}

	/**
	 * initializeAction
	 *
	 * @return void
	 */
	public function initializeAction() {
		$this->pageUid = (int)t3lib_div::_GET('id');
		$this->apikey = md5(t3lib_div::_GET('apikey'));
		$this->helpers = new Tx_Typo3mind_Utility_Helpers();
        $this->extConfSettings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['typo3mind']);
		$this->tt = t3lib_div::makeInstance('t3lib_timetrack');
		$this->tt->start();
		$this->t3mmExport = new Tx_Typo3mind_Domain_Export_mmMain($this->settings,$this->t3MindRepository);


		if( !isset($this->extConfSettings['apikey']) || trim($this->extConfSettings['apikey'])=='' ){
			throw new Exception('Please set an API Key in the Extension Manager. 1336458461');
		}

	}

	/**
	 * Checks if an entry exists and redirects to the corresponding data row
	 *
	 * @return void
	 */
	public function dispatchAction() {

		$this->checkPageUid(__LINE__.'/1336458130');

		$T3mind = $this->t3MindRepository->findOneBypageUid( $this->pageUid );

		if( $T3mind == NULL ){
			$T3mind = new Tx_Typo3mind_Domain_Model_T3mind();
			$T3mind->setpageUid( $this->pageUid );
			$this->t3MindRepository->add($T3mind);
			$persistenceManager = Tx_Extbase_Dispatcher::getPersistenceManager();
			$persistenceManager->persistAll();
			$T3mind = $this->t3MindRepository->findOneBypageUid( $this->pageUid );
		}

		$T3_THIS_LOCATION = urlencode('mod.php?M=web_list&id='.$this->pageUid);
		$this->view->assign('redirect','alt_doc.php?returnUrl='.$T3_THIS_LOCATION.'&edit[tx_typo3mind_domain_model_t3mind]['.$T3mind->getUid().']=edit');
		$this->view->assign('page', t3lib_BEfunc::getRecord('pages', $this->pageUid, 'uid,title' ) );
	}
	/**
	 * action export
	 * @param none
	 * @return void
	 */
	public function exportAction() {
		$this->checkPageUid(__LINE__.'/1336458153');

		libxml_use_internal_errors(true);
		$this->settings['pageUid'] = $this->pageUid;

		$mmFile = $this->t3mmExport->getContent();

		$this->view->assign('downloadURL', $mmFile['file']);
		$this->view->assign('filename', basename($mmFile['file']) );
		$this->view->assign('filekb', $mmFile['filekb'] );
		$this->view->assign('iserror', $mmFile['iserror'] );
		$this->view->assign('errors', $mmFile['errors'] );
		$this->view->assign('duration', ($this->tt->getDifferenceToStarttime() /1000) );
	}


	/*************************************************************************************************
		NOT USED, but kept for later ....
	*************************************************************************************************/
	/**
	 * action editPages
	 *
	 * @param $T3mind
	 * @dontvalidate $T3mind
	 * @return void
	 */
	public function editPagesAction(Tx_Typo3mind_Domain_Model_T3mind $T3mind = NULL) {

		die('<h1>Use a TYPO3 Sysfolder and edit there the TYPO3Mind node properties for a page!</h1>');

		/*TODO: */
		if ($T3mind == NULL) {

			$T3mind = $this->t3MindRepository->findOneBypageUid( $this->pageUid );

			if( $T3mind == NULL ){
				$T3mind = t3lib_div::makeInstance('Tx_Typo3mind_Domain_Model_T3mind');
				$T3mind->setpageUid( $this->pageUid );
				$this->t3MindRepository->add($T3mind);
			}
		}


		$this->view->assign('T3mind', $T3mind );
		$this->view->assign('page', t3lib_BEfunc::getRecord('pages', $this->pageUid, 'uid,title' ) );
		$this->view->assign('icons', $this->t3MindRepository->getIcons( $this->settings ) );
		$this->view->assign('userIcons', $this->t3MindRepository->getUserIcons( $this->settings ) );
		$this->view->assign('nodeStyles', $this->helpers->trimExplodeVK(',', $this->settings['nodeStyles'] ) );
		$this->view->assign('edgeStyles', $this->helpers->trimExplodeVK(',', $this->settings['edgeStyles'] ) );
		$this->view->assign('edgeWidths', $this->helpers->trimExplodeVK(',', $this->settings['edgeWidths'] ) );

	}

	/**
	 * action editPagesSave
	 *
	 * @param $T3mind
	 * @param array $options
	 * @return void
	 */
	public function editPagesSaveAction(Tx_Typo3mind_Domain_Model_T3mind $T3mind, $options ) {

		die('<h1>Use a TYPO3 Sysfolder and edit there the TYPO3Mind node properties for a page!</h1>');

		/*TODO: */

		foreach($options as $k=>$v){
			$options[$k] = (int)$v;
		}

		$this->pageUid = isset($options['pageUid']) ? $options['pageUid'] : 0;

		echo '<pre>';
		var_dump($options);
		echo '<hr/>';
		var_dump($T3mind);
		echo '</pre>';


		if( $this->pageUid == 0 ){
			$this->redirect('editPages');
		}

		$this->t3MindRepository->update($T3mind);

		$this->view->assign('options', $options );
		$this->view->assign('page', t3lib_BEfunc::getRecord('pages', $this->pageUid, 'title' ) );

	}

	/**
	 * action export via eID
	 http://xxxxxxxxxxxxx/index.php?eID=typo3mind&id=6&apikey=xxxxxxx
	 http://stuff.lime-flavour.de/link-typo3-eid-use-with-extbase-and-fluid/
	 *
	 * $apikey string the key
	 * @return void
	 */
	public function exportEIDAction() {

		if( $this->apikey <> md5($this->extConfSettings['apikey']) ){
			die('API Keys does not match!');
		}

		throw new Exception('Still not supported');

		$expObj = new Tx_Typo3mind_Export_mmExport($this->settings,$this->t3MindRepository);
		$typo3tempFilename = $expObj->getContent();

		echo '<pre>';
		var_dump($typo3tempFilename);
		echo '</pre>';

		return '';

	}

	/**
	 * Translate key from locallang.xml.
	 *
	 * @param string $key Key to translate
	 * @param array $arguments Array of arguments to be used with vsprintf on the translated item.
	 * @return string Translation output.
	 */
	protected function translate($key, $arguments = null) {
		return Tx_Extbase_Utility_Localization::translate($key, 'Typo3mind', $arguments);
	}

}
