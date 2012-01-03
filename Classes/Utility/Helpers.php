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

class Tx_Typo3mind_Utility_Helpers {


	/**
	 * Getting the tree data: frees data handle
	 *
	 * @param	array	$array
	 * @return	array
	 */
	public static function arrayKeysEqualValues($array) {
		$a = array();
		foreach($array as $k=>$v){
			$a[ $v ] = $v;
		}
		return $a;
	}

	/**
	 * trimExplode VK = value also in keys
	 *
	 * @param	string	$d delimiter
	 * @param	string	$s from the TypoScript settings
	 * @return	array
	 */
	public static function trimExplodeVK($d,$s) {

		return self::arrayKeysEqualValues ( t3lib_div::trimExplode($d, $s ,1 ) );
	}

	/**
	 * To click and view TS Files
	 *
	 * @param	string		Unparsed TypoScript
	 * @return	array
	 * @static
	 */
	public static function  TSReplaceFileLinkWithHref($TSstring) {
		$fileNames = self::TSgetIncludedFileNames($TSstring);
		// @todo implement secure preview URL via eID with API key for a short time valid ...
		foreach($fileNames as $tsFile){
			$TSstring = preg_replace('~(FILE\s*:\s*)'.preg_quote($tsFile).'~i','\\1|lt|a href="#"|gt|'.$tsFile.'|lt|/a|gt|',$TSstring);
		}
		return $TSstring;
	}
	/**
	 *  (c) 1999-2011 Kasper Skårhøj (kasperYYYY@typo3.com)
	 *  All rights reserved
	 * Checks the input string (un-parsed TypoScript) for include-commands ("<INCLUDE_TYPOSCRIPT: ....")
	 * Use: t3lib_TSparser::checkIncludeLines()
	 *
	 * @param	string		Unparsed TypoScript
	 * @param	integer		Counter for detecting endless loops
	 * @return	array
	 * @static
	 */
	private static function  TSgetIncludedFileNames($string, $cycle_counter = 1 ) {
		$includedFiles = array();
		if ($cycle_counter > 100) {
			t3lib_div::sysLog('It appears like TypoScript code is looping over itself. Check your templates for "&lt;INCLUDE_TYPOSCRIPT: ..." tags', 'Core', 2);
			if ($returnFiles) {
				return array(
					'typoscript' => '',
					'files' => $includedFiles,
				);
			}
			return "\n###\n### ERROR: Recursion!\n###\n";
		}
		$splitStr = '<INCLUDE_TYPOSCRIPT:';
		if (strstr($string, $splitStr)) {
			$newString = '';
			$allParts = explode($splitStr, LF . $string . LF); // adds line break char before/after
			foreach ($allParts as $c => $v) {
				if (!$c) { // first goes through

				} elseif (preg_match('/\r?\n\s*$/', $allParts[$c - 1])) { // There must be a line-break char before.
					$subparts = explode('>', $v, 2);
					if (preg_match('/^\s*\r?\n/', $subparts[1])) { // There must be a line-break char after
							// SO, the include was positively recognized:

						$params = t3lib_div::get_tag_attributes($subparts[0]);

						if ($params['source']) {
							$sourceParts = explode(':', $params['source'], 2);
							switch (strtolower(trim($sourceParts[0]))) {
								case 'file':
									$filename = t3lib_div::getFileAbsFileName(trim($sourceParts[1]));
									if (strcmp($filename, '')) { // Must exist and must not contain '..' and must be relative
										if (t3lib_div::verifyFilenameAgainstDenyPattern($filename)) { // Check for allowed files
											if (@is_file($filename) && filesize($filename) < 100000) { // Max. 100 KB include files!
													// check for includes in included text
												$includedFiles[] = str_replace(PATH_site,'',$filename);
												$included_text = self::TSgetIncludedFileNames(t3lib_div::getUrl($filename), $cycle_counter + 1);
												$includedFiles = array_merge($includedFiles, $included_text);
											}
										} else {
											t3lib_div::sysLog('File "' . $filename . '" was not included since it is not allowed due to fileDenyPattern', 'Core', 2);
										}
									}
								break;
							}
						}
					}
				}
			}

		}
			// When all included files should get returned, simply return an compound array containing
			// the TypoScript with all "includes" processed and the files which got included
			return $includedFiles;

	}

}
