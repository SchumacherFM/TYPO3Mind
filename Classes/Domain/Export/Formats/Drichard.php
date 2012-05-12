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
 * @link http://drichard.org/mindmaps/
 *
 */
class Tx_Typo3mind_Domain_Export_Formats_Drichard implements Tx_Typo3mind_Domain_Export_Formats_FormatInterface
{

	/**
	 * The root root root node of the xml file
	 *
	 * @var SimpleXMLElement
	 */
	protected $mapXmlRoot;

	/**
	 *
	 * @var stdClass $parentObject
	 */
	protected $parentObject;

	/**
	 * passes the parent object (used for additional functions)
	 *
	 * @param stdClass $that
	 */
	public function __construct($that)
	{
		$this->parentObject = $that;
	}

	/**
	 * returns the whole map, must be called at the end.
	 *
	 * @return SimpleXMLElement
	 */
	public function getMapXmlRoot()
	{
		return $this->mapXmlRoot;
	}

	/**
	 * gets the root map element and creates the map
	 *
	 * @param    none
	 * @return    SimpleXMLElement
	 */
	public function getMap()
	{
		$this->mapXmlRoot = new SimpleXMLElement('<map></map>', LIBXML_NOXMLDECL | LIBXML_PARSEHUGE);

		$attributes = array(
			'TEXT' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']
		);

		$rootNode = $this->addNode($this->mapXmlRoot, $attributes);

		$ThisFileInfoNode = $this->addImgNode($rootNode, array(
			'TEXT' => $this->parentObject->translate('tree.fileInfo'),
				)
		);


		$this->addNode($ThisFileInfoNode, array(
			'TEXT' => 'TYPO3: ' . TYPO3_version,
		));

		$this->addNode($ThisFileInfoNode, array(
			'TEXT' => 'Backend HTTP Address: ' . $this->parentObject->getBEHttpHost(),
		));
		$this->addNode($ThisFileInfoNode, array(
			'TEXT' => 'Created: ' . date('Y-m-d H:i:s'),
		));
		$this->addNode($ThisFileInfoNode, array(
			'TEXT' => 'MD5 Hash: ###MD5_FILE_HASH####',
		));
		$this->addNode($ThisFileInfoNode, array(
			'TEXT' => 'Map Mode: ' . $this->parentObject->settings['mapMode'],
		));

		return $rootNode;
	}

	/**
	 * Converts meaningful xml characters to xml entities
	 *
	 * @param  string
	 * @return string
	 */
	public function xmlentities($value = '')
	{
		return trim(str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;'), (string) $value));
	}

	/**
	 * adds attributs to a Child
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    array $attributes
	 * @return    nothing
	 */
	public function addAttributes(SimpleXMLElement $xmlNode, $attributes)
	{
		foreach ($attributes as $k => $v) {
			if ($v <> '') {
				$xmlNode->addAttribute($k, $this->xmlentities($v));
			}
		}
	}

	/**
	 * adds an builtin icon
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    string $iconName the name or a comma seperate string with the names
	 * @return    nothing
	 */
	public function addIcon(SimpleXMLElement $xmlNode, $iconName)
	{

	}

	/**
	 * adds a node
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    array $attributes  key is the name and value the value
	 * @return    SimpleXMLElement
	 */
	public function addNode(SimpleXMLElement $xmlNode, $attributes)
	{
		$child = $xmlNode->addChild('node', '');

		$this->addAttributes($child, $this->checkNodeAttr($attributes));
		return $child;
	}

	/**
	 * adds a note
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    array $attributes  key is the name and value the value
	 * @param    string html content
	 * @return    SimpleXMLElement
	 */
	public function addNote(SimpleXMLElement $xmlNode, $attributes, $html)
	{
		return $this->addRichContentNote($xmlNode, $attributes, $html, array(), array(), 'NOTE');
	}

	/**
	 * adds an edge
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    array $attributes
	 * @return    nothing
	 */
	public function addEdge(SimpleXMLElement $xmlNode, $attributes)
	{

	}

	/**
	 * adds a cloud
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    array $attributes
	 * @return    nothing
	 */
	public function addCloud(SimpleXMLElement $xmlNode, $attributes)
	{

	}

	/**
	 * adds a font
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    array $attributes
	 * @return    nothing
	 */
	public function addFont(SimpleXMLElement $xmlNode, $attributes)
	{
		$font = $xmlNode->addChild('font', '');

		if (isset($attributes['NAME'])) {
			unset($attributes['NAME']);
		}
		if (!isset($attributes['size'])) {
			$attributes['size'] = 15;
		}

		$this->addAttributes($font, $attributes);
	}

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
	public function addRichContentNode(SimpleXMLElement $xml, $attributes, $htmlContent, $addEdgeAttr = array(), $addFontAttr = array())
	{

		return $this->addRichContentNote($xml, $attributes, $htmlContent, $addEdgeAttr, $addFontAttr, 'NODE');
	}

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
	public function addRichContentNote(SimpleXMLElement $xml, $attributes, $htmlContent, $addEdgeAttr = array(), $addFontAttr = array(), $type = 'NOTE')
	{

		$node = $xml->addChild('node', '');
		$attributes = $this->checkNodeAttr($attributes);
		$this->addAttributes($node, $attributes);

		return $node;
	}

	/**
	 * Sets an attribute for a node or font or ...
	 *
	 * @param    array $t3mind
	 * @param    string $t3mindName
	 * @param    array $attributes
	 * @param    string $attributeName
	 * @return    array
	 */
	public function setAttr($t3mind, $t3mindName, $attributes, $attributeName)
	{

		if (isset($t3mind[$t3mindName]) && !empty($t3mind[$t3mindName]) && $t3mind[$t3mindName] !== 'false') {
			$attributes[$attributeName] = $t3mind[$t3mindName];
		}
		return $attributes;
	}

	/**
	 * Sets the font for a node
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    array $t3mind from the DB
	 * @return    array
	 */
	public function setNodeFont(SimpleXMLElement $xmlNode, $t3mind)
	{

		$attributes = array();
		$attributes = $this->setAttr($t3mind, 'style', $attributes, 'ITALIC');
		$attributes = $this->setAttr($t3mind, 'weight', $attributes, 'BOLD');
//		$attributes = $this->setAttr($t3mind, 'decoration', $attributes, 'SIZE');
		$attributes = $this->setAttr($t3mind, 'size', $attributes, 'SIZE');
		$attributes = $this->setAttr($t3mind, 'color', $attributes, 'COLOR');

		if (count($attributes) > 0) {
			$this->addFont($xmlNode, $attributes);
		}
	}

	/**
	 * Checks if neccessary attributes are set for a node
	 *
	 * @param    array $attributes
	 * @return    nothing
	 */
	protected function checkNodeAttr($attributes)
	{

		if (!isset($attributes['ID'])) {
			$attributes['ID'] = 't3m' . mt_rand();
		}

		if (!isset($attributes['TEXT'])) {
			$attributes['TEXT'] = 'No Text set!';
		}

		$attributes['TEXT'] = htmlentities($this->parentObject->strip_tags($attributes['TEXT']), ENT_XML1 | ENT_IGNORE, 'UTF-8');

		if (isset($attributes['LINK']) && empty($attributes['LINK'])) {
			unset($attributes['LINK']);
		}

		return $attributes;
	}

	/**
	 * Creates the TLF attributes array (text, link, folded)
	 *
	 * @param    array $attributes
	 * @return    nothing
	 */
	public function createTLFattr($text, $link = '', $folded = '')
	{
		$a = array();
		if (!empty($text)) {
			$a['TEXT'] = $text;
		}
		if (!empty($link)) {
			$a['LINK'] = $link;
		}
		if (!empty($folded)) {
			$a['FOLDED'] = $folded;
		}

		return $a;
	}

	/**
	 * adds one image to a node
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    array $attributes
	 * @param    string $imgRelPath relativ image path like ../typo3conf/ext/..../ext_icon.gif
	 * @param    string $imgHTML additionl html for the img tag
	 * @return    nothing
	 */
	public function addImgNode(SimpleXMLElement $xmlNode, $attributes, $imgRelPath, $imgHTML = '')
	{
		return $this->addNode($xmlNode, $attributes);
	}

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
	public function addImgNote(SimpleXMLElement $xmlNode, $attributes, $imgRelPath, $imgHTML = '', $noteHTML = '', $addEdgeAttr = array(), $addFontAttr = array())
	{

		$iconLocal = str_replace('../', '', $imgRelPath);

		$img = '';
		if (is_file(PATH_site . $iconLocal)) {
			$img = '<img ' . $imgHTML . ' src="' . $this->parentObject->getBEHttpHost() . $iconLocal . '"/>@#160;@#160;';
		}

		$htmlContent = array(
			'NODE' => $img . htmlspecialchars($attributes['TEXT']),
			'NOTE' => $noteHTML,
		);

		$childNode = $this->addRichContentNote($xmlNode, $attributes, $htmlContent, $addEdgeAttr, $addFontAttr, 'BOTH');


		return $childNode;
	}

	/**
	 * adds multiple images with links to a note node
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    array $attributes
	 * @param    array $images [] = array(path=>,html=>,link=>) relativ image path like ../typo3conf/ext/..../ext_icon.gif
	 * @param    string $noteHTML
	 * @return    nothing
	 */
	public function addImagesNote(SimpleXMLElement $xmlNode, $attributes, $images, $noteHTML)
	{
		return $this->addNode($xmlNode, $attributes);
	}

	/**
	 * adds multiple images with links to a node - HYPERLINKS ARE NOT SUPPORTED in the images BY FREEMIND IN RICHCONTENT NODES!
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    array $attributes
	 * @param    array $images [] = array(path=>,html=>,link=>) relativ image path like ../typo3conf/ext/..../ext_icon.gif
	 * @return    nothing
	 */
	public function addImagesNode(SimpleXMLElement $xmlNode, $attributes, $images)
	{
		return $this->addNode($xmlNode, $attributes);
	}

	/**
	 * adds adds an arrowlink to a destination ...
	 *
	 * @param    SimpleXMLElement $xmlNode
	 * @param    array $attributes
	 * @return    nothing
	 */
	public function addArrowlink(SimpleXMLElement $xmlNode, $attributes)
	{

		return $xmlNode;
	}

	/**
	 * Saves the SimpleXMLElement as a xml file in the typo3temp dir
	 *
	 * @param    SimpleXMLElement $xml
	 * @param    array $attributes  key is the name and value the value
	 * @return    array
	 */
	public function finalOutputFile(SimpleXMLElement $xml)
	{

		$fileName = str_replace('[sitename]', preg_replace('~[^a-z0-9]+~i', '', $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']), $this->parentObject->settings['outputFileName']);

		$fileName = preg_replace('~\[([a-z_\-]+)\]~ie', 'date(\'\\1\')', $fileName);
		$fileName = empty($fileName) ? 'TYPO3Mind_' . mt_rand() . '.mm' : $fileName;

		$xml = str_replace(
				array('|lt|', '|gt|', '@#', '&amp;gt;', '&amp;lt;', '&amp;amp;'), array('<', '>', '&#', '&gt;', '&lt;', '&amp;'), $xml->asXML()
		);

		$fileName = '/typo3temp/' . $fileName;

		$md5 = md5($xml);

		$xml = str_replace(
						array('###MD5_FILE_HASH####'), array($md5), $xml
				) . '<!--HiddenMD5:' . md5($xml) . '-->';

		$bytesWritten = file_put_contents(PATH_site . $fileName, $xml);

		unset($xml);

		if ($bytesWritten === false) {
			die('<h2>Write to file ' . PATH_site . $fileName . ' failed ... check permissions!</h2>');
		} elseif ($bytesWritten == 0) {
			die('<h2>Zero bytes written to file ' . PATH_site . $fileName . ' ... hmmm.... ?</h2>');
		}

		/* check if file has been build successfully */
		$return = array();
		$return['iserror'] = simplexml_load_file(PATH_site . $fileName) === false ? true : false;
		$return['errors'] = array_reverse(libxml_get_errors(), true);
		foreach ($return['errors'] as $k => $v) {
			if ($v->level > 2) {
				$return['errors'][$k] = (array) $v;
			} else {
				unset($return['errors'][$k]);
			}
		}
		$return['filekb'] = sprintf('%.2f', $bytesWritten / 1024);
		$return['file'] = $fileName;

		return $return;
	}

	/**
	 * convert < and > to special internal strings to recover it in xml out to original < and > ;-)
	 *
	 * @param string $string
	 * @return    string
	 */
	public function convertLTGT($string)
	{
		return str_replace(array('<', '>'), array('|lt|', '|gt|'), $string);
	}

}