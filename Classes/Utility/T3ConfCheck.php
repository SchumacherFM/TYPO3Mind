<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2011 Kasper Skårhøj (kasperYYYY@typo3.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Contains the class for the Install Tool
 *	from file class.tx_install.php
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 * @author	Ingmar Schlecht <ingmar@typo3.org>
 * @cocoauthor Cyrill Schumacher <Cyrill@Schumacher.fm>

 * 0:     function getDefaultConfigArrayComments($string,$mainArray=array(),$commentArray=array())
 * 0:     function checkDirs()

 */
 
class Tx_Typo3mind_Utility_T3ConfCheck {

	public $messages = array();

	/**
	 * Make an array of the comments in the t3lib/config_default.php file
	 *
	 * @param string $string The contents of the config_default.php file
	 * @param array $mainArray
	 * @param array $commentArray
	 * @return array
	 */
	public function getDefaultConfigArrayComments() {
		$string = t3lib_div::getUrl(PATH_t3lib.'config_default.php');
	
		$lines = explode(LF, $string);
		$in=0;
		$mainKey='';
		foreach ($lines as $lc) {
			$lc = trim($lc);
			if ($in) {
				if (!strcmp($lc,');')) {
					$in=0;
				} else {
					if (preg_match('/["\']([[:alnum:]_-]*)["\'][[:space:]]*=>(.*)/i',$lc,$reg)) {
						preg_match('/,[\t\s]*\/\/(.*)/i',$reg[2],$creg);
						$theComment = trim( isset($creg[1]) ? $creg[1] : '' );
						if (substr(strtolower(trim($reg[2])),0,5)=='array' && !strcmp($reg[1],strtoupper($reg[1]))) {
							$mainKey=trim($reg[1]);
							$mainArray[$mainKey]=$theComment;
						} elseif ($mainKey) {
							$commentArray[$mainKey][$reg[1]]=$theComment;
						}
					}
				}
			}
			if (!strcmp($lc, '$TYPO3_CONF_VARS = array(')) {
				$in=1;
			}
		}
		return array($mainArray,$commentArray);
	}

	/**
	 * Checking and testing that the required writable directories are writable.
	 *
	 * @return void
	 */
	public function checkDirs() {
		// Check typo3/temp/
		$ext='Directories';

		$uniqueName = md5(uniqid(microtime()));

			// The requirement level (the integer value, ie. the second value of the value array) has the following meanings:
			// -1 = not required, but if it exists may be writable or not
			//  0 = not required, if it exists the dir should be writable
			//  1 = required, don't has to be writable
			//  2 = required, has to be writable

		$checkWrite=array(
			'typo3temp/' => array('This folder is used by both the frontend (FE) and backend (BE) interface for all kind of temporary and cached files.',2,'dir_typo3temp'),
			'typo3temp/pics/' => array('This folder is part of the typo3temp/ section. It needs to be writable, too.',2,'dir_typo3temp'),
			'typo3temp/temp/' => array('This folder is part of the typo3temp/ section. It needs to be writable, too.',2,'dir_typo3temp'),
			'typo3temp/llxml/' => array('This folder is part of the typo3temp/ section. It needs to be writable, too.',2,'dir_typo3temp'),
			'typo3temp/cs/' => array('This folder is part of the typo3temp/ section. It needs to be writable, too.',2,'dir_typo3temp'),
			'typo3temp/GB/' => array('This folder is part of the typo3temp/ section. It needs to be writable, too.',2,'dir_typo3temp'),
			'typo3temp/locks/' => array('This folder is part of the typo3temp/ section. It needs to be writable, too.',2,'dir_typo3temp'),
			'typo3conf/' => array('This directory contains the local configuration files of your website. TYPO3 must be able to write to these configuration files during setup and when the Extension Manager (EM) installs extensions.',2),
			'typo3conf/ext/' => array('Location for local extensions. Must be writable if the Extension Manager is supposed to install extensions for this website.',0),
			'typo3conf/l10n/' => array('Location for translations. Must be writable if the Extension Manager is supposed to install translations for extensions.',0),
			TYPO3_mainDir.'ext/' => array('Location for global extensions. Must be writable if the Extension Manager is supposed to install extensions globally in the source.',-1),
			'uploads/' => array('Location for uploaded files from RTE, in the subdirectories for uploaded files of content elements.',2),
			'uploads/pics/' => array('Typical location for uploaded files (images especially).',0),
			'uploads/media/' => array('Typical location for uploaded files (non-images especially).',0),
			'uploads/tf/' => array('Typical location for uploaded files (TS template resources).',0),
			$GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'] => array('Location for local files such as templates, independent uploads etc.',-1),
			$GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'] . '_temp_/' => array('Typical temporary location for default upload of files by administrators.',0),
		);

		foreach ($checkWrite as $relpath => $descr) {

				// Check typo3temp/
			$general_message = $descr[0];

				// If the directory is missing, try to create it
			if (!@is_dir(PATH_site.$relpath)) {
				t3lib_div::mkdir(PATH_site.$relpath);
			}

			if (!@is_dir(PATH_site.$relpath)) {
				if ($descr[1]) {	// required...
					$this->message('smily_bad', $relpath.' directory does not exist and could not be created', '
						<p>
							<em>Full path: ' . PATH_site . $relpath . '</em>
							<br />
							' . $general_message . '
						</p>
						<p>
							This error should not occur as ' . $relpath . ' must
							always be accessible in the root of a TYPO3 website.
						</p>
					', 3);
				} else {
					if ($descr[1] == 0) {
						$msg = 'This directory does not necessarily have to exist but if it does it must be writable.';
					} else {
						$msg = 'This directory does not necessarily have to exist and if it does it can be writable or not.';
					}
					$this->message('smiley-neutral', $relpath.' directory does not exist', '
						<p>
							<em>Full path: ' . PATH_site . $relpath . '</em>
							<br />
							' . $general_message . '
						</p>
						<p>
							' . $msg . '
						</p>
					', 2);
				}
			} else {
				$file = PATH_site.$relpath.$uniqueName;
				@touch($file);
				if (@is_file($file)) {
					unlink($file);
					if ( isset($descr[2]) ) { $this->config_array[$descr[2]]=1; }
					$this->message('ksmiletris', $relpath.' writable','',-1);
				} else {
					$severity = ($descr[1]==2 || $descr[1]==0) ? 3 : 2;
					if ($descr[1] == 0 || $descr[1] == 2) {
						$msg = 'The directory '.$relpath.' must be writable!';
					} elseif ($descr[1] == -1 || $descr[1] == 1) {
						$msg = 'The directory '.$relpath.' does not neccesarily have to be writable.';
					}
					$this->message('smily_bad', $relpath .' directory not writable', '
						<p>
							<em>Full path: ' . $file . '</em>
							<br />
							' . $general_message . '
						</p>
						<p>
							Tried to write this file (with touch()) but didn\'t
							succeed.
							<br />
							' . $msg . '
						</p>
					', $severity);
				}
			}
		}
	}
	/**
	 * Setting a message in the message-log and sets the fatalError flag if error type is 3.
	 * Modified by SchumacherFM
	 *
	 * @param string $icon freemind icon
	 * @param string $short_string A short description
	 * @param string $long_string A long (more detailed) description
	 * @param integer $type -1=OK sign, 0=message, 1=notification, 2=warning, 3=error
	 * @param boolean $force Print message also in "Advanced" mode (not only in 1-2-3 mode)
	 * @return void
	 */
	private function message($icon, $short_string='', $long_string='', $type=0, $force=0) {
	
		$this->messages[] = array(
			'icon'=>$icon, 'short'=>$short_string, 'long'=>$long_string
		);
	}

}