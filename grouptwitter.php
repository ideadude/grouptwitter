<?php
/*
Plugin Name: Group Twitter
Plugin URI: http://www.strangerstudios.com/wordpress-plugins/grouptwitter/
Description: Cache Twitter updates from multiple accounts.
Version: .2
Author: Jason Coleman
Author URI: http://www.strangerstudios.com/

Updates
= .2 =
* Added widget.
*/

	global $wpdb, $wp_grouptwitter, $wp_grouptwitter_accounts, $gt_secretkey;
	$gt_secretkey = "6E2C2EFA";		//this value is used to secure the udpatecache script
	$gt_db_version = ".1";
	$wp_grouptwitter = $wpdb->prefix ."grouptwitter";
	$wp_grouptwitter_accounts = $wpdb->prefix ."grouptwitter_accounts";
	
	require_once(dirname(__FILE__) . "/classes/class.twitter.php");
	require_once(dirname(__FILE__) . "/classes/class.grouptwitter_widget.php");
	
	function gt_install()
	{
		global $wpdb;
		global $gt_db_version;
		global $wp_grouptwitter;
		global $wp_grouptwitter_accounts;
		
		$table_name = $wp_grouptwitter;
		$table2_name = $wp_grouptwitter_accounts;
		if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
		{		  
		  //our table
		  $sql = "CREATE TABLE " . $table_name . " (		  	
			 `id` BIGINT( 10 ) UNSIGNED NOT NULL ,
			 `account_id` VARCHAR( 255 ) NOT NULL ,
			 `created_at` DATETIME NOT NULL ,
			 `source` VARCHAR( 255 ) NOT NULL ,
			 `in_reply_to_screen_name` VARCHAR( 255 ) NOT NULL ,
			 `text` VARCHAR( 255 ) NOT NULL ,
			PRIMARY KEY  `id` ( `id` ),
			KEY `account_id` (`account_id`)
			) ENGINE = MYISAM DEFAULT CHARSET = utf8";
		  
		  $sql2 = "CREATE TABLE " . $table2_name . " (													 
			 `id` BIGINT(10) NOT NULL ,
			 `name` VARCHAR( 255 ) NOT NULL ,
			 `profile_image_url` VARCHAR( 255 ) NOT NULL ,
			 `last_update` datetime NOT NULL,
			PRIMARY KEY (  `id` )
			) ENGINE = MYISAM";
		
		  //need this to run the dbDelta to create table
		  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		  dbDelta($sql);
		  dbDelta($sql2);
					
		  //incase we upgrade DB in the future
		  add_option("gt_db_version", $gt_db_version);		
		}
	}				
		
	function gt_menu()
	{
		 add_options_page('Group Twitter', 'Group Twitter', 8, 'grouptwitter', 'gt_options_page');
	}
	
	function gt_options_page()
	{								
		require_once(dirname(__FILE__) . "/adminpages/options.php");
	}	
	
	function gt_addAdminHeaderCode()
	{		
		echo '<link type="text/css" rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/grouptwitter/css/admin.css" />' . "\n";
	}
	
	function gt_addFrontendHeaderCode()
	{		
		echo '<link type="text/css" rel="stylesheet" href="' . $url . '/wp-content/plugins/grouptwitter/css/frontend.css" media="screen" />' . "\n";		
	}
	
	function gt_linkify( $text ) {
	  $text = preg_replace( '/(?!<\S)(\w+:\/\/[^<>\s]+\w)(?!\S)/i', '<a href="$1" target="_blank">$1</a>', $text );
	  $text = preg_replace( '/(?!<\S)#(\w+\w)(?!\S)/i', '<a href="http://twitter.com/search?q=#$1" target="_blank">#$1</a>', $text );
	  $text = preg_replace( '/(?!<\S)@(\w+\w)(?!\S)/i', '@<a href="http://twitter.com/$1" target="_blank">$1</a>', $text );
	  return $text;
	}
	
	function gt_getTweets($n = 5, $a = "all", $s = "", $p = 1, $details = false)
	{
		global $wpdb, $wp_grouptwitter, $wp_grouptwitter_accounts;
		
		$end = $p * $n;
		$start = $end - $n;
		
		$sqlQuery = "SELECT SQL_CALC_FOUND_ROWS t.*, UNIX_TIMESTAMP(t.created_at) as created_at, a.name, a.profile_image_url FROM $wp_grouptwitter t LEFT JOIN $wp_grouptwitter_accounts a ON t.account_id = a.id WHERE 1=1 ";
		if($a && $a != "all")
			$sqlQuery .= "AND a.name = '$a' ";
		if($s)
		{
			$terms = split(" ", $s);
			foreach($terms as $term)
			{
				$term = trim($term);
				$sqlQuery .= "AND t.text LIKE '%$term%' ";
			}
		}
		
		$sqlQuery .= " ORDER BY t.id DESC LIMIT $start, $n ";
		
		$tweets = $wpdb->get_results($sqlQuery);
		
		$totalrows = $wpdb->get_var("SELECT FOUND_ROWS() as found_rows");
		$end = min($end, $totalrows);
		
		//linkify
		for($i = 0; $i < count($tweets); $i++)
		{
			$tweets[$i]->text = gt_linkify($tweets[$i]->text);
		}
		
		if($details)
		{
			$temp->tweets = $tweets;
			$temp->start = $start;
			$temp->last = $end;
			$temp->totalrows = $totalrows;
			
			return $temp;
		}
		else
			return $tweets;
	}
	
	function gt_displayDateTime($dt)
	{
		if(date("Y") == date("Y", $dt))
			return str_replace(" ", "&nbsp;", date("g:iA M jS", $dt));
		else
			return str_replace(" ", "&nbsp;", date("g:iA M j, Y", $dt));
	}
	
	function gt_showTweets($n = 5, $a = "all", $s = "", $p = 1, $shownav = false, $class = "twitterfeed", $avatar = false)
	{
		if($shownav)
		{
			$results = gt_getTweets($n, $a, $s, $p, true);
			$tweets = $results->tweets;			
			if($results->last)
			{
			?>
            	<p class="gt_summary">Showing tweets <span><?=$results->start+1?></span> to <span><?=$results->last?></span> of <span><?=$results->totalrows?></span>.</p>
            <?php
            }
            else
            {
            ?>
            	<p class="gt_summary">No tweets found.</p>
            <?php
            }            
		}
		else
			$tweets = gt_getTweets($n, $a, $s, $p);
		?>
        <ul class="<?=$class?>">
        <?php
		foreach($tweets as $tweet)
		{
		?>
        	<li>
            	<?php if(!empty($avatar)) { ?>
				<a class="twitterfeed-avatar" target="_blank" href="http://twitter.com/<?=$tweet->name?>" title="<?=$tweet->name?>"><img height="32" border="0" width="32" alt="<?=$tweet->name?>" src="<?=$tweet->profile_image_url?>"/></a>				
				<?php } ?>
				<div class="twitterfeed-content">
					<?php echo make_clickable($tweet->text); ?>
                	<small>&bull; <a target="_blank" href="http://twitter.com/<?=$tweet->name?>/status/<?=$tweet->id?>"><?=gt_displayDatetime($tweet->created_at)?></a></small>
				</div>
            </li>
		<?php
		}
		?>
        </ul>
        <?php
		
		if($shownav)
		{
		?>
		<div class="line"></div>
		<div class="navigation">
			<?php
			if($p > 1)
			{
				?>
               <div class="alignleft"><a href="/twitter/?ts=<?=$s?>&ta=<?=$a?>&tp=<?=$p-1?>">&laquo; Newer Tweets</a> &nbsp;</div>
                <?php								
			}
			
			if($results->totalrows > $results->last)
			{
				?>
                <div class="alignright"><a href="/twitter/?ts=<?=$s?>&ta=<?=$a?>&tp=<?=$p+1?>">Older Tweets &raquo;</a></div>
                <?php
			}
			?>
			<div class="clear"></div>
		</div> <!-- end navigation -->
		<?php
		}
	}
	
	register_activation_hook(__FILE__,'gt_install');
	add_action('admin_menu', 'gt_menu');
	add_action('admin_head', 'gt_addAdminHeaderCode');
	add_action('wp_head', 'gt_addFrontendHeaderCode');
	wp_enqueue_script('gt_js', '/wp-content/plugins/grouptwitter/js/gt.js', array('prototype'));		
?>
