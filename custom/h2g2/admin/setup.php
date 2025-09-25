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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    h2g2/admin/setup.php
 * \ingroup h2g2
 * \brief   H2G2 setup page.
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

global $langs, $user, $conf, $db;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
dol_include_once('/h2g2/lib/h2g2.lib.php');

// Translations
$langs->loadLangs(array("admin", "h2g2@h2g2"));

// Access control
if (!$user->admin) accessforbidden();

// Parameters
$action = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');
$setupnotempty = 0;

$arrayofparameters = array(
	'H2G2_DISABLE_MODULE_WIZARD' => array('css' => 'minwidth200', 'enabled' => 1, 'type' => 'sellist', 'arraykeyval' => array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"))),
	'H2G2_INCLUDE_DEV_LIB' => array('css' => 'minwidth200', 'enabled' => 1, 'type' => 'sellist', 'arraykeyval' => array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"))),
	'H2G2_DISABLE_VERSION_MODULES' => array('css' => 'minwidth200', 'enabled' => 1, 'type' => 'sellist', 'arraykeyval' => array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"))),
	'H2G2_DISABLE_CHECK_LAST_VERSION' => array('css' => 'minwidth200', 'enabled' => 1, 'type' => 'sellist', 'arraykeyval' => array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"))),
	'H2G2_VERSION_MODULES_URL' => array('css' => 'minwidth200', 'type' => 'text', 'enabled' => 1),
    'H2G2_CLPBRD_FUNCTION' => array('css' => 'minwidth200', 'enabled' => 1, 'type' => 'sellist', 'arraykeyval' => array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"))),
);

/*
 * Actions
 */

if ((float) DOL_VERSION >= 6) include DOL_DOCUMENT_ROOT . '/core/actions_setmoduleoptions.inc.php';

/*
 * View
 */

$page_name = "H2G2Setup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . ($backtopage ? $backtopage : DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1') . '">' . $langs->trans("BackToModuleList") . '</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'object_h2g2@h2g2');

// Configuration header
$head = h2g2AdminPrepareHead();
print dol_get_fiche_head($head, 'settings', '', 0, "h2g2@h2g2");

// Setup page goes here
echo '<span class="opacitymedium">' . $langs->trans("H2G2SetupPage") . '</span><br><br>';

if ($action == 'edit') {
	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '" id="apiv2SetupForm">';
	print '<input type="hidden" name="token" value="' . newToken() . '">';
	print '<input type="hidden" name="action" value="update">';

	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre"><td class="titlefield">' . $langs->trans("Parameter") . '</td><td>' . $langs->trans("Value") . '</td></tr>';

	foreach ($arrayofparameters as $key => $val) {
		if ($key == 'H2G2_INCLUDE_DEV_LIB' && $conf->global->MAIN_FEATURES_LEVEL < 2) continue; // Dev include is only available in MAIN_FEATURES_LEVEL 2

		$setupnotempty++;

		print '<tr class="oddeven"><td>';
		$tooltiphelp = (($langs->trans($key . 'Tooltip') != $key . 'Tooltip') ? $langs->trans($key . 'Tooltip') : '');
		print $form->textwithpicto($langs->trans($key), $tooltiphelp);
		print '</td><td>';
		switch ($val['type']) {
			case 'sellist':
				print '<select name="' . $key . '"  class="flat ' . (empty($val['css']) ? 'minwidth200' : $val['css']) . '">';
				foreach ($val['arraykeyval'] as $itemkey => $itemvalue) {
					print '<option value="' . $itemkey . '" ' . ((!empty($conf->global->$key) && $conf->global->$key == $itemkey) ? 'selected' : '') . '>' . $itemvalue . '</option>';
				}
				print '</select>';
				break;
			default:
				print '<input name="' . $key . '"  class="flat ' . (empty($val['css']) ? 'minwidth200' : $val['css']) . '" value="' . ($conf->global->$key ?? "") . '">';
				break;
		}
		print '</td></tr>';
	}
	print '</table>';

	print '<br><div class="center">';
	print '<input class="button button-save" type="submit" value="' . $langs->trans("Save") . '">';
	print '</div>';

	print '</form>';
	print '<br>';
} else {
	if (!empty($arrayofparameters)) {
		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre"><td class="titlefield">' . $langs->trans("Parameter") . '</td><td>' . $langs->trans("Value") . '</td></tr>';

		foreach ($arrayofparameters as $key => $val) {
			if ($key == 'H2G2_INCLUDE_DEV_LIB' && $conf->global->MAIN_FEATURES_LEVEL < 2) continue; // Dev include is only available in MAIN_FEATURES_LEVEL 2

			// #[48] By default we set disable at true
			if ($key == 'H2G2_DISABLE_VERSION_MODULES' && !isset($conf->global->H2G2_DISABLE_VERSION_MODULES)) dolibarr_set_const($db, 'H2G2_DISABLE_VERSION_MODULES', 1, 'sellist', 1, '', $conf->entity);

			// #[48] By default we set url with correct link to our article
			if ($key == 'H2G2_VERSION_MODULES_URL' && !isset($conf->global->H2G2_VERSION_MODULES_URL)) {
				dolibarr_set_const($db, 'H2G2_VERSION_MODULES_URL', 'https://control.code42.io/custom/lareponse/public/public_article.php?token=afc7640e2d2b190213dd8ba3d82b39b1', 'text', 1, '', $conf->entity);
			}

			print '<tr class="oddeven"><td>';
			$tooltiphelp = (($langs->trans($key . 'Tooltip') != $key . 'Tooltip') ? $langs->trans($key . 'Tooltip') : '');
			print $form->textwithpicto($langs->trans($key), $tooltiphelp);
			print '</td><td>';
			if (property_exists($conf->global, $key)) {
				switch ($val['type']) {
					case 'sellist':
						print $val['arraykeyval'][$conf->global->$key];
						break;
					default:
						print $conf->global->$key;
						break;
				}
			}
			print '</td></tr>';
		}
		print '</table>';

		print '<div class="tabsAction">';
		print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?action=edit">' . $langs->trans("Modify") . '</a>';
		print '</div>';
	} else {
		print '<br>' . $langs->trans("NothingToSetup");
	}
}

// Page end
print dol_get_fiche_end();

llxFooter();
$db->close();
