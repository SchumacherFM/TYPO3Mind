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
	 * shows the flash browser and load the current tree
	 *
	 * @return void
	 */
	public function browserAction() {
	
	}
	
	/**
	 * action editPages
	 *
	 * @return void
	 */
	public function editPagesAction() {
		
	 

		$this->view->assign('page', t3lib_BEfunc::getRecord('pages', $this->pageUid, 'title' ) );
		$this->view->assign('icons', $this->fmConfigRepository->getIcons( $this->settings ) );
		$this->view->assign('userIcons', $this->fmConfigRepository->getUserIcons( $this->settings ) );
		$this->view->assign('edgeStyles', $this->helpers->trimExplodeVK(',', $this->settings['edgeStyles'] ) );
		$this->view->assign('edgeWidths', $this->helpers->trimExplodeVK(',', $this->settings['edgeWidths'] ) );
		
	}

	/**
	 * action export
	 *
	 * @return void
	 */
	public function exportAction() {

		// echo '<pre>'; var_dump($this->pageUid); exit;
		// todo better error handling

		$this->view->assign('fmConfigs', $fmConfigs);
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

		$expObj = t3lib_div::makeInstance('Tx_Freemind2_Export_mmExport',$this->pageUid);
		$xml = $expObj->getContent();

		file_put_contents('/home/www/schumacherfm/typo3temp/test.mm',$xml);
		
		// xml header ...

echo '<pre>'; 
// echo( htmlspecialchars($xml) );
echo 'wrote file to typo3temp';
echo '</pre>';

		exit;
		
	
	}
 
}
