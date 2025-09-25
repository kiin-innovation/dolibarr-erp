<?php
/*
 * Copyright (C) 2025 THERSANE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    collabtrack/class/actions_collabtrack.class.php
 * \ingroup collabtrack
 * \brief   Example hook overload.
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonhookactions.class.php';

/**
 * Class ActionsCollabtrack
 */
class ActionsCollabtrack extends CommonHookActions
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	/**
	 * @var string[] Errors
	 */
	public $errors = array();

	/**
	 * @var mixed[] Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var ?string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var int		Priority of hook (50 is used if value is not defined)
	 */
	public $priority;

	/**
	 * Constructor
	 *
	 *  @param	DoliDB	$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Overloading the formObjectOptions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function formObjectOptions($parameters, &$object, &$action, $hookmanager)
	{
		global $user, $db;

		if(!empty($user) && $this->isContext($parameters, 'globalcard') && !empty($object) && is_object($object) && !empty($object->element) && !empty($object->id)) {
			require_once __DIR__ . '/collabTrackPresence.class.php';
			$collabTrackPresence = new CollabTrackPresence($db);
			$res = $collabTrackPresence->addElementInUserHistory($user->id, $object->id, $collabTrackPresence->getObjectElementType($object), $this->isEditAction($action));
			if($res < 0) {
				$this->error = 'CollabTrackError';
				$this->errors = $collabTrackPresence->errors;
				return 0;
			}

			print $this->generateJsCall($user->id, $object->id, $collabTrackPresence->getObjectElementType($object), $this->isEditAction($action));
		}

		return 0; // no fail mode
	}

	public function isEditAction($action){
//			if(in_array($action, ['edit', 'editline'])) {
//				return true;
//			}
		return !empty($action);
	}

	/**
	 * @param int    $userid
	 * @param int    $elementid
	 * @param string $elementtype
	 * @param false  $edit
	 *
	 * @return int|string
	 */
	public function generateJsCall(int $userid, int $elementid, string $elementtype, $edit = false) {
		global $langs;

		$print = GETPOST('optioncss');
		if($print == 'print') return '';

		$langs->load('collabtrack@collabtrack');

		$confToJs = [
			'interfaceUrl' => dol_buildpath('collabtrack/interface.php', 1),
			'token' => newToken(),
			'elementid' => $elementid,
			'elementtype' => $elementtype,
			'edit' => (int)$edit,
			'pingWait' => getDolGlobalInt('COLLABTRACK_PING_WAIT', 10)
		];

		$jsLangs = [
			'errorAjaxCall' => $langs->trans('ErrorAjaxCall')
		];

		return '<script nonce="' . getNonce() . '" >collabTrack.init(' . json_encode($confToJs) . ', ' . json_encode($jsLangs) . ');</script>';
	}
}
