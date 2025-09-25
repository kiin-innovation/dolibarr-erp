<?php
/*
	* Fichier CSS pour le module externsite :
	*	- affichage des images de menu pour chaque site
	*	- affichage par défaut
*/

require '../../../main.inc.php';
header('Content-Type: text/css');

?>
/*
	*	Affichage d'un logo "de base", si aucun n'est trouvé
*/
.mainmenu {
    background-image: url(/dolibarr-7.0.3/dolibarr-7.0.3/htdocs/custom/externsite/img/object_externsite.png);
}

<?php
/*
	*	Affichage des images de chaque site (va chercher le logo en base)
*/

$menus_extern=$db->query("SELECT * from llx_externsite");
while ($donnees = $db->fetch_object($menus_extern))
{
	$nom=str_replace(" ","_",$donnees->nom);
	$nom=str_replace("(","_",$nom);
	$nom=str_replace(")","_",$nom);

?>
	.mainmenu.<?php echo "exts_".$nom; ?> {
		background-image: url(/dolibarr-7.0.3/dolibarr-7.0.3/htdocs/custom/externsite/img/<?php echo $donnees->thumb_address; ?>);
	}

<?php
}
?>
