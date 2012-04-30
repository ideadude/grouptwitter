<?php
class GT_Twitter {
  public function __construct($twitter_id) {
	global $wpdb, $wp_grouptwitter_accounts;
	$this->id = (int)$twitter_id;
	$this->profile_image_url = $wpdb->get_var("SELECT profile_image_url FROM $wp_grouptwitter_accounts WHERE id = '$this->id' LIMIT 1");
  }
 
  public function user_timeline($page, $count = '50', $since_id = '') {
    $url = 'http://twitter.com/statuses/user_timeline/' . $this->id . '.xml?count=' . $count . '&page=' . $page;   	
	if ($since_id && $since_id != '') {
      $url .= '&since_id=' . $since_id;
    }
    
	$response = wp_remote_retrieve_body(wp_remote_get($url));
	if(!empty($response))
	{
		if (class_exists('SimpleXMLElement')) {
		  return new SimpleXMLElement($response);
		} else {
		  return $response;
		}
	}
	else
		return false;
  }
 
  public function rebuild_archive($your_timezone) {
    global $wpdb, $wp_grouptwitter, $wp_grouptwitter_accounts;
	$orig_tz = date_default_timezone_get();
    date_default_timezone_set('GMT');
    $tz = new DateTimeZone($your_timezone);
    $sql = "SELECT id FROM $wp_grouptwitter WHERE account_id = '$this->id' ORDER BY id DESC LIMIT 1";	
	$since_id = $wpdb->get_var($sql);	
    $tweet_count = 0;
    for ($page = 1; $page <= 1; ++$page) {
      if ($twitter_xml = $this->user_timeline($page, '50', $since_id)) {        
		//check the profile image on the first page
		if($page == 1)
		{			  
			if($twitter_xml->status[0]->user->profile_image_url && $twitter_xml->status[0]->user->profile_image_url != $this->profile_image_url)
			{
				$wpdb->query("UPDATE $wp_grouptwitter_accounts SET profile_image_url = '" . addslashes($twitter_xml->status[0]->user->profile_image_url) . "' WHERE id = '$this->id' LIMIT 1");
			}
		}
		
		foreach ($twitter_xml->status as $key => $status) {
          $datetime = new DateTime($status->created_at);
          $datetime->setTimezone($tz);
          $created_at = $datetime->format('Y-m-d H:i:s');
          $sql = "INSERT IGNORE INTO $wp_grouptwitter
                    (id, account_id, created_at, source, in_reply_to_screen_name, text)
                  VALUES (
                    '" . $status->id . "',
                    '" . $this->id . "', 
					'" . $created_at . "',
                    '" . addslashes((string)$status->source) . "',
                    '" . addslashes((string)$status->in_reply_to_screen_name) . "',
                    '" . addslashes((string)$status->text) . "'
                  )";
          $wpdb->query($sql);		  
          ++$tweet_count;		  		  
        }
      } else {
        break;
      }
    }
    //$sql = "ALTER TABLE $wp_grouptwitter ORDER BY `id`";
    //$wpdb->query($sql);
    date_default_timezone_set($orig_tz);
    return $tweet_count;
  }
}
?>
