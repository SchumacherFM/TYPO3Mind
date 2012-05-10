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
 * @package typo3mind
 * @subpackage formats
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 */
class Tx_Typo3mind_Domain_Export_Formats_Freemind implements Tx_Typo3mind_Domain_Export_Formats_FormatInterface /* extends SimpleXMLElement */ {
    /**
     * The root root root node of the xml file
     *
     * @var SimpleXMLElement
     */
    protected $mapXmlRoot;

    /**
     * @var string
     */
    protected $mmVersion = '0.9.0';

    /**
     * gets the root map element and creates the map
     *
     * @param    none
     * @return    SimpleXMLElement
     */
    public function getMap() {
        $this->mapXmlRoot = new SimpleXMLElement('<map></map>', LIBXML_NOXMLDECL | LIBXML_PARSEHUGE);
        $this->mapXmlRoot->addAttribute('version',$this->mmVersion);


        $attributes = array(
            'COLOR'=>'#993300',
        );

        $html = '<center><img src="'.$this->getBEHttpHost().'typo3/sysext/t3skin/icons/gfx/loginlogo_transp.gif" alt="TYPO3 Logo" />
        <h2>'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'].'</h2>
        <p style="font-size:10px;">TYPO3: '.TYPO3_version.'</p></center>';
        $rootNode = $this->addRichContentNode($this->mapXmlRoot,$attributes,$html);

        $ThisFileInfoNode = $this->addImgNode($rootNode,array(
            'POSITION'=>'left',
//            'FOLDED'=>'false',
            'TEXT'=>$this->translate('tree.fileInfo'),
        ), 'typo3/sysext/about/ext_icon.gif' );



        $this->addNode($ThisFileInfoNode,array(
            'TEXT'=>'Backend HTTP Address: '.$this->getBEHttpHost(),
        ));
        $this->addNode($ThisFileInfoNode,array(
            'TEXT'=>'Created: '.date('Y-m-d H:i:s'),
	    ));
        $this->addNode($ThisFileInfoNode,array(
            'TEXT'=>'MD5 Hash: ###MD5_FILE_HASH####',
        ));
        $this->addNode($ThisFileInfoNode,array(
            'TEXT'=>'Map Mode: '.$this->settings['mapMode'],
        ));

        return $rootNode;
    }


   /**
     * Converts meaningful xml characters to xml entities
     *
     * @param  string
     * @return string
     */
    public function xmlentities($value = ''){
        return trim( str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;'), (string)$value) );
    }

    /**
     * adds attributs to a Child
     *
     * @param    SimpleXMLElement $xmlNode
     * @param    array $attributes
     * @return    nothing
     */
    public function addAttributes(SimpleXMLElement $xmlNode,$attributes) {
        foreach($attributes as $k=>$v){
            if( $v <> '' ){
                $xmlNode->addAttribute($k,$this->xmlentities($v) );
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
    public function addIcon(SimpleXMLElement $xmlNode,$iconName) {

        $iconName = preg_replace('~\.[a-z]{3,4}~i','',$iconName);
        if( stristr($iconName,',') !== false ){
            $icons = t3lib_div::trimExplode(',',$iconName,1);
            foreach($icons as $name){
                $icon = $xmlNode->addChild('icon','');
                $this->addAttributes($icon,array('BUILTIN'=>$name));
            }
        }else{
            $icon = $xmlNode->addChild('icon','');
            $this->addAttributes($icon,array('BUILTIN'=>$iconName));
        }
    }

    /**
     * adds a node
     *
     * @param    SimpleXMLElement $xmlNode
     * @param    array $attributes  key is the name and value the value
     * @return    SimpleXMLElement
     */
    public function addNode(SimpleXMLElement $xmlNode,$attributes) {
        $child = $xmlNode->addChild('node','');

        $this->addAttributes($child, $this->checkNodeAttr($attributes) );
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
    public function addNote(SimpleXMLElement $xmlNode,$attributes,$html) {
        return $this->addRichContentNote($xmlNode,$attributes,$html,array(),array(), 'NOTE' );
    }

    /**
     * adds an edge
     *
     * @param    SimpleXMLElement $xmlNode
     * @param    array $attributes
     * @return    nothing
     */
    public function addEdge(SimpleXMLElement $xmlNode,$attributes) {
        $edge = $xmlNode->addChild('edge','');

        if( !isset($attributes['STYLE']) ){
            $attributes['STYLE'] = 'bezier';
        }
        if( !isset($attributes['WIDTH']) ){
            $attributes['WIDTH'] = 'thin';
        }

        $this->addAttributes($edge,$attributes);

    }

    /**
     * adds a cloud
     *
     * @param    SimpleXMLElement $xmlNode
     * @param    array $attributes
     * @return    nothing
     */
    public function addCloud(SimpleXMLElement $xmlNode,$attributes) {
        $cloud = $xmlNode->addChild('cloud','');
        $this->addAttributes($cloud,$attributes);
    }

    /**
     * adds a font
     *
     * @param    SimpleXMLElement $xmlNode
     * @param    array $attributes
     * @return    nothing
     */
    public function addFont(SimpleXMLElement $xmlNode,$attributes) {
        $font = $xmlNode->addChild('font','');

        if( !isset($attributes['NAME']) ){
            $attributes['NAME'] = 'SansSerif';
        }
        if( !isset($attributes['SIZE']) ){
            $attributes['SIZE'] = 12;
        }

        $this->addAttributes($font,$attributes);
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
    public function addRichContentNode(SimpleXMLElement $xml,$attributes,$htmlContent,$addEdgeAttr = array(),$addFontAttr = array()  ) {

        return $this->addRichContentNote($xml,$attributes,$htmlContent,$addEdgeAttr,$addFontAttr, 'NODE' );

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
    public function addRichContentNote(SimpleXMLElement $xml,$attributes,$htmlContent,$addEdgeAttr = array(),$addFontAttr = array(), $type = 'NOTE' ) {

        $htmlContent = str_replace( array('<','>'), array('|lt|','|gt|'), $htmlContent );

        $css = '';

        $node = $xml->addChild('node','');
        $attributes = $this->checkNodeAttr($attributes);
        $this->addAttributes($node,$attributes);

        $realType = $type;
        if( $type == 'BOTH' ){

            $rc = $node->addChild('richcontent','');
            $rc->addAttribute('TYPE','NOTE');
            $html = $rc->addChild('html','');
                    $html->addChild('head',$css);
            $body = $html->addChild('body',$htmlContent['NOTE']);

            $htmlContent = $htmlContent['NODE'];
            $realType = 'NODE';
        }

        $rc = $node->addChild('richcontent','');
        $rc->addAttribute('TYPE',$realType);
        $html = $rc->addChild('html','');
                $html->addChild('head',$css);
        $body = $html->addChild('body',$htmlContent);



        if( count($addEdgeAttr)>0 ){
            $this->addEdge($node, $addEdgeAttr );
        }
        if( count($addFontAttr)>0 ){
            $this->addFont($node, $addFontAttr );
        }

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
    public function setAttr($t3mind,$t3mindName,$attributes,$attributeName) {

        if( isset($t3mind[$t3mindName]) && !empty($t3mind[$t3mindName]) && $t3mind[$t3mindName] !== 'false' ){
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
    public function setNodeFont(SimpleXMLElement $xmlNode,$t3mind) {
        $attributes = array();
        $attributes = $this->setAttr($t3mind,'font_face',$attributes,'NAME');
        $attributes = $this->setAttr($t3mind,'font_size',$attributes,'SIZE');
        $attributes = $this->setAttr($t3mind,'font_bold',$attributes,'BOLD');
        $attributes = $this->setAttr($t3mind,'font_italic',$attributes,'ITALIC');

        if( count($attributes)>0 ){
            $this->addFont($xmlNode,$attributes);
        }
    }

    /**
     * Checks if neccessary attributes are set for a node
     *
     * @param    array $attributes
     * @return    nothing
     */
    protected function checkNodeAttr($attributes) {

        if( !isset($attributes['ID']) ){
            $attributes['ID'] = 't3m'.mt_rand();
        }

        if( !isset($attributes['TEXT']) ){
            $attributes['TEXT'] = 'No Text set!';
        }

        $attributes['TEXT'] = htmlentities($this->strip_tags( $attributes['TEXT'] ) ,ENT_XML1 | ENT_IGNORE,'UTF-8' );

        if( isset($attributes['LINK']) && empty($attributes['LINK']) ){
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
    protected function createTLFattr($text,$link='',$folded='') {
        $a = array();
        if( !empty($text) ){ $a['TEXT'] = $text; }
        if( !empty($link) ){ $a['LINK'] = $link; }
        if( !empty($folded) ){ $a['FOLDED'] = $folded; }

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
    public function addImgNode(SimpleXMLElement $xmlNode,$attributes,$imgRelPath,$imgHTML='') {

        $iconLocal = str_replace('../','',$imgRelPath);

        if( is_file(PATH_site.$iconLocal)  ){

            $nodeHTML = '<img '.$imgHTML.' src="'.$this->getBEHttpHost().$iconLocal.'"/>'.
                        '@#160;@#160;'.htmlspecialchars( $attributes['TEXT'] );
            $childNode = $this->addRichContentNode($xmlNode, $attributes ,$nodeHTML);

        }else {
            $childNode = $this->addNode($xmlNode,$attributes);
        }

        return $childNode;
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
    public function addImgNote(SimpleXMLElement $xmlNode, $attributes, $imgRelPath, $imgHTML = '', $noteHTML='', $addEdgeAttr = array(), $addFontAttr = array()) {

        $iconLocal = str_replace('../','',$imgRelPath);

        $img = '';
        if( is_file(PATH_site.$iconLocal)  ){
            $img = '<img '.$imgHTML.' src="'.$this->getBEHttpHost().$iconLocal.'"/>@#160;@#160;';
        }

        $htmlContent = array(
            'NODE' => $img . htmlspecialchars( $attributes['TEXT']),
            'NOTE' => $noteHTML,
        );

        $childNode = $this->addRichContentNote($xmlNode, $attributes ,$htmlContent,$addEdgeAttr,$addFontAttr,'BOTH');


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
    public function addImagesNote(SimpleXMLElement $xmlNode,$attributes,$images,$noteHTML ) {

        $html = array();

        foreach($images as $img){
            $iconLocal = str_replace('../','',$img['path']);
            if( is_file(PATH_site.$iconLocal)  ){

                if( isset($img['link']) ){
                    $img['link'] = str_replace('&','&amp;',$img['link']);
                    $html[] = '<a href="'.$img['link'].'"><img border="0" '.$img['html'].' src="'.$this->getBEHttpHost().$iconLocal.'"/></a>';
                }else{
                    $html[] = '<img '.$img['html'].' src="'.$this->getBEHttpHost().$iconLocal.'"/>';
                }

            }
        }
        $htmlContent = array(
            'NODE' => implode('@#160;@#160;',$html).'@#160;@#160;'.htmlspecialchars( $attributes['TEXT'] ),
            'NOTE' => $noteHTML,
        );

        if( count($html) > 0  ){

            $childNode = $this->addRichContentNote($xmlNode, $attributes ,$htmlContent,array(),array(),'BOTH');

        }else {
            $childNode = $this->addRichContentNote($xmlNode, $attributes ,$noteHTML,array(),array(),'BOTH');
        }
        return $childNode;
    }

    /**
     * adds multiple images with links to a node - HYPERLINKS ARE NOT SUPPORTED in the images BY FREEMIND IN RICHCONTENT NODES!
     *
     * @param    SimpleXMLElement $xmlNode
     * @param    array $attributes
     * @param    array $images [] = array(path=>,html=>,link=>) relativ image path like ../typo3conf/ext/..../ext_icon.gif
     * @return    nothing
     */
    public function addImagesNode(SimpleXMLElement $xmlNode,$attributes,$images ) {

        $html = array();

        foreach($images as $img){
            $iconLocal = str_replace('../','',$img['path']);
            if( is_file(PATH_site.$iconLocal)  ){

                if( isset($img['link']) ){
                    $img['link'] = str_replace('&','&amp;',$img['link']);
                    $html[] = '<a href="'.$img['link'].'"><img border="0" '.$img['html'].' src="'.$this->getBEHttpHost().$iconLocal.'"/></a>';
                }else{
                    $html[] = '<img '.$img['html'].' src="'.$this->getBEHttpHost().$iconLocal.'"/>';
                }

            }
        }

        if( count($html) > 0  ){

            $nodeHTML = implode('@#160;@#160;',$html).'@#160;@#160;'.htmlspecialchars( $attributes['TEXT'] );
            $childNode = $this->addRichContentNode($xmlNode, $attributes ,$nodeHTML);

        }else {
            $childNode = $this->addNode($xmlNode,$attributes);
        }

        return $childNode;
    }
    /**
     * adds adds an arrowlink to a destination ...
     *
     * @param    SimpleXMLElement $xmlNode
     * @param    array $attributes
     * @return    nothing
     */
    public function addArrowlink(SimpleXMLElement $xmlNode,$attributes) {

        // @todo set arrow color ... somewhere ...
        $attributes['COLOR'] = '#FF0025';
        $attributes['ENDARROW'] = 'Default'; /* there is an arrow */
        $attributes['STARTARROW'] = 'Default'; /* there is an arrow */
        $attributes['ID'] = 'Arrow_ID_'.mt_rand();
        $attributes['ENDINCLINATION'] = '440;0;';
        $attributes['STARTINCLINATION'] = '440;0;';

        if( !isset($attributes['DESTINATION']) || empty($attributes['DESTINATION']) ){
            die('addArrowlink(): DESTINATION not set!');
        }

        $child = $xmlNode->addChild('arrowlink','');

        $this->addAttributes($child, $this->checkNodeAttr($attributes) );
        return $child;

    }
    /**
     * Saves the SimpleXMLElement as a xml file in the typo3temp dir
     *
     * @param    SimpleXMLElement $xml
     * @param    array $attributes  key is the name and value the value
     * @return    array
     */
    protected function finalOutputFile(SimpleXMLElement $xml) {

        $fileName = str_replace('[sitename]',
            preg_replace('~[^a-z0-9]+~i','',$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']),
            $this->settings['outputFileName']);

        $fileName = preg_replace('~\[([a-z_\-]+)\]~ie','date(\'\\1\')',$fileName);
        $fileName = empty($fileName) ? 'TYPO3Mind_'.mt_rand().'.mm' : $fileName;

        $xml = str_replace(
            array('|lt|',    '|gt|',    '@#',    '&amp;gt;',    '&amp;lt;',    '&amp;amp;'),
            array('<',        '>',    '&#',    '&gt;',        '&lt;',        '&amp;'),
            $xml->asXML()
        );

        $fileName = '/typo3temp/'.$fileName;

        $md5 = md5($xml);

        $xml = str_replace(
            array('###MD5_FILE_HASH####'),
            array($md5),
            $xml
        ).'<!--HiddenMD5:'.md5($xml).'-->';

        $bytesWritten = file_put_contents(PATH_site.$fileName, $xml );

        unset($xml);

        if( $bytesWritten === false ){
            die('<h2>Write to file '.PATH_site.$fileName.' failed ... check permissions!</h2>');
        }
        elseif( $bytesWritten == 0 ){
            die('<h2>Zero bytes written to file '.PATH_site.$fileName.' ... hmmm.... ?</h2>');
        }

        /* check if file has been build successfully */
        $return = array();
        $return['iserror'] = simplexml_load_file(PATH_site.$fileName) === false ? true : false;
        $return['errors'] = array_reverse( libxml_get_errors(), true);
        foreach($return['errors'] as $k=>$v){
            if( $v->level > 2 ){ $return['errors'][$k] = (array)$v; } else { unset($return['errors'][$k]); }
        }
        $return['filekb'] = sprintf('%.2f',$bytesWritten/1024);
        $return['file'] = $fileName;

        return $return;
    }
    /**
     * convert < and > to special internal strings to recover it in xml out to original < and > ;-)
     *
     * @param string $string
     * @return    string
     */
    public function convertLTGT($string){
        return str_replace( array('<','>'), array('|lt|','|gt|'), $string );
    }

}