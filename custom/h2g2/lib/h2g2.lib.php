<?php
/* Copyright (C) 2020 SuperAdmin
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
 * \file    h2g2/lib/h2g2.lib.php
 * \ingroup h2g2
 * \brief   Library files with common functions for H2G2
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function h2g2AdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("h2g2@h2g2");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/h2g2/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;
	$head[$h][0] = dol_buildpath("/h2g2/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;
	$head[$h][0] = dol_buildpath("/h2g2/admin/news.php", 1);
	$head[$h][1] = $langs->trans("H2G2NewsTab");
	$head[$h][2] = 'news';
	$h++;
	$head[$h][0] = dol_buildpath("/h2g2/admin/theme.php", 1);
	$head[$h][1] = $langs->trans("H2G2ThemeTab");
	$head[$h][2] = 'theme';
	$h++;

	complete_head_from_modules($conf, $langs, null, $head, $h, 'h2g2');

	return $head;
}

/**
 * Get the url of datatable translation file
 *
 * @return string                   Url
 */
function getDatatableLanguageUrl()
{
	global $langs;

	return dol_buildpath('/h2g2/langs/' . $langs->getDefaultLang() . '/datatable.json', 1);
}

/**
 * Check if H2G2 module is installed with a version equal or superior as the given version
 *
 * @param  string    $minVersion    H2G2 module min version to check
 * @return bool
 */
function isH2G2InstalledWithMinVersion($minVersion)
{
	global $db;

	dol_include_once('/h2g2/core/modules/modH2G2.class.php');
	$h2g2 = new modH2G2($db);
	return version_compare($h2g2->version, $minVersion, '>=');
}

/**
 * Get list of modules not up to date
 *
 * @return array
 */
function getH2G2ModulesNotUpToDate()
{
	global $db, $conf, $langs;

	// Get all h2g2 modules installed with their max version on db
	$modulesInstalled = array();
	$sql = "SELECT module_name AS name, MAX(module_version) AS lastVersion FROM " . MAIN_DB_PREFIX . "c42migration WHERE entity = " . $conf->entity . " GROUP BY module_name";
	$resql = $db->query($sql);
	if ($resql) {
		while ($module = $db->fetch_object($resql)) {
			$modulesInstalled[$module->name] = $module->lastVersion;
		}
	}

	// Check which module is not updated
	$modulesToUpdate = array();
	$modulesdir = dolGetModulesDirs();
	foreach ($modulesdir as $dir) {
		$handle = @opendir(dol_osencode($dir));
		if (is_resource($handle)) {
			while (($file = readdir($handle)) !== false) {
				if (is_readable($dir . $file) && preg_match("/^(mod.*)\.class\.php$/i", $file, $reg)) {
					$modulePath = str_replace(DOL_DOCUMENT_ROOT, '', $dir . $file);
					$moduleClassname = $reg[1];
					dol_include_once($modulePath);
					$moduleDescriptor = new $moduleClassname($db);
					$name = $moduleDescriptor->rights_class;
					$version = $moduleDescriptor->version;
					if (array_key_exists($name, $modulesInstalled) && version_compare($version, $modulesInstalled[$name], '>')) {
						$langs->load($name . '@' . $name);
						$modulesToUpdate[] = array('actual_version' => $modulesInstalled[$name], 'update_version' => $version, 'fullname' => $langs->trans($moduleDescriptor->name), 'name' => $name, 'className' => $moduleClassname);
					}
				}
			}
		}
	}

	return $modulesToUpdate;
}

/**
 * Display a multi select button with a dropdown.
 * A button definition must be defined like the following :
 *
 * array('href', 'withoutAction', 'label', 'disabled', 'title', 'picto', 'color')
 *
 * Example :
 *
 * array(
 *     'href' => $_SERVER['PHP_SELF'].'?action=new',
 *     'withoutAction' => false,
 *     'label' => $langs->trans('New'),
 *     'picto' => 'plus-circle',
 *     'colorbtn' => '#ffa500',
 *     'disabled' => $user->rights->h2g2->read,
 *     'title' => ($user->rights->h2g2->read ? '' : $langs->trans('NoRight')),
 * );
 *
 * @param  array      $mainBtn      Main button definition
 * @param  array      $entries      List of button definition
 * @param  boolean    $direction    Option direction display. Up if true, Down if false
 * @return string                      HTML to display
 */
function buildMultiEntriesButton($mainBtn, $entries, $direction = true)
{
	// Without action buttons are only used to group multiple actions with a main bouton
	$withoutAction = false;
	$morecss = '';
	if (key_exists('withoutAction', $mainBtn) && $mainBtn['withoutAction']) {
		$withoutAction = true;
		$morecss = ' without-action';
	}

	$str_direction = 'up';
	$str_directioninv = 'down';

	if (!$direction) {
		$str_direction = 'down';
		$str_directioninv = 'up';
	}

	$ret = '<div class="h2g2multiselect' . $morecss . '">';

	// Main button
	$ret .= '<div class="h2g2tooltip" data-direction="left">';

	if ($mainBtn['disabled'] ?? false) {
		$ret .= '<a href="#" class="butActionRefused h2g2multiselect__button h2g2tooltip__initiator' . $morecss . '">';

		$ret .= $mainBtn['picto'] . " " . $mainBtn['label'];

		if ($withoutAction) {
			// Without action button integrate the toggle in the button itself
			$ret .= '<span class="h2g2multiselect__chevron options' . $str_direction . '"><i class="fas fa-chevron-' . $str_direction . '"></i></span>';
		}
		$ret .= '</a>';
	} else {
		if ($mainBtn['colorbtn'] ?? false) {
			$ret .= '<a title="'. $mainBtn['title'] .'" href="' . $mainBtn['href'] . '" ' . (isset($mainBtn['colorbtn']) ? 'style="--btn-color:' . $mainBtn['colorbtn'] . '"' : '') . ' class="butAction h2g2multiselect__button classfortooltip' . $morecss . '">';
		} else {
			$ret .= '<a title="'. $mainBtn['title'] .'" href="' . $mainBtn['href'] . '" class="butAction h2g2multiselect__button classfortooltip' . $morecss . '">';
		}
		$ret .= $mainBtn['picto'] . " " . $mainBtn['label'];
		if ($withoutAction) {
			// Without action button integrate the toggle in the button itself
			$ret .= '<span style="background:' . $mainBtn['colorbtn'] . '" class="h2g2multiselect__chevron options' . $str_direction . '"><i class="fas fa-chevron-' . $str_direction . '"></i></span>';
		}
		$ret .= '</a>';
	}

	$ret .= '</div>';

	// Toggle button
	if (!$withoutAction) {
		// on récupere ici tout les paramètres dans href que l'on stock dans $params sous forme de tableau
		parse_str(parse_url($mainBtn['href'], PHP_URL_QUERY), $params);
		// The toggle button is not seprated for without action button
		$ret .= '<a ' . (isset($params['action']) ? 'href="action=' . $params['action'] . '"' : '') . ' class="butAction h2g2multiselect__chevron ' . $str_directioninv . '" ' . (isset($mainBtn['colorbtn']) ? 'style="--btn-color: ' . $mainBtn['colorbtn'] . '"' : '') . '><i class="fas fa-chevron-' . $str_directioninv . '"></i> </a>';
	}

	// Entries
	$ret .= '<div class="h2g2multiselect__options options' . $str_direction . ' hidden">';
	foreach ($entries as $entry) {
		$ret .= '<div title="' . $entry['title'] . '" class="classfortooltip" data-direction="left">';
		$ret .= '<div onclick="window.location.href=\'' . $entry['href'] . '\'" class="h2g2multiselect__options-entry ' . (!empty($entry['disabled']) ? 'disabled' : '') . ' h2g2tooltip__initiator">';
		if ($entry['pictocolor'] ?? false) $entry['picto'] = str_replace('class=', 'style="color:' . $entry['pictocolor'] . '" class=', $entry['picto']);
		$ret .= '<div class="h2g2multiselect__options-entry_label">';
		if (!empty($entry['disabled'])) $ret .= '<a href="#">' . $entry['picto'] . $entry['label'] . '</a>';
		else $ret .= '<a href="' . $entry['href'] . '">' . $entry['picto'] . $entry['label'] . '</a>';
		$ret .= '</div>';
		$ret .= '</div>';
		$ret .= '</div>';
	}
	$ret .= '</div>';
	$ret .= '</div>';

	return $ret;
}


/**
 * Display a multi select button with a dropdown.
 * A button definition must be defined like the following :
 *
 * array('href', 'rights')
 *
 * Example :
 *
 * array(
 *     'href' => $_SERVER['PHP_SELF'].'?action=new',
 *     'rights' => $user->rights->h2g2->read,
 * );
 *
 * @param  string    $type       create or delete
 * @param  array     $mainBtn    Main button definition
 * @param  array     $entries    List of button definition
 * @return string                      HTML to display
 */
function buildStandarMultiEntriesButton($type, $mainBtn, $entries)
{
	global $langs;
	// Without action buttons are only used to group multiple actions with a main bouton
	$morecss = '';

	$ret = '<div class="h2g2multiselect' . $morecss . '">';
	$ret .= '<div class="h2g2tooltip" data-direction="left">';

	$ret .= '<a style="--btn-color: var(' . ( $type == 'create' ? '--btn-color-success' : '--btn-color-danger' ) . ')"';

	if ($mainBtn['rights']) {
		$ret .= 'href="' . $mainBtn['href'] . '" class="h2g2multiselect_' . $type . ' butAction h2g2multiselect__button h2g2tooltip__initiator' . $morecss . '">';
	} else {
		$ret .= 'href="#" class="butActionRefused h2g2multiselect__button h2g2tooltip__initiator' . $morecss . '">';
	}

	if ($type == 'create') {
		$ret .= '<i class="fas fa-plus"></i> ' . $langs->trans('Create');
	} else {
		$ret .= '<i class="fas fa-times"></i> ' . $langs->trans('Delete');
	}

	$ret .= '</a>';
	$ret .= '</div>';

	$ret .= '<a style="--btn-color: var(' . ( $type == 'create' ? '--btn-color-success' : '--btn-color-danger' ) . ')"';

	$ret .= 'href="#" class="h2g2multiselect_' . $type . ' butAction h2g2multiselect__chevron down"><i class="fas fa-chevron-down"></i> </a>';

	// Entries
	$ret .= '<div class="h2g2multiselect__options optionsup hidden">';

	if ($type == 'create') {
		// Modify Button
		$modifyBtn = $entries[0];
		$ret .= '<div class="h2g2tooltip" data-direction="left">';
		$ret .= '<div class="h2g2multiselect__options-entry ' . ($modifyBtn['rights'] ? '' : 'disabled') . ' h2g2tooltip__initiator">';
		$ret .= '<div class="h2g2multiselect__options-entry_icon">';
		$ret .= '<i class="fas fa-pen" style="color:orange"></i>';
		$ret .= '</div>';
		$ret .= '<div class="h2g2multiselect__options-entry_label">';
		if ($modifyBtn['rights']) {
			$ret .= '<a href="' . $modifyBtn['href'] . '">' . $langs->trans('Modify') . '</a>';
		} else {
			$ret .= '<a href="#">' . $langs->trans('Modify') . '</a>';
		}
		$ret .= '</div>';
		$ret .= '</div>';
		$ret .= '</div>';

		// Save Button
		$saveBtn = $entries[1];
		$ret .= '<div class="h2g2tooltip" data-direction="left">';
		$ret .= '<div class="h2g2multiselect__options-entry ' . ($saveBtn['rights'] ? '' : 'disabled') . ' h2g2tooltip__initiator">';
		$ret .= '<div class="h2g2multiselect__options-entry_icon">';
		$ret .= '<i class="fas fa-save" style="color:green"></i>';
		$ret .= '</div>';
		$ret .= '<div class="h2g2multiselect__options-entry_label">';
		if ($saveBtn['rights']) {
			$ret .= '<a href="' . $saveBtn['href'] . '">' . $langs->trans('Save') . '</a>';
		} else {
			$ret .= '<a href="#">' . $langs->trans('Save') . '</a>';
		}
	} else {
		// Cancel Button
		$cancelBtn = $entries[0];
		$ret .= '<div class="h2g2tooltip" data-direction="left">';
		$ret .= '<div class="h2g2multiselect__options-entry ' . ($cancelBtn['rights'] ? '' : 'disabled') . ' h2g2tooltip__initiator">';
		$ret .= '<div class="h2g2multiselect__options-entry_icon">';
		$ret .= '<i class="fas fa-redo" style="color:gray"></i>';
		$ret .= '</div>';
		$ret .= '<div class="h2g2multiselect__options-entry_label">';
		if ($cancelBtn['rights']) {
			$ret .= '<a href="' . $cancelBtn['href'] . '">' . $langs->trans('Cancel') . '</a>';
		} else {
			$ret .= '<a href="#">' . $langs->trans('Cancel') . '</a>';
		}
	}

	$ret .= '</div>';
	$ret .= '</div>';
	$ret .= '</div>';
	$ret .= '</div>';
	$ret .= '</div>';

	return $ret;
}

/**
 * Create a TopBarInfo
 * @param   string      $title      title of the news
 * @param   string      $content    content of the news
 * @param   int         $type       type of the news<br/>
 *                                  448312 => Green / Success<br/>
 *                                  448313 => LightBlue / Information<br/>
 *                                  448314 => Yellow / Warning<br/>
 *                                  448315 => Red / Danger (can't be close)<br/>
 * @param   datetime    $datestart  when the news are gonna to be display<br/>
 *                                  by default -> dol_now()
 * @param   datetime    $dateend    when the news are gonna to be remove<br/>
 *                                  by default -> dol_now() + 12 hours
 * @return  int                     <0 if KO, id of the news if OK
 */
function createTopBarInfo($title = '', $content = '', $type = 448312, $datestart = '', $dateend = '')
{
	global $db, $user;

	require_once DOL_DOCUMENT_ROOT . '/comm/action/class/actioncomm.class.php';

	if (empty($datestart)) $datestart = dol_now();
	if (empty($dateend)) $dateend = dol_now() + 48 * 3600; // 48 hours later


    $sql = 'SELECT id FROM ' . MAIN_DB_PREFIX . 'actioncomm';
    $sql .= ' WHERE (label LIKE "%' . $db->escape($title) . '%" OR note LIKE "%' . $db->escape($content) . '%")';
    $sql .= ' AND fk_action = ' . $type;
    $sql .= " AND datep >= '" . $db->idate(($datestart - (48 * 3600))) . "' AND datep2 <= '" . $db->idate($dateend) . "'";

    $resql = $db->query($sql);
    if ($resql) {
        if ($db->num_rows($resql) > 0) {
            $obj = $db->fetch_object($resql);

            $actioncomm = new ActionComm($db);
            $actioncomm->fetch($obj->id);

            $actioncomm->datep = $datestart;
            $actioncomm->datef = $dateend;

            $ret = $actioncomm->update($user);

            return $ret;
        } else {
            $actioncomm = new ActionComm($db);
            $actioncomm->userownerid = $user->id;
            $actioncomm->label = $title;
            $actioncomm->note = $content;
            $actioncomm->type_code = $type;
            $actioncomm->datep = $datestart;
            $actioncomm->datef = $dateend;
            $id = $actioncomm->create($user);
            if ($id > 0) return $id;
            else return -1;
        }
    }

    return -1;
}
