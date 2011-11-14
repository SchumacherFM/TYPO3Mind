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
class Tx_Freemind2_Domain_Model_FmConfig extends Tx_Extbase_DomainObject_AbstractEntity {

	/**
	 * name
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $name;

	/**
	 * defaultValue
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $defaultValue;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

	}

	/**
	 * Returns the name
	 *
	 * @return string $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the name
	 *
	 * @param string $name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Returns the defaultValue
	 *
	 * @return string $defaultValue
	 */
	public function getDefaultValue() {
		return $this->defaultValue;
	}

	/**
	 * Sets the defaultValue
	 *
	 * @param string $defaultValue
	 * @return void
	 */
	public function setDefaultValue($defaultValue) {
		$this->defaultValue = $defaultValue;
	}

}
?>