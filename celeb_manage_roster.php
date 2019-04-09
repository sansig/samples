<?php
/*
This page is a celebrity search page for a celebrity fantasy website.  The page allows users to search a name, and then searches Wikipedia.org to determine if the person exists and is alive.  The script then searches the internet for news and general results to determine if they are famous enough to be selected, which is handled by a formula based on the number of results found.  This page is a part of a larger project that I've been working on in my spare time with several "celebrity fantasy games" where players would earn points based on movie ticket sales, albums sold, scandals and other scoring metrics.
*/
include ("./includes/header.php");

$year = 31536000;  //A year in seconds
if($_GET['search_term'] <> '')
$search_term = strtolower($_GET['search_term']);
if($_POST['search_term'] <> '')
$search_term = strtolower($_POST['search_term']);

$league_id = $_GET['id'];
include ("./deathpool_connection.php");

if($_GET['celebrity_id'] > 0)
	$celebrity_id = $_GET['celebrity_id'];

include ("./includes/menu.php");
$user_id = $_SESSION['user_id'];
echo "<body><div id=\"main\">";
echo "<div id=\"celebrity_box\">";
$status[0] = "Dead";
$status[-1] = "Alive";
$sql = "SELECT * FROM tbl_leagues INNER JOIN tbl_leagues_x_players ON tbl_leagues_x_players.league_id = tbl_leagues.league_id WHERE tbl_leagues.league_id = " . $league_id;

$result = mysql_query($sql);
$rs = mysql_fetch_assoc($result);
$league_status = $rs['status'];


if($_POST['action'] == "Add" && $league_status < 1)
{
	$url = stripslashes($_POST['url']);
	$search  = array('_');
	$replace = array(' ');
	$name = stripslashes(str_replace($search, $replace, $_POST['name']));	
	$bday = stripslashes($_POST['bday']);
	$dday = stripslashes($_POST['dday']);
	$yahoo_count = stripslashes($_POST['yahoo_count']);
	$yahoo_news_count = stripslashes($_POST['yahoo_news_count']);
	$score = number_format((str_replace(",", "", $yahoo_count) / 500000) + (str_replace(",", "", $yahoo_news_count) / 100), 2);
	$sql = "SELECT * FROM tbl_celebrities WHERE url = '" . $url . "'";
	$results = mysql_query($sql);
	if(mysql_num_rows($results))  			///////////////////////////////////// Already in database, adding to roster ///////////////////////////////////// 
	{
		$rs = mysql_fetch_assoc($results);
		$sql = "SELECT * FROM tbl_x_ref WHERE league_id = '" . $league_id . "' AND user_id = '" . $user_id . "' AND celebrity_id = '" . $rs['celebrity_id'] . "'";
		if(mysql_num_rows(mysql_query($sql)) > 0)
		{
			echo "<p class=\"error\">" . $rs['full_name'] . " is already on your roster<p>";
		}
		else
		{
			$sql = "INSERT INTO tbl_x_ref (`league_id`, `user_id`, `celebrity_id`) VALUES ('" . $league_id . "', '" . $_SESSION['user_id'] . "', '" . $rs['celebrity_id'] . "')";
			e($sql);
			$success = mysql_query($sql);
			if($success)
			{
				echo "<p class=\"success\">Successfully added <a href=\"show_celebrity.php?id=" . $rs['celebrity_id'] . "\">" . $rs['full_name'] . "</a> to your roster</p>";
			}
			else
			{
				echo "<p class=\"error\">Failed insert to roster!</p>";
			}
		}
	}
	else		///////////////////////////////////// NOT IN DATABASE, ADD TO DATABASE AND ROSTER ///////////////////////////////////// 
	{
		$sql = "INSERT INTO tbl_celebrities  (`celebrity_id`, `first_name`, `last_name`, `full_name`, `url`, `status`, `yahoo_count`, `yahoo_news_count`, `score`, `birthdate`, `deathdate`, `eligible`, `cron_check`, `user_id`) VALUES ('','','','" . $name . "','" . $url . "','-1','" . $yahoo_count . "','" . $yahoo_news_count . "','" . $score . "','" . $bday . "','" . $dday . "','-1','-1', '" . $_SESSION['user_id'] . "')";

		$success = mysql_query($sql) or die(mysql_error());
		if($success)
		{
			$sql = "INSERT INTO tbl_x_ref (`league_id`, `user_id`, `celebrity_id`) VALUES ('" . $league_id . "', '" . $_SESSION['user_id'] . "', '" . mysql_insert_id() . "')";
			e($sql);
			$success = mysql_query($sql);
			if($success)
			{
				echo "Successfully added to roster";
			}
			else
			{
				echo "Failed insert to roster!";
			}
		}
		else
		{
			echo "Failed INSERT";
		}
	}
}

if($_POST['action'] == "Drop" && $league_status < 1)
{
	$league_id = stripslashes($_POST['league_id']);
	$celebrity_id = stripslashes($_POST['celebrity_id']);	
	$sql = "DELETE FROM tbl_x_ref WHERE league_id = '" . $league_id . "' AND user_id = '" . $_SESSION['user_id'] . "' AND celebrity_id = '" . $celebrity_id . "'";
	$success = mysql_query($sql);
	if($success)
	{
		echo "Successfully removed from roster";
	}
	else
	{
		echo "Failed to remove from roster";
	}

}

$sql = "SELECT tbl_leagues.league_id, COUNT(celebrity_id) AS total_count, max_roster_size FROM `tbl_x_ref` RIGHT JOIN tbl_leagues ON tbl_leagues.league_id = tbl_x_ref.league_id WHERE tbl_leagues.league_id = " . $league_id . " AND user_id = " . $user_id;	
$result = mysql_query($sql);
$rs = mysql_fetch_assoc($result);
$total_count = $rs['total_count'];
$max_roster_size = $rs['max_roster_size'];

if($league_status <> 0)
{
	$drop = "";
	echo "<h3>Your league has started, you can no longer make roster changes.</h3>";
}
else
{
	$drop = "<input type=\"submit\" name=\"action\" value=\"Drop\">";
	if($total_count < $max_roster_size)
	{
		?>
		<h4>Enter the name of the celebrity you wish to add, english letters only no special characters or numbers please</h4>
		<h4>People must have wikipedia pages with full birthdates to be eligible.</h4>
		<form action="" method="get" id="search" name="search">
		<input type="text" name="search_term" value="<? echo $search_term; ?>" />
		<input type="hidden" name="id" value="<?php echo $league_id; ?>">
		<input type="submit" name="action" value="Search"/>
		</form>
		<?php
	}
	else
	{
		echo "<h3>Your roster is currently full, drop an existing celebrity if you wish to add a new one.</h3>";
	}
}


if($search_term <> "" && $_GET['action'] == "Search")
{
	
	$year = 31536000;  //A year in seconds
	$sql = "SELECT * FROM tbl_celebrities WHERE SOUNDEX(full_name) = SOUNDEX(\"" . $search_term . "\")";

	$results = mysql_query($sql);
	if(mysql_num_rows($results) > 0)
	{
		echo "<h2>Results in our database:</h2>";
		while($rs=mysql_fetch_assoc($results))
		{
			if($rs['eligible'] == -1)
			{
				echo "<td><form action=\"manage_roster.php?id=" . $league_id . "\" method=\"post\"><a href=\"" . $url . "\" target=\"blank\"><span class=\"Alive\">" . $rs['full_name'] . "</name></a> is alive and valued at: " . round((110-((mktime() - $rs['birthdate']) / $year)),2) . " points<input type=\"hidden\" name=\"league_id\" value=\"" . $league_id . "\"><input type=\"hidden\" name=\"celebrity_id\" value=\"" . $celebrity_id . "\"><input type=\"hidden\" name=\"user_id\" value=\"" . $user_id . "\"><input type=\"hidden\" name=\"name\" value=\"" . $celebrity . "\"><input type=\"hidden\" name=\"url\" value=\"" . $rs['url'] . "\"><input type=\"hidden\" name=\"bday\" value=\"" . $birthdate . "\"><input type=\"hidden\" name=\"dday\" value=\"" . $deathdate . "\"><input type=\"hidden\" name=\"yahoo_count\" value=\"" . str_replace(",", "", $yahoo_count) . "\"><input type=\"hidden\" name=\"yahoo_news_count\" value=\"" . str_replace(",", "", $yahoo_news_count) . "\"><br>";
				echo "<br>Born: " . date("n/d/y",$rs['birthdate']);
				echo "<p>Search Score: " . $rs['yahoo_count'] . "</p>";
				echo "<p>News Score: " . $rs['yahoo_news_count'] . "</p>";
				echo "<input type=\"submit\" value=\"Add\" name=\"action\">";
				echo "<p>These are not the droids you're looking for?&nbsp;<input type=\"submit\" value=\"Continue searching\" name=\"action\"><input type=\"hidden\" name=\"search_term\" value=\"" . $search_term . "\"></p></form>";
			}
			else
			{
				echo "<td><form action=\"manage_roster.php?id=" . $league_id . "\" method=\"post\"><a href=\"" . $url . "\" target=\"blank\"><span class=\"Dead\">" . $rs['full_name'] . "</name></a> is not eligible.<br>";
				echo "<br>Why not? ";
				if($rs['status'] == 0)
				{
					echo "That person is dead.  Dead celebrities can't earn ya any points, eh?";
				}
				else
				{
					echo "They're just not famous enough.  Maybe next year?  <p>Who the hell is " . $rs['full_name'] . " anyway?</p>";
				}
				echo "<p>Search Score: " . $rs['yahoo_count'] . "</p>";
				echo "<p>News Score: " . $rs['yahoo_news_count'] . "</p>";
				echo "<p>These are not the droids you're looking for?&nbsp;<input type=\"submit\" value=\"Continue searching\" name=\"action\"><input type=\"hidden\" name=\"search_term\" value=\"" . $search_term . "\"></p></form>";
			}
			
		}
	}
	else
	{
		$search_term =  str_replace(" ", "+", $search_term);
		$celebrity = find_celebrity($search_term);
	
		echo "<table>";
		if(count($celebrity) > 0)
		{
			foreach($celebrity as $key=>$value)
			{
				check_eligibilty($value, $celebrity_id, $league_id, $user_id);
			}
		}
		else
		{
			echo "<p class=\"warning\">No results found</p>";	
		}
	}
}

if($search_term <> "" && $_POST['action'] == "Continue searching")
{

		$search_term =  str_replace(" ", "+", $search_term);
		$celebrity = find_celebrity($search_term);
		echo "<table>";
		foreach($celebrity as $key=>$value)
		{
			check_eligibilty($value, $celebrity_id, $league_id, $user_id);
		}

}

echo "</table></div><div id=\"search_box\">";

$x = 0;

echo "<table><tr><td colspan=\"3\"><h3>Current Roster: (" . $total_count . "/" . $max_roster_size . ")</h3></td></tr>";

$sql = "SELECT *, tbl_x_ref.celebrity_id AS id FROM tbl_celebrities INNER JOIN tbl_x_ref ON tbl_celebrities.celebrity_id = tbl_x_ref.celebrity_id WHERE tbl_x_ref.user_id = '" . $user_id . "' AND league_id = " . $league_id . " ORDER BY full_name ASC";

$results = mysql_query($sql);
$points = 0;
if(mysql_num_rows($results) > 0)
{
	while($rs=mysql_fetch_assoc($results))
	{
		echo "<form action=\"\" method=\"post\">";
		$x++;
		if($rs['status'] == 0) /////////////////// Dead /////////////////////
		{
			echo "<tr><td class=\"" . $status[$rs['status']] . "\">" . $x . "</td><td width=\"200\" class=\"" . $status[$rs['status']] . "\"><a href=\"show_celebrity.php?id=" . $rs['id'] . "\">" . urldecode($rs['full_name']) . "</a></td><td title=\"" . $rs['Points'] . "\" class=\"" . $status[$rs['status']] . "\" align=\"right\">" . round($rs['Points'],2) . "</td><td><input type=\"hidden\" name=\"celebrity_id\" value=\"" . $rs['id'] . "\"><input type=\"hidden\" name=\"league_id\" value=\"" . $rs['league_id'] . "\">" . $drop . "</td></tr>";
			$points = $points + $rs['Points'];
		}
		else /////////////////// Alive /////////////////////
		{
			echo "<tr><td class=\"" . $status[$rs['status']] . "\">" . $x . "</td><td width=\"200\" class=\"" . $status[$rs['status']] . "\"><a href=\"show_celebrity.php?id=" . $rs['id'] . "\">" . urldecode($rs['full_name']) . "</a></td><td><input type=\"hidden\" name=\"celebrity_id\" value=\"" . $rs['id'] . "\"><input type=\"hidden\" name=\"league_id\" value=\"" . $rs['league_id'] . "\">" . $drop . "</td></tr>";
		}
		echo "</form>";
	}
}
else
{
	echo "<tr><td>Your roster is currently empty</td></tr>";
}


$contents = "";
$today = date("Y-m-d");  
$x = 1;

function find_celebrity($n)
{
	
	/*
	$sql = "SELECT * FROM tbl_celebrities WHERE full_name LIKE '" . str_replace('+', ' ', $n) . "'";
	$results = mysql_query($sql);
	if(mysql_num_rows($results) > 0)
	{
		/////////////////////////////////////////// ADD CODE HERE FOR QUICK ADD //////////////////////////////////////
	}
	*/


	$local_url = "/home/micsan58/expirationsweepstakes.com/searches/" . $n . ".html";
	$remote_url = "http://en.wikipedia.org/w/index.php?search=" . $n . "&fulltext=Search";
	$handle = read_file($local_url, $remote_url, true);
	if ($handle) 
	{
		$data_end = false;
		$contents = move_the_stream($handle, "mw-search-results");
		
		for($i=0;$i<5;$i+=1)
		{
		
			$contents = move_the_stream($handle, "<li>");
			$pattern = "|(?<=\"\/wiki\/)([\S\w]*)(?=\")|";
	
			preg_match($pattern, $contents, $matches);
			
			$url_results[$i] = $matches[0];
			
			//$pattern = "/(?<=\")([\S\w]*)(?=\")/";
	
			//preg_match_all($pattern, $contents, $matches);


			//echo "<p><a href=\"http://en.wikipedia.org/wiki/" . $matches[0][0] . "\">This</a> page found, checking eligibility...</p>";
			
			//echo "<br><br>";
			
			//preg_match("/(?<year>\d\d)(?<month>\d\d)(?<day>\d\d)/", $matches["date"], $current_date);
	
			//var_dump($matches);
		
		}
		fclose($handle);
		
		//////////////////////////////Celebrity page found, searching for born and death dates.////////////////////////////////
		
		return $url_results;
	
	}
	
	
}

function check_eligibilty($celebrity, $celebrity_id, $league_id, $user_id)
{
	$search  = array('_', '"');
	$replace = array(' ', '');

	$year = 31536000;  //A year in seconds
	$dead = false;
	$person = false;
	

	$local_url = "/home/micsan58/expirationsweepstakes.com/wiki/" . $celebrity . ".html";
	$remote_url = "http://en.wikipedia.org/wiki/" . $celebrity;

	$url = "http://en.wikipedia.org/wiki/" . $celebrity;

	$handle = read_file($local_url, $remote_url, false);
	
	if ($handle) 
	{
		$data_end = false;
		$contents = move_the_stream($handle, "bday\">");
			

		
		if($contents)
		{
			$person = true;
			

			$pattern = "/([0-9]{4})-([0-9]{2})-([0-9]{2})/";
			
			preg_match_all($pattern, $contents, $matches);
			$birthdate = mktime(0, 0, 0, $matches[2][0], $matches[3][0], $matches[1][0]);
			
				
			$contents = check_dead($handle, "<span class=\"dday");
			if($contents)
			{
				preg_match_all($pattern, $contents, $matches);
				$deathdate = mktime(0, 0, 0, $matches[2][0], $matches[3][0], $matches[1][0]);
			
				$alive = false;
			
			}
			else
			{
				$deathdate = 0;
				$alive = true;
			}
		}
		else
		{
			$person = false;
			$error = 'Not a person';
		}
		
		if($person)
		{
			$name = str_replace("_", "%20", $celebrity);

			$pattern = "/(\([A-Za-z]+\))/";
			
			preg_match_all($pattern, $name, $matches);
				
			$name = str_replace($matches[0][0], "", $name);


			$yahoo_count = check_yahoo_count($name);
			$yahoo_news_count = check_yahoo_news_count($name);
				$score = number_format((str_replace(",", "", $yahoo_count) / 500000) + (str_replace(",", "", $yahoo_news_count) / 100), 2);
			if($alive)
			{	
				if($score >= .15)
				{
					$eligible = "-1";
					echo "<td><form action=\"manage_roster.php?id=" . $league_id . "\" method=\"post\"><a href=\"" . $url . "\" target=\"blank\"><span class=\"Alive\">" . urldecode($name) . "</name></a> is alive and valued at " . round((110-((mktime() - $birthdate) / $year)),2) . " points<input type=\"hidden\" name=\"league_id\" value=\"" . $league_id . "\"><input type=\"hidden\" name=\"celebrity_id\" value=\"" . $celebrity_id . "\"><input type=\"hidden\" name=\"user_id\" value=\"" . $user_id . "\"><input type=\"hidden\" name=\"name\" value=\"" . $celebrity . "\"><input type=\"hidden\" name=\"url\" value=\"" . $url . "\"><input type=\"hidden\" name=\"bday\" value=\"" . $birthdate . "\"><input type=\"hidden\" name=\"dday\" value=\"" . $deathdate . "\"><input type=\"hidden\" name=\"yahoo_count\" value=\"" . str_replace(",", "", $yahoo_count) . "\"><input type=\"hidden\" name=\"yahoo_news_count\" value=\"" . str_replace(",", "", $yahoo_news_count) . "\"><br>";
					echo "<br>Born: " . date("n/d/y",$birthdate);
					echo "<p>Search Score: " . $yahoo_count . "</p>";
					echo "<p>News Score: " . $yahoo_news_count . "</p>";
					echo "<p>Popularity Score: " . $score . "</p>";
					echo "<input type=\"submit\" value=\"Add\" name=\"action\"></form>";
				}
				else
				{
					$eligible = "0";
					echo "<td><form action=\"manage_roster.php?id=" . $league_id . "\" method=\"post\"><a href=\"" . $url . "\" target=\"blank\"><span class=\"Alive\">" . urldecode($name) . "</name></a> is alive and valued at " . round((110-((mktime() - $birthdate) / $year)),2) . " points<input type=\"hidden\" name=\"league_id\" value=\"" . $league_id . "\"><input type=\"hidden\" name=\"celebrity_id\" value=\"" . $celebrity_id . "\"><input type=\"hidden\" name=\"user_id\" value=\"" . $user_id . "\"><input type=\"hidden\" name=\"name\" value=\"" . $celebrity . "\"><input type=\"hidden\" name=\"url\" value=\"" . $url . "\"><input type=\"hidden\" name=\"bday\" value=\"" . $birthdate . "\"><input type=\"hidden\" name=\"dday\" value=\"" . $deathdate . "\"><input type=\"hidden\" name=\"yahoo_count\" value=\"" . $yahoo . "\"><input type=\"hidden\" name=\"yahoo_news_count\" value=\"" . $yahoo_news_count . "\"><br>";
					echo "<br>Born: " . date("n/d/y",$birthdate);
					echo "<p>Search Score: " . $yahoo_count . "</p>";
					echo "<p>News Score: " . $yahoo_news_count . "</p>";
					echo "<p class=\"Dead\">Popularity Score: " . $score . "</p>";
					echo "Ineligible score.  If you feel this is a mistake, you can contact <a href=\"mailto:michael.sansig@gmail.com\">michael.sansig@gmail.com</a>, or maybe go make a sex tape with them?  Yeah, that might get them over the threshold.";

				}
				echo "<br><br></td></tr>";
				$sql = "INSERT INTO tbl_celebrities  (`celebrity_id`, `first_name`, `last_name`, `full_name`, `url`, `status`, `yahoo_count`, `yahoo_news_count`, `score`, `birthdate`, `deathdate`, `eligible`, `cron_check`, `user_id`) VALUES ('','','','" . urldecode($name) . "','" . $url . "','-1','" . $yahoo_count . "','" . $yahoo_news_count . "','" . $score . "','" . $birthdate . "','" . $dday . "','" . $eligible . "','-1', '" . $_SESSION['user_id'] . "')";
			}
			else
			{
				echo "That person is dead.  Dead celebrities can't earn ya any points, eh?</td></tr>";
			}
		}
		else
		{
			//echo $url . " could not be verified as a person, or alive<br>";
		}

	}

			fclose($handle);
}


function check_yahoo_count($name)
{

		$url = 'http://search.yahoo.com/search?p="' . $name . '"';
		$ch = curl_init($url);
		$fp = fopen("yahoo.xml", "w");
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch,CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15") );
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
		
		$year = 31536000;  //A year in seconds
		$dead = false;
		$person = false;
		
		$handle = fopen("yahoo.xml", "r");


			$pattern = "/([0-9]{4})-([0-9]{2})-([0-9]{2})/";
			$pattern = "|(\<span id=\"resultCount\"\>)([0-9]+)|U";
			$pattern = "|([0-9,]+) results|";

			while ((!feof($handle))) 
			{
				$contents = fgets($handle);
				if($contents)
				{
					preg_match($pattern, $contents, $matches);

					if($matches[1] > 0)
					{
						$news_results = $matches[1];
					}
					if($matches2[1] > 0)
					{
						$news_results = $matches2[1];
					}
				}
			}	
			fclose($handle);
			return $news_results;
}

function check_yahoo_news_count($name)
{


	$url = 'http://news.search.yahoo.com/search/news;?p="' . $name . '"';
	$ch = curl_init($url);
	$fp = fopen("yahoo.xml", "w");
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch,CURLOPT_TIMEOUT, 15);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, Array("User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15") );
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);
	
	$year = 31536000;  //A year in seconds
	$dead = false;
	$person = false;
	
	$handle = fopen("yahoo.xml", "r");


	$pattern = "/([0-9]{4})-([0-9]{2})-([0-9]{2})/";
	$pattern = "|(\<span id=\"resultCount\"\>)([0-9]+)|U";
	$pattern = "|([0-9,]+) results|";

	$pattern2 = "|id=\"resultCount\" class=\"count\"\>([0-9,]+)|";

	while ((!feof($handle))) 
	{
		$contents = fgets($handle);
		if($contents)
		{
			preg_match($pattern, $contents, $matches);
			preg_match($pattern2, $contents, $matches2);


			if($matches[1] > 0)
			{
				$news_results = $matches[1];
			}
			if($matches2[1] > 0)
			{
				$news_results = $matches2[1];
			}

		}
	}	
	fclose($handle);
	return $news_results;
}


function check_dead($file_handle, $search_text)
{
	$pos1 = false;
	$x=0;
    while ((!feof($file_handle)) && ($pos1 === false) && $x < 10) 
	{
        $contents = fgets($file_handle);
		$pos1 = strpos($contents, $search_text);
		$x++;
    }
	if(feof($file_handle) || ($x >9))
	{
		$contents = null;

	}
	else
	{
		return $contents;
	}
}


?>

</div></div>

</body>
</html>
