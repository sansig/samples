<?php
/*
This is a collection of functions that are used to run the ATM website project
*/

/*
	Load the main search site and find ATM data
*/ 
function search_atm($zip_code)
{
	//URL for atm search page - site does not exist, IP changed
	$url = "http://64.132.199.34/Results.aspx?ZipCode=" . $zip_code . "&ButtonID=1";
	$ch = curl_init($url);
	$fp = fopen("temp.dat", "w");
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch,CURLOPT_TIMEOUT, 15);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, Array("User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15") );
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);
	$handle = fopen("temp.dat", "r");
	
	//Find the start of ATM location data
	$contents = file_search($handle, "background-color:#CCCCCC;border-color:#004040;border-width:0px;border-style:Solid;");
	
	//Pass the file and the first file contents containing atm location info
	read_bank_info($handle, $zip_code, $contents);
	fclose($handle);

}

/*
	Load the moneypass search site and find ATM data
*/ 
function search_atm_2($zip_code)
{
	//Need to keep track of how many locations the first ATM search function has found so we don't overwrite results
	$x = count($locations);
	
	//URL for moneypass search page
	$url = "http://locator.moneypass.com/searchresults.aspx";
	$ch = curl_init($url);
	$fp = fopen("temp.xml", "w");
	$data = "distance=10&typeofloc=&street=&city=&state=&zipcode=" . $zip_code . "&country=&institutionName=&latitude=&longitude=";
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch,CURLOPT_TIMEOUT, 15);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, Array("User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15") );
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);
	
	$handle = fopen("temp.xml", "r");
	$pos1 = true;
	while ((!feof($handle)) && ($contents !== false))
	{
		//Skip ahead in the file to the start of relevant data
		$contents = file_search($handle, "institutionName");
		//Pass the file and the first file contents containing atm location info
		read_bank_info_2($handle, $contents, $zip_code);
	}
	$pos1 = false;
	fclose($handle);
}

/*
	Load the nyce search site and find ATM data
*/ 
function search_atm_nyce($zip_code)
{
	//Need to keep track of how many locations the first ATM search function has found so we don't overwrite results
	$x = count($locations);
	//URL for nyce search page
	$url = "http://www.nyce.net/atm-locator/NYCELocator.aspx";
	$ch = curl_init($url);
	$fp = fopen("temp.xml", "w");
	$data = "__EVENTVALIDATION=/wEWQQL/vqKyBQL+s4fwDALR/cSXCwKh3D0Codw5AqHchQECodxlAqPcIQKj3CkCo9xdAqLcEQKk3D0Cp9whAqfcUQKW3EECmdwdApncPQKZ3DUCmdwhApvcWQKb3IEBAprcIQKd3BECndwdAp3cIQKd3EECndw1Ap3cWQKd3CkCndxdApzcEQKc3FUCnNxNApzcRQKc3DECnNyBAQKc3BkCnNwdAp3cbQKf3E0Cn9w5Ap/cZQKO3CECjtxlApDcQQKT3BkCk9wdApLcNQKS3I0BApXcXQKU3F0ClNxBApTcIQKX3CECotwZApfcVQKX3EECl9yBAQKts/fKBwKGrKe3CAK0jMjtBwKlsIm/CgLSvMfmBAKnsOnlBwL3uvOGAlj0E4P+LFX8nXlYtrDL8KahX2Th3Hda9ymZYMSAupEp&ctl00$ContentPlaceHolder1$btnSearch=Search&ctl00$ContentPlaceHolder1$txtZip=" . $zip_code;
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch,CURLOPT_TIMEOUT, 15);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, Array("User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15") );
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);
	
	$handle = fopen("temp.xml", "r");
	$pos1 = true;
	while ((!feof($handle)) && ($contents !== false))
	{
		//Skip ahead in the file to the start of relevant data
		$contents = file_search($handle, "institutionName");
		//Pass the file and the first file contents containing atm location info
		read_bank_info_nyce($handle, $contents, $zip_code);
	}
	$pos1 = false;
	fclose($handle);
}

/*
	This function was originally used to handle all search sites but at a later date the format was changed on nyce and moneypass the code was broken up into different functions
*/
function read_bank_info($file_handle, $zip_code, $contents)
{
	global $locations;
	$pos1 = false;
	$x = 1;

	while ((!feof($file_handle)) && ($pos1 === false)) 
	{

		$pos1 = strpos($contents, "</table>");
		$pattern = "|([\S\s]*?)</td>|";
		$count = preg_match_all($pattern, $contents, $matches);
		if($count == 6 && $matches[0][0] <> "")
		{
			$locations[$x]['name'] = strip_tags($matches[0][0]);
			$locations[$x]['address'] = strip_tags($matches[0][1]) . " " . strip_tags($matches[0][2]) . ", " . strip_tags($matches[0][3]) . " " . strip_tags($matches[0][4]);
			$locations[$x]['search_string'] = strip_tags($matches[0][1]) . " " . strip_tags($matches[0][2]) . ", " . strip_tags($matches[0][3]) . " " . strip_tags($matches[0][4]);
			$sql = "INSERT INTO tbl_locations (location_id, location_name, location_address, search_zip) VALUES ('', '" . $locations[$x]['name'] . "','" . $locations[$x]['address'] . "','" . $zip_code . "')";
			if($locations[$x]['name'] != "")
				mysql_query($sql);
			$x++;
			
		}
        $contents = trim(fgets($file_handle));
	}
}

function read_bank_info_2($file_handle, $name, $zip_code)
{
	if($name <> "")
	{
		$pos1 = false;
		$search = array('<td>', '</td>');
		$replace = array('','');
		global $locations;
		$x = count($locations);
		$locations[($x+1)]['name'] = " " . strip_tags($name);
		$contents = fgets($file_handle);
		$contents = fgets($file_handle);
		$contents = fgets($file_handle);
		$contents = trim(fgets($file_handle));
		$locations[$x+1]['address'] = strip_tags(trim($contents, "\x9"));
		$contents = fgets($file_handle);
		$contents = strip_tags(trim($contents, "\x9"));
		$arr1 = str_split(strip_tags($contents));
		$size = count($arr1);
		$arr1[$size - 1] = "";
		$arr1[$size - 2] = "";
		$locations[$x+1]['address'] .= " " . implode("",$arr1);
			$sql = "INSERT INTO tbl_locations (location_id, location_name, location_address, search_zip) VALUES ('', '" . trim($locations[($x+1)]['name']) . "','" . $locations[$x+1]['address'] . "','" . $zip_code . "')";
			if($locations[($x+1)]['name'] != "")
				mysql_query($sql);
		$x++;
	}
}

function read_bank_info_nyce($file_handle, $name, $zip_code)
{
	if($name <> "")
	{
		$pos1 = false;
		$search = array('<td>', '</td>');
		$replace = array('','');
		global $locations;
		$x = count($locations);
		$locations[($x+1)]['name'] = " " . strip_tags($name);
		$contents = fgets($file_handle);
		$contents = fgets($file_handle);
		$contents = fgets($file_handle);
		$contents = trim(fgets($file_handle));
		$locations[$x+1]['address'] = strip_tags(trim($contents, "\x9"));
		$contents = fgets($file_handle);
		$contents = strip_tags(trim($contents, "\x9"));
		$arr1 = str_split(strip_tags($contents));
		$size = count($arr1);
		$arr1[$size - 1] = "";
		$arr1[$size - 2] = "";
		$locations[$x+1]['address'] .= " " . implode("",$arr1);
			$sql = "INSERT INTO tbl_locations (location_id, location_name, location_address, search_zip) VALUES ('', '" . trim($locations[($x+1)]['name']) . "','" . $locations[$x+1]['address'] . "','" . $zip_code . "')";
			if($locations[($x+1)]['name'] != "")
				mysql_query($sql);
		$x++;
	}
}



function file_search($file_handle, $search_text)
{

	$pos1 = false;
    while ((!feof($file_handle)) && ($pos1 === false)) 
	{
        $contents = fgets($file_handle);
		$pos1 = strpos($contents, $search_text);
    }
	if(feof($file_handle))
	{
		$contents = false;
	}
	else
	{
		return $contents;
	}
}
?>