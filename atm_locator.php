<?php 
/*
This project  was for a client that wanted to relay ATM location information for their bank but using two other banks websites to pull location data from

The code checks the database for ATM information first, and then if nothing is found submits a form on two different bank websites and adds the data to the client's database before displaying it

There was a cron job attached that ran every so often to clear out old zip code data in order to keep things relatively accurate without having to check the bank websites often
*/


set_time_limit(0);
include('./includes/functions.php'); 
include('./includes/connection.php');
//Default zip code if none is passed
$zip_code = '04101';

//Get the state, city code and zip code from the form
$state = filter_var($_GET['state'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK);
$city_code = filter_var($_GET['city_code'], FILTER_VALIDATE_INT);
$zip_code = filter_var($_GET['zip_code'], FILTER_VALIDATE_INT);

//Use the city code if it was supplied
if($city_code > 0)
{
	$zip_code = $_GET['city_code'];
}
else
{
	$zip_code = $_GET['zip_code'];
}


//Pull up the zip code from the database and find out if it's local, we already have all locations stored so no need to look them up
$sql = "SELECT * FROM tbl_zip_codes WHERE zip_code = '" . $zip_code . "'";
$results = mysql_query($sql);
$rs=mysql_fetch_assoc($results);
if($rs['state'] == "MASSACHUSETTS")
{
	//Use DISTINCT so we don't list the same location twice - in case of slight differences in ATM title
	$sql = "SELECT DISTINCT location_name, location_address FROM tbl_locations WHERE search_zip = '" . $zip_code . "'";
	$results = mysql_query($sql);
	$x = 1;
	
	//Add all the locations from the db result to the $locations var to output later for the map to find them.
	while($rs=mysql_fetch_assoc($results))
	{
		if(!(strtoupper(trim($locations[$x-1]['name'])) == strtoupper(trim($rs['location_name'])) && (strtoupper(trim($locations[$x-1]['address'])) == strtoupper(trim($rs['location_address'])))))
		{
			$locations[$x]['name'] = strtoupper($rs['location_name']);
			$locations[$x]['address'] = strtoupper($rs['location_address']);
			$x++;
		}
	}
}
else
{
	//Search the two websites for atm location info
	search_atm($zip_code);
	search_atm_2($zip_code);
}
global $locations;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html dir="ltr">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
    
     <title>ATM Locator – RCB</title>

<link href="style.css" rel="stylesheet" type="text/css" />

	<!-- TOP MENU CSS -->
	<link rel="stylesheet" href="CSS3_Menu_files/css3menu1/style.css" type="text/css" /><style>._css3m{display:none}</style>
	<!-- TOP MENU CSS -->
    
    <!-- MAIN MENU CSS -->
    <link rel="stylesheet" href="main_nav_files/css3menu2/style2.css" type="text/css" /><style>._css3m{display:none}</style>
    <!-- MAIN MENU CSS -->
    
    <meta name="keywords" content="RCB, Reading, North Reading, Wilmington, Woburn, Burlington, Billerica, Tewksbury, Andover, Massachusetts, Mortgage, Savings, Checking, Commercial Loan">

<meta name="description" content="Find an ATM within our network.">

<link rel="icon" href="favicon.ico" type="image/x-icon" />


<!--- $$$$$$$$$$$$$$$$$  ICON POPUPS --->
 <link rel='stylesheet' href='popscripts/popbox.css' type='text/css' media='screen' charset='utf-8'>
  <script type='text/javascript' charset='utf-8' src='popscripts/jquery.js'></script>
  <script type='text/javascript' charset='utf-8' src='popscripts/popbox.js'></script>
  <script type='text/javascript' charset='utf-8' src='popscripts/popbox2.js'></script>
  <script type='text/javascript' charset='utf-8' src='popscripts/popbox3.js'></script>
  <script type='text/javascript' charset='utf-8' src='popscripts/popbox4.js'></script>
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
<script src="js/HelperGoogleMap.js" type="text/javascript"></script>
<script src="js/HelperListFilter.js" type="text/javascript"></script>
  
  


    <!--- POP BOX 4 --->
    
    
    <!--- $$$$$$$$$$$$$$$$$$$$$  /ICON POPUPS --->
<script> 
$(document).ready(function() {
	var usamap = new HelperGoogleMap();
	
	usamap.getAddress = function(){
		var saveAddr = [];
		$(".getMap").each(function(){
			if($(this).css("display") != "none"){
				var title = $(this).find("a").html();
				var addr = $(this).find(".listaddress").html();
				var contentString = $(this).find(".content").html();
				var itemNumber = $(this).find(".itemNumber").html();

				
				var json = {"title":title, "address" : addr, "contentString" : contentString, "itemNumber" : itemNumber}
				saveAddr.push(json)
			}
		})
		var addresses = { "markers" :saveAddr}
		usamap.AddMarkers(addresses)
	}
	
	usamap.getAddress();
	$(document).bind("listFiltered", function() {	usamap.getAddress()		})
	/*
	var addresses = { "markers" : [
		{"title":"Title 1", "address" : "1 Learjet Way, Wichita, KS, United States"},
		{"title":"Title 2", "address" : "1 Croton Point Avenue, Croton On Hudson, NY, United States"}
	]}
	*/
	
	var stateFilter = new HelperListFilter({
		"container"	:".sitesListing", 
		"sorting"	:".sort", 
		"filter" 	:['#filter','#state']
	})
})

var http; 
var xmlHttp;

 function createRequestObject()

 {

	var request_o;

	var browser = navigator.appName; 

	if(browser == "Microsoft Internet Explorer")

	{		

  request_o=new ActiveXObject("Msxml2.XMLHTTP");

	}

	else

	{		

		request_o = new XMLHttpRequest();

	}

	return request_o; 

  }
  

function select_city(test)
{
	http = createRequestObject();	
	id = document.search_form.state.value;	 
	http.open('get', './includes/get_city.php?state='+id+'&city_id='+test+'&field=city_code');	
	http.onreadystatechange = handle_city; 	
	http.send(null);
}

function handle_city()
{	
	if(http.readyState == 4)
	{ 
		var response = http.responseText;	
		document.getElementById('city').innerHTML = response;

	}
} 
</script>





	
</head>
<body onLoad="select_city('<?php echo $city_code; ?>');">

<div class="body">
<center>

<!--- TOP NAV --->
<?php include('includes/top_nav-header.php'); ?>
<!--- TOP NAV --->





<!--- MAIN NAV --->
<?php include('includes/main_nav.php'); ?>
<!--- /MAIN NAV --->

</td>
</tr>
</table>

</td>
</tr>
</table>

<div style="height:3px;"></div>


<!--- UPPER BODY --->

<table width="1000" cellpadding="0" cellspacing="0" border="0">
<tr>
<td width="214" align="left" valign="top" background="images/bg_left_col.jpg">


<div style="margin-top:8px; margin-left:10px;">
<?php include('includes/left_nav_online_banking.php'); ?>
</div>



<div style="margin-left:12px; margin-top:10px;">

<div style=" margin-top:10px; line-height:1.75;">

<span class="head-light-green">ATM Locator</span>

<div style="height:4px;"></div>
<span class="light-green"></span><br>




</div>


</div>
</td>
<!--- LEFT COL --->




<!--- BODY --->
<td align="left" valign="top">
<div style="padding-left:26px; margin-top:47px; margin-left:10px;">

<span class="head-dark-green">Locate an ATM Within Our Network</span>

<div style="height:20px;"></div>


<?php

//User is searching
if($_GET['action'] == "Search")
{
?>


   	<form action="" method="get" name="search_form">
    <span class="label">Zip:</span><input type="text" name="zip_code" value="<?php echo $zip_code; ?>" id="zip_code" /><br/>
    <span class="label">-or-</span><br/>
    <span class="label">State:</span>
    <select name="state" id="state" onChange="select_city();"/>
    <?php
	//Pull state info from the db for output in the select field
	$sql_states = "SELECT DISTINCT state FROM `tbl_zip_codes` ORDER BY `state` ASC ";
	$result_states = mysql_query($sql_states);
	if($result_states)
	{
		while($rs_states = mysql_fetch_assoc($result_states))
		{
		echo "<option value=\"" . $rs_states['state'] . "\"";
		if ($state == trim($rs_states['state']))
			echo "selected";
		echo ">" . $rs_states['state'] . "</option>\r\n";
		}
	}
	?>
    </select>
    <br/>
    <span class="label">City:</span>
    <span id="city">
	  <?
      echo "<select name=\"city_code\" id=\"city_code\"disabled=\"disabled\">";
      echo "<option value=\"\">" . $_GET['city_code'] . "</option>";
      echo "</select>";
      ?>
    </span>
    <input type="hidden" name="lat" value="<?php echo $_GET['lat']; ?>" id="lat" />
    <input type="hidden" name="lon" value="<?php echo $_GET['lon']; ?>" id="lon" />
    <input type="submit" value="Search" name="action" />
    </form>

        <div id="gMap"></div>
         </div>
        <div class="content">
            <ul class="sitesListing">
<?php
	$no_per_page = 50;
	if(count($locations) > 0)
	{
		$x=1;

		foreach($locations as $key =>$value)
		{
			if(trim($locations[$key]['address']) <> "" && $x <= $no_per_page)
			{
				if($x==1)//////////////////////////// MAKE THIS A TABLE TO ALIGN EVERYTHING /////////////////////////////////////////////
				{
					echo "<input type=\"hidden\" value=\"" . strtoupper($locations[$key]['address']) . "\" id=\"map_center\" />";
				}
				$address = explode(' ',$locations[$key]['address']);
				foreach($address as $key2=>$value2)
				{
					if(strpos($value2, ',') !== false)
					{
						$address[$key2] = "<br/>" . $value2;
					}
				}
				$address = implode($address, ' ');
				echo "<div class=\"itemNumber\">" .  $key . ".</div><div class=\"listing_box\"><div class=\"name_listing\">" . strtoupper($locations[$key]['name']) . "</div>";
				echo "<div class=\"address_listing\">" . $address . "";
				echo "</div></div>";	
				
				echo "<div class=\"data\"><span class=\"getMap\">" .  $key . " " . strtoupper($locations[$key]['name']);
				echo "<div class=\"listaddress\">" . strtoupper($locations[$key]['address']) . "</div>";
				echo "<div class=\"content\"><div class=\"itemNumber\">" .  $key . "</div>" . strtoupper($locations[$key]['name']) . "<br/>" . strtoupper($address) . "<br/><a href=\"https://maps.google.com/maps?daddr=" . urlencode($locations[$key]['address']) . "&t=h\" target=\"_blank\">Get Directions</a></div>";
				echo "</span></div>";	
	
				$x++;
			}
		}

	}
	else
	{
		echo "Nothing found";
	}
}
else
{
	?>
   	<form action="" method="get" name="search_form">
    <span class="label">Zip:</span><input type="text" name="zip_code" value="<?php echo $zip_code; ?>" id="zip_code" /><br/>
    <span class="label">-or-</span><br/>
    <span class="label">State:</span>
    <select name="state" id="state" onChange="select_city();"/>
    <?php
	$sql_states = "SELECT DISTINCT state FROM `tbl_zip_codes` ORDER BY `state` ASC ";
	$result_states = mysql_query($sql_states);
	if($result_states)
	{
		while($rs_states = mysql_fetch_assoc($result_states))
		{
		echo "<option value=\"" . $rs_states['state'] . "\"";
		if ($state == trim($rs_states['state']))
			echo "selected";
		echo ">" . $rs_states['state'] . "</option>\r\n";
		}
	}
	?>
    </select>
    <br/>
    <span class="label">City:</span>	<span id="city">
	  <?
      echo "<select name=\"city\" disabled=\"disabled\">";
      echo "<option value=\"\">&nbsp;</option>";
      echo "</select>";
      ?>
      </span>
    <input type="hidden" name="lat" value="<?php echo $_GET['lat']; ?>" id="lat" />
    <input type="hidden" name="lon" value="<?php echo $_GET['lon']; ?>" id="lon" />
    <input type="submit" value="Search" name="action" />
    </form>

    <?php
}
?>

</div>
</td>
</tr>
</table>






<!--- UPPER BODY --->




<div style="height:20px;"></div>


</center>
</div>

<!--- FOOTER --->

<?php include('includes/footer.php'); ?>

<!--- FOOTER --->
</body>
</html>
