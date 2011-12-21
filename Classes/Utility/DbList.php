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
	 * sets the pid
	 * @param pid
	 * @return	void
	 */
	public function setPID($pid){
		$this->id = (int)$pid;
	}


	/**
	 * Traverses the table(s) to be listed and renders the output code for each:
	 * The HTML is accumulated in $this->HTMLcode
	 * Finishes off with a stopper-gif
	 *
	 * @return	void
	 */
	public function generateList()	{
		global $TCA;


		$this->pidSelect = 'pid='.intval($this->id);



			// Traverse the TCA table array:
		foreach ($TCA as $tableName => $value) {

			// Load full table definitions:
			t3lib_div::loadTCA($tableName);

// for later ... Don't show table if hidden by TCA ctrl section
//		$hideTable = $GLOBALS['TCA'][$tableName]['ctrl']['hideTable'] ? TRUE : FALSE;

			// Setting fields to select:
			$fields = $this->makeFieldList($value,$tableName);
/*
 echo '<pre>';
 var_dump( $value['ctrl']['enablecolumns'] );
 //var_dump($this->setFields);
 exit; */

				$orderBy = ($value['ctrl']['sortby']) ? 'ORDER BY '.$value['ctrl']['sortby'] : ( isset($value['ctrl']['default_sortby']) ? $value['ctrl']['default_sortby'] : 'ORDER BY uid desc' );

				$sql = 'select '.implode(',',$fields).' from '.$tableName.' where '.$this->pidSelect.' '.$orderBy.' limit 0,10';
echo "$sql<br>";

		}/* endforeach */

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
	 * @return	array		Array, where values are fieldnames to include in query
	 */
	function makeFieldList($tcaCurrent,$table)	{
		global $TCA;

		$fields = array();
		if (is_array($TCA[$table]))	{
			 

			$fields = array('uid','pid');
			foreach($this->addFieldsDependedIfTheyAreSetOrNot as $k=>$column){
				if( isset( $tcaCurrent['ctrl'][$column] ) && !empty($tcaCurrent['ctrl'][$column]) ){
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

				if( $k == 'enablecolumns' ){
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






}
