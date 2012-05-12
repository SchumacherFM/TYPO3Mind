<?php

/* * *************************************************************
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
 * ************************************************************* */

/**
 * @package typo3mind
 * @subpackage formats
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 */
interface Tx_Typo3mind_Domain_Export_Formats_FormatInterface
{

	/**
	 * passes the parent object (used for additional functions)
	 *
	 * @param stdClass $that
	 */
	public function __construct($that);

	/**
	 * returns the whole map, must be called at the end.
	 *
	 * @return SimpleXMLElement
	 */
	public function getMapXmlRoot();

	/**
	 * gets the root map element and creates the map
	 *
	 * @param    none
	 * @return    SimpleXMLElement
	 */
	public function getMap();

	/**
	 * Converts meaningful xml characters to xml entities
	 *
	 * @param  string
	 * @return string
	 */
	public function xmlentities($value = '');

	/**
	 * adds attributs to a Child
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    array $attributes
	 * @return    nothing
	 */
	public function addAttributes(SimpleXMLElement $xmlNode, $attributes);

	/**
	 * adds an builtin icon
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    string $iconName the name or a comma seperate string with the names
	 * @return    nothing
	 */
	public function addIcon(SimpleXMLElement $xmlNode, $iconName);

	/**
	 * adds a node
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    array $attributes  key is the name and value the value
	 * @return    SimpleXMLElement
	 */
	public function addNode(SimpleXMLElement $xmlNode, $attributes);

	/**
	 * adds a note
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    array $attributes  key is the name and value the value
	 * @param    string html content
	 * @return    SimpleXMLElement
	 */
	public function addNote(SimpleXMLElement $xmlNode, $attributes, $html);

	/**
	 * adds an edge
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    array $attributes
	 * @return    nothing
	 */
	public function addEdge(SimpleXMLElement $xmlNode, $attributes);

	/**
	 * adds a cloud
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    array $attributes
	 * @return    nothing
	 */
	public function addCloud(SimpleXMLElement $xmlNode, $attributes);

	/**
	 * adds a font
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    array $attributes
	 * @return    nothing
	 */
	public function addFont(SimpleXMLElement $xmlNode, $attributes);

	/**
	 * Creates a rich content node
	 *
	 * @param    SimpleXMLElement $xml
	 * @param    array $attributes  key is the name and value the value
	 * @param    string $htmlContent
	 * @param    array $addEdgeAttr
	 * @param    array $addFontAttr
	 * @return    SimpleXMLElement
	 */
	public function addRichContentNode(SimpleXMLElement $xml, $attributes, $htmlContent, $addEdgeAttr = array(), $addFontAttr = array());

	/**
	 * Creates a rich content note
	 *
	 * @param    SimpleXMLElement $xml
	 * @param    array $attributes  key is the name and value the value
	 * @param    string $htmlContent
	 * @param    array $addEdgeAttr
	 * @param    array $addFontAttr
	 * @param    string $type defined how this rich content will look... like a node or a note or both!
	 * @return    SimpleXMLElement
	 */
	public function addRichContentNote(SimpleXMLElement $xml, $attributes, $htmlContent, $addEdgeAttr = array(), $addFontAttr = array(), $type = 'NOTE');

	/**
	 * Sets an attribute for a node or font or ...
	 *
	 * @param    array $t3mind
	 * @param    string $t3mindName
	 * @param    array $attributes
	 * @param    string $attributeName
	 * @return    array
	 */
	public function setAttr($t3mind, $t3mindName, $attributes, $attributeName);

	/**
	 * Sets the font for a node
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    array $t3mind from the DB
	 * @return    array
	 */
	public function setNodeFont(SimpleXMLElement $xmlNode, $t3mind);

	/**
	 * Checks if neccessary attributes are set for a node
	 *
	 * @param    array $attributes
	 * @return    nothing
	 */
	protected function checkNodeAttr($attributes);

	/**
	 * Creates the TLF attributes array (text, link, folded)
	 *
	 * @param    array $attributes
	 * @return    nothing
	 */
	public function createTLFattr($text, $link = '', $folded = '');

	/**
	 * adds one image to a node
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    array $attributes
	 * @param    string $imgRelPath relativ image path like ../typo3conf/ext/..../ext_icon.gif
	 * @param    string $imgHTML additionl html for the img tag
	 * @return    nothing
	 */
	public function addImgNode(SimpleXMLElement $xmlNode, $attributes, $imgRelPath, $imgHTML = '');

	/**
	 * adds one image to a note
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    array $attributes
	 * @param    string $imgRelPath relativ image path like ../typo3conf/ext/..../ext_icon.gif
	 * @param    string $imgHTML
	 * @param    string $noteHTML
	 * @param    array $addEdgeAttr
	 * @param    array $addFontAttr
	 * @return    nothing
	 */
	public function addImgNote(SimpleXMLElement $xmlNode, $attributes, $imgRelPath, $imgHTML = '', $noteHTML = '', $addEdgeAttr = array(), $addFontAttr = array());

	/**
	 * adds multiple images with links to a note node
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    array $attributes
	 * @param    array $images [] = array(path=>,html=>,link=>) relativ image path like ../typo3conf/ext/..../ext_icon.gif
	 * @param    string $noteHTML
	 * @return    nothing
	 */
	public function addImagesNote(SimpleXMLElement $xmlNode, $attributes, $images, $noteHTML);

	/**
	 * adds multiple images with links to a node - HYPERLINKS ARE NOT SUPPORTED in the images BY FREEMIND IN RICHCONTENT NODES!
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    array $attributes
	 * @param    array $images [] = array(path=>,html=>,link=>) relativ image path like ../typo3conf/ext/..../ext_icon.gif
	 * @return    nothing
	 */
	public function addImagesNode(SimpleXMLElement $xmlNode, $attributes, $images);

	/**
	 * adds adds an arrowlink to a destination ...
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    array $attributes
	 * @return    nothing
	 */
	public function addArrowlink(SimpleXMLElement $xmlNode, $attributes);

	/**
	 * Saves the SimpleXMLElement as a xml file in the typo3temp dir
	 *
	 * @param    SimpleXMLElement $xml
	 * @param    array $attributes  key is the name and value the value
	 * @return    array
	 */
	public function finalOutputFile(SimpleXMLElement $xml);

	/**
	 * convert < and > to special internal strings to recover it in xml out to original < and > ;-)
	 *
	 * @param string $string
	 * @return    string
	 */
	public function convertLTGT($string);
}
