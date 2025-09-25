<?php
/* 
 * Copyright (C) 2025 Massaoud Bouzenad    <massaoud@dzprod.net>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @file       dolidiag_setup.php
 * @brief      DoliDiag module admin page
 * @ingroup    dolidiag
 * @author     Massaoud Bouzenad <massaoud@dzprod.net>
 */
// Load Dolibarr environment
if (file_exists('../../main.inc.php')) {
    require '../../main.inc.php';
} elseif (file_exists('../../../main.inc.php')) {
    require '../../../main.inc.php';
} else {
    die('Include of main fails');
}

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
dol_include_once('/dolidiag/lib/dolidiag.lib.php');
dol_include_once('/dolidiag/class/dolidiag.class.php');


global $langs, $user, $conf, $db;

$langs->load("dolidiag@dolidiag");

$action = GETPOST('action', 'aZ09');

// Security check
if (!$user->rights->dolidiag->read) {
    accessforbidden();
}

if ($action == 'update') {
    $system_info = GETPOST('SYSTEM_INFO', 'int');
    $dolibarr_info = GETPOST('DOLIBARR_INFO', 'int');
    $module_info = GETPOST('MODULE_INFO', 'int');
    $database_info = GETPOST('DATABASE_INFO', 'int');
    $error_logs = GETPOST('ERROR_LOGS', 'int');
    $security_status = GETPOST('SECURITY_STATUS', 'int');
    $redact_default = GETPOST('REDACT_DEFAULT', 'int');
    $report_format = GETPOST('REPORT_FORMAT', 'aZ09');

    $errors = array();
    $consts = [
        'DOLIDIAG_SYSTEM_INFO' => $system_info,
        'DOLIDIAG_DOLIBARR_INFO' => $dolibarr_info,
        'DOLIDIAG_MODULE_INFO' => $module_info,
        'DOLIDIAG_DATABASE_INFO' => $database_info,
        'DOLIDIAG_ERROR_LOGS' => $error_logs,
        'DOLIDIAG_SECURITY_STATUS' => $security_status,
        'DOLIDIAG_REDACT_DEFAULT' => $redact_default,
        'DOLIDIAG_REPORT_FORMAT' => $report_format
    ];

    foreach ($consts as $key => $value) {
        if (dolibarr_set_const($db, $key, $value, 'chaine', 0, '', $conf->entity) <= 0) {
            $errors[] = $langs->trans("ErrorSavingConst", $key) . ': ' . $db->lasterror();
        }
    }

    if (empty($errors)) {
        setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    } else {
        setEventMessages($langs->trans("SetupSaveFailed"), $errors, 'errors');
    }
}

// Page output
$title = $langs->trans("DoliDiagSetup");
llxHeader('', $title);

// Configuration header with tabs
$head = dolidiagAdminPrepareHead();
print dol_get_fiche_head($head, 'settings', $langs->trans("DoliDiagSetup"), -1, 'generic@dolidiag');

print load_fiche_titre($title, '', 'generic@dolidiag');

print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="update">';

print '<table class="noborder allwidth">';
print '<tr class="liste_titre">';
print '<th>' . $langs->trans("Parameter") . '</th>';
print '<th>' . $langs->trans("Value") . '</th>';
print '</tr>';

$sections = [
    'SYSTEM_INFO' => $langs->trans("SystemInformation"),
    'DOLIBARR_INFO' => $langs->trans("DolibarrInformation"),
    'MODULE_INFO' => $langs->trans("ModuleInformation"),
    'DATABASE_INFO' => $langs->trans("DatabaseInformation"),
    'ERROR_LOGS' => $langs->trans("ErrorLogs"),
    'SECURITY_STATUS' => $langs->trans("SecurityStatus"),
    'REDACT_DEFAULT' => $langs->trans("RedactSensitiveDataDefault")
];

foreach ($sections as $key => $label) {
    print '<tr>';
    print '<td>' . $label . '</td>';
    print '<td>';
    print '<input type="checkbox" name="' . $key . '" value="1"' . (!empty($conf->global->{'DOLIDIAG_' . $key}) ? ' checked' : '') . '>';
    print '</td>';
    print '</tr>';
}

print '<tr>';
print '<td>' . $langs->trans("ReportFormat") . '</td>';
print '<td>';
print '<select name="REPORT_FORMAT">';
print '<option value="pdf"' . ((!empty($conf->global->DOLIDIAG_REPORT_FORMAT) && $conf->global->DOLIDIAG_REPORT_FORMAT === 'pdf') ? ' selected' : '') . '>PDF</option>';
print '<option value="html"' . ((!empty($conf->global->DOLIDIAG_REPORT_FORMAT) && $conf->global->DOLIDIAG_REPORT_FORMAT === 'html') ? ' selected' : '') . '>HTML</option>';
print '<option value="md"' . ((!empty($conf->global->DOLIDIAG_REPORT_FORMAT) && $conf->global->DOLIDIAG_REPORT_FORMAT === 'md') ? ' selected' : '') . '>Markdown</option>';
print '</select>';
print '</td>';
print '</tr>';

print '</table>';

print '<div class="center">';
print '<input type="submit" class="button" value="' . $langs->trans("Save") . '">';
print '</div>';

print '</form>';

print dol_get_fiche_end();

llxFooter();
$db->close();
?>