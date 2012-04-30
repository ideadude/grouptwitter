<?php
	set_time_limit(360);
	
	global $wpdb, $wp_grouptwitter, $wp_grouptwitter_accounts, $gt_secretkey;	
	
	//are we adding an account?
	$tname = trim($_REQUEST['tname']);
	$taccount = trim($_REQUEST['taccount']);
	if($tname && $taccount)
	{
		$sql = "INSERT INTO $wp_grouptwitter_accounts (id, name) VALUES('$taccount', '$tname')";
		if($wpdb->query($sql))
		{
			$msg = true;
			$msgt = "Account added successfully.";
		}
		else
		{
			$msg = -1;
			$msgt = "Error adding account.";
		}
	}
	elseif($tname)
	{
		$msg = -1;
		$msgt = "Please enter the Twitter Account Number as well.";
	}
	elseif($taccount)
	{
		$msg = -1;
		$msgt = "Please enter the Twitter Username as well.";
	}
	
	//are we deleting?
	$delete = $_REQUEST['delete'];
	if($delete)
	{
		//remove all tweets
		$sql = "DELETE FROM $wp_grouptwitter WHERE account_id = '$delete'";
		$wpdb->query($sql);
		
		//remove the account
		$sql = "DELETE FROM $wp_grouptwitter_accounts WHERE id = '$delete' LIMIT 1";
		if($wpdb->query($sql))
		{
			$msg = true;
			$msgt = "Account deleted successfully.";
		}
		else
		{
			$msg = -1;
			$msgt = "Error deleting account #$delete.";
		}
	}
	
	//get the accounts
	$gtaccounts = $wpdb->get_results("SELECT * FROM $wp_grouptwitter_accounts ORDER BY last_update");
	
	//updating?
	$update = $_REQUEST['update'];
	if($update)
	{		
		foreach($gtaccounts as $gta)
		{
			echo $gta->name . "[";
			$Twitter = new GT_Twitter($gta->id);
			$n = $Twitter->rebuild_archive('America/New_York');
			if($n !== FALSE)
			{
				$wpdb->query("UPDATE $wp_grouptwitter_accounts SET last_update = now()");
				echo $n;
			}
			else
				echo "X";
			echo "] ";
		}
		echo "<hr />";
	}
	
	if($msg)
	{
	?>
		<div id="message" class="<?php if($msg > 0) echo "updated fade"; else echo "error"; ?>"><p><?=$msgt?></p></div>
	<?php
	}
?>
<div class="wrap nosub">
	<div id="icon-options-general" class="icon32"></div>
	<h2>Group Twitter Options</h2>
    <p>Use this form to add and manage the Twitter accounts you would like to include in the database.</p>
    <form class="gt_newaccount" action="options-general.php?page=grouptwitter" method="post">
    	<div>
	        <label>Twitter Username</label>
            <input class="text" type="text" id="tname" name="tname" value="" />
        </div>
        <div>
	        <label>Account Number</label>
            <input class="text" type="text" id="taccount" name="taccount" onfocus="if(this.value=='') getTwitterAccountNumber($F('tname'));" value="" />
        </div>
        <div>
	        <label>&nbsp;</label>
            <input type="submit" value="Add Account" />
        </div>
    </form>
    <span id="accountnumber" style="display: none;"></span>
    
    <div class="clear"></div><br />
    <h3>Accounts</h3>
    <ul>
    	<?php		
			//get the accounts
			$gtaccounts = $wpdb->get_results("SELECT * FROM $wp_grouptwitter_accounts ORDER BY name");
			
			foreach($gtaccounts as $gta)
			{
				?>
                <li><a target="_blank" href="http://twitter.com/<?=$gta->name?>"><?=$gta->name?></a> [<a href="javascript:askfirst('Are you sure you would like to remove the <?=$gta->name?> account from the DB?', 'options-general.php?page=grouptwitter&delete=<?=$gta->id?>');">X</a>]</li>
                <?php
			}
		?>
    </ul>
    
    <div class="clear"></div><br />
    <h3>Tweets</h3>
    <?php
		$ntweets = $wpdb->get_var("SELECT COUNT(*) FROM $wp_grouptwitter");		
	?>
    <p>There are <strong><?=$ntweets?></strong> tweet(s) in the database now. Click here to <a href="options-general.php?page=grouptwitter&update=1">refresh the tweet database</a>.</p>
    
    <p>Add this script to a cronjob (run no more than every 15 minutes) to update your cache automatically:</p>
    <code>/usr/bin/curl -d mypp_cmd=status <?=get_bloginfo("home")?>/wp-content/plugins/grouptwitter/services/updatecache.php?key=<?=$gt_secretkey?></code>
</div>