<?php
/* 
 * Copyright (C) 2025 Massaoud Bouzenad    <massaoud@dzprod.net>
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
 * @file       dolidiag.php
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


require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
dol_include_once('/dolidiag/lib/dolidiag.lib.php');
dol_include_once('/dolidiag/class/dolidiag.class.php');

global $langs, $user, $conf, $db;

$langs->load("dolidiag@dolidiag");

$action = GETPOST('action', 'aZ09');
$report_id = GETPOST('report_id', 'int');
$redact_sensitive = GETPOST('redact_sensitive', 'int');
$user_issue = GETPOST('user_issue', 'alpha');

// Security check
if (!$user->rights->dolidiag->read) {
    accessforbidden();
}

$diroutput = DOL_DATA_ROOT . '/dolidiag/';
if (!file_exists($diroutput)) {
    dol_mkdir($diroutput);
}

$dolidiag = new DoliDiag($db);

if ($action == 'generate') {
    $result = $dolidiag->generateReport($redact_sensitive, $user_issue);
    if ($result > 0) {
        setEventMessages($langs->trans("ReportGenerated"), null, 'mesgs');
    } else {
        setEventMessages($dolidiag->error, $dolidiag->errors, 'errors');
    }
}

if ($action == 'download' && $report_id) {
    $dolidiag->downloadReport($report_id);
    exit;
}

if ($action == 'delete' && $report_id) {
    $result = $dolidiag->deleteReport($report_id);
    if ($result > 0) {
        setEventMessages($langs->trans("ReportDeleted"), null, 'mesgs');
    } else {
        setEventMessages($dolidiag->error, $dolidiag->errors, 'errors');
    }
}

$reports = $dolidiag->getReports();

// Page output
$title = $langs->trans("DoliDiag");
llxHeader('', $title);

// Configuration header with tabs
$head = dolidiagAdminPrepareHead();
print dol_get_fiche_head($head, 'dolidiag', $langs->trans("DoliDiag"), -1, 'generic@dolidiag');

print load_fiche_titre($title, '', 'generic@dolidiag');

print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="generate">';

print '<table class="noborder allwidth">';
print '<tr class="liste_titre">';
print '<th>' . $langs->trans("Parameter") . '</th>';
print '<th>' . $langs->trans("Value") . '</th>';
print '<th>' . $langs->trans("Description") . '</th>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>' . $langs->trans("RedactSensitiveData") . '</td>';
print '<td><input type="checkbox" name="redact_sensitive" value="1"' . (!empty($conf->global->DOLIDIAG_REDACT_DEFAULT) ? ' checked' : '') . '></td>';
print '<td>' . $langs->trans("RedactSensitiveDataDesc") . '</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>' . $langs->trans("UserReportedIssue") . '</td>';
print '<td><textarea name="user_issue" rows="4" cols="50">' . htmlspecialchars($user_issue) . '</textarea></td>';
print '<td>' . $langs->trans("UserReportedIssueDesc") . '</td>';
print '</tr>';

print '</table>';

print '<div class="center">';
print '<input type="submit" class="button" value="' . $langs->trans("GenerateReport") . '">';
print '</div>';

print '</form>';

print '<br><br>';
print '<table class="noborder allwidth">';
print '<tr class="liste_titre">';
print '<th>' . $langs->trans("ReportDate") . '</th>';
print '<th>' . $langs->trans("FileName") . '</th>';
print '<th>' . $langs->trans("Action") . '</th>';
print '</tr>';

if (is_array($reports) && !empty($reports)) {
    foreach ($reports as $report) {
        print '<tr>';
        print '<td>' . dol_print_date($report['date_creation'], 'dayhour') . '</td>';
        print '<td>' . $report['filename'] . '</td>';
        print '<td>';
        print '<a href="' . $_SERVER["PHP_SELF"] . '?action=download&report_id=' . $report['rowid'] . '&token=' . newToken() . '" class="paddingright">';
        print img_picto('', 'download') . ' ' . $langs->trans("Download") . '</a>';
        print '<a href="' . $_SERVER["PHP_SELF"] . '?action=delete&report_id=' . $report['rowid'] . '&token=' . newToken() . '" class="paddingleft" onclick="return confirm(\'' . $langs->trans("ConfirmDeleteReport") . '\');">';
        print '<i class="fa fa-trash"></i> ' . $langs->trans("Delete") . '</a>';
        print '</td>';
        print '</tr>';
    }
} else {
    print '<tr><td colspan="3">' . $langs->trans("NoReportsAvailable") . '</td></tr>';
}
print '</table>';

llxFooter();
$db->close();
?>