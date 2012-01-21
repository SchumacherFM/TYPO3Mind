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

class Tx_Typo3mind_Utility_DbList {

		// Internal, static:
	private $id;					// Page id
	/*
	not needed but for future implementations for T3Mind

	private $table='';					// Tablename if single-table mode
	private $listOnlyInSingleTableMode=FALSE;		// If true, records are listed only if a specific table is selected.
	private $firstElementNumber=0;			// Pointer for browsing list
	private $showLimit=0;				// Number of records to show
	private $pidSelect='';				// List of ids from which to select/search etc. (when search-levels are set high). See start()
	private $perms_clause='';				// Page select permissions
	private $calcPerms=0;				// Some permissions...
	private $clickTitleMode = '';			// Mode for what happens when a user clicks the title of a record.
	private $modSharedTSconfig = array();		// Shared module configuration, used by localization features
	private $pageRecord = array();		// Loaded with page record with version overlay if any.
	private $hideTables = '';			// Tables which should not get listed
	private $tableTSconfigOverTCA = array(); //TSconfig which overwrites TCA-Settings
	private $tablesCollapsed = array(); // Array of collapsed / uncollapsed tables in multi table view

		// Internal, dynamic:
	private $JScode = '';				// JavaScript code accumulation
	private $HTMLcode = '';				// HTML output
	private $iLimit=0;					// "LIMIT " in SQL...
	private $eCounter=0;				// Counting the elements no matter what...
	private $totalItems='';				// Set to the total number of items for a table when selecting.
	private $recPath_cache=array();			// Cache for record path
	private $setFields=array();				// Fields to display for the current table
	private $currentTable = array();			// Used for tracking next/prev uids
	private $duplicateStack=array();			// Used for tracking duplicate values of fields

	private $modTSconfig;				// module configuratio
	*/
		/* see TCA */
	private $addFieldsDependedIfTheyAreSetOrNot = array( /*yeah nice array name ;-) */
			'label',
			'label_alt',
			'tstamp',
			'crdate',
			'cruser_id',
			'languageField',
			'delete',
			'enablecolumns' => array(
				'disabled',
				'starttime',
				'endtime',
			),

		);

	/**
	 * returns all
	 * @var array
	 */
	private $tablesInSysFolder;

	/**
	 * the parent object
	 * @var object
	 */
	private $parentObject;

	/**
	 * sets the pid
	 * @param pid
	 * @return	void
	 */
	public function __construct(&$parentObject){
		$this->parentObject = $parentObject;
	}

	/**
	 * sets the pid
	 * @param pid
	 * @return	void
	 */
	public function setPID($pid){
		$this->id = (int)$pid;
	}

	/**
	 * prints the sysfolder content for ONE uid
	 * TR = Tree recursive
	 *
	 * @param	SimpleXMLElement 	$xmlNode
	 * @param	integer				$uid
	 * @param	integer				$depth
	 * @param	array				$t3mind		for recursive mode!
	 * @return	SimpleXMLElement
	 */
	public function getTRsysFolderContent(SimpleXMLElement &$xmlNode,$uid,$depth,$t3mind = NULL) {
		GLOBAL $TCA;
		$this->setPID($uid);
		$this->_generateList();

		// todo hier geht es weiter ... LINKS einbauen, icons, etc TCA hide table auswerten ...
		foreach($this->tablesInSysFolder as $tableName=>$values){

			/* we do not want to list the pages in a sysfolder ... that means subsysfolders */
			if( $tableName == 'pages' ){ continue; }
		
			$attr = array('ID'=>'tISF139');
			$llangTitle = $GLOBALS['LANG']->sL( $TCA[$tableName]['ctrl']['title'] );
			$attr['TEXT'] = ( !empty($llangTitle) ? $llangTitle : $tableName ) . ' (C:'.$values['TotalItems'].')';

			unset($values['TotalItems']);
			if( count($values) > 1 ){
				$attr['FOLDED'] = 'true';
			}
			if( $this->parentObject->mapMode['isbe'] ) {
				$attr['LINK'] = $this->parentObject->getBEHttpHost().'typo3/mod.php?M=web_list&id='.$uid.'&table='.$tableName;
			}

			$attr = $this->parentObject->setAttr($t3mind,'font_color',$attr,'COLOR');
			$attr = $this->parentObject->setAttr($t3mind,'node_color',$attr,'BACKGROUND_COLOR');

			// table icon?
			$icon = $TCA[$tableName]['ctrl']['iconfile'];
			$tableNode = $this->parentObject->addImgNode($xmlNode,$attr,$icon);

			/*<add font>*/
			$this->parentObject->setNodeFont($tableNode,$t3mind);
			/*</add font>*/

			/*<list the entries in a sysfolder node>*/
			foreach($values as $k=>$row){

				$attr = array('ID'=>'tISF139v','TEXT'=>$row['titInt0'] /* we'll have it in the note: .' (ID:'.$row['uid'].')' */ );
				$attr = $this->parentObject->setAttr($t3mind,'font_color',$attr,'COLOR');
				$attr = $this->parentObject->setAttr($t3mind,'node_color',$attr,'BACKGROUND_COLOR');

				if( $this->parentObject->mapMode['isbe'] ) {
					$attr['LINK'] = $this->parentObject->getBEHttpHost().'typo3/alt_doc.php?edit['.$tableName.']['.$row['uid'].']=edit';
				}

				$htmlContent = $this->parentObject->getNoteContentFromRow($tableName,$row);
				$rowNode = $this->parentObject->addRichContentNote($tableNode,$attr,$htmlContent);


				if( isset($row['deleted']) && $row['deleted'] == 1 ){
					$this->parentObject->addIcon($rowNode,'button_cancel');
				}
				if( isset($row['hidden']) && $row['hidden'] == 1 ){
					$this->parentObject->addIcon($rowNode,'closed');
				}
				$this->parentObject->setNodeFont($rowNode,$t3mind);


			}/*endforeach*/
			/*</list the entries in a sysfolder node>*/

		}/*endforeach*/

	}/*</getTRsysFolderContent>*/

	/**
	 * Traverses the table(s) to be listed and renders the output code for each:
	 * The HTML is accumulated in $this->HTMLcode
	 * Finishes off with a stopper-gif
	 *
	 * @return	void
	 */
	private function _generateList()	{
		global $TCA;


		$this->pidSelect = 'pid='.intval($this->id);


		$this->tablesInSysFolder = array();
			// Traverse the TCA table array:
		foreach ($TCA as $tableName => $value) {

			// Load full table definitions:
			t3lib_div::loadTCA($tableName);

			// for later ... Don't show table if hidden by TCA ctrl section
			// $hideTable = $GLOBALS['TCA'][$tableName]['ctrl']['hideTable'] ? TRUE : FALSE;

			/* Setting fields to select: */
			$fields = $this->_makeFieldList($value,$tableName);

			/* get user defined columns ... for each table listet in a SysFolder */
			if( isset($this->parentObject->settings['SysFolderContentListAdditionalColumns'][$tableName]) ){

				$sqlRaw = preg_replace('~[^a-z0-9_,]+~i','',$this->parentObject->settings['SysFolderContentListAdditionalColumns'][$tableName]);
				$sqlRawE = t3lib_div::trimExplode(',',$sqlRaw,1);
				$fields = array_merge($fields,$sqlRawE);
			}

			$orderBy = isset($value['ctrl']['sortby']) ? 'ORDER BY '.$value['ctrl']['sortby'] : ( isset($value['ctrl']['default_sortby']) ? $value['ctrl']['default_sortby'] : 'ORDER BY uid desc' );

			$queryParts = array(
				'SELECT' => implode(',',$fields),
				'FROM' => $tableName,
				'WHERE' => $this->pidSelect,
				'GROUPBY' => '',
				'ORDERBY' => $GLOBALS['TYPO3_DB']->stripOrderBy($orderBy),
				'LIMIT' => '0,10'
			);

			$result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($queryParts);
			$dbCount = $GLOBALS['TYPO3_DB']->sql_num_rows($result);

			$accRows = false;
			if( $dbCount ){
				$this->tablesInSysFolder[$tableName] = array(
					'TotalItems'=>$this->_getTotalItems($queryParts),
				);
				$accRows = array();	// Accumulate rows here
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))	{
					// In offline workspace, look for alternative record:
					// t3lib_BEfunc::workspaceOL($table, $row, $GLOBALS['BE_USER']->workspace, TRUE);

					if (is_array($row))	{
						$accRows = true;
						$this->tablesInSysFolder[$tableName][] = $row;
					}
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($result);
			} /* endif $dbCount */


		}/* endforeach */

	}



	/**
	 * Based on input query array
	 *
	 * @param	array		Query array
	 * @return	void
	 */
	private function _getTotalItems($queryParts)	{
		return $GLOBALS['TYPO3_DB']->exec_SELECTcountRows(
			'*',
			$queryParts['FROM'],
			$queryParts['WHERE']
		);
	}




	/**
	 * Makes the list of fields to select for a table
	 *
	 * @param	string		Table name
	 * @param	boolean		If set, users access to the field (non-exclude-fields) is NOT checked.
	 * @return	array		Array, where values are fieldnames to include in query
	 */
	private function _makeFieldList($tcaCurrent,$table)	{
		global $TCA;

		$fields = array();
		if (is_array($TCA[$table]))	{


			$fields = array('uid','pid');
			foreach($this->addFieldsDependedIfTheyAreSetOrNot as $k=>$column){
				/* @TODO $column for "enablecolumns" resolven  */
				
				if( !is_array($column) && isset( $tcaCurrent['ctrl'][$column] ) && !empty($tcaCurrent['ctrl'][$column]) ){
					$fields[$column]=$tcaCurrent['ctrl'][$column];
					if( $column == 'label' ){
						$fields[$column] .= ' as titInt0'; /* title internal */
					}
					elseif( $column == 'label_alt' ){
						/* just to be secure that no one has entered several commas without column names, avoid SQL errors */
						$exploded = t3lib_div::trimExplode(',',$tcaCurrent['ctrl'][$column],1);
						foreach($exploded as $ke=>$ve){
							$ke++;
							$exploded[$ke] = $ve.' as titInt'.$ke;
						}
						$fields[$column] = implode(',',$exploded);
					}

				}

				if( $k === 'enablecolumns' ){
					foreach($column as $kc=>$vc){
						if( isset( $tcaCurrent['ctrl'][$k][$vc] ) && !empty($tcaCurrent['ctrl'][$k][$vc]) ){
							$fields[$vc]=$tcaCurrent['ctrl'][$k][$vc];
						}
					}
				}

			}/*endforeach*/
	
		} /*endif is array*/
		return $fields;

	}


	/**

	SchumacherFM for later implementation

	 * Creates the display of sys_notes for the page.
	 * Relies on the "sys_note" extension to be loaded.
	 *
	 * @return	string		HTML for the sys-notes (if any)
	 */
	public function showSysNotesForPage()	{
		global $TCA;

		$out='';

			// Checking if extension is loaded:
		if (!t3lib_extMgm::isLoaded('sys_note'))	return '';

			// Create query for selecting the notes:
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','sys_note','pid IN ('.$this->id.') AND (personal=0 OR cruser='.intval($GLOBALS['BE_USER']->user['uid']).')'.t3lib_BEfunc::deleteClause('sys_note').t3lib_BEfunc::versioningPlaceholderClause('sys_note'));

			// Executing query:
		$dbCount = $GLOBALS['TYPO3_DB']->sql_num_rows($result);

			// If some notes were found, render them:
		if ($dbCount)	{
			$cat = array();

				// Load full table description:
			t3lib_div::loadTCA('sys_note');

				// Traverse note-types and get labels:
			if ($TCA['sys_note'] && $TCA['sys_note']['columns']['category'] && is_array($TCA['sys_note']['columns']['category']['config']['items']))	{
				foreach($TCA['sys_note']['columns']['category']['config']['items'] as $el)	{
					$cat[$el[1]]=$GLOBALS['LANG']->sL($el[0]);
				}
			}

				// For each note found, make rendering:
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))	{

					// Create content:
				$iconImg = t3lib_iconWorks::getSpriteIconForRecord('sys_note', $row);
				$subject = htmlspecialchars($row['subject']);
				$fields = array();
				$fields['Author:'] = htmlspecialchars($row['author'].($row['email'] && $row['author'] ? ', ':'').$row['email']);
				$fields['Category:'] = htmlspecialchars($cat[$row['category']]);
				$fields['Note:'] = nl2br(htmlspecialchars($row['message']));

					// Compile content:
				$out.='


				<!--
					Sys-notes for list module:
				-->
					<table border="0" cellpadding="1" cellspacing="1" id="typo3-dblist-sysnotes">
						<tr><td colspan="2" class="bgColor2">'.$iconImg.'<strong>'.$subject.'</strong></td></tr>
						<tr><td class="bgColor4">'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.category',1).'</td><td class="bgColor4">'.$fields['Category:'].'</td></tr>
						<tr><td class="bgColor4">'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.author',1).'</td><td class="bgColor4">'.$fields['Author:'].'</td></tr>
						<tr><td class="bgColor4">'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.note',1).'</td><td class="bgColor4">'.$fields['Note:'].'</td></tr>
					</table>
				';
			}
		}
		return $out;
	}

}