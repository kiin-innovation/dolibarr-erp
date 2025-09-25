<?php
/* Copyright (C) 2024 John BOTELLA
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

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');				// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK', '1');				// Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL', '1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK', '1');				// Do not check style html tag into posted data
if (! defined('NOREQUIREMENU')) define('NOREQUIREMENU', '1');				// If there is no need to load and show top and left menu
if (! defined('NOREQUIREHTML')) define('NOREQUIREHTML', '1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX')) define('NOREQUIREAJAX', '1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN", '1');					// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined('NOIPCHECK'))                define('NOIPCHECK', '1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT', 'auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE', 'aloginmodule');	// Force authentication handler
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN', 1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message
//if (! defined("FORCECSP"))                 define('FORCECSP', 'none');				// Disable all Content Security Policies
//if (! defined('CSRFCHECK_WITH_TOKEN'))     define('CSRFCHECK_WITH_TOKEN', '1');		// Force use of CSRF protection with tokens even for GET
//if (! defined('NOBROWSERNOTIF'))     		 define('NOBROWSERNOTIF', '1');				// Disable browser notification


$sapi_type = php_sapi_name();
$script_file = basename(__FILE__);
$path = dirname(__FILE__) . '/';

// Include and load Dolibarr environment variables
$res = 0;
if (!$res && file_exists($path . "main.inc.php")) $res = @include($path . "main.inc.php");
if (!$res && file_exists($path . "../main.inc.php")) $res = @include($path . "../main.inc.php");
if (!$res && file_exists($path . "../../main.inc.php")) $res = @include($path . "../../main.inc.php");
if (!$res && file_exists($path . "../../../main.inc.php")) $res = @include($path . "../../../main.inc.php");
if (!$res) die("Include of master fails");


require_once __DIR__ . '/class/jsonResponse.class.php';

require_once __DIR__ . '/class/collabTrackPresence.class.php';
if (!class_exists('Validate')) { require_once DOL_DOCUMENT_ROOT . '/core/class/validate.class.php'; }

global $langs, $db, $hookmanager, $user, $mysoc;
/**
 * @var DoliDB $db
 */
$hookmanager->initHooks('collabtrackinterface');

// Load traductions files requiredby by page
$langs->loadLangs(array("collabtrack@collabtrack", "other", 'main'));

$action = GETPOST('action');

// Security check
if (!isModEnabled('collabtrack')) accessforbidden('Module not enabled');

$jsonResponse = new collabtrack\JsonResponse();



$reshook = $hookmanager->executeHooks('collabtrackInterface', [], $jsonResponse, $action);
if ($reshook < 0) {
	$jsonResponse->msg = $hookmanager->error;
	if(!empty($hookmanager->errors)){
		$jsonResponse->msg = (!empty($hookmanager->error) ? '<br>' : '') . implode('<br>', $hookmanager->errors);
	}
	$jsonResponse->result = 0;
	print $jsonResponse->getResponse();
	$db->close();    // Close $db database opened handler
	exit;
}


if($reshook > 0) {
	// Nothing to do all is done in hook
}
elseif($action === 'ping') {
	__pingUserOnDocument($jsonResponse);
}
else{
	$jsonResponse->msg = 'Action not found';
}

print $jsonResponse->getResponse();

$db->close();    // Close $db database opened handler

/**
 * @param collabtrack\JsonResponse $jsonResponse
 * @return bool|void
 */
function __pingUserOnDocument($jsonResponse){
	global $user, $langs, $db;
	$jsonResponse->data = new stdClass();
	$jsonResponse->data->users = [];
	$data = GETPOST("data", "array");

	if(empty($data['elementid']) && !is_numeric($data['elementid'])){
		$jsonResponse->msg = 'Need element Id';
		return false;
	}

	if(empty($data['elementtype'])){
		$jsonResponse->msg = 'Need element type';
		return false;
	}

	$ctPresence = new CollabTrackPresence($db);
	$addResult = $ctPresence->addElementInUserHistory((int)$user->id, (int)$data['elementid'], $data['elementtype'], !empty($data['edit']));
	if($addResult < 0){
		$jsonResponse->result = 0;
		$jsonResponse->msg = 'Error add ping code '.(int)$addResult. ' '.$ctPresence->errorsToString();
		return false;
	}

	if ($user->hasRight('collabtrack', 'presence', 'read')) {

		$sql = 'SELECT entity,fk_user,element_id,element_type, MAX(action_edit) action_edit, MAX(date_last_view) date_last_view ';
		$sql .= " FROM ".$ctPresence->db->prefix().$ctPresence->table_element.' as t';
		$sql.= ' WHERE entity IN ('.getEntity($data['elementtype']).')';
		$sql.= ' AND fk_user != ' . (int)$user->id;
		$sql.= ' AND element_id = ' . (int)$data['elementid'];
		$sql.= ' AND element_type = "'.$db->escape($data['elementtype']).'" ';
		$sql.= ' AND date_last_view > "'.$db->idate(time() - getDolGlobalInt('COLLABTRACK_PING_OFFSET', 60)).'" ';
		$sql.= ' GROUP BY entity,fk_user,element_id,element_type ';
		$sql.= ' ORDER BY MAX(action_edit) DESC, rowid DESC ';
		$sql.= ' LIMIT 50 ';

		$presences = $db->getRows($sql);
		if($presences){
			foreach ($presences as $presence ) {
				$userIn = new User($db);
				if($userIn->fetch($presence->fk_user)<=0){
					continue;
				}

				$userReadingItem = new stdClass();
				$userReadingItem->userName = $userIn->getFullName($langs);
				$userReadingItem->userImg = CollabTrackPresence::getUserImg($userIn, 'collab-track-user-img');
				$userReadingItem->edit = (int)$presence->action_edit;
				$userReadingItem->userId = $userIn->id;

				$jsonResponse->data->users[] = $userReadingItem;
			}
		}

	}

	$jsonResponse->result = 1;
	return true;
}

