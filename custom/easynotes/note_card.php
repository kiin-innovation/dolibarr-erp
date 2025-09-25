<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
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
 *   	\file       note_card.php
 *		\ingroup    easynotes
 *		\brief      Page to create/edit/view note
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
dol_include_once('/easynotes/class/note.class.php');
dol_include_once('/easynotes/lib/easynotes_note.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("easynotes@easynotes", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'notecard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
//$lineid   = GETPOST('lineid', 'int');

// Initialize technical objects
$object = new Note($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->easynotes->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('notecard', 'globalcard')); // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all = GETPOST("search_all", 'alpha');
$search = array();
foreach ($object->fields as $key => $val) {
	if (GETPOST('search_'.$key, 'alpha')) {
		$search[$key] = GETPOST('search_'.$key, 'alpha');
	}
}

if (empty($action) && empty($id) && empty($ref)) {
	$action = 'view';
}

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once.


$permissiontoread = 1; //$user->rights->easynotes->note->read;
$permissiontoadd = 1; // $user->rights->easynotes->note->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
$permissiontodelete = 1; // $user->rights->easynotes->note->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
$permissionnote = $user->rights->easynotes->note->write; // Used by the include of actions_setnotes.inc.php
$permissiondellink = $user->rights->easynotes->note->write; // Used by the include of actions_dellink.inc.php
$upload_dir = $conf->easynotes->multidir_output[isset($object->entity) ? $object->entity : 1].'/note';

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->element, $object->id, $object->table_element, '', 'fk_soc', 'rowid', $isdraft);
//if (empty($conf->easynotes->enabled)) accessforbidden();
//if (!$permissiontoread) accessforbidden();


/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	//setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	$error = 0;

	$backurlforlist = dol_buildpath('/easynotes/easynotesindex.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/easynotes/easynotesindex.php', 1).'?noteid='.($id > 0 ? $id : '__ID__');
			}
		}
	}

	$triggermodname = 'EASYNOTES_NOTE_MODIFY'; // Name of trigger action code to execute when we modify record

	// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';

	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Action to move up and down lines of object
	//include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';

	// Action to build doc
	include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';

	if ($action == 'set_thirdparty' && $permissiontoadd) {
		$object->setValueFrom('fk_soc', GETPOST('fk_soc', 'int'), '', '', 'date', '', $user, $triggermodname);
	}
	if ($action == 'classin' && $permissiontoadd) {
		$object->setProject(GETPOST('projectid', 'int'));
	}
	
}




/*
 * View
 *
 * Put here all code to build page
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);

$title = $langs->trans("EasyNotes");
$help_url = '';
llxHeader('', $title, $help_url);

// Example : Adding jquery code
// print '<script type="text/javascript" language="javascript">
// jQuery(document).ready(function() {
// 	function init_myfunc()
// 	{
// 		jQuery("#myid").removeAttr(\'disabled\');
// 		jQuery("#myid").attr(\'disabled\',\'disabled\');
// 	}
// 	init_myfunc();
// 	jQuery("#mybutton").click(function() {
// 		init_myfunc();
// 	});
// });
// </script>';


// Part to create
if ($action == 'create') {
	//print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("EasyNotes")), '', 'object_'.$object->picto);
	//print load_fiche_titre($langs->trans("EasyNote"), '', 'object_'.$object->picto);
	?>
	<table class="centpercent notopnoleftnoright table-fiche-title">
		<tr>
			<td class="nobordernopadding widthpictotitle valignmiddle col-picto">
				<span class="far fa-clipboard infobox-project valignmiddle pictotitle widthpictotitle" style=""></span>
			</td>
			<td class="nobordernopadding valignmiddle col-title">
				<div class="titre inline-block">
					<span style="padding: 0px; padding-right: 3px !important;">EasyNotes</span>
				</div>
			</td>
			<td class="nobordernopadding valignmiddle col-title" align="right">
				<a class="btnTitle btnTitlePlus" href="<?php echo DOL_URL_ROOT; ?>/custom/easynotes/easynotesindex.php" title="EasyNotes"><span class="fa fa-list-alt valignmiddle btnTitle-icon"></span></a>
			</td>
		</tr>
	</table>
	<?php

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}

	print dol_get_fiche_head(array(), '');

	// Set some default values
	//if (! GETPOSTISSET('fieldname')) $_POST['fieldname'] = 'myvalue';

	print '<table class="border centpercent tableforfieldcreate">'."\n";

	// Common attributes
	//include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_add.tpl.php';
	//************************************88
	$object->fields = dol_sort_array($object->fields, 'position');

	foreach ($object->fields as $key => $val) {
		// Discard if extrafield is a hidden field on form
		if (abs($val['visible']) != 1 && abs($val['visible']) != 3) {
			continue;
		}

		if (array_key_exists('enabled', $val) && isset($val['enabled']) && !verifCond($val['enabled'])) {
			continue; // We don't want this field
		}

		print '<tr class="field_'.$key.'">';
		print '<td';
		print ' class="titlefieldcreate';
		if (isset($val['notnull']) && $val['notnull'] > 0) {
			print ' fieldrequired';
		}
		if ($val['type'] == 'text' || $val['type'] == 'html') {
			print ' tdtop';
		}
		print '"';
		print '>';
		if (!empty($val['help'])) {
			print $form->textwithpicto($langs->trans($val['label']), $langs->trans($val['help']));
		} else {
			print $langs->trans($val['label']);
		}
		print '</td>';
		print '<td class="valuefieldcreate">';
		if (!empty($val['picto'])) {
			print img_picto('', $val['picto'], '', false, 0, 0, '', 'pictofixedwidth');
		}
		if (in_array($val['type'], array('int', 'integer'))) {
			$value = GETPOST($key, 'int');
		} elseif ($val['type'] == 'double') {
			$value = price2num(GETPOST($key, 'alphanohtml'));
		} elseif ($val['type'] == 'text' || $val['type'] == 'html') {
			$value = GETPOST($key, 'restricthtml');
		} elseif ($val['type'] == 'date') {
			$value = dol_mktime(12, 0, 0, GETPOST($key.'month', 'int'), GETPOST($key.'day', 'int'), GETPOST($key.'year', 'int'));
		} elseif ($val['type'] == 'datetime') {
			$value = dol_mktime(GETPOST($key.'hour', 'int'), GETPOST($key.'min', 'int'), 0, GETPOST($key.'month', 'int'), GETPOST($key.'day', 'int'), GETPOST($key.'year', 'int'));
		} elseif ($val['type'] == 'boolean') {
			$value = (GETPOST($key) == 'on' ? 1 : 0);
		} elseif ($val['type'] == 'price') {
			$value = price2num(GETPOST($key));
		} else {
			$value = GETPOST($key, 'alphanohtml');
		}
		if (!empty($val['noteditable'])) {
			print $object->showOutputField($val, $key, $value, '', '', '', 0);
		} else {
			if ($key=='fk_user') {
				print $form->select_dolusers($value?$value: $user->id, 'fk_user', 0, '', 0, '', '', 0, 0, 0, '', 1, '', $val['css']);
	
			} else {
				print $object->showInputField($val, $key, $value, '', '', '', 0);
			}
		}
		print '</td>';
		print '</tr>';
	}

	//****************************

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_add.tpl.php';

	print '</table>'."\n";

	print dol_get_fiche_end();

	print '<div class="center">';
	print '<input type="submit" class="button" name="add" value="'.dol_escape_htmltag($langs->trans("Create")).'">';
	print '&nbsp; ';
	print '<input type="'.($backtopage ? "submit" : "button").'" class="button button-cancel" name="cancel" value="'.dol_escape_htmltag($langs->trans("Cancel")).'"'.($backtopage ? '' : ' onclick="javascript:history.go(-1)"').'>'; // Cancel for create does not post form if we don't know the backtopage
	print '</div>';

	print '</form>';

	//dol_set_focus('input[name="ref"]');
}

// Part to edit record
if (($id || $ref) && $action == 'edit') {
	//print load_fiche_titre($langs->trans("EasyNote"), '', 'object_'.$object->picto);
	?>
	<table class="centpercent notopnoleftnoright table-fiche-title">
		<tr>
			<td class="nobordernopadding widthpictotitle valignmiddle col-picto">
				<span class="far fa-clipboard infobox-project valignmiddle pictotitle widthpictotitle" style=""></span>
			</td>
			<td class="nobordernopadding valignmiddle col-title">
				<div class="titre inline-block">
					<span style="padding: 0px; padding-right: 3px !important;">EasyNotes</span>
				</div>
			</td>
			<td class="nobordernopadding valignmiddle col-title" align="right">
				<a class="btnTitle btnTitlePlus" href="<?php echo DOL_URL_ROOT; ?>/custom/easynotes/easynotesindex.php" title="EasyNotes"><span class="fa fa-list-alt valignmiddle btnTitle-icon"></span></a>
			</td>
		</tr>
	</table>
	<?php

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}

	print dol_get_fiche_head();

	print '<table class="border centpercent tableforfieldedit">'."\n";

	// Common attributes
	//include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_edit.tpl.php';
	//************************************
	$object->fields = dol_sort_array($object->fields, 'position');

	foreach ($object->fields as $key => $val) {
		// Discard if extrafield is a hidden field on form
		if (abs($val['visible']) != 1 && abs($val['visible']) != 3 && abs($val['visible']) != 4) {
			continue;
		}

		if (array_key_exists('enabled', $val) && isset($val['enabled']) && !verifCond($val['enabled'])) {
			continue; // We don't want this field
		}

		print '<tr class="field_'.$key.'"><td';
		print ' class="titlefieldcreate';
		if (isset($val['notnull']) && $val['notnull'] > 0) {
			print ' fieldrequired';
		}
		if (preg_match('/^(text|html)/', $val['type'])) {
			print ' tdtop';
		}
		print '">';
		if (!empty($val['help'])) {
			print $form->textwithpicto($langs->trans($val['label']), $langs->trans($val['help']));
		} else {
			print $langs->trans($val['label']);
		}
		print '</td>';
		print '<td class="valuefieldcreate">';
		if (!empty($val['picto'])) {
			print img_picto('', $val['picto'], '', false, 0, 0, '', 'pictofixedwidth');
		}
		if (in_array($val['type'], array('int', 'integer'))) {
			$value = GETPOSTISSET($key) ?GETPOST($key, 'int') : $object->$key;
		} elseif ($val['type'] == 'double') {
			$value = GETPOSTISSET($key) ? price2num(GETPOST($key, 'alphanohtml')) : $object->$key;
		} elseif (preg_match('/^(text|html)/', $val['type'])) {
			$tmparray = explode(':', $val['type']);
			if (!empty($tmparray[1])) {
				$check = $tmparray[1];
			} else {
				$check = 'restricthtml';
			}
			$value = GETPOSTISSET($key) ? GETPOST($key, $check) : $object->$key;
		} elseif ($val['type'] == 'price') {
			$value = GETPOSTISSET($key) ? price2num(GETPOST($key)) : price2num($object->$key);
		} else {
			$value = GETPOSTISSET($key) ? GETPOST($key, 'alpha') : $object->$key;
		}
		//var_dump($val.' '.$key.' '.$value);
		if ($val['noteditable']) {
			print $object->showOutputField($val, $key, $value, '', '', '', 0);
		} else {
			if ($key=='fk_user') {
				print $form->select_dolusers($value?$value: $user->id, 'fk_user', 0, '', 0, '', '', 0, 0, 0, '', 1, '', $val['css']);
	
			} else {
				print $object->showInputField($val, $key, $value, '', '', '', 0);
			}
		}
		print '</td>';
		print '</tr>';
	}
	//************************************

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_edit.tpl.php';

	print '</table>';

	print dol_get_fiche_end();

	print '<div class="center"><input type="submit" class="button button-save" name="save" value="'.$langs->trans("Save").'">';
	print ' &nbsp; <input type="submit" class="button button-cancel" name="cancel" value="'.$langs->trans("Cancel").'">';
	print '</div>';

	print '</form>';
}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
	//print load_fiche_titre($langs->trans("EasyNote"), '', 'object_'.$object->picto);
	$res = $object->fetch_optionals();

	//$head = notePrepareHead($object);
	//print dol_get_fiche_head($head, 'card', $langs->trans("Workstation"), -1, $object->picto);

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteNote'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
	}
	// Confirmation to delete line
	if ($action == 'deleteline') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&lineid='.$lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
	}
	// Clone confirmation
	if ($action == 'clone') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('ToClone'), $langs->trans('ConfirmCloneAsk', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
	}


	// Call Hook formConfirm
	$parameters = array('formConfirm' => $formconfirm, 'lineid' => $lineid);
	$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) {
		$formconfirm .= $hookmanager->resPrint;
	} elseif ($reshook > 0) {
		$formconfirm = $hookmanager->resPrint;
	}

	// Print form confirm
	print $formconfirm;


	if ((int)$id>0) {

		$notes_user = $user->id; //mine
		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."easynotes_note as t 
				WHERE (fk_user_creat='$notes_user' OR fk_user IS NULL OR fk_user=0) AND t.rowid = ".((int)$id)." 
				ORDER BY tms DESC ";
		$result = $db->query($sql);
		$nbtotalofrecords = $db->num_rows($result);

		if ($nbtotalofrecords>0) {
			$obj = $db->fetch_object($result);

			?>
			<table class="centpercent notopnoleftnoright table-fiche-title">
				<tr>
					<td class="nobordernopadding widthpictotitle valignmiddle col-picto">
						<span class="far fa-clipboard infobox-project valignmiddle pictotitle widthpictotitle" style=""></span>
					</td>
					<td class="nobordernopadding valignmiddle col-title">
						<div class="titre inline-block">
							<span style="padding: 0px; padding-right: 3px !important; font-size: 120%;"><?php echo $obj->label; ?></span>
						</div>
					</td>
					<td class="nobordernopadding valignmiddle col-title" align="right">
						<a class="btnTitle btnTitlePlus" href="<?php echo DOL_URL_ROOT; ?>/custom/easynotes/easynotesindex.php" title="EasyNotes"><span class="fa fa-list-alt valignmiddle btnTitle-icon"></span></a>
					</td>
				</tr>
			</table>
			<?php

			$canedit = 0;
			
			if ($obj->fk_user_creat == $notes_user || $user->rights->easynotes->easynotes->delete) {
				$canedit = 1;
			}

			$tuser = new User($db);
			$tuser->fetch($obj->fk_user_creat);

			print '<div style="clear:both; overflow:hidden;">';
			print '<div class="dash_in" style="float:right;">';

			if ($canedit) {
				print '<div style="float:right;">';
				print '<a class="marginleftonly" href="'.DOL_URL_ROOT.'/custom/easynotes/note_card.php?action=edit&token='.newToken().'&id='.$obj->rowid.'&backtopage='.urlencode("easynotesindex.php?noteid=".$obj->rowid).'">'.img_edit()."</a>";

				print '<a class="marginleftonly" href="'.DOL_URL_ROOT.'/custom/easynotes/note_card.php?action=delete&token='.newToken().'&id='.$obj->rowid.'&backtopage='.urlencode("easynotesindex.php?noteid=".$obj->rowid).'">'.img_delete()."</a>";
				
				print '</div>';
			} 
			
			print $tuser->getNomUrl(-2);
			if ($obj->fk_user_creat != $obj->fk_user) { //shared content
				print " &nbsp;";
				if ($obj->fk_user>0) {
					$tuser->fetch($obj->fk_user);
					print $tuser->getNomUrl(-2); 
					
				} else {
					print "<i class='fas fa-share-alt' title='Shared with Everyone'></i>";
				}
			}

			
			print '<div style="clear:both; margin-top: 5px; padding-top: 5px;border-top: 1px solid rgba(0,0,0,0.1);font-size:90%;">';		
			if ($obj->tms) {
				print 'Last updated: '.date("d.m.Y h:ma", strtotime($obj->tms));	
			} else {
				print 'Created: '.date("d.m.Y h:ma", strtotime($obj->date_creation));	
			}
			print '</div>';


			print '</div>';
			print '</div>';

			echo '<div class="fullnotes">'.$obj->note.'</div>';
			
			
		}
	}

	print '<div class="clearboth"></div>';

	print dol_get_fiche_end();


	/*
	 * Lines
	 */

	if (!empty($object->table_element_line)) {
		// Show object lines
		$result = $object->getLinesArray();

		print '	<form name="addproduct" id="addproduct" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.(($action != 'editline') ? '' : '#line_'.GETPOST('lineid', 'int')).'" method="POST">
		<input type="hidden" name="token" value="' . newToken().'">
		<input type="hidden" name="action" value="' . (($action != 'editline') ? 'addline' : 'updateline').'">
		<input type="hidden" name="mode" value="">
		<input type="hidden" name="page_y" value="">
		<input type="hidden" name="id" value="' . $object->id.'">
		';

		if (!empty($conf->use_javascript_ajax) && $object->status == 0) {
			include DOL_DOCUMENT_ROOT.'/core/tpl/ajaxrow.tpl.php';
		}

		print '<div class="div-table-responsive-no-min">';
		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline')) {
			print '<table id="tablelines" class="noborder noshadow" width="100%">';
		}

		if (!empty($object->lines)) {
			$object->printObjectLines($action, $mysoc, null, GETPOST('lineid', 'int'), 1);
		}

		// Form to add new line
		if ($object->status == 0 && $permissiontoadd && $action != 'selectlines') {
			if ($action != 'editline') {
				// Add products/services form

				$parameters = array();
				$reshook = $hookmanager->executeHooks('formAddObjectLine', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
				if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
				if (empty($reshook))
					$object->formAddObjectLine(1, $mysoc, $soc);
			}
		}

		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline')) {
			print '</table>';
		}
		print '</div>';

		print "</form>\n";
	}

}

// End of page
llxFooter();
$db->close();
