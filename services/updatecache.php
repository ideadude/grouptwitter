<?php
	require('../../../../wp-blog-header.php');
	global $wpdb, $gt_secretkey;
		
	$ta = $_REQUEST['ta'];
	$key = $_REQUEST['key'];
	if($key != $gt_secretkey)
		die("Invalid key.");
		
	//get the accounts
	$sqlQuery = "SELECT * FROM $wp_grouptwitter_accounts ";
	if($ta)
		$sqlQuery .= " WHERE name = '$ta' ";
	$sqlQuery .= "ORDER BY last_update";
	$gtaccounts = $wpdb->get_results($sqlQuery);
	
	//updating?	
	foreach($gtaccounts as $gta)
	{
		echo $gta->name . "[";
		$Twitter = new GT_Twitter($gta->id);
		$n = $Twitter->rebuild_archive('America/New_York');
		if($n !== FALSE)
		{
			$wpdb->query("UPDATE $wp_grouptwitter_accounts SET last_update = now() WHERE name = '$gta->name' LIMIT 1");
			echo $n;
		}
		else
			echo "X";
		echo "] ";
	}	
?>
