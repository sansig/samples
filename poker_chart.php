<?php
/*
This file is a google chart creator for a poker statistic website I created for a professional poker player friend.  When a player's name is requested, this chart will show the dates they played as well as their wins or losses.
*/
include ("./includes/header.php");
include ("./includes/menu.php");
$one_day = 86400;
$one_week = $one_day * 7;
$one_year = $one_day * 365;
if($_GET['action'] == "Filter")
{
	$begin_time = time() - ($_GET['years'] * $one_year) - ($_GET['weeks'] * $one_week) - ($_GET['days'] * $one_day);
	$where_clause =  " WHERE time > " . $begin_time . " ";
}
elseif($_GET['action'] == "Filter by Date")
{
	$begin_time = explode("/", $_GET['date_start']);
	$m = $begin_time[0]; 
	$d = $begin_time[1]; 
	$y= $begin_time[2];  
	$begin_time = mktime(0,0,0, $m, $d, $y);
	
	$end_time = explode("/", $_GET['date_end']);
	$m = $end_time[0]; 
	$d = $end_time[1]; 
	$y= $end_time[2];  
	$end_time = mktime(0,0,0, $m, $d, $y);
	
	$where_clause =  " WHERE time > " . $begin_time . " AND time < " . $end_time . " ";
	$where_clause_game =  " WHERE start_time > " . $begin_time . " AND start_time < " . $end_time . " ";
}
elseif($_GET['action'] == "All")
{
	$begin_time = 0;
	$where_clause =  " WHERE 1 ";
}
else
{
	$begin_time = time() -  $one_year;
	$where_clause =  " WHERE time > " . $begin_time . " ";
	$where_clause_game =  " WHERE start_time > " . $begin_time . " ";
}
$url = "index.php?r=a";
$order_clause = " ORDER BY total DESC";
switch($_GET['s'])
{
	case "t":
		$order_clause = " ORDER BY total DESC";
		break;
	case "g":
		$order_clause = " ORDER BY buyin_count DESC";
		break;
}


$sql = "SELECT * FROM tbl_games " . $where_clause_game . "ORDER BY start_time DESC";
$results = mysql_query($sql);
$x=0;
$record_count = mysql_num_rows($results);
while($rs=mysql_fetch_assoc($results))
{
	$game[$x]['start_time'] = $rs['start_time'];
	$game[$x]['game_id'] = $rs['game_id'];
	$x++;
}

$sql = "SELECT game_id, player_id, SUM(amount * type) as total FROM `tbl_transactions` " . $where_clause . "GROUP BY game_id, player_id";
$results = mysql_query($sql);
$x=0;
while($rs=mysql_fetch_assoc($results))
{
	$data[$rs['player_id']][$rs['game_id']]['total'] = $rs['total'];
}
echo "<form>";
echo "<table><tr><td>Days:</td><td><input type=\"text\" name=\"days\" value=\"" . $_GET['days'] . "\"></td></tr>";
echo "<tr><td>Weeks:</td><td><input type=\"text\" name=\"weeks\" value=\"" . $_GET['weeks'] . "\"></td></tr>";
echo "<tr><td>Years:</td><td><input type=\"text\" name=\"years\" value=\"" . $_GET['years'] . "\"></td></tr>";
echo "<tr><td><input type=\"submit\" name=\"action\" value=\"Filter\"></td><td><input type=\"submit\" name=\"action\" value=\"All\"></td></tr>";
echo "</table></form>";
echo "<form><table><tr><td>Date Start:</td>";
?>
    <td><div id="datetimepicker" class="input-append date">
      <input type="text" name="date_start" value="<?php echo $_GET['date_start'];?>"></input>
      <span class="add-on">
        <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
      </span>
    </div></td></tr>
    <script type="text/javascript">
      $('#datetimepicker').datetimepicker({
        format: 'MM/dd/yyyy',
      pickTime: false
      });
    </script>
    <?php
	echo "<tr><td>Date End:</td>";
	?>
    <td><div id="datetimepicker2" class="input-append date">
      <input type="text" name="date_end" value="<?php echo $_GET['date_end'];?>"></input>
      <span class="add-on">
        <i data-time-icon="icon-time" data-date-icon="icon-calendar"></i>
      </span>
    </div></td></tr>
    <script type="text/javascript">
      $('#datetimepicker2').datetimepicker({
        format: 'MM/dd/yyyy',
      pickTime: false
      });
    </script>
<?php
echo "<tr><td><input type=\"submit\" name=\"action\" value=\"Filter by Date\"></td></tr>";
echo "</table></form>";
echo "<table bordercolor=\"#000000\" cellspacing=\"0\" id=\"roster\">";
echo "<tr align=\"left\"><th>Name</td><th width=\"50\"><a href=\"" . $url . "&s=g\">Games</a></td><th width=\"50\"><a href=\"" . $url . "&s=t\">Totals</a></td><td>Average</td><td></td>";
for ($x = 0; $x < $record_count; $x++) 
{
	echo "<th align=\"right\"><a href=\"./admin/manage_game.php?game_id=" . $game[$x]['game_id'] . "\" title=\"" . date("m/d/y", $game[$x]['start_time']) . "\">" . date("m/d", $game[$x]['start_time']) . "</a></td>";
}
echo "</tr>";
$sql = "SELECT tbl_players.player_id, SUM(amount * type) as total, COUNT(game_id) AS buyin_count, first_name, last_name, nick_name, active, time FROM `tbl_transactions` INNER JOIN tbl_players ON tbl_players.player_id = tbl_transactions.player_id " . $where_clause . "GROUP BY tbl_players.player_id " . $order_clause;


debug($sql);
$results = mysql_query($sql);
$y=0;
while($rs=mysql_fetch_assoc($results))
{	


	$sql = "SELECT DISTINCT game_id, player_id FROM `tbl_transactions` " . $where_clause . "AND `player_id` = " . $rs['player_id'];
	//echo $sql;

	$results2 = mysql_query($sql);
	$total_sessions = mysql_num_rows($results2);
	
	if($rs['total'] > 0)
	{
		$status = "out";
	}
	else
	{
		$status = "in";
	}
	echo "<td class=\"name\"><a href=\"./player.php?player_id=" . $rs['player_id'] . "\">" . $rs['nick_name'] . "</a></td>";
	echo "<td align=\"right\">" . $total_sessions . "</td>";
	echo "<td align=\"right\" class=\"" . $status . "\">" . $rs['total'] . "</td>";
	echo "<td align=\"right\" class=\"" . $status . "\">" . number_format($rs['total'] / $total_sessions,2) . "</td><td></td>";
	
	for ($x = 0; $x < $record_count; $x++)
	{	
		if($data[$rs['player_id']][$game[$x]['game_id']]['total'] > 0)
		{
			$status = "out";
		}
		else
		{
			$status = "in";
		}
		if($data[$rs['player_id']][$game[$x]['game_id']]['total'] == 0)
		{
			echo "<td align=\"center\" class=\"empty\"><a href=\"./admin/edit_stats.php?game_id=" . $game[$x]['game_id'] . "&player_id=" . $rs['player_id'] . "\">-</a></td>";
		}
		else
		{
			echo "<td width=\"70\" class=\"" . $status . "\" align=\"right\"><a href=\"./admin/edit_stats.php?game_id=" . $game[$x]['game_id'] . "&player_id=" . $rs['player_id'] . "\">" . $data[$rs['player_id']][$game[$x]['game_id']]['total'] . "</a></td>";
		}
	}
echo "</tr>";
}
echo "</table";

$sql = "SELECT SUM(tbl_transactions.amount) AS total, tbl_players.nickname FROM `tbl_transactions`, tbl_players, tbl_games WHERE tbl_transactions.game_id = tbl_games.game_id AND tbl_transactions.player_id = tbl_players.player_id " . $where_clause . "GROUP BY tbl_players.player_id ORDER BY total DESC";

?>

</p>
</body>
</html>
