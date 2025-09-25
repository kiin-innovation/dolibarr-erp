<?php
/* Copyright (C) 2020	Arthur Croix		<arthur@code42.fr>
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
 * 	\defgroup   public article     Module Lareponse
 *  \brief      public article view
 *
 *  \file       htdocs/custom/lareponse/public/public_article.php
 */

use Luracast\Restler\Format\UploadFormat;

if (!defined('NOCSRFCHECK'))    define('NOCSRFCHECK', '1'); // Do not check anti CSRF attack test
if (!defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', '1'); // Do not check anti POST attack test
if (!defined('NOREQUIREMENU'))  define('NOREQUIREMENU', '1'); // If there is no need to load and show top and left menu
if (!defined('NOREQUIREHTML'))  define('NOREQUIREHTML', '1'); // If we don't need to load the html.form.class.php
if (!defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX', '1'); // Do not load ajax.lib.php library
if (!defined("NOLOGIN"))        define("NOLOGIN", '1'); // If this page is public (can be called outside logged session)
if (!defined("NOSESSION"))      define("NOSESSION", '1');


$res = 0;
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) $res = @include "../main.inc.php";
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";

if (!$res) die("Include of main fails");

dol_include_once('/lareponse/class/article.class.php');
dol_include_once('/lareponse/lib/lareponse_article.lib.php');
$cssfilepath = dol_buildpath('/lareponse/css/lareponse_public.css.php', 1);
print '<link rel="stylesheet" type="text/css" href="'.$cssfilepath.'">';
print '<link rel="stylesheet" type="text/css" href="'.DOL_URL_ROOT.'/theme/common/fontawesome-5/css/all.min.css">';
print '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>';
print '<script src="'.dol_buildpath('lareponse/js/lareponse_article_public.js.php', 2).'"></script>';
print '<script src="'.dol_buildpath('lareponse/js/mermaid.js', 2).'"></script>';
print '<script src="'.dol_buildpath('lareponse/js/mermaid.min.js', 2).'"></script>';
print '<title>Lareponse Article</title>';
global $conf, $db, $langs;

$langs->load('lareponse@lareponse');

// A token is used here for public articles. A new one is generated every time an article is set to web
$token = GETPOST("token");
$article = new Article($db);
$sql = 'SELECT ';
foreach ($article->fields as $key => $val) {
	$sql .= 't.'.$key.', ';
}
$sql = preg_replace('/,\s*$/', '', $sql);
$sql .= ' FROM llx_lareponse_article AS t WHERE t.publish_token = "'.$token.'"';
$resql = $db->query($sql);
if (!$resql) {
	dol_print_error($db);
	exit;
}
$article = $db->fetch_object($resql);

if (empty($article->type)) $article->type = 0;

$bannerColor = getDolGlobalString("LAREPONSE_PUBLIC_BANNER_COLOR");

// Get the image registered into the current Dolibarr society
$img_dir = ((float) DOL_VERSION >= 10 ? 'logos/thumbs/' : 'thumbs/');
$urllogo = DOL_URL_ROOT.'/viewimage.php?modulepart=mycompany&amp;file='.urlencode($img_dir.$mysoc->logo_mini);
print '<div id="id-top">';
print '<div class="public_article_header" ' . (empty($bannerColor) ? '' : 'style="background-color: ' . $bannerColor . '!important"') . '>';
print '<div class="border-div" id="border-div-left">';
print '<img id="society-logo" src="'.dol_buildpath($urllogo, 1).'">';
print '</div>';
print '<div id="title-div">';
print '<h1>'.$article->title.'</h1>';
print '</div>';
print '<div class="border-div" id="border-div-right"></div>';
print '</div>';
print '</div>';
print '<div class="container">';
print '<div class="fiche ' . ($article->type > 0 ? 'iframe' : '') . '">';
// This functions replaces all <br /> by ''
if ($article->type > 0) {
	print '<span id="fullscreen-btn" onclick="fullScreenMode()" class="fas fa-expand-alt"></span>';
	print '<iframe src="' . $article->content . '" frameborder="0" width="100%" height="800"></iframe>';
} else print '<p>' . changeContentForMermaid($article->content) . '</p>';
print '</div></div>';
print '<footer class="public_article_footer">';
print '<div id="provided-by">';
print '<img id="code42-logo" src="'.dol_buildpath('/lareponse/public/img/code42_apple-touch-icon-72x72.png', 1).'">';
print '<p>'.$langs->trans('ProvidedModule').' <strong>Code42</strong></p>';
print '</div>';
print '<div id="inbetween-footer"></div>';
print '<p>'.$langs->trans('GeneratedArticle').' <strong>Lareponse</strong></p>';
print '</footer>';
