<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2009 Kasper Skårhøj (kasperYYYY@typo3.com)
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
 * Include file extending t3lib_recordList
 * Shared between Web>List (db_list.php) and Web>Page (sysext/cms/layout/db_layout.php)
 *
 * $Id$
 * Revised for TYPO3 3.6 December/2003 by Kasper Skårhøj
 * XHTML compliant
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   86: class recordList extends t3lib_recordList
 *  148:     function start($id,$table,$pointer,$search="",$levels="",$showLimit=0)
 *  211:     function generateList()
 *  275:     function getSearchBox($formFields=1)
 *  319:     function showSysNotesForPage()
 *
 *              SECTION: Various helper functions
 *  421:     function thumbCode($row,$table,$field)
 *  434:     function makeQueryArray($table, $id, $addWhere="",$fieldList='*')
 *  481:     function setTotalItems($queryParts)
 *  536:     function linkWrapTable($table,$code)
 *  553:     function linkWrapItems($table,$uid,$code,$row)
 *  617:     function linkUrlMail($code,$testString)
 *  644:     function listURL($altId='',$table=-1,$exclList='')
 *  663:     function requestUri()
 *  674:     function makeFieldList($table,$dontCheckUser=0)
 *  721:     function getTreeObject($id,$depth,$perms_clause)
 *  739:     function localizationRedirect($justLocalized)
 *
 * TOTAL FUNCTIONS: 17
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */











/**
 * Child class for rendering of Web > List (not the final class. see class.db_list_extra)
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage core
 * @see localRecordList
 */
class Tx_Typo3mind_Utility_DbList /* extends t3lib_recordList */ {

		// External, static:
	var $tableList='';				// Specify a list of tables which are the only ones allowed to be displayed.
	var $returnUrl='';				// Return URL
	var $thumbs = 0;				// Boolean. Thumbnails on records containing files (pictures)
	var $itemsLimitPerTable = 20;			// default Max items shown per table in "multi-table mode", may be overridden by tables.php
	var $itemsLimitSingleTable = 100;		// default Max items shown per table in "single-table mode", may be overridden by tables.php
	var $widthGif = '<img src="clear.gif" width="1" height="4" hspace="160" alt="" />';
	var $script = 'index.php';			// Current script name
	var $allFields=1;				// Indicates if all available fields for a user should be selected or not.
	var $localizationView=FALSE;			// Whether to show localization view or not.

		// Internal, static: GPvar:
	var $csvOutput=FALSE;				// If set, csvList is outputted.
	var $sortField;					// Field, to sort list by
	var $sortRev;					// Field, indicating to sort in reverse order.
	var $displayFields;				// Array, containing which fields to display in extended mode
	var $duplicateField;				// String, can contain the field name from a table which must have duplicate values marked.

		// Internal, static:
	var $id;					// Page id
	var $table='';					// Tablename if single-table mode
	var $listOnlyInSingleTableMode=FALSE;		// If true, records are listed only if a specific table is selected.
	var $firstElementNumber=0;			// Pointer for browsing list
	var $showLimit=0;				// Number of records to show
	var $pidSelect='';				// List of ids from which to select/search etc. (when search-levels are set high). See start()
	var $perms_clause='';				// Page select permissions
	var $calcPerms=0;				// Some permissions...
	var $clickTitleMode = '';			// Mode for what happens when a user clicks the title of a record.
	var $modSharedTSconfig = array();		// Shared module configuration, used by localization features
	var $pageRecord = array();		// Loaded with page record with version overlay if any.
	var $hideTables = '';			// Tables which should not get listed
	var $tableTSconfigOverTCA = array(); //TSconfig which overwrites TCA-Settings
	var $tablesCollapsed = array(); // Array of collapsed / uncollapsed tables in multi table view

		// Internal, dynamic:
	var $JScode = '';				// JavaScript code accumulation
	var $HTMLcode = '';				// HTML output
	var $iLimit=0;					// "LIMIT " in SQL...
	var $eCounter=0;				// Counting the elements no matter what...
	var $totalItems='';				// Set to the total number of items for a table when selecting.
	var $recPath_cache=array();			// Cache for record path
	var $setFields=array();				// Fields to display for the current table
	var $currentTable = array();			// Used for tracking next/prev uids
	var $duplicateStack=array();			// Used for tracking duplicate values of fields

	var $modTSconfig;				// module configuratio

	
	
	public function setPID($pid){
		$this->id = (int)$pid;
	}

	/**
	 * Initializes the list generation
	 *
	 * @param	integer		Page id for which the list is rendered. Must be >= 0
	 * @param	string		Tablename - if extended mode where only one table is listed at a time.
	 * @param	integer		Browsing pointer.
	 * @param	string		Search word, if any
	 * @param	integer		Number of levels to search down the page tree
	 * @param	integer		Limit of records to be listed.
	 * @return	void
	 */
	function start($id,$table,$pointer,$search="",$levels="",$showLimit=0)	{
		global $TCA;

			// Setting internal variables:
		$this->id=intval($id);					// sets the parent id
		if ($TCA[$table])	$this->table=$table;		// Setting single table mode, if table exists:
		$this->firstElementNumber=$pointer;

		$this->showLimit=t3lib_div::intInRange($showLimit,0,10000);



			// Init dynamic vars:
		$this->counter=0;
		$this->JScode='';
		$this->HTMLcode='';

			// limits
		if(isset($this->modTSconfig['properties']['itemsLimitPerTable'])) {
			$this->itemsLimitPerTable = t3lib_div::intInRange(intval($this->modTSconfig['properties']['itemsLimitPerTable']), 1, 10000);
		}
		if(isset($this->modTSconfig['properties']['itemsLimitSingleTable'])) {
			$this->itemsLimitSingleTable = t3lib_div::intInRange(intval($this->modTSconfig['properties']['itemsLimitSingleTable']), 1, 10000);
		}

			// Set select levels:
		$this->perms_clause = $GLOBALS['BE_USER']->getPagePermsClause(1);

			// this will hide records from display - it has nothing todo with user rights!!
		if ($pidList = $GLOBALS['BE_USER']->getTSConfigVal('options.hideRecords.pages')) {
			if ($pidList = $GLOBALS['TYPO3_DB']->cleanIntList($pidList)) {
				$this->perms_clause .= ' AND pages.uid NOT IN ('.$pidList.')';
			}
		}


		$this->pidSelect = 'pid='.intval($id);

			// Initialize languages:
		if ($this->localizationView)	{
			$this->initializeLanguages();
		}
	}

	/**
	 * Traverses the table(s) to be listed and renders the output code for each:
	 * The HTML is accumulated in $this->HTMLcode
	 * Finishes off with a stopper-gif
	 *
	 * @return	void
	 */
	function generateList()	{
		global $TCA;


		/* see TCA */
		$addFieldsDependedIfTheyAreSetOrNot = array( /*yeah nice array name ;-) */
			'label',
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
	
			// Traverse the TCA table array:
		foreach ($TCA as $tableName => $value) {

	

					// Load full table definitions:
				t3lib_div::loadTCA($tableName);

					// Don't show table if hidden by TCA ctrl section
				$hideTable = $GLOBALS['TCA'][$tableName]['ctrl']['hideTable'] ? TRUE : FALSE;

				
				

					// Setting fields to select:

				// $fields = $this->makeFieldList($tableName);
				$fields = array('uid','pid');

				foreach($addFieldsDependedIfTheyAreSetOrNot as $k=>$column){
					if( isset( $value['ctrl'][$column] ) && !empty($value['ctrl'][$column]) ){
						$fields[]=$value['ctrl'][$column];
					}
					
					if( $k == 'enablecolumns' ){
						foreach($column as $kc=>$vc){
							
							if( isset( $value['ctrl'][$k][$vc] ) && !empty($value['ctrl'][$k][$vc]) ){
								$fields[]=$value['ctrl'][$k][$vc];
							}						
						}
					}

				}
/*
 echo '<pre>'; 
 var_dump( $value['ctrl']['enablecolumns'] ); 
 //var_dump($this->setFields);
 exit; */
				
/*				if (is_array($this->setFields[$tableName]))	{
					$fields = array_intersect($fields,$this->setFields[$tableName]);
				} else {
					$fields = array();
				} */

					// keine ahnung ob wir das hier brauchen ...
					$this->pidSelect = 'pid='.intval($this->id);
				

				// Finally, render the list:
					
				// $this->HTMLcode.=$this->getTable($tableName, $this->id, implode(',',$fields));
				$sql = 'select '.implode(',',$fields).' from '.$tableName.' where '.$this->pidSelect;
echo "$sql<br>";				
			
		}/* endforeach */
		
	}

	
	/**
	
	SchumacherFM for later implementation
	
	 * Creates the display of sys_notes for the page.
	 * Relies on the "sys_note" extension to be loaded.
	 *
	 * @return	string		HTML for the sys-notes (if any)
	 */
	function showSysNotesForPage()	{
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





	
	/**
	 * Returns the SQL-query array to select the records from a table $table with pid = $id
	 *
	 * @param	string		Table name
	 * @param	integer		Page id (NOT USED! $this->pidSelect is used instead)
	 * @param	string		Additional part for where clause
	 * @param	string		Field list to select, * for all (for "SELECT [fieldlist] FROM ...")
	 * @return	array		Returns query array
	 */
	function makeQueryArray($table, $id, $addWhere='', $fieldList='*')	{
		global $TCA, $TYPO3_CONF_VARS;

		$hookObjectsArr = array();
		if (is_array ($TYPO3_CONF_VARS['SC_OPTIONS']['typo3/class.db_list.inc']['makeQueryArray'])) {
			foreach ($TYPO3_CONF_VARS['SC_OPTIONS']['typo3/class.db_list.inc']['makeQueryArray'] as $classRef) {
				$hookObjectsArr[] = t3lib_div::getUserObj($classRef);
			}
		}

			// Set ORDER BY:
		$orderBy = ($TCA[$table]['ctrl']['sortby']) ? 'ORDER BY '.$TCA[$table]['ctrl']['sortby'] : $TCA[$table]['ctrl']['default_sortby'];
		if ($this->sortField)	{
			if (in_array($this->sortField,$this->makeFieldList($table,1)))	{
				$orderBy = 'ORDER BY '.$this->sortField;
				if ($this->sortRev)	$orderBy.=' DESC';
			}
		}

			// Set LIMIT:
		$limit = $this->iLimit ? ($this->firstElementNumber ? $this->firstElementNumber.',' : '').($this->iLimit+1) : '';

			// Filtering on displayable pages (permissions):
		$pC = ($table=='pages' && $this->perms_clause)?' AND '.$this->perms_clause:'';


			// Compiling query array:
		$queryParts = array(
			'SELECT' => $fieldList,
			'FROM' => $table,
			'WHERE' => $this->pidSelect.
						' '.$pC.
						t3lib_BEfunc::deleteClause($table).
						t3lib_BEfunc::versioningPlaceholderClause($table).
						' '.$addWhere.
						' '.$search,
			'GROUPBY' => '',
			'ORDERBY' => $GLOBALS['TYPO3_DB']->stripOrderBy($orderBy),
			'LIMIT' => $limit
		);

			// Apply hook as requested in http://bugs.typo3.org/view.php?id=4361
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'makeQueryArray_post')) {
				$_params = array(
					'orderBy' => $orderBy,
					'limit' => $limit,
					'pC' => $pC,
					'search' => $search,
				);
				$hookObj->makeQueryArray_post($queryParts, $this, $table, $id, $addWhere, $fieldList, $_params);
			}
		}

			// Return query:
		return $queryParts;
	}

	/**
	 * Based on input query array (query for selecting count(*) from a table) it will select the number of records and set the value in $this->totalItems
	 *
	 * @param	array		Query array
	 * @return	void
	 * @see makeQueryArray()
	 */
	function setTotalItems($queryParts)	{
		$this->totalItems = $GLOBALS['TYPO3_DB']->exec_SELECTcountRows(
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
	 * @param	boolean		If set, also adds crdate and tstamp fields (note: they will also be added if user is admin or dontCheckUser is set)
	 * @return	array		Array, where values are fieldnames to include in query
	 */
	function makeFieldList($table,$dontCheckUser=0,$addDateFields=0)	{
		global $TCA,$BE_USER;

			// Init fieldlist array:
		$fieldListArr = array();

			// Check table:
		if (is_array($TCA[$table]))	{
			t3lib_div::loadTCA($table);

				// Traverse configured columns and add them to field array, if available for user.
			foreach($TCA[$table]['columns'] as $fN => $fieldValue)	{
				if ($dontCheckUser ||
					((!$fieldValue['exclude'] || $BE_USER->check('non_exclude_fields',$table.':'.$fN)) && $fieldValue['config']['type']!='passthrough'))	{
					$fieldListArr[]=$fN;
				}
			}

				// Add special fields:
			if ($dontCheckUser || $BE_USER->isAdmin())	{
				$fieldListArr[]='uid';
				$fieldListArr[]='pid';
			}

				// Add date fields
			if ($dontCheckUser || $BE_USER->isAdmin() || $addDateFields)	{
				if ($TCA[$table]['ctrl']['tstamp'])	$fieldListArr[]=$TCA[$table]['ctrl']['tstamp'];
				if ($TCA[$table]['ctrl']['crdate'])	$fieldListArr[]=$TCA[$table]['ctrl']['crdate'];
			}

				// Add more special fields:
			if ($dontCheckUser || $BE_USER->isAdmin())	{
				if ($TCA[$table]['ctrl']['cruser_id'])	$fieldListArr[]=$TCA[$table]['ctrl']['cruser_id'];
				if ($TCA[$table]['ctrl']['sortby'])	$fieldListArr[]=$TCA[$table]['ctrl']['sortby'];
				if ($TCA[$table]['ctrl']['versioningWS'])	{
					$fieldListArr[]='t3ver_id';
					$fieldListArr[]='t3ver_state';
					$fieldListArr[]='t3ver_wsid';
					if ($table==='pages')	{
						$fieldListArr[]='t3ver_swapmode';
					}
				}
			}
		}
		return $fieldListArr;
	}
 

}
