<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2020 SuperAdmin
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
 * \file    lareponse/admin/setup.php
 * \ingroup lareponse
 * \brief   Lareponse setup page.
 */

if (!defined('NOCSRFCHECK')) define('NOCSRFCHECK', '1'); // Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) { $i--; $j--; }
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
dol_include_once('/lareponse/lib/lareponse.lib.php');
dol_include_once('/lareponse/lib/lareponse_setup.lib.php');

// Classes
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

global $langs, $user, $db, $conf;

// Translations
$langs->loadLangs(array("admin", "lareponse@lareponse"));

// Access control
if (!$user->rights->lareponse->configure) accessforbidden();

// Parameters
$action = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');
$error = 0;
$msg = '';

$demos = array(
	"wizardindex"      => $conf->global->LAREPONSE_WIZARD_INDEX,
	"wizardarticlelist"    => $conf->global->LAREPONSE_WIZARD_ARTICLE_LIST,
	"LaReponseWizardArticleAssistant" => $conf->global->LAREPONSE_WIZARD_ARTICLE_ASSISTANT,
);
$parameters = array(
	'LA_REPONSE_PROTECT_TAG_DELETION' => array('action' => 'setprotecttagdeletion', 'type' => 'switch', 'label' => 'LaReponseActivateTagDeletionProtection'),
	'LA_REPONSE_WIDGET_NUMBER' => array('action' => 'setwidgetnumber', 'type' => 'input', 'label' => 'LaReponseWidgetNumberNext', 'min' => 0, 'max' => 100),
	'LAREPONSE_PREVIEW_TOOLTIP' => array('action' => 'setarticlepreviewtooltip', 'type' => 'input', 'label' => 'LaReponseArticlePreviewTooltip', 'min' => 5),
);
/*
 * Actions
 */

printSetupAction("LAREPONSE_NOTIFICATION_CHECK", "integer", "int");
printSetupAction("LAREPONSE_EMAIL_TEMPLATE_FOR_NOTIFICATIONS", "int", "int");
// Update frequency of cron
printSetupAction("LAREPONSE_TIME_BEFORE_NOTIFICATION_CHECK", "integer", "int");
printSetupAction("LAREPONSE_COMMENT_NUMBER", "integer", "int");
printSetupAction("LAREPONSE_PUBLIC_BANNER_COLOR");
$frequency = GETPOST("update_LAREPONSE_TIME_BEFORE_NOTIFICATION_CHECK", "int");
if (!empty($frequency)) {
	$sql = "UPDATE " . MAIN_DB_PREFIX . "cronjob SET frequency = " . $frequency . " WHERE label = 'LaReponseCronNotificationArticle'";
	$resql = $db->query($sql);
	if ($resql) setEventMessages("LaReponseSetupFrequencyCronUpdated", "", "mesgs");
	else setEventMessages("LaReponseSetupFrequencyCronUpdatedError", "", "errors");
}

if ($action == 'confirmedit') {
	// Set all demo to a value
	if (!empty(GETPOST('submit'))) {
		$wizardindex = empty(GETPOST('wizardindex')) ? $conf->global->LAREPONSE_WIZARD_INDEX : GETPOST('wizardindex', 'none');
		$wizardarticlelist =  empty(GETPOST('wizardarticlelist')) ? $conf->global->LAREPONSE_WIZARD_ARTICLE_LIST : GETPOST('wizardarticlelist', 'none');
		$wizardArticleAssitant =  empty(GETPOST('LaReponseWizardArticleAssistant')) ? $conf->global->LAREPONSE_WIZARD_ARTICLE_ASSISTANT : GETPOST('LaReponseWizardArticleAssistant', 'none');

		if ((float) DOL_VERSION >= 10) {
			dolibarr_set_const($db, 'LAREPONSE_WIZARD_INDEX', $wizardindex, 'string', 0, '', $conf->entity);
		} else {
			dolibarr_set_const($db, 'LAREPONSE_WIZARD_INDEX', $wizardindex, $conf->entity);
		}
		if (!$res > 0)
			$error++;

		if ((float) DOL_VERSION >= 10) {
			dolibarr_set_const($db, 'LAREPONSE_WIZARD_ARTICLE_LIST', $wizardarticlelist, 'string', 0, '', $conf->entity);
		} else {
			dolibarr_set_const($db, 'LAREPONSE_WIZARD_ARTICLE_LIST', $wizardarticlelist, $conf->entity);
		}
		if (!$res > 0)
			$error++;

		if ((float) DOL_VERSION >= 10) {
			dolibarr_set_const($db, 'LAREPONSE_WIZARD_ARTICLE_ASSISTANT', $wizardArticleAssitant, 'string', 0, '', $conf->entity);
		} else {
			dolibarr_set_const($db, 'LAREPONSE_WIZARD_ARTICLE_ASSISTANT', $wizardArticleAssitant, $conf->entity);
		}
		if (!$res > 0)
			$error++;

		if ($error > 0)
			setEventMessages($langs->trans("Error"), '', 'errors');
		else setEventMessages($langs->trans("SetupSaved"), '', 'mesgs');
	}
}

// Activate or deactivate the link with inter module
if ($action == "setdemoactive") {
	$value = GETPOST("status", "int");

	// Set const value
	if ((float) DOL_VERSION >= 10) {
		dolibarr_set_const($db, 'LAREPONSE_WIZARD_ACTIVE', $value, 'integer', 0, '', $conf->entity);
	} else {
		dolibarr_set_const($db, 'LAREPONSE_WIZARD_ACTIVE', $value, $conf->entity);
	}
}
// GestionParc Tags into Lareponse module
if ($action == "settaggestionparcactive") {
	$value = GETPOST("status", "int");

	// Set const value
	if ((float) DOL_VERSION >= 10) {
		dolibarr_set_const($db, 'LAREPONSE_TAG_GESTIONPARC_ACTIVE', $value, 'integer', 0, '', $conf->entity);
	} else {
		dolibarr_set_const($db, 'LAREPONSE_TAG_GESTIONPARC_ACTIVE', $value, $conf->entity);
	}
}
// Categories Tags into Lareponse module
if ($action == "settagcategoriesactive") {
	$value = GETPOST("status", "int");

	// Set const value
	if ((float) DOL_VERSION >= 10) {
		dolibarr_set_const($db, 'LAREPONSE_TAG_CATEGORIES_ACTIVE', $value, 'integer', 0, '', $conf->entity);
	} else {
		dolibarr_set_const($db, 'LAREPONSE_TAG_CATEGORIES_ACTIVE', $value, $conf->entity);
	}
}

// Action for LaReponse const
if ($action == "setprotecttagdeletion") {
		$value = GETPOST("status", "int");
		dolibarr_set_const($db, 'LA_REPONSE_PROTECT_TAG_DELETION', $value, 'int', 1, '', $conf->entity);
		exit(header("Location:" . $_SERVER['PHP_SELF']));
}

$setWidgetNumber = GETPOST("setwidgetnumber", "int");
$setArticlePreviewTooltip = GETPOST("setarticlepreviewtooltip", "int");

// Action for LaReponse const
if (!empty($setArticlePreviewTooltip) || !empty($setWidgetNumber)) {
	dolibarr_set_const($db, 'LA_REPONSE_WIDGET_NUMBER', $setWidgetNumber ? : $conf->global->LA_REPONSE_WIDGET_NUMBER, 'int', 1, '', $conf->entity);
	dolibarr_set_const($db, 'LAREPONSE_PREVIEW_TOOLTIP', $setArticlePreviewTooltip ? : $conf->global->LAREPONSE_PREVIEW_TOOLTIP, 'int', 1, '', $conf->entity);
	exit(header("Location:" . $_SERVER['PHP_SELF']));
}

if ($action == "setlareponsewysiwygmode") {
	$mode = GETPOST("mode", "string");
	dolibarr_set_const($db, 'LAREPONSE_WYSIWYG_MODE', $mode, 'string', 1, '', $conf->entity);
	exit(header("Location:" . $_SERVER['PHP_SELF']));
}

/*
 * View
 */

$page_name = $langs->trans('ConfugurationOfModule', $langs->trans('Lareponse'));
llxHeader('', $page_name, '', '', 0, 0, '', array('/lareponse/css/wysiwyg.css'));
// Subheader
$linkback = '<a href="'.($backtopage?$backtopage:DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

$head = lareponseAdminPrepareHead();

print load_fiche_titre($page_name, $linkback, 'lareponse_black_50@lareponse');

dol_fiche_head($head, 'settings', $page_name, 0, '');

print '<h1>'.$langs->trans('SetupDemo').'</h1>';

print '<form name="LareponseSetup" method="POST" action="'.$_SERVER["PHP_SELF"].'">';
if ((float) DOL_VERSION >= 11) {
	print '<input type="hidden" name="token" value="' . newToken() . '">';
} else {
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
}
if ($action == 'edit')
	print '<input type="hidden" name="action" value="confirmedit">';

if ($action != 'edit') {
	print '<table class="noborder" width="100%"><tbody>';
	print '<tr class="liste_titre">';
	print '<td><h4 align="left" width="30%">'.$langs->trans('Parameter').'</h4></td>';
	print '<td align="center" width="70%"><h4>'.$langs->trans('Value').'</h4></td>';
	print '</tr>';
	print '<tr>';
	print '<td>'.$langs->trans('ActivateDemoMode').'</td>';
	if (dolibarr_get_const($db, "LAREPONSE_WIZARD_ACTIVE", $conf->entity) == '1') {
		print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=setdemoactive&status=0">';
		print img_picto($langs->trans("Activated"), 'switch_on');
		print '</a></td>';
	} else {
		print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=setdemoactive&status=1">';
		print img_picto($langs->trans("Disabled"), 'switch_off');
		print '</a></td>';
	}

	print '</tr>';
	print '</table>';
}

print '<table class="noborder" width="100%"><tbody>';
print '<tr class="liste_titre">';
print '<td><h4 align="left" width="30%">'.$langs->trans('Parameter').'</h4></td>';
print '<td align="center" width="70%"><h4>'.$langs->trans('Value').'</h4></td>';
print '</tr>';

foreach ($demos as $key => $demo) {
	print '<tr class="oddeven">';
	print '<td>'.$langs->trans($key).'</td>';
	if ($action == 'edit') {
		print '<td width="70%">';
		$doleditor = new DolEditor($key, $demo, '', 142, 'dolibarr_notes', 'In', false, true, true, ROWS_4, '90%');
		$doleditor->Create();
		print '</td>';
	} else print '<td align="center">' . $demo . '</span></td>';
	print '</tr>';
}
print '</tbody></table><br />';
print '<div style="width: 100%" align="center">';
if ($action == 'edit') {
	print '<div class="center">';
	print '<input class="button" type="submit" name="submit" value="'.$langs->trans('Save').'"> &nbsp;';
	print '<input class="button" type="submit" name="cancel" value="'.$langs->trans('Cancel').'"></div>';
} else print '<a href="'.$_SERVER['PHP_SELF'].'?action=edit" class="button">'.$langs->trans('Modify').'</a>';

print '</div>';

print '</form>';
print '<h1>'.$langs->trans('SetupTags').'</h1>';

// To check if categorie and gestionparc modules are activated or not
$iscategorieenabled = $conf->categorie->enabled;
if (isset($conf->gestionparc->enabled)) $isgestionparcenabled = $conf->gestionparc->enabled;
if ($action != 'edit') {
	print '<table class="noborder" width="100%"><tbody>';
	print '<tr class="liste_titre">';
	print '<td><h4 align="left" width="30%">'.$langs->trans('Parameter').'</h4></td>';
	print '<td align="center" width="70%"><h4>'.$langs->trans('Value').'</h4></td>';
	print '</tr>';
	if ($iscategorieenabled) {
		if (isset($isgestionparcenabled) && $isgestionparcenabled) {
			// Setup GestionParc Tags switch
			print '<tr>';
			print '<td>'.$langs->trans('TagsGestionParc').' '.$langs->trans('Lareponse').'</td>';
			if (dolibarr_get_const($db, "LAREPONSE_TAG_GESTIONPARC_ACTIVE", $conf->entity) == '1') {
				print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=settaggestionparcactive&status=0">';
				print img_picto($langs->trans("Activated"), 'switch_on');
				print '</a></td>';
			} else {
				print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=settaggestionparcactive&status=1">';
				print img_picto($langs->trans("Disabled"), 'switch_off');
				print '</a></td>';
			}
			print '</tr>';
		}
		// Setup Categories Tags switch
		print '<tr>';
		print '<td>'.$langs->trans('TagsCategories').'</td>';
		if (dolibarr_get_const($db, "LAREPONSE_TAG_CATEGORIES_ACTIVE", $conf->entity) == '1') {
			print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=settagcategoriesactive&status=0">';
			print img_picto($langs->trans("Activated"), 'switch_on');
			print '</a></td>';
		} else {
			print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action=settagcategoriesactive&status=1">';
			print img_picto($langs->trans("Disabled"), 'switch_off');
			print '</a></td>';
		}
		print '</tr>';
	}
	print '</table>';
}

// Display lareponse global parameters input
print '</br>';
print '</br>';

// #233
if (!empty($conf->fckeditor->enabled)) {
	print '<h1><span class="fas fa-paragraph"></span> ' . $langs->trans('Module2000Name') . '</h1>'; //Module2000Name = WYSIWYG module name
	if ($action != 'edit') {
		print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
		print '<table class="noborder" width="100%">';
		print '<tbody>';
		print '<tr><td>' . $langs->trans('LaReponseWYSIWYGReduceToolbar') . '</td>';

		$mode = 'default';

		if (isset($conf->global->LAREPONSE_WYSIWYG_MODE) && $conf->global->LAREPONSE_WYSIWYG_MODE == 'custom') {
			print '<td align="center"><a href="'.$_SERVER['PHP_SELF'] . '?action=setlareponsewysiwygmode&mode=default">';
			print img_picto($langs->trans("Activated"), 'switch_on');
			print '</a></td>';
			$mode = 'custom';
		} else {
			print '<td align="center"><a href="'.$_SERVER['PHP_SELF'] . '?action=setlareponsewysiwygmode&mode=custom">';
			print img_picto($langs->trans("Disabled"), 'switch_off');
			print '</a></td>';
		}
		print '</tr>';

		print '<tr><td>' . $langs->trans('Preview') . '</td><td class="wysiwyg_' . $mode . '">';
		$doleditor = new DolEditor('test', '', '', 50, 'Full', 'In', true, true, true, ROWS_7, '90%');
		$doleditor->Create();
		print '</td></tr>';
		print '</tbody>';
		print '</table>';
		print '</form>';
	}
	// Display lareponse global parameters input
	print '</br>';
}

print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
if ((float) DOL_VERSION >= 11) {
	print '<input type="hidden" name="token" value="' . newToken() . '">';
} else {
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
}
print '<h1>'.$langs->trans('LaReponseWidgetNumberTitle').'</h1>';
print '<table class="noborder" width="100%"><tbody>';
print '<tr class="liste_titre">';
print '<td style="width: 30% ;"><h4>' . $langs->trans('Parameter') . '</h4></td>';
print '<td style="width: 70% ;" align="center">' . $langs->trans('Value') . '</td>';
print '</tr>';
foreach ($parameters as $key => $value) {
	if ($value['type'] == 'switch') {
		print '<tr class="oddeven">';
		print '<td >' . $langs->trans("LaReponseActivateTagDeletionProtection").'</td>';
		if (getDolGlobalInt("LA_REPONSE_PROTECT_TAG_DELETION") == 1) {
			print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action='.$value['action'].'&status=0">';
			print img_picto($langs->trans("Activated"), 'switch_on');
			print '</a></td>';
		} else {
			print '<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?action='.$value['action'].'&status=1">';
			print img_picto($langs->trans("Disabled"), 'switch_off');
			print '</a></td>';
		}
		print '</tr>';
	}
	// Switch input (number)
	if ($value['type'] == 'input') {
		print '<tr>';
		print '<td>' . $langs->trans($value['label']) . '</td>';
		$max = (!empty($value['max']) ? 'max ="' . $value['max'] . '"' : ""); // max value of input : max="..."
		$min = (!empty($value['min']) ? 'min ="' . $value['min'] . '"' : ""); // min value of input : min="..."
		print '<td align="center"><input class="width75" name="' . $value['action'] . '" type="number" pattern="^[0-9]*$" value="' . getDolGlobalInt($key) . '"' . $min . " " . $max  . '></td>';
		print '</tr>';
		print '</td>';
	}
}
print '<tr class="pair"><td colspan="3" align="center"><button type="submit" class="butAction">' . $langs->trans('Save') . '</button></td></tr>';
print '</tbody>';
print '</table>';
print '</form>';

// [#116] Configuration of notifications
// Start section
printSetupStartSection("LaReponseSetupNotifications", "far fa-envelope");

// Print toggle switch to enable / disable notifications
printSetupInput("LaReponseSetupNotificationSend", "LAREPONSE_NOTIFICATION_CHECK", "switch", "fas fa-bell");

// Print int input to modify time between each notification check
// If cron frequency is not the same has this const, we update the const (This may be the case where we updated cron and not this const)
$cronFrequency = getArticleNotificationCronFrequency();
$cronFrequencyInMinutes = ($cronFrequency["frequency"] * $cronFrequency["unit"] / 60); // Unit frequency in minutes equals to 60, so we have to divide by 60 to get frequency in minutes
if (getDolGlobalInt("LAREPONSE_NOTIFICATION_CHECK") != $cronFrequencyInMinutes) dolibarr_set_const($db, 'LAREPONSE_NOTIFICATION_CHECK', $cronFrequencyInMinutes, 'int', 0, '', $conf->entity);
$timeAttributes = array(
	"default" => $cronFrequency["frequency"],
	"min" => 5,
	"help" => $langs->trans("LaReponseSetupTimeBeforeNotificationSendHelp")
);
printSetupInput("LaReponseSetupTimeBeforeNotificationSend", "LAREPONSE_TIME_BEFORE_NOTIFICATION_CHECK", "int", "fas fa-stopwatch", $timeAttributes);

// Print select input to choose mail template to use for notification
// Const for template is linked to any template, we add a warning next to the const
if (empty(getLaReponseEmailTemplate(getDolGlobalInt("LAREPONSE_EMAIL_TEMPLATE_FOR_NOTIFICATIONS")))) $templateWarning = "<span class='fa fa-exclamation-triangle' style='color: #e3bd00' title='" . $langs->trans("LaReponseSetupWarningTemplateEmpty") . "'></span>";
$templateAttributes = array(
	"list" => getArticleTemplateList(),
	"help" => $langs->trans("LaReponseSetupEmailTemplateUsedForNotificationHelp"),
	"afterInput" => ($templateWarning ?? "")
);
printSetupInput("LaReponseSetupEmailTemplateUsedForNotification", "LAREPONSE_EMAIL_TEMPLATE_FOR_NOTIFICATIONS", "list", "fas fa-envelope-open-text", $templateAttributes);

// End of notification setup section
printSetupEndSection();

// Start Comments section
printSetupStartSection("LaReponseSetupComments", "far fa-keyboard");

// [#257] Configuration of comments number
$commentAttributes = array(
	"default" => 5,
	"min" => 3
);
printSetupInput("LaReponseSetupCommentNumber", "LAREPONSE_COMMENT_NUMBER", "int", "fas fa-list-ol", $commentAttributes);

// End of comments setup section
printSetupEndSection();

// Start Banner color in article public page

printSetupStartSection("LaReponseSetupPublic", "fas fa-globe");

printSetupInput("LaReponseSetupBannerColorLabel", "LAREPONSE_PUBLIC_BANNER_COLOR", "color", "fas fa-palette");

printSetupEndSection();

// End of Banner color section

// Page end
dol_fiche_end();

llxFooter();
$db->close();
