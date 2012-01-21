<?php
/* **************************************************************
*  Copyright notice
*
*  (c) webservices.nl
*  (c) 2006-2010 Karsten Dambekalns <karsten@typo3.org>
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
 * extremly modified by SchumacherFM
 * This class handles extension listings
 *
 */

class Tx_Typo3mind_Utility_ExtensionManager {

	/** @var tx_em_Tools_XmlHandler */
	protected $xmlHandler;

	/**
	 *  Displays a list of extensions where a newer version is available
	 *  in the TER than the one that is installed right now
	 *  integrated from the extension "ter_update_check" for TYPO3 4.2 by Christian Welzel
	 *
	 * @return array
	 */
	public function showExtensionsToUpdate() {
		global $LANG;
		$extList = $this->getInstalledExtensions();

		$this->xmlHandler = t3lib_div::makeInstance('tx_em_Tools_XmlHandler');

		$content = array();

		foreach ($extList[0] as $name => $data) {
			$this->xmlHandler->searchExtensionsXMLExact($name, '', '', TRUE, TRUE);
			if ( !isset($this->xmlHandler->extensionsXML[$name]) || !is_array($this->xmlHandler->extensionsXML[$name])) {
				continue;
			}

			$v = $this->xmlHandler->extensionsXML[$name]['versions'];
			$versions = array_keys($v);
			natsort($versions);
			$lastversion = end($versions);

			if ((t3lib_extMgm::isLoaded($name) /* || $this->parentObject->MOD_SETTINGS['display_installed'] */ ) &&
					($data['EM_CONF']['shy'] == 0 /* || $this->parentObject->MOD_SETTINGS['display_shy'] */ ) &&
					tx_em_Tools::versionDifference($lastversion, $data['EM_CONF']['version'], 1)) {

				$imgInfo = @getImageSize(tx_em_Tools::getExtPath($name, $data['type']) . '/ext_icon.gif');
				if (is_array($imgInfo)) {
					$icon = tx_em_Tools::typeRelPath($data['type']) . $name . '/ext_icon.gif';
				} else {
					$icon = 'clear.gif';
				}
				$comment = array();
				foreach ($versions as $vk) {
					$va = & $v[$vk];
					if (t3lib_div::int_from_ver($vk) <= t3lib_div::int_from_ver($data['EM_CONF']['version'])) {
						continue;
					}
					$comment[] = $vk . ( isset($va['uploadcomment']) ?  ' '. nl2br($va['uploadcomment']) : '' );
				}


				$content[$name] = array(
					'nicename' => $data['EM_CONF']['title'],
					'icon'=>$icon,
					'comment'=>implode('<br/>',$comment),
					'version_local'=>$data['EM_CONF']['version'],
					'version_remote'=>$lastversion,
				);

			}
		}

		return $content;
	}

	/**
	 * Returns the list of available (installed) extensions
	 *
	 * @param	boolean		if set, function will return a flat list only
	 * @return	array		Array with two arrays, list array (all extensions with info) and category index
	 * @see getInstExtList()
	 */
	public function getInstalledExtensions( ) {
		$list = array();

		$cat = tx_em_Tools::getDefaultCategory();

		$path = PATH_typo3 . 'sysext/';
		$this->getInstExtList($path, $list, $cat, 'S');

		$path = PATH_typo3 . 'ext/';
		$this->getInstExtList($path, $list, $cat, 'G');

		$path = PATH_typo3conf . 'ext/';
		$this->getInstExtList($path, $list, $cat, 'L');

		return array($list, $cat);
	}

	/**
	 * Gathers all extensions in $path
	 *
	 * @param	string		Absolute path to local, global or system extensions
	 * @param	array		Array with information for each extension key found. Notice: passed by reference
	 * @param	array		Categories index: Contains extension titles grouped by various criteria.
	 * @param	string		Path-type: L, G or S
	 * @return	void		'Returns' content by reference
	 * @see getInstalledExtensions()
	 */
	protected function getInstExtList($path, &$list, &$cat, $type) {

		if (@is_dir($path)) {
			$extList = t3lib_div::get_dirs($path);
			if (is_array($extList)) {
				foreach ($extList as $extKey) {
					if (@is_file($path . $extKey . '/ext_emconf.php')) {
						$emConf = tx_em_Tools::includeEMCONF($path . $extKey . '/ext_emconf.php', $extKey);
						if (is_array($emConf)) {
							if ( isset($list[$extKey]) && is_array($list[$extKey])) {
								$list[$extKey] = array('doubleInstall' => $list[$extKey]['doubleInstall']);
							}
							$list[$extKey]['extkey'] = $extKey;
							if( isset($list[$extKey]['doubleInstall'])) { 	$list[$extKey]['doubleInstall'] .= $type;	}
							$list[$extKey]['type'] = $type;
							$list[$extKey]['installed'] = t3lib_extMgm::isLoaded($extKey);
							$list[$extKey]['EM_CONF'] = $emConf;
							$list[$extKey]['files'] = t3lib_div::getFilesInDir($path . $extKey, '', 0, '');

							tx_em_Tools::setCat($cat, $list[$extKey], $extKey);
						}
					}
				}
			}
		}
	}


	/**
	 * Make constraints readable
	 *
	 * @param  array $constraints
	 * @return array
	 */
	public function humanizeConstraints($constraints) {
		$depends = $conflicts = $suggests = array();
		$result = array(
			'depends' => '',
			'conflicts' => '',
			'suggests' => ''
		);

		if (is_array($constraints) && count($constraints)) {
			if (is_array($constraints['depends']) && count($constraints['depends'])) {
				foreach ($constraints['depends'] as $key => $value) {
					if ($value) {
						$tmp = t3lib_div::trimExplode('-', $value, TRUE);
						if (isset($tmp[1]) && trim($tmp[1]) && trim($tmp[1]) !== '0.0.0') {
							$value = $tmp[0] . ' - ' . $tmp[1];
						} else {
							$value = $tmp[0];
						}
					}
					$depends[] = $key . ($value ? ' (' . $value . ')' : '');
				}
			}
			if (is_array($constraints['conflicts']) && count($constraints['conflicts'])) {
				foreach ($constraints['conflicts'] as $key => $value) {
					if ($value) {
						$tmp = t3lib_div::trimExplode('-', $value, TRUE);
						if ( isset($tmp[1]) && trim($tmp[1]) && trim($tmp[1]) !== '0.0.0') {
							$value = $tmp[0] . ' - ' . $tmp[1];
						} else {
							$value = $tmp[0];
						}
					}
					$conflicts[] = $key . ($value ? ' (' . $value . ')' : '');
				}
			}
			if (is_array($constraints['suggests']) && count($constraints['suggests'])) {
				foreach ($constraints['suggests'] as $key => $value) {
					if ($value) {
						$tmp = t3lib_div::trimExplode('-', $value, TRUE);
						if (trim($tmp[1]) && trim($tmp[1]) !== '0.0.0') {
							$value = $tmp[0] . ' - ' . $tmp[1];
						} else {
							$value = $tmp[0];
						}
					}
					$suggests[] = $key . ($value ? ' (' . $value . ')' : '');
				}
			}
			if (count($depends)) {
				$result['depends'] = htmlspecialchars(implode(', ', $depends));
			}
			if (count($conflicts)) {
				$result['conflicts'] = htmlspecialchars(implode(', ', $conflicts));
			}
			if (count($suggests)) {
				$result['suggests'] = htmlspecialchars(implode(', ', $suggests));
			}
		}
		return $result;
	}

}