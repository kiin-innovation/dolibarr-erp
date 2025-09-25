<?php
$notes_user = $user->id; //mine
$tuser = new User($db);



$sql = "SELECT * FROM ".MAIN_DB_PREFIX."easynotes_note as t 
		WHERE (fk_user_creat='$notes_user' OR fk_user IS NULL OR fk_user=0)  
		ORDER BY tms DESC, rowid DESC ";
		
//  
// echo $sql;
$result = $db->query($sql);
$nbtotalofrecords = $db->num_rows($result);

if ($nbtotalofrecords>0) {
	print '<div class="dashboard_column">';


	$i = 0;
	while ($i<$nbtotalofrecords) {
		$obj = $db->fetch_object($result);
		$noteid = $obj->rowid;
		//$url = DOL_URL_ROOT.'/custom/easynotes/easynotesindex.php?noteid='.$noteid;
		$url = 'note_card.php?id='.$noteid;
		
		?>
		<figure>
		<div class="dash_in notes">

			<a href="<?php echo $url; ?>">
				<i class="fas fa-arrow-circle-right arrow"></i> 
				<?php if (!empty($obj->label)) { ?>
				<div class='title'>
					<?php echo $obj->label; ?>
				</div>	
				<?php } ?>				
			</a>
			
			<div class="note_truncate">
				<?php echo $obj->note; ?>
			</div>
		</div>	
		</figure>
		
		<?php
		$i++;		
	}
	
	print '</div>'; //end dashboard_column
	
} 

$db->free($result);

?>
</div>

