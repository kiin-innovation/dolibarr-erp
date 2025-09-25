<?php
/**
 * DoliDiag Library Functions
 */

/**
 * Prepare admin pages header tabs
 *
 * @return array Array of tabs
 */
function dolidiagAdminPrepareHead()
{
    global $langs, $conf;

    // Load module language file
    $langs->load("dolidiag@dolidiag");

    $h = 0;
    $head = array();

    // DoliDiag main interface tab
    $head[$h][0] = dol_buildpath("/dolidiag/dolidiag.php", 1);
    $head[$h][1] = $langs->trans("DoliDiag");
    $head[$h][2] = 'dolidiag';
    $h++;

    // Settings tab
    $head[$h][0] = dol_buildpath("/dolidiag/admin/dolidiag_setup.php", 1);
    $head[$h][1] = $langs->trans("Settings");
    $head[$h][2] = 'settings';
    $h++;

    // Allow other modules to add their own tabs
    complete_head_from_modules($conf, $langs, null, $head, $h, 'dolidiag');

    return $head;
}
?>