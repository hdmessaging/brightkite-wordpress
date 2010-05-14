<?php 
if($_POST['bk-hidden'] == 'Y') {
	//Form data sent
	$bk_username = $_POST['bk-username'];
	update_option('bk-username', $_POST['bk-username']);
	
	$enableMap = $_POST['bk-enableMap'];
	update_option('bk-enableMap', $enableMap);
	
	$numCheckins = $_POST['bk-numCheckins'];
	update_option('bk-numCheckins', $numCheckins);
	
	echo '<div class="updated"><p><strong>Options Saved.</strong></p></div>';
} else {
	$bk_username = get_option('bk-username');
	$enableMap = get_option('bk-enableMap');
	$numCheckins = get_option('bk-numCheckins');
}
?>

<div class="wrap">
	<?php    echo "<h2>" . __( 'Brightkite Options', 'bk-trdom' ) . "</h2>"; ?>
	
	<p>
	 This will display your last checkins that you have made on Brightkite with the privacy set to "Everyone"
	</p>
	
	<form name="bk-form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<input type="hidden" name="bk-hidden" value="Y">

		<p><?php _e("Brightkite Username: " ); ?><input type="text" name="bk-username" value="<?php echo $bk_username; ?>" size="30" /></p>
	
	
		<h4>Other options</h4>
		
		<p><?php _e("Show my latest number of checkins: " ); ?><input type="text" name="bk-numCheckins" value="<?php echo empty($numCheckins) ? '10' : $numCheckins; ?>" size="4" /> Default is 10.</p>
	
		<p class="submit">
		<input type="submit" name="Submit" value="<?php _e('Update Options', 'bk-trdom' ) ?>" />
		</p>
	</form>
</div>
