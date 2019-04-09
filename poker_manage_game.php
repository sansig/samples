<?php
/*
This file is a poker game manager page for a poker statistic tracking page I created for a professional poker player friend of mine
*/
include ("./includes/header.php");
include ("./includes/menu.php");
?>
<SCRIPT LANGUAGE="JavaScript">
	var today = new Date();
	var day   = today.getDate();
	var month = today.getMonth();
function remove_player()
{
var x = confirm("Are you sure you want to remove this player?");
  if (x)
      return true;
  else
      return false;
}
function delete_note()
{
var x = confirm("Are you sure you want to delete this note?");
  if (x)
      return true;
  else
      return false;
}
</SCRIPT>
<?php

if($_SESSION['game_id'] > 0) 
{
	$game_id = $_SESSION['game_id'];
}
elseif($_GET['game_id'] > 0)
{
	$game_id = $_GET['game_id'];
}
else
{
	$sql = "SELECT game_id FROM tbl_games ORDER BY game_id DESC LIMIT 1";
	$result = mysql_query($sql);
	$rs = mysql_fetch_assoc($result);
	$game_id = $rs['game_id'];
}


if($_SESSION['admin'] == -1)
{
	
	$trans_type[1] = "out";
	$trans_type[-1] = "in";
	$player_id = $_POST['player_id'];
	$note_id = $_POST['note_id'];
	$type = $_POST['type'];
	$amount = $_POST['amount'];
	$time = mktime();
	$first_name = $_POST['first_name'];
	$last_name = $_POST['last_name'];

	
	
	
	if($_POST['game_id'] > 0)
	{
		$game_id = $_POST['game_id'];
		$_SESSION['game_id'] = $_POT['game_id'];
	}
	
	if($_GET['game_id'] > 0)
	{
		$game_id = $_GET['game_id'];
		$_SESSION['game_id'] = $_GET['game_id'];
		if($_GET['action']=="remove")
		{
			$sql = "DELETE FROM tbl_stats WHERE game_id = " . $game_id . " AND player_id = " . $_GET['player_id'];
			$results = mysql_query($sql);
			$sql = "DELETE FROM tbl_transactions WHERE game_id = " . $game_id . " AND player_id = " . $_GET['player_id'];
			$results = mysql_query($sql);
			//$sql = "UPDATE tbl_tokens SET value = '" . $new_token . "' WHERE ip_address = '" . $_ENV['REMOTE_ADDR'] . "'";
			//echo $sql;
			//$token = $new_token;
			//$results = mysql_query($sql);
			
		}
	}
	
	if($_GET['note_id'] > 0 && $_GET['action']=="delete")
	{
		$sql = "DELETE FROM tbl_notes WHERE note_id = " . $_GET['note_id'];
		$results = mysql_query($sql);
	}
	
	
	if($_POST['trans_id'] > 0)
	{
		$trans_id = $_POST['trans_id'];
	}
	
	if($_GET['trans_id'] > 0)
	{
		$trans_id = $_GET['trans_id'];
	}
	
	if($_POST['action'] == "Load Game")
	{
		$_SESSION['game_id'] = $_POST['game_id'];
	}
	
	if($_POST['start_time'] <> 0)
	{
		$date_from = explode("/", $_POST['start_time']);
		$m = $date_from[0]; 
		$d = $date_from[1]; 
		$y= $date_from[2];  
		$start_time = mktime(0,0,0,$m,$d,$y);
	}
	else
	{	
		$start_time = mktime();
	}
	
	if($_POST['location'] == "")
	{
		$location = htmlentities($_POST['location']);
	}
	else
	{	
		$location = htmlentities("Rokasino");  
	}
	
	
	if($_POST['action'] == "In" || $_POST['action'] == "Out" || $_POST['action'] == "BUSTO")
	{
		if($amount == "" && $_POST['action'] == "In")
			$amount = "75";
		if($amount == "" && $_POST['action'] == "Out")
			$amount = "0";
		if($amount == "" && $_POST['action'] == "BUSTO")
			$amount = "0";
		$sql = "INSERT INTO tbl_transactions (game_id, player_id, type, amount, time) VALUES ('" . $game_id . "', '" . $player_id . "', '" . $type . "', '" . $amount . "', '" . $time . "')";
		//echo $sql;
		$results = mysql_query($sql);
		$sql = "UPDATE tbl_tokens SET value = '" . $new_token . "' WHERE token = 'post' AND ip_address = '" . $_ENV['REMOTE_ADDR'] . "'";
		//$token = $new_token;
		//$results = mysql_query($sql);
		if($_POST['action'] == "Out")
			$sql = "UPDATE tbl_stats SET time_out = " . $time . " WHERE player_id = '" . $player_id . "' AND game_id = '" . $game_id . "'";
			//echo $sql;
			$results = mysql_query($sql);
	}
	
	if($_POST['action'] == "Add Player")
	{
		$sql = "INSERT INTO tbl_stats (game_id, player_id, notes, time_in) VALUES ('" . $game_id . "', '" . $player_id . "', '" . $notes . "',  '" . $time . "')";
		echo $sql;
		$results = mysql_query($sql) or die(mysql_error());
		$sql = "INSERT INTO tbl_transactions (game_id, player_id, type, amount, time) VALUES ('" . $game_id . "', '" . $player_id . "', '-1', '75', '" . $time . "')";
		$results = mysql_query($sql) or die(mysql_error());
	}
	
	if($_POST['action'] == "New Player")
	{
		$sql = "INSERT INTO tbl_players (first_name, last_name, active) VALUES ('" . $first_name . "', '" . $last_name . "', '-1')";
		//echo $sql;
		$results = mysql_query($sql);
		$player_id = mysql_insert_id();
		$sql = "INSERT INTO tbl_stats (game_id, player_id, notes) VALUES ('" . $game_id . "', '" . $player_id . "', '" . $notes . "')";
		$results = mysql_query($sql);
		$sql = "UPDATE tbl_tokens SET value = '" . $new_token . "' WHERE token = 'post' AND ip_address = '" . $_ENV['REMOTE_ADDR'] . "'";
		//$token = $new_token;
		//$results = mysql_query($sql);
	}

	
	if($_POST['action'] == "Update")
	{
		$game_id = $_POST['game_id'];
		$sql = "UPDATE tbl_games SET start_time = '" . $start_time . "', location = '" . $location . "' WHERE game_id = " . $game_id;
		//echo $sql;
		$results = mysql_query($sql);
		$sql = "UPDATE tbl_tokens SET value = '" . $new_token . "' WHERE token = 'post' AND ip_address = '" . $_ENV['REMOTE_ADDR'] . "'";
		//$token = $new_token;
		//$results = mysql_query($sql);
	}

	
	if($_POST['action'] == "Add")
	{

		$notes = $_POST['notes'];
		$sql = "INSERT INTO`tbl_notes` (note, date_modified) VALUES ('" . $notes . "','" . $time . "')";
		$results = mysql_query($sql);
		$sql = "UPDATE tbl_tokens SET value = '" . $new_token . "' WHERE token = 'post' AND ip_address = '" . $_ENV['REMOTE_ADDR'] . "'";
		//$token = $new_token;
		//$results = mysql_query($sql);
	}


}
else
{
	if($_POST['action'] == "Login")
	{
		$sql = "SELECT * FROM tbl_players WHERE email = '" . $_POST['email'] . "'";
		$result = mysql_query($sql);
		$rs = mysql_fetch_assoc($result);
		if(md5($_POST['password']) == $rs['password'])
		{
			$_SESSION['player_id'] = $rs['player_id'];
			$_SESSION['admin'] = $rs['admin'];
		}
	}
	else
	{
		echo "<h3>This is an admin only area</h3>";
		?>
		<form action="" name="login" method="post">
		<br /><input type="text" name="email" value="<?php echo $_POST['email']; ?>" />
		<br /><input type="password" name="password" value="" />
		<br /><input type="submit" name="action" value="Login" />
		</form>	
		<?php
	}
	
}

echo "<body>";
debug($_SESSION);
debug($_POST);

/////////////////////////////////////////////////////////////////  TRANSACTION REGISTER  /////////////////////////////////////////////////////////////////
echo "<div id=\"main\">";

$sql = "SELECT * FROM tbl_games a, tbl_players b WHERE `game_id` = " . $game_id . " AND a.creator_id = b.player_id";
$results = mysql_query($sql);
$rs = mysql_fetch_assoc($results);
?>

<!-- /////////////////////////////////////////////////////////////////  GAME INFO  ///////////////////////////////////////////////////////////////// -->
<table>
<tr><td width="5"></td><h3>Game on <?php echo date("D, F dS Y",$rs['start_time']) . " @ " . $rs['location'] . " created by " . $rs['nick_name'];?></h3></td></tr>
<tr><td width="5"></td><td>Player</td><td>Total</td></tr>
<?php


$sql = "SELECT * FROM `tbl_stats` INNER JOIN tbl_players ON tbl_stats.player_id = tbl_players.player_id WHERE `game_id` = " . $game_id;

$results = mysql_query($sql);
$total_players = 0;
$cashed_out = 0;
while($rs = mysql_fetch_assoc($results))
{
	/////////////////////////////////////////////////////////////////  PLAYER LIST  /////////////////////////////////////////////////////////////////
	$total_players++;
	$trans_type[0] = "active";
	echo "<tr><td><form method=\"POST\" action=\"manage_game.php?player_id=" . $rs['player_id'] . "&game_id=" . $rs['game_id'] . "&action=remove\">";
	echo "<button onclick=\"return remove_player()\">X</button></form></td>";
	echo "<td class=\"name\"><a href=\"player.php?player_id=" . $rs['player_id'] . "\">" . $rs['nick_name'] . "</td>";
	$sql = "SELECT * FROM tbl_transactions WHERE `game_id` = " . $game_id . " AND `player_id` = " . $rs['player_id'];
	$results_p = mysql_query($sql);
	$cash_out = 0;
	if($results_p)
	{
		while($rs_p = mysql_fetch_assoc($results_p))
		{
			$cash_out = $cash_out + ($rs_p['amount'] * $rs_p['type']);
			$total_cash_out = $total_cash_out + ($rs_p['amount'] * $rs_p['type']);
			if($rs_p['type'] == 1)
			{
				
				if($cash_out > 0)
				{
					$trans_type[0] = "out";
				}
				else
				{
					$trans_type[0] = "in";
				}
				$cashed_out++;
			}
		}
	}
	echo "<td class=\"" . $trans_type[0] . "\">" . $cash_out . "</td>";
	
	echo "<td><form method=\"post\">";
	echo "<input type=\"text\" name=\"amount\" class=\"w30\">";
	echo "<input type=\"hidden\" name=\"id\" value=\"" . $game_id . "\">";
	echo "<input type=\"hidden\" name=\"player_id\" value=\"" . $rs['player_id'] . "\">";
	echo "<input type=\"hidden\" name=\"type\" value=\"-1\">";
	echo "<input type=\"hidden\" name=\"token\" value=\"" . $new_token . "\"></td>";
	echo "<td><input type=\"submit\" name=\"action\" value=\"In\"></form></td>";
	echo "<td><form method=\"post\">";
	echo "<input type=\"hidden\" name=\"id\" value=\"" . $game_id . "\">";
	echo "<input type=\"hidden\" name=\"player_id\" value=\"" . $rs['player_id'] . "\">";
	echo "<input type=\"hidden\" name=\"type\" value=\"1\">";
	echo "<input type=\"hidden\" name=\"token\" value=\"" . $new_token . "\"></td>";
	echo "<td class=\"data\"><input type=\"submit\" name=\"action\" value=\"BUSTO\"></form></td>";
	
	/////////////////////////////////////////////////////////////////  LIVE TRANSACTIONS  /////////////////////////////////////////////////////////////////
	$sql = "SELECT * FROM tbl_transactions WHERE `game_id` = " . $game_id . " AND `player_id` = " . $rs['player_id'] . " ORDER BY time ASC";
	$results_t = mysql_query($sql);
	if($results_t)
	{
		while($rs_t = mysql_fetch_assoc($results_t))
		{
			
			echo "<td class=\"" . $trans_type[$rs_t['type']] . "\" title=\"" . date("h:i", $rs_t['time']) . "\" class=\"data\"><a href=\"javascript:window.open('./edit_transaction.php?trans_id=" . $rs_t['trans_id'] . "','add_player', 'height=140, width=350, screenX=300, screenY=150')\" target=\"_new\">" . $rs_t['amount'] . "</a></td>";
			
		}
	}
	echo "<td class=\"data\"><form method=\"post\"><input type=\"text\" name=\"amount\" class=\"w30\"><input type=\"hidden\" name=\"id\" value=\"" . $game_id . "\"><input type=\"hidden\" name=\"player_id\" value=\"" . $rs['player_id'] . "\"><input type=\"hidden\" name=\"type\" value=\"1\"><input type=\"hidden\" name=\"token\" value=\"" . $new_token . "\"></td><td class=\"data\"><input type=\"submit\" name=\"action\" value=\"Out\"></form></td>";
	echo "</tr>";
}

/////////////////////////////////////////////////////////////////  TOTALS  /////////////////////////////////////////////////////////////////

echo "<tr><td></td><td>Total Players(In/Out)</td><td>" . ($total_players - $cashed_out) . " / " . $cashed_out . "</td></tr>";
echo "<tr><td></td><td>Cash in play</td><td>" . ($total_cash_out * -1) . "</td></tr>";
echo "<tr><td></td></tr></table>";

/////////////////////////////////////////////////////////////////  NOTES  /////////////////////////////////////////////////////////////////

echo "<table id=\"transaction_register\"><tr><td>Last transactions:</td></tr>";
$sql = "SELECT * FROM `tbl_transactions` INNER JOIN tbl_players ON tbl_players.player_id = tbl_transactions.player_id WHERE game_id = " . $game_id . " ORDER BY time DESC";
//echo $sql;
$results = mysql_query($sql);
while($rs = mysql_fetch_assoc($results))
{
	echo "<tr><td>" . $rs['nick_name'] . "</td>";
	echo "<td>" . date("h:i:s",$rs['time']) . "</td>";
	echo "<td><a href=\"edit_stats.php?game_id=" . $game_id . "&player_id=" . $rs['player_id'] . "\">" . ($rs['amount'] * $rs['type']) . "</a></td></tr>";
}
echo "</table>";
$sql = "SELECT * FROM `tbl_notes` ORDER BY date_modified DESC";
$results = mysql_query($sql);
echo "<form action=\"\" method=\"post\">";
echo "<input type=\"hidden\" name=\"token\" value=\"" . $new_token . "\">";
echo "<input type=\"text\" name=\"notes\"><input type=\"submit\" name=\"action\" value=\"Add\"><div id=\"note_section\"></form><table><tr><td></td></tr>";

while($rs = mysql_fetch_assoc($results))
{
	echo "<tr><td><form method=\"GET\" action=\"manage_game.php?game_id=" . $game_id . "\"><input type=\"hidden\" name=\"note_id\" value=\"" . $rs['note_id'] . "\"><input type=\"hidden\" name=\"action\" value=\"delete\"><button onclick=\"return delete_note()\">X</button>" . date("m/d/Y",$rs['date_modified']) . ": " . $rs['note'] . "</form></td></tr>";
}
echo "<tr><td></td></tr></table></div>";

$sql = "SELECT * FROM `tbl_games` WHERE game_id = " . $game_id;
$results = mysql_query($sql);
if(mysql_num_rows($results) > 0)
{
	$rs=mysql_fetch_assoc($results);
	echo "<table><tr><td>Date:</td><td><input type=\"text\" name=\"start_time\" value=\"" . date("m/d/Y",$rs['start_time']) . "\"></td></tr>";
	echo "<tr><td>Location:</td><td><input type=\"text\" name=\"location\" value=\"" . $rs['location'] . "\"></td>";
	echo "<input type=\"hidden\" name=\"token\" value=\"" . $new_token . "\">";
	echo "<input type=\"hidden\" name=\"game_id\" value=\"" . $game_id . "\">";
	echo "<td><input type=\"submit\" name=\"action\" value=\"Update\"></td></tr></form>";
}
else
{
	echo "Game not found please start over again.<br>";
}
echo "<tr><td>Your IP: " . $_ENV['REMOTE_ADDR'] . "</td></tr></table>";
echo "</div>";

include ("./includes/footer.php");
?>