<?php
/*
	* Page appelée lors d'une requête d'ajout de site au module externsite :
	*	- verification des informations passées (upload du logo, calibrage des noms (espaces, caractères "interdits"))
	*	- ajout en base du nouveau site
	*	- création d'un menu pour le nouveau site
*/
require_once '../../../main.inc.php';

require_once DOL_DOCUMENT_ROOT.'/custom/externsite/class/externsite.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/menubase.class.php';
$langs->load("externsite");

// Security check
$socid=0;
if ($user->societe_id > 0) $socid=$user->societe_id;
if (!$user->rights->externsite->acces) accessforbidden();

llxHeader("",$langs->trans("Paramétrage des sites externes"),"EN:Module_Note|FR:Module_Note");

//verifications sur l'upload de l'apercu du site
$flag = 0;

if($_FILES['thumb']['size']>0){

	if($_FILES['thumb']['error'] > 0) { $erreur= "Erreur lors du transfert"; $flag++; }

	if($flag > 0)
	{
		echo "<b>".$erreur."</b>";
		echo "<br>Problème lors de l'upload de votre image, veuillez retourner à la configuration";
	}
}
$res=$db->query("SELECT * FROM llx_externsite");
while($data = $db->fetch_object($res)) {
	if($data->url == $_POST['url']) {
		echo "<br>Un site avec la même URL est déjà actif. Ajout annulé";
		$flag++;
	}
}
if($flag==0){ 

	$date = date_create();

	//Ajout en base du site externe
		$object = new Externsite($db);
		
		$object->nom = $_POST['nom'];
		$object->url = $_POST['url'];
		if($_FILES['thumb']['size']>0){
			$thumb_name= "thumb_".strtolower($_FILES['thumb']['name']);
			$thumb_name= str_replace(' ','',$thumb_name);
			$res=move_uploaded_file($_FILES['thumb']['tmp_name'], DOL_DOCUMENT_ROOT."/custom/externsite/img/".$thumb_name);
			if($res)
				$object->thumb_address=$thumb_name;
		}
		else
			$object->thumb_address="thumb_default.png";
		$object->create($user);
			 
		echo('<strong>');	
			print("Site ".$_POST['nom']." ajouté au module Sites externes !");		
		echo('</strong>');


	//Ajout d'un menu pour le site ajouté

		$menu = new Menubase($db);

		$nom=str_replace(" ","_",$_POST['nom']);
		$nom=str_replace("(","_",$nom);
		$nom=str_replace(")","_",$nom);
		
		$menu->menu_handler='all';
		$menu->module='externsite';
		$menu->type='top';
		$menu->mainmenu="exts_".$nom."";
		$menu->fk_menu='0';
		$menu->fk_mainmenu=substr("exts_".$nom, 0, 23);
		$menu->titre=$_POST['nom'];
		$menu->url="custom/externsite/pages/frames.php?page=".$_POST['url']."&mainmenu=externsite&idmenu=".$idmenu.($theme?'&theme='.$theme:'').($codelang?'&lang='.$codelang:'')."";
		$menu->position=150;
		$menu->perms=1;
		$menu->target="";
		$menu->user=2;
		$menu->enabled='$conf->externsite->enabled';

		$result=$menu->create($user);
	//gestion des erreurs
		if($result <=0)
		echo  "<pre>".$menu->error."</pre>";
		
		$db->close();
		setEventMessages("Site ajouté", null, 'mesgs');

} else {
	setEventMessages("Ajout du site annulé", null, 'warnings'); //can be mesgs, warnings or errors
}



print '<br><br>';
	?>
	<input type="button" name="lienparam" value="Retour à la configuration" onclick="location.href='<?echo DOL_URL_ROOT;?>/custom/externsite/admin/setup.php'"class="mybut" />
	<?
llxFooter();
?>
