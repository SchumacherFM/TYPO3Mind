<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 
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
	 * Page ID
	 *
	 * @var integer
	 */
	protected $pageUid;

	/**
	 * Font Face
	 *
	 * @var string
	 */
	protected $fontFace;

	/**
	 * Font Color
	 *
	 * @var string
	 */
	protected $fontColor;

	/**
	 * Font Size
	 *
	 * @var integer
	 */
	protected $fontSize;

	/**
	 * is Bold?
	 *
	 * @var boolean
	 */
	protected $fontBold;

	/**
	 * is Italic?
	 *
	 * @var boolean
	 */
	protected $fontItalic;

	/**
	 * Display as cloud?
	 *
	 * @var boolean
	 */
	protected $cloudIs;

	/**
	 * Cloud Color
	 *
	 * @var string
	 */
	protected $cloudColor;

	/**
	 * Node color
	 *
	 * @var string
	 */
	protected $nodeColor;

	/**
	 * Is the node folded?
	 *
	 * @var boolean
	 */
	protected $nodeFolded;

	/**
	 * Node position, left or right? aendern auf varchar
	 *
	 * @var integer
	 */
	protected $nodePosition;

	/**
	 * Node style
	 *
	 * @var integer
	 */
	protected $nodeStyle;

	/**
	 * Node icon (build in into FreeMind)
	 *
	 * @var string
	 */
	protected $nodeIcon;

	/**
	 * User node icon
	 *
	 * @var string
	 */
	protected $nodeUserIcon;

	/**
	 * Edge color
	 *
	 * @var string
	 */
	protected $edgeColor;

	/**
	 * Edge style
	 *
	 * @var integer
	 */
	protected $edgeStyle;

	/**
	 * Edge width
	 *
	 * @var integer
	 */
	protected $edgeWidth;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

	}

	/**
	 * Returns the pageUid
	 *
	 * @return integer $pageUid
	 */
	public function getPageUid() {
		return $this->pageUid;
	}

	/**
	 * Sets the pageUid
	 *
	 * @param integer $pageUid
	 * @return void
	 */
	public function setPageUid($pageUid) {
		$this->pageUid = $pageUid;
	}

	/**
	 * Returns the fontFace
	 *
	 * @return string $fontFace
	 */
	public function getFontFace() {
		return $this->fontFace;
	}

	/**
	 * Sets the fontFace
	 *
	 * @param string $fontFace
	 * @return void
	 */
	public function setFontFace($fontFace) {
		$this->fontFace = $fontFace;
	}

	/**
	 * Returns the fontColor
	 *
	 * @return string $fontColor
	 */
	public function getFontColor() {
		return $this->fontColor;
	}

	/**
	 * Sets the fontColor
	 *
	 * @param string $fontColor
	 * @return void
	 */
	public function setFontColor($fontColor) {
		$this->fontColor = $fontColor;
	}

	/**
	 * Returns the fontSize
	 *
	 * @return integer $fontSize
	 */
	public function getFontSize() {
		return $this->fontSize;
	}

	/**
	 * Sets the fontSize
	 *
	 * @param integer $fontSize
	 * @return void
	 */
	public function setFontSize($fontSize) {
		$this->fontSize = $fontSize;
	}

	/**
	 * Returns the fontBold
	 *
	 * @return boolean $fontBold
	 */
	public function getFontBold() {
		return $this->fontBold;
	}

	/**
	 * Sets the fontBold
	 *
	 * @param boolean $fontBold
	 * @return void
	 */
	public function setFontBold($fontBold) {
		$this->fontBold = $fontBold;
	}

	/**
	 * Returns the boolean state of fontBold
	 *
	 * @return boolean
	 */
	public function isFontBold() {
		return $this->getFontBold();
	}

	/**
	 * Returns the fontItalic
	 *
	 * @return boolean $fontItalic
	 */
	public function getFontItalic() {
		return $this->fontItalic;
	}

	/**
	 * Sets the fontItalic
	 *
	 * @param boolean $fontItalic
	 * @return void
	 */
	public function setFontItalic($fontItalic) {
		$this->fontItalic = $fontItalic;
	}

	/**
	 * Returns the boolean state of fontItalic
	 *
	 * @return boolean
	 */
	public function isFontItalic() {
		return $this->getFontItalic();
	}

	/**
	 * Returns the cloudIs
	 *
	 * @return boolean $cloudIs
	 */
	public function getCloudIs() {
		return $this->cloudIs;
	}

	/**
	 * Sets the cloudIs
	 *
	 * @param boolean $cloudIs
	 * @return void
	 */
	public function setCloudIs($cloudIs) {
		$this->cloudIs = $cloudIs;
	}

	/**
	 * Returns the boolean state of cloudIs
	 *
	 * @return boolean
	 */
	public function isCloudIs() {
		return $this->getCloudIs();
	}

	/**
	 * Returns the cloudColor
	 *
	 * @return string $cloudColor
	 */
	public function getCloudColor() {
		return $this->cloudColor;
	}

	/**
	 * Sets the cloudColor
	 *
	 * @param string $cloudColor
	 * @return void
	 */
	public function setCloudColor($cloudColor) {
		$this->cloudColor = $cloudColor;
	}

	/**
	 * Returns the nodeColor
	 *
	 * @return string $nodeColor
	 */
	public function getNodeColor() {
		return $this->nodeColor;
	}

	/**
	 * Sets the nodeColor
	 *
	 * @param string $nodeColor
	 * @return void
	 */
	public function setNodeColor($nodeColor) {
		$this->nodeColor = $nodeColor;
	}

	/**
	 * Returns the nodeFolded
	 *
	 * @return boolean $nodeFolded
	 */
	public function getNodeFolded() {
		return $this->nodeFolded;
	}

	/**
	 * Sets the nodeFolded
	 *
	 * @param boolean $nodeFolded
	 * @return void
	 */
	public function setNodeFolded($nodeFolded) {
		$this->nodeFolded = $nodeFolded;
	}

	/**
	 * Returns the boolean state of nodeFolded
	 *
	 * @return boolean
	 */
	public function isNodeFolded() {
		return $this->getNodeFolded();
	}

	/**
	 * Returns the nodePosition
	 *
	 * @return integer $nodePosition
	 */
	public function getNodePosition() {
		return $this->nodePosition;
	}

	/**
	 * Sets the nodePosition
	 *
	 * @param integer $nodePosition
	 * @return void
	 */
	public function setNodePosition($nodePosition) {
		$this->nodePosition = $nodePosition;
	}

	/**
	 * Returns the nodeStyle
	 *
	 * @return integer $nodeStyle
	 */
	public function getNodeStyle() {
		return $this->nodeStyle;
	}

	/**
	 * Sets the nodeStyle
	 *
	 * @param integer $nodeStyle
	 * @return void
	 */
	public function setNodeStyle($nodeStyle) {
		$this->nodeStyle = $nodeStyle;
	}

	/**
	 * Returns the nodeIcon
	 *
	 * @return string $nodeIcon
	 */
	public function getNodeIcon() {
		return $this->nodeIcon;
	}

	/**
	 * Sets the nodeIcon
	 *
	 * @param string $nodeIcon
	 * @return void
	 */
	public function setNodeIcon($nodeIcon) {
		$this->nodeIcon = $nodeIcon;
	}

	/**
	 * Returns the nodeUserIcon
	 *
	 * @return string $nodeUserIcon
	 */
	public function getNodeUserIcon() {
		return $this->nodeUserIcon;
	}

	/**
	 * Sets the nodeUserIcon
	 *
	 * @param string $nodeUserIcon
	 * @return void
	 */
	public function setNodeUserIcon($nodeUserIcon) {
		$this->nodeUserIcon = $nodeUserIcon;
	}

	/**
	 * Returns the edgeColor
	 *
	 * @return string $edgeColor
	 */
	public function getEdgeColor() {
		return $this->edgeColor;
	}

	/**
	 * Sets the edgeColor
	 *
	 * @param string $edgeColor
	 * @return void
	 */
	public function setEdgeColor($edgeColor) {
		$this->edgeColor = $edgeColor;
	}

	/**
	 * Returns the edgeStyle
	 *
	 * @return integer $edgeStyle
	 */
	public function getEdgeStyle() {
		return $this->edgeStyle;
	}

	/**
	 * Sets the edgeStyle
	 *
	 * @param integer $edgeStyle
	 * @return void
	 */
	public function setEdgeStyle($edgeStyle) {
		$this->edgeStyle = $edgeStyle;
	}

	/**
	 * Returns the edgeWidth
	 *
	 * @return integer $edgeWidth
	 */
	public function getEdgeWidth() {
		return $this->edgeWidth;
	}

	/**
	 * Sets the edgeWidth
	 *
	 * @param integer $edgeWidth
	 * @return void
	 */
	public function setEdgeWidth($edgeWidth) {
		$this->edgeWidth = $edgeWidth;
	}

}