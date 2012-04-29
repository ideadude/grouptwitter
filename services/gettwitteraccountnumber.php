<?php
	//this service will attempt to get a user's twitter account number 
	$a = $_REQUEST['a'];
	$profilepage = file_get_contents("http://twitter.com/" . $a);
	if($profilepage)
	{
		$n = gt_getMatches("/\/([0-9]*)\.rss/", $profilepage, true);
		if($n)
			echo $n;
		?>
        <script language="javascript" type="text/javascript">
			try {
			$('taccount').value = '<?=$n?>';
			} catch(err){};
		</script>
        <?php
	}
	
	function gt_getMatches($p, $s, $firstvalue = FALSE, $n = 1)
	{
		$ok = preg_match_all($p, $s, $matches);		
		
		if(!$ok)
			return false;
		else
		{		
			if($firstvalue)
				return $matches[$n][0];
			else
				return $matches[$n];
		}
	}
?>