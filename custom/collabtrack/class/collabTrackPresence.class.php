<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2025 THERSANE
 *
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file        class/collabtrackpresence.class.php
 * \ingroup     collabtrackpresence
 * \brief       This file is a CRUD class file for CollabTrackPresence (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Class for CollabTrackPresence
 */
class CollabTrackPresence extends CommonObject
{
	/**
	 * @var string ID of module.
	 */
	public $module = 'collabtrack';

	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'presence';

	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
	 */
	public $table_element = 'collabtrack_presence';

	/**
	 * @var int  Does this object support multicompany module ?
	 * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
	 */
	public $ismultientitymanaged = 1;

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 0;

	/**
	 * @var string String with name of icon for collabtrackpresence. Must be the part after the 'object_' into object_collabtrackpresence.png
	 */
	public $picto = 'collabtrack@collabtrack';

	/**
	 *  'type' field format ('integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter[:Sortfield]]]', 'sellist:TableName:LabelFieldName[:KeyFieldName[:KeyFieldParent[:Filter[:Sortfield]]]]', 'varchar(x)', 'double(24,8)', 'real', 'price', 'text', 'text:none', 'html', 'date', 'datetime', 'timestamp', 'duration', 'mail', 'phone', 'url', 'password')
	 *         Note: Filter can be a string like "(t.ref:like:'SO-%') or (t.date_creation:<:'20160101') or (t.nature:is:NULL)"
	 *  'label' the translation key.
	 *  'picto' is code of a picto to show before value in forms
	 *  'enabled' is a condition when the field must be managed (Example: 1 or '$conf->global->MY_SETUP_PARAM)
	 *  'position' is the sort order of field.
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form only (not create). 5=Visible on list and view only (not create/not update). Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'noteditable' says if field is not editable (1 or 0)
	 *  'default' is a default value for creation (can still be overwrote by the Setup of Default Values if field is editable in creation form). Note: If default is set to '(PROV)' and field is 'ref', the default value will be set to '(PROVid)' where id is rowid when a new record is created.
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 or 2 if field can be used for measure. Field type must be summable like integer or double(24,8). Use 1 in most cases, or 2 if you don't want to see the column total into list (for example for percentage)
	 *  'css' and 'cssview' and 'csslist' is the CSS style to use on field. 'css' is used in creation and update. 'cssview' is used in view mode. 'csslist' is used for columns in lists. For example: 'css'=>'minwidth300 maxwidth500 widthcentpercentminusx', 'cssview'=>'wordbreak', 'csslist'=>'tdoverflowmax200'
	 *  'help' is a 'TranslationString' to use to show a tooltip on field. You can also use 'TranslationString:keyfortooltiponlick' for a tooltip on click.
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'disabled' is 1 if we want to have the field locked by a 'disabled' attribute. In most cases, this is never set into the definition of $fields into class, but is set dynamically by some part of code.
	 *  'arrayofkeyval' to set a list of values if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel"). Note that type can be 'integer' or 'varchar'
	 *  'autofocusoncreate' to have field having the focus on a create form. Only 1 field should have this property set to 1.
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *	'validate' is 1 if need to validate with $this->validateField()
	 *  'copytoclipboard' is 1 or 2 to allow to add a picto to copy value into clipboard (1=picto after label, 2=picto after value)
	 *
	 *  Note: To have value dynamic, you can set value to 0 in definition and edit the value on the fly into the constructor.
	 */


	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'css'=>'left', 'comment'=>"Id"),
		'entity' =>array('type'=>'integer', 'label'=>'Entity', 'default'=>1, 'enabled'=>1, 'visible'=>-2, 'notnull'=>1, 'position'=>10, 'index'=>1),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>0, 'visible'=>-2,),
		'fk_user' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'enabled'=>'1', 'position'=>25, 'notnull'=>1, 'visible'=>-2, 'foreignkey'=>'user.rowid',),
		'element_id' => array('type'=>'integer', 'label'=>'Elementid', 'enabled'=>'1', 'position'=>30, 'notnull'=>0, 'visible'=>-1,),
		'element_type' => array('type'=>'varchar(60)', 'label'=>'Elementtype', 'enabled'=>'1', 'position'=>35, 'notnull'=>0, 'visible'=>-1,),
		'action_edit' => array('type'=>'varchar(32)', 'label'=>'ActionEdit', 'enabled'=>'1', 'position'=>35, 'notnull'=>0, 'visible'=>-1,),
		'date_last_view' => array('type'=>'timestamp', 'label'=>'DateLastView', 'enabled'=>'1', 'position'=>40, 'notnull'=>1, 'visible'=>1,),
	);
	public $rowid;
	public $entity;
	public $tms;
	public $fk_user;
	public $element_id;
	public $element_type;
	public $date_last_view;
	public $action_edit;

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $langs;

		$this->db = $db;

		if (empty(getDolGlobalString('MAIN_SHOW_TECHNICAL_ID')) && isset($this->fields['rowid'])) {
			$this->fields['rowid']['visible'] = 0;
		}
		if (!isModEnabled('multicompany') && isset($this->fields['entity'])) {
			$this->fields['entity']['enabled'] = 0;
		}

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val) {
			if (isset($val['enabled']) && empty($val['enabled'])) {
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs)) {
			foreach ($this->fields as $key => $val) {
				if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval'])) {
					foreach ($val['arrayofkeyval'] as $key2 => $val2) {
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		return $this->createCommon($user, $notrigger);
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param $id ID Object
	 * @param $userid ID User
	 * @param $elementid ID Element
	 * @param $elementtype Element type
	 * @return int <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $userid = 0, $elementid = 0, $elementtype = '', $edit = false)
	{
		if(!empty($id)) {
			return $this->fetchCommon($id);
		} else if(!empty($userid) && !empty($elementid) && !empty($elementtype)) {
			$moreWhere = ' AND fk_user = ' . (int)$userid;
			$moreWhere.= ' AND element_id = ' . (int)$elementid;
			$moreWhere.= ' AND element_type = "'.$this->db->escape($elementtype).'" ';
			$moreWhere.= ' AND action_edit = ' . (int)$edit;
			return $this->fetchCommon(0, null, $moreWhere);
		}
		return -1;
	}



	/**
	 * @param CommonObject    $object
	 * @return string formatted as elementType
	 */
	static public function getObjectElementType($object)
	{
		// Elements of the core modules which have `$module` property but may to which we don't want to prefix module part to the element name for finding the linked object in llx_element_element.
		// It's because an entry for this element may be exist in llx_element_element before this modification (version <=14.2) and ave named only with their element name in fk_source or fk_target.
		$coreModules = array('knowledgemanagement', 'partnership', 'workstation', 'ticket', 'recruitment', 'eventorganization');
		// Add module part to target type if object has $module property and isn't in core modules.

		if(!empty($object->module) && !in_array($object->module, $coreModules)){
			$modulePrefix = $object->module . '_';
			if(strpos($object->element, $modulePrefix) === false){
				return $modulePrefix.$object->element;
			}
		}

		return $object->element;
	}


	/**
	 * Load list of objects in memory from the database.
	 *
	 * @param  string      $sortorder    Sort Order
	 * @param  string      $sortfield    Sort field
	 * @param  int         $limit        limit
	 * @param  int         $offset       Offset
	 * @param  array       $filter       Filter array. Example array('field'=>'valueforlike', 'customurl'=>...)
	 * @param  string      $filtermode   Filter mode (AND or OR)
	 * @return array|int                 int <0 if KO, array of pages if OK
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
	{
		global $conf;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$records = array();

		$sql = "SELECT ";
		$sql .= $this->getFieldList('t');
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) {
			$sql .= " WHERE t.entity IN (".getEntity($this->table_element).")";
		} else {
			$sql .= " WHERE 1 = 1";
		}
		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 't.rowid') {
					$sqlwhere[] = $key." = ".((int) $value);
				} elseif (in_array($this->fields[$key]['type'], array('date', 'datetime', 'timestamp'))) {
					$sqlwhere[] = $key." = '".$this->db->idate($value)."'";
				} elseif ($key == 'customsql') {
					$sqlwhere[] = $value;
				} elseif (strpos($value, '%') === false) {
					$sqlwhere[] = $key." IN (".$this->db->sanitize($this->db->escape($value)).")";
				} else {
					$sqlwhere[] = $key." LIKE '%".$this->db->escape($value)."%'";
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= " AND (".implode(" ".$filtermode." ", $sqlwhere).")";
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .= $this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < ($limit ? min($limit, $num) : $num)) {
				$obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);
				$record->object = $this->getObjectByElement($record->element_type, $record->element_id);

				if($record->object){
					$records[$record->id] = $record;
				}

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		return $this->deleteCommon($user, $notrigger);
	}

	/**
	 * Add an element in the user history
	 * Update the user history
	 *
	 * @param int $userid ID of concerned user
	 * @param int $elementid ID of element concerned
	 * @param string $elementtype Type of element concerned
	 * @param int $nbToKeep Number max of element to keep in history for the user
	 * @return int 0 on success, < 0 on error
	 */
	public function addElementInUserHistory(int $userid, int $elementid, string $elementtype, $edit = false)
	{
		global $user;

		$this->db->begin();

		// We try to load the element in the user history to check if it's already existing
		$res = $this->fetch(0, $userid, $elementid, $elementtype, $edit);
		if($res < 0){
			return -1;
		}

		if ($res > 0) { // Element is already in history, we just update the last view date
			$this->date_last_view = dol_now();
			if($this->update($user) < 0){
				$this->db->rollback();
				return -2;
			}
		} else { // Element is not in user history so we add it
			$this->fk_user = $userid;
			$this->element_id = $elementid;
			$this->element_type = $elementtype;
			$this->date_last_view = dol_now();
			$this->action_edit = (int)$edit;
			if($this->create($user) < 0){
				$this->db->rollback();
				return -3;
			}
		}

		$this->db->commit();

		if($this->cleanUserHistory($userid) < 0) {
			return -4;
		}

		return 0;
	}

	/**
	 * Remove user history
	 *
	 * @param int $userid ID of the concerned user
	 * @return int 1 on success, < 0 on error
	 */
	public function cleanUserHistory(int $userid)
	{
		$sql = 'DELETE FROM '.MAIN_DB_PREFIX.$this->table_element;
		$sql.= ' WHERE fk_user = '.$userid;
		$sql.= ' AND date_last_view < "'.$this->db->idate(time() - getDolGlobalInt('COLLABTRACK_MAX_HISTORY_OFFSET', 300)).'" ';

		$resql = $this->db->query($sql);
		if($resql === false) {
			$this->errors[] = $this->db->lasterror();
			return -1;
		}

		return 1;
	}

	/**
	 * Remove history
	 *
	 * @return int 1 on success, < 0 on error
	 */
	public function cleanHistory()
	{
		$sql = 'DELETE FROM '.MAIN_DB_PREFIX.$this->table_element;
		$sql.= ' AND date_last_view < "'.$this->db->idate(time() - getDolGlobalInt('COLLABTRACK_MAX_HISTORY_OFFSET', 300)).'" ';

		$resql = $this->db->query($sql);
		if(!$resql) {
			$this->errors[] = $this->db->lasterror();
			return -1;
		}

		return 1;
	}

	/**
	 * @param User 		$user
	 * @param string 	$cssClass	CSS name to use on img for photo
	 * @param string 	$imageSize 	'mini', 'small' or '' (original)
	 * @return string html image
	 */
	static public function getUserImg(User $user, $cssClass = '', $imageSize = 'small')
	{
		$modulepart = 'userphoto';
		if (!class_exists('Form')) { include_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php'; }
		return Form::showphoto($modulepart, $user, 0, 0, 0, $cssClass, $imageSize, false, 1);
	}
}
