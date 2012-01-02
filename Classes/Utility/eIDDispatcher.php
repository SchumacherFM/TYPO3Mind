<?php
/***************************************************************
* Copyright notice
*
*   2010 Daniel Lienert <daniel@lienert.cc>, Michael Knoll <mimi@kaktusteam.de>
* All rights reserved
*
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
* Utility to include defined frontend libraries as jQuery and related CSS
*  
*
* @package Utility
* @author Daniel Lienert <daniel@lienert.cc>
* @author Cyrill Schumacher <cyrill@schumacher.fm>
*/

/**
 *
 *
 * @package typo3mind
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 */
 
 
class Tx_Typo3mind_Utility_eIDDispatcher {
	
	
	/**
	 * Extbase Object Manager
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;
	
	
	/**
	 * @var string
	 */
	protected $extensionName;
	
	
	/**
	 * @var string
	 */
	protected $pluginName;
	
	
	/**
	 * @var string
	 */
	protected $controllerName;
	
	
	/**
	 * @var string
	 */
	protected $actionName;
	
	/*
	 * page id
	 * @var integer
	*/
	protected $id;
	
	/**
	 * @var array
	 */
	protected $arguments;
	
	public function __construct(){
		global $GLOBALS,$TYPO3_CONF_VARS;
		
		$this->id = (int)t3lib_div::_GP('id');

		/*
		$GLOBALS['LANG'] = t3lib_div::makeInstance('language');
		$GLOBALS['LANG']->init('default');
//		echo $GLOBALS['LANG']->sL('holla');
		$temp_TSFEclassName = t3lib_div::makeInstance('tslib_fe');
		$GLOBALS['TSFE'] = new $temp_TSFEclassName($TYPO3_CONF_VARS, $this->id, 0, true);	
		$GLOBALS['TSFE']->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$GLOBALS['TSFE']->tmpl = t3lib_div::makeInstance('t3lib_tstemplate');
		$GLOBALS['TSFE']->tmpl->init();	
		// t3lib_userAuth
		$GLOBALS['BE_USER'] = t3lib_div::makeInstance('t3lib_beUserAuth'); // New backend user object
		// $GLOBALS['BE_USER']->start(); // Object is initialized
		//$GLOBALS['BE_USER']->backendCheckLogin();       // Checking if there's a user logged in
		//define('TYPO3_PROCEED_IF_NO_USER', true);
				
		// $GLOBALS['TSFE']->connectToDB();
		$GLOBALS['TSFE']->initFEuser();
		$GLOBALS['TSFE']->determineId();
		$GLOBALS['TSFE']->getCompressedTCarray();
		$GLOBALS['TSFE']->initTemplate();
		$GLOBALS['TSFE']->getConfigArray();
		*/
	}
	
	/**
	 * Called by ajax.php / eID.php
	 * Builds an extbase context and returns the response
	 */
	public function dispatch() {
		
		$this->prepareCallArguments();
		
		$configuration['extensionName'] = $this->extensionName;
		$configuration['pluginName'] = $this->pluginName;
		
		
		$bootstrap = t3lib_div::makeInstance('Tx_Extbase_Core_Bootstrap');
		$bootstrap->initialize($configuration);
		
		$this->objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');

		$request = $this->buildRequest();
		$response = $this->objectManager->create('Tx_Extbase_MVC_Web_Response');
		
		$dispatcher =  $this->objectManager->get('Tx_Extbase_MVC_Dispatcher');
		$dispatcher->dispatch($request, $response);

        $response->sendHeaders();
        echo $response->getContent();
         
        $this->cleanShutDown();
	}

    protected function cleanShutDown() {
        $this->objectManager->get('Tx_Extbase_Persistence_Manager')->persistAll();
        $this->objectManager->get('Tx_Extbase_Reflection_Service')->shutdown();
    }	
	
	/**
	 * Build a request object
	 * 
	 * @return Tx_Extbase_MVC_Web_Request $request
	 */
	protected function buildRequest() {
		$request = $this->objectManager->get('Tx_Extbase_MVC_Web_Request'); /* @var $request Tx_Extbase_MVC_Request */
		$request->setControllerExtensionName($this->extensionName);
		$request->setPluginName($this->actionName);
		$request->setControllerName($this->controllerName);
		$request->setControllerActionName($this->actionName);
		$request->setArguments($this->arguments);
		
		return $request;
	}
	
	
	
	/**
	 * Prepare the call arguments
	 * @TODO escape / unescape values ?
	 */
	protected function prepareCallArguments() {
/*		$callJSON = t3lib_div::_GP('call');
		//http://t3develop.harper/typo3/ajax.php?ajaxID=yagAjaxDispatcher&id=22&call={%22extensionName%22:%22Yag%22,%22pluginName%22:%22pi1%22,%22controllerName%22:%22Item%22,%22actionName%22:%22showSingle%22,%22arguments%22:{%22item%22:1}}
		
		$call = json_decode($/, true);
		$this->extensionName 	= $call['extensionName'];
		$this->pluginName 		= $call['pluginName'];
		$this->controllerName 	= $call['controllerName'];
		$this->actionName 		= $call['actionName'];
		$this->arguments 		= $call['arguments'];	
*/
		$apikey = t3lib_div::_GP('apikey');
		$this->extensionName 	= 'typo3mind';
		$this->pluginName 		= 'fm2be';
		$this->controllerName 	= 'T3mind';
		$this->actionName 		= 'exportEID';
		$this->arguments 		= array('apikey'=>$apikey,'id'=>$this->id);	
	}
}
/**
 * Loads the TypoScript for the given extension prefix, e.g. tx_cspuppyfunctions_pi1, for use in a backend module.
 *
 * @param string $extKey
 * @return array
 */
function loadTypoScriptForBEModule($extKey) {
	require_once(PATH_t3lib . 'class.t3lib_page.php');
	require_once(PATH_t3lib . 'class.t3lib_tstemplate.php');
	require_once(PATH_t3lib . 'class.t3lib_tsparser_ext.php');
	list($page) = t3lib_BEfunc::getRecordsByField('pages', 'pid', 0);
	$pageUid = intval($page['uid']);
	$sysPageObj = t3lib_div::makeInstance('t3lib_pageSelect');
	$rootLine = $sysPageObj->getRootLine($pageUid);
	$TSObj = t3lib_div::makeInstance('t3lib_tsparser_ext');
	$TSObj->tt_track = 0;
	$TSObj->init();
	$TSObj->runThroughTemplates($rootLine);
	$TSObj->generateConfig();
	return $TSObj->setup['plugin.'][$extKey . '.'];
}

// eID specific initialization of user and database
tslib_eidtools::connectDB();
tslib_eidtools::initFeUser();

// initialize TSFE
require_once(PATH_tslib.'class.tslib_fe.php');
require_once(PATH_t3lib.'class.t3lib_page.php');
$temp_TSFEclassName = t3lib_div::makeInstanceClassName('tslib_fe');
$GLOBALS['TSFE'] = new $temp_TSFEclassName($TYPO3_CONF_VARS, (int)t3lib_div::_GP('id'), 0, true);
$GLOBALS['TSFE']->connectToDB();
$GLOBALS['TSFE']->initFEuser();
$GLOBALS['TSFE']->determineId();
$GLOBALS['TSFE']->getCompressedTCarray();
$GLOBALS['TSFE']->initTemplate();
$GLOBALS['TSFE']->getConfigArray();
$GLOBALS['LANG'] = t3lib_div::makeInstance('language');
$GLOBALS['LANG']->init('default');

/*
	hmm there are still a lot of classes missing to get it run via eID ... and I not sure if a backend modul kan even run via eID
*/

$dispatcher = t3lib_div::makeInstance('Tx_Typo3mind_Utility_eIDDispatcher');
$dispatcher->dispatch();
