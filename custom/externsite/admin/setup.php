<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003      Eric Seigne          <erics@rycks.com>
 * Copyright (C) 2004-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Sebastien Di Cintio  <sdicintio@ressource-toi.org>
 * Copyright (C) 2004      Benoit Mortier       <benoit.mortier@opensides.be>
 * Copyright (C) 2011-2012 Juanjo Menent		<jmenent@2byte.es>
 * Copyright (C) 2016-2017 Quentin Sutkowski for HdF industrilab <quentin.sutkowski@gmail.com>
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
 *    \file       htdocs/externsite/admin/externiste.php
 *    \ingroup    externsite
 *    \brief      Page de configuration du module externsite
 */

require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';

/*	security check	*/
if (!$conf->externsite->enabled)
    accessforbidden();


$langs->load("admin");
$langs->load("other");
$langs->load("externsite");

$def = array();

$action = GETPOST('action','alpha');

llxHeader();

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($langs->trans("Configuration des sites externes"),$linkback,'title_setup');

/*
	* Passage permettant de supprimer un site du module :
	* 	- suppression en base du site
	*	- suppression de l'image d'aperçu du site sur le serveur
	*	- suppression du menu permettant l'acces au site
*/
if(isset($_REQUEST['externsite_delete']))
{	
	$menus_extern=$db->query("SELECT * from llx_externsite WHERE rowid=".$_REQUEST['externsite_delete']."");
	while ($donnees = $db->fetch_object($menus_extern))
	{
		if($donnees->thumb_address != "../img/thumb_default.png")
			unlink($donnees->thumb_address);
	}
	$db->query('DELETE FROM llx_externsite WHERE rowid='.$_REQUEST['externsite_delete'].'');
	
}

if(isset($_REQUEST['externsite_del_menu']))
{
	$nom=str_replace('_',' ',$_REQUEST['externsite_del_menu']);
	$db->query("DELETE FROM llx_menu WHERE titre=\"$nom\"");
	setEventMessages("Site supprimé", null, 'mesgs');	
}

/*
	* Passage permettant d'afficher les sites actuellement activés, et qui donne la possibilité de supprimer un site
*/
?>
<br>
<div class="mytab tabBar">
	<table>
		<tr class="liste_titre">
			<th width="10%">Nom du site</th>
			<th width="90%">URL du site</th>
		</tr>
<?php
			$activ_sites=$db->query('SELECT * from llx_externsite');			
				while ($donnees = $db->fetch_object($activ_sites))
				{
				?>
					<tr>
						<td width="30%"><?php echo $donnees->nom ; ?></td>
						<td  width="60%"><?php echo $donnees->url ; ?></td>
						<?php $nom = str_replace(' ','_',$donnees->nom);?>
						
						<td style="cursor:pointer;" width="10%" onclick="suppr_row(<?php echo $donnees->rowid.",'".$nom."'"; ?>);"><i class="fa fa-trash fa-4x"></i></td>
					</tr>
				<?php	
				}

?>
	</table>
</div>
<?php
/*
	* Passage permettant l'ajout d'un site au module, sont obligatoires :
	*	- le nom de site à ajouter
	*	- l'URL du site à ajouter
	*	- un aperçu (ou logo) pour l'affichage dans le menu top associé (OPTIONNEL)
*/
print "<br>";
print load_fiche_titre("Ajouter un site externe");
?>

<html>

	<form method="post" action="new_site.php" enctype="multipart/form-data" onsubmit="return verifForm(this)">
		<label for="nom">Nom du site à ajouter :</label><br> 
			<input type="text" name="nom" value="Nom du site (obligatoire)" onFocus="if(this.value=='Nom du site (obligatoire)')this.value=''" onblur="if(this.value=='')this.value='Nom du site (obligatoire)'" autocomplete="off"/>

<br>
		<label for="url">URL du site à ajouter :</label><br> 
			<input type="text" name="url" rows="12" cols="60" value="URL du site (obligatoire)" onFocus="if(this.value=='URL du site (obligatoire)')this.value=''" onblur="if(this.value=='')this.value='URL du site (obligatoire)'" autocomplete="off"/>
<br>
		<label for="thumb">Logo ou aperçu du site (format <i>.png OU .jpg OU .jpeg</i> 250 px requis, max. 1 Mo) :</label><br>
			<input type="hidden" name="MAX_FILE_SIZE" value="1048576"/>			
			<input type="file" name="thumb"/><br>

		<input type="submit" value="<?php echo $langs->trans('Add'); ?>"/>
	</form>
</html>


<?php
llxFooter();

$db->close();
?>

<script type="text/javascript">
	//fonctions de verification du formulaire d'ajout de site au module sites externes
	function surligne(champ, erreur){
		if(erreur)
			champ.style.backgroundColor = "#fba";
		else
			champ.style.backgroundColor = "";
	}

	function verifNom(nom){
		if(nom.value.length < 1 || nom.value.length > 50 || nom.value=='Nom du site (obligatoire)')
		{
			surligne(nom, true);
			return false;
		}
		else
		{
			surligne(nom, false);
			return true;
		}
	}

	function verifUrl(url){
		if (url.value.length < 1 || url.value=='URL du site (obligatoire)')
		{
			surligne(url, true);
			return false;
		}
		else
		{
			surligne(url, false);
			return true;
		}
	}

	function verifForm(form){
		var nomOK = verifNom(form.nom);
		var urlOK = verifUrl(form.url);

		if(nomOK && urlOK){
			return true;
		}
		else
		{
			alert("Veuillez remplir correctement tous les champs (nom et url obligatoires)");
			return false;
		}

	}
//fonction appelée pour recharger la page en vue d'un suppression d'un site pour le module
	function suppr_row(id,nom){
		document.location.href="?externsite_delete="+id+"&externsite_del_menu="+nom;
	}

</script>


