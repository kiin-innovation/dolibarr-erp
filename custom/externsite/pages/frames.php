<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2016 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2010 Regis Houssin        <regis.houssin@capnetworks.com>
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
 *       \file       htdocs/projet/index.php
 *       \ingroup    projet
 *       \brief      Main project home page
 */

require '../../../main.inc.php';
/*ajout de cookie pour tentative de passage de parametre aux sites externes

$donnees = $db->query('SELECT * FROM llx_user WHERE rowid ="'.$user->id.'"');
$current_user = $db->fetch_object($donnees);

$login=strtolower(substr($current_user->firstname, 0,1). $current_user->lastname);
setcookie("login", '', time());
setcookie("login", $login, time() + 365*24*3600, null, null, false, true);
*/

require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/externsite/class/externsite.class.php';


$langs->load("externsite");
$langs->load("companies");


$mainmenu=GETPOST('mainmenu', 'alpha');
$leftmenu=GETPOST('leftmenu', 'alpha');
$idmenu=GETPOST('idmenu', 'int');
$theme=GETPOST('theme', 'alpha');
$codelang=GETPOST('lang', 'alpha');	

$mine = GETPOST('mode')=='mine' ? 1 : 0;

// Security check
$socid=0;
if ($user->societe_id > 0) $socid=$user->societe_id;
if (!$user->rights->externsite->acces) accessforbidden();

$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');

$URL = $_GET['page'];

/*
	* Page d'affichage des sites externes via un frameset en 2 parties :
	*	- le "bandeau" des menus dolibarr
	*	- le site externe en lui même
	* Gestion des erreurs sinon (navigateur sans prise en charge)
*/
print"

<html>
<head>

</head>
	<frameset ".(empty($conf->global->MAIN_MENU_INVERT)?"rows":"cols")."=\"".$heightforframes.",*\" border=0 framespacing=0 frameborder=0>
    <frame name=\"barre\" src=\"frametop.php?mainmenu=".$mainmenu."&leftmenu=".$leftmenu."&idmenu=".$idmenu.($theme?'&theme='.$theme:'').($codelang?'&lang='.$codelang:'')."&nobackground=1\" noresize scrolling=\"NO\" noborder>
    <frame name=\"main\" src=\"$URL\">
    <noframes>
    <body>

    </body>
    </noframes>
</frameset>

<noframes>
<body>
	<br><div class=\"center\">
	Sorry, your browser is too old or not correctly configured to view this area.<br>
	Your browser must support frames.<br>
	</div>
</body>
</noframes>

</html>
";


llxFooter();

$db->close();

?>
