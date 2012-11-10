<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 1999-2011 Kasper Skårhøj (kasperYYYY@typo3.com)
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
 * Contains base class for creating a browsable array/page/folder tree in HTML
 *
 * @author    Kasper Skårhøj <kasperYYYY@typo3.com>
 * @coauthor    René Fritz <r.fritz@colorcube.de>
 * @cocoauthor    Cyrill Schumacher <cyrill@schumacher.fm>
 *
 * based on t3lib_treeView but modified for the FreeMind ext
 */

class Tx_Typo3mind_Utility_PageTree
{

    // EXTERNAL, static:
    public $expandFirst = 0; // If set, the first element in the tree is always expanded.
    public $thisScript = ''; // Holds the current script to reload to.
    public $title = 'no title'; // Used if the tree is made of records (not folders for ex.)

    /**
     * Needs to be initialized with $GLOBALS['BE_USER']
     * Done by default in init()
     *
     * @var t3lib_beUserAuth
     */
    public $BE_USER = '';

    /**
     * Needs to be initialized with e.g. $GLOBALS['WEBMOUNTS']
     * Default setting in init() is 0 => 0
     * The keys are mount-ids (can be anything basically) and the values are the ID of the root element (COULD be zero or anything else. For pages that would be the uid of the page, zero for the pagetree root.)
     */
    public $MOUNTS = '';


    /**
     * Database table to get the tree data from.
     * Leave blank if data comes from an array.
     * should always be the pages table
     */
    public $table = 'pages';

    /**
     * Defines the field of $table which is the parent id field (like pid for table pages).
     */
    public $parentField = 'pid';

    /**
     * WHERE clause used for selecting records for the tree. Is set by function init.
     * Only makes sense when $this->table is set.
     * @see init()
     */
    public $clause = '';

    /**
     * Field for ORDER BY. Is set by function init.
     * Only makes sense when $this->table is set.
     * @see init()
     */
    public $orderByFields = 'pid,sorting';

    /**
     * Default set of fields selected from the tree table.
     * Make SURE that these fields names listed herein are actually possible to select from $this->table (if that variable is set to a TCA table name)
     * @see addField()
     */
    public $fieldArray = array('uid', 'title', 'deleted', 'hidden', 'doktype', 'shortcut_mode', 'crdate', 'tstamp', 'module',
        'cruser_id', 'starttime', 'endtime', 'storage_pid', 'TSconfig', 'no_cache', 'media', 'subtitle'
    );
    /* @TODO add additional cloumns via TS ... if columns are empty automatic remove ... SysFolderContentListAdditionalColumns */

    /*
     * assoc array for faster access ... if set then this column will be removed when empty()==true
     * @var array
    */
    public $fieldArrayUnsetColumns = array(
        'storage_pid' => 1,
        'starttime' => 1,
        'endtime' => 1,
        'TSconfig' => 1,
        'no_cache' => 1,
        'media' => 1,
        'subtitle' => 1,
    );

    /**
     * List of other fields which are ALLOWED to set (here, based on the "pages" table!)
     * @see addField()
     */
    var $defaultList = 'uid,pid,tstamp,sorting,deleted,perms_userid,perms_groupid,perms_user,perms_group,perms_everybody,crdate,cruser_id';


    /**
     * Sets the associative array key which identifies a new sublevel if arrays are used for trees.
     * This value has formerly been "subLevel" and "--sublevel--"
     */
    public $subLevelID = '_SUB_LEVEL';


    // *********
    // Internal
    // *********
    // For record trees:
    public $ids = array(); // one-dim array of the uid's selected.
    public $ids_hierarchy = array(); // The hierarchy of element uids
    public $orig_ids_hierarchy = array(); // The hierarchy of versioned element uids
    public $buffer_idH = array(); // Temporary, internal array


    public $tree = array(); // Tree is accumulated in this variable
    public $recs = array(); // Accumulates the displayed records.

    public $hasTTContent = array();

    /**
     * Initialize the tree class. Needs to be overwritten
     * Will set ->fieldsArray, and ->clause
     *
     * @param    string        record WHERE clause
     * @param    string        record ORDER BY field
     * @return    void
     */
    public function init($clause = '', $orderByFields = '')
    {
        $this->BE_USER = $GLOBALS['BE_USER']; // Setting BE_USER by default

        if ($clause) {
            $this->clause = $clause;
        } // Setting clause
        if ($orderByFields) {
            $this->orderByFields = $orderByFields;
        }

        if (!is_array($this->MOUNTS)) {
            $this->MOUNTS = array(0 => 0); // dummy
        }

        if ($this->table) {
            t3lib_div::loadTCA($this->table);
        }

        $this->_setHasTTContent();

    }

    private function _setHasTTContent()
    {

        $queryParts = array(
            'SELECT' => 'pid,count(*) cpid',
            'FROM' => 'tt_content',
            'WHERE' => '', // all! even the deleted
            'GROUPBY' => 'pid',
            'ORDERBY' => '',
            'LIMIT' => ''
        );

        $result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($queryParts) or
            die('Please fix this error!<br>' . __FILE__ . ' Line ' . __LINE__ . ":\n<br>\n" . mysql_error() . "<hr>" . var_export($queryParts, 1));

        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
            $this->hasTTContent[$row['pid']] = $row['cpid'];
        }
    }

    /**
     * Adds a field name array to the internal array ->fieldArray
     *
     * @param    array        Field name to
     * @return    boolean
     */
    public function addFields($fields)
    {
        global $TCA;

        if (!is_array($fields) || count($fields) == 0) {
            return false;
        }

        foreach ($fields as $column) {
            if (is_array($TCA[$this->table]['columns'][$column]) || t3lib_div::inList($this->defaultList, $column)) {
                $this->fieldArray[] = $column;
            }

        }
        // unique values only
        $this->fieldArray = array_unique($this->fieldArray);
        return true;
    }


    /**
     * Resets the tree, recs, ids, ids_hierarchy and orig_ids_hierarchy internal variables. Use it if you need it.
     *
     * @return    void
     */
    public function reset()
    {
        $this->tree = array();
        $this->recs = array();
        $this->ids = array();
        $this->ids_hierarchy = array();
        $this->orig_ids_hierarchy = array();
    }


    /*******************************************
     *
     * tree handling
     *
     *******************************************/

    /********************************
     *
     * tree data buidling
     *
     ********************************/

    /**
     * Fetches the data for the tree
     *
     * @param    integer        item id for which to select subitems (parent id)
     * @param    integer        Max depth (recursivity limit)
     * @param    string        HTML-code prefix for recursive calls.
     * @param    string        ? (internal)
     * @return    integer        The count of items on the level
     */
    public function getTree($uid, $depth = 999, $depthData = '', $blankLineCode = '')
    {

        // Buffer for id hierarchy is reset:
        $this->buffer_idH = array();

        // Init vars
        $depth = intval($depth);
        $a = 0;

        $res = $this->_getDataInit($uid);
        $c = $this->_getDataCount($res);
        $crazyRecursionLimiter = 999;

        $idH = array();

        // Traverse the records:
        while ($crazyRecursionLimiter > 0 && $row = $this->_getDataNext($res)) {
            $a++;
            $crazyRecursionLimiter--;

            $newID = $row['uid'];

            if ($newID == 0) {
                throw new RuntimeException('Endless recursion detected: TYPO3 has detected an error in the database. Please fix it manually (e.g. using phpMyAdmin) and change the UID of ' . $this->table . ':0 to a new value.<br /><br />See <a href="http://bugs.typo3.org/view.php?id=3495" target="_blank">bugs.typo3.org/view.php?id=3495</a> to get more information about a possible cause.');
            }
            /* not needed
                        $this->tree[] = array(); // Reserve space.
                        end($this->tree);
                        $treeKey = key($this->tree); // Get the key for this space	*/


            $LN = ($a == $c) ? 'blank' : 'line';

            // If records should be accumulated, do so
            // todo get all columns and store here so that we don't need a 2nd DB query
            $rowRecs = $row;
            foreach ($row as $rrk => $rrv) {
                /* unset empty columns ... @TODO define via TS in settings */
                $rrv = trim($rrv);
                if (isset($this->fieldArrayUnsetColumns[$rrk]) && empty($rrv)) {
                    unset($rowRecs[$rrk]);
                }
            }
            $this->recs[$row['uid']] = $rowRecs;

            // Accumulate the id of the element in the internal arrays
            $this->ids[] = $idH[$row['uid']]['uid'] = $row['uid'];
            $this->ids_hierarchy[$depth][] = $row['uid'];
            $this->orig_ids_hierarchy[$depth][] = isset($row['_ORIG_uid']) ? $row['_ORIG_uid'] : $row['uid'];

            // Make a recursive call to the next level
            if ($depth > 1 && (!isset($row['php_tree_stop']) || $row['php_tree_stop'] == 0)) {
                $nextCount = $this->getTree(
                    $newID,
                    $depth - 1,
                    '',
                    $blankLineCode . ',' . $LN
                );
                if (count($this->buffer_idH)) {
                    $idH[$row['uid']]['subrow'] = $this->buffer_idH;
                }
                $exp = 1; // Set "did expand" flag
            } else {
                $nextCount = $this->_getCount($newID);
                $exp = 0; // Clear "did expand" flag
            }
            /*
                            not needed
                            // Finally, add the row/HTML content to the ->tree array in the reserved key.
                        $this->tree[$treeKey] = array(
                            'row' => $row,
                            'invertedDepth' => $depth,
                            'blankLineCode' => $blankLineCode,
                        ); */
        }

        $this->_getDataFree($res);
        $this->buffer_idH = $idH;
        return $c;
    }


    /********************************
     *
     * Data handling
     * Works with records and arrays
     *
     ********************************/

    /**
     * Returns the number of records having the parent id, $uid
     *
     * @param    integer        id to count subitems for
     * @return    integer
     * @access private
     */
    private function _getCount($uid)
    {

        return $GLOBALS['TYPO3_DB']->exec_SELECTcountRows(
            'uid',
            $this->table,
            $this->parentField . '=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($uid, $this->table) .
                t3lib_BEfunc::deleteClause($this->table) .
                t3lib_BEfunc::versioningPlaceholderClause($this->table) .
                $this->clause // whereClauseMightContainGroupOrderBy
        );
    }

    /**
     * Returns the record for a uid.
     * For tables: Looks up the record in the database.
     * For arrays: Returns the fake record for uid id.
     *
     * @param    integer        UID to look up
     * @return    array        The record
     */
    private function _getRecord($uid)
    {
        return t3lib_BEfunc::getRecordWSOL($this->table, $uid);
    }

    /**
     * Getting the tree data: Selecting/Initializing data pointer to items for a certain parent id.
     * For tables: This will make a database query to select all children to "parent"
     *
     * @param    integer        parent item id
     * @return    mixed        data handle (Tables: An sql-resource, arrays: A parentId integer. -1 is returned if there were NO subLevel.)
     * @access private
     */
    private function _getDataInit($parentId)
    {
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            implode(',', $this->fieldArray),
            $this->table,
            $this->parentField . '=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($parentId, $this->table) .
                t3lib_BEfunc::deleteClause($this->table) .
                t3lib_BEfunc::versioningPlaceholderClause($this->table) .
                $this->clause, // whereClauseMightContainGroupOrderBy
            '',
            $this->orderByFields
        );
        return $res;
    }

    /**
     * Getting the tree data: Counting elements in resource
     *
     * @param    mixed        data handle
     * @return    integer        number of items
     * @access private
     * @see _getDataInit()
     */
    private function _getDataCount(&$res)
    {
        $c = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
        return $c;
    }

    /**
     * Getting the tree data: next entry
     *
     * @param    mixed        data handle
     * @return    array        item data array OR FALSE if end of elements.
     * @access private
     * @see _getDataInit()
     */
    private function _getDataNext(&$res)
    {
        while ($row = @$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            t3lib_BEfunc::workspaceOL($this->table, $row, $this->BE_USER->workspace, TRUE);
            if (is_array($row)) {
                break;
            }
        }
        return $row;
    }

    /**
     * Getting the tree data: frees data handle
     *
     * @param    mixed        data handle
     * @return    void
     * @access private
     */
    private function _getDataFree(&$res)
    {
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
    }


    /*
        array(
            [id1] => array(
                'title'=>'title...',
                'id' => 'id1',
                'icon' => 'icon ref, relative to typo3/ folder...'
            ),
            [id2] => array(
                'title'=>'title...',
                'id' => 'id2',
                'icon' => 'icon ref, relative to typo3/ folder...'
            ),
            [id3] => array(
                'title'=>'title...',
                'id' => 'id3',
                'icon' => 'icon ref, relative to typo3/ folder...'
                $this->subLevelID => array(
                    [id3_asdf#1] => array(
                        'title'=>'title...',
                        'id' => 'asdf#1',
                        'icon' => 'icon ref, relative to typo3/ folder...'
                    ),
                    [5] => array(
                        'title'=>'title...',
                        'id' => 'id...',
                        'icon' => 'icon ref, relative to typo3/ folder...'
                    ),
                    [6] => array(
                        'title'=>'title...',
                        'id' => 'id...',
                        'icon' => 'icon ref, relative to typo3/ folder...'
                    ),
                )
            ),
        )
*/
}
