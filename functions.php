<?php
/*
A collection of commonly used functions I created and use on various websites
*/

//This function is used to compare two values and return a css code to highlight the element green if the value is greater, red if it is smaller.
function check_rate($value, $trigger)	
{
	if($value > $trigger)
	{
		 return("high");
	}
	else
	{
		 return("low");
	}	
}
//This function is used to compare two values and return a css code to highlight the element green if the value is smaller, red if it is greater.
function check_sub_rate($value, $trigger)	
{
	if($value < $trigger)
	{
		 return("high");
	}
	else
	{
		 return("low");
	}	
}
//This function is used to compare a value with two values and return a css code to highlight the element green if the value is beyond the first value, red if it is beyond the second value or no code if it is between the two values.  If value1 is greater than value2, the function considers higher than value1 to be preferable, and vice versa when value2 is greater than value1
function check_multi_rate($subject, $value1, $value2)	
{
	if($value1 > $value2)
	{
		if($subject > $value1)
		{
			return("high");
		}
		elseif($subject < $value2)
		{
			return("low");
		}
		else
		{
			return("average");
		}
	}
	elseif($value1 < $value2)
	{
		if($subject < $value1)
		{
			return("high");
		}
		elseif($subject > $value2)
		{
			return("low");
		}
		else
		{
			return("average");
		}
	}	
}
//Copies a remote file to a local file.  If overwrite is set to false, will only return the local file instead of updating it.
function read_file($local_url, $remote_url, $overwrite = true, $debug = '')
{
	if(!file_exists($local_url) || $overwrite === true)
	{
		$ch = curl_init($remote_url);
		$fp = fopen($local_url, "w");
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15") );
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt( $ch, CURLOPT_ENCODING, "UTF-8" );  
		curl_exec($ch);
		if($debug <> '')
		{
			$info = curl_getinfo($ch);
			foreach($debug as $key=>$value)
			{
				e($info[$value]);
			}
		}
		curl_close($ch);
		fclose($fp);
	}

	$fp = fopen($local_url, "r");
	return($fp);
}

//Debug function for var_dump that displays data in a more readable output if the user is logged in with user id 1(Me)
function vd($array, $depth = 0)
{
	if($_SESSION['user_id']==1)
	{
		echo "<p>";
		if(count($array) > 0)
		{
			foreach($array as $key => $value)
			{
				$depth_txt = '';
				for($i=0;$i<$depth;$i++)
				{
					$depth_txt .= '=>';
				}
				e($depth_txt . $key . ": " . $value);
				if(is_array($value))
					vd($value, $depth+1);
			}
		}
		else
		{
			e('Empty array: ' . var_dump($array));
		}
		echo "</p>";
	}
}

//Debug function that displays array data and halts execution
function vdd($array)
{
	vd($array);
	die();
}
//Debug function that outputs data in a readable manner if the user is logged in with user id 1(Me)
function e($output)
{
	if($_SESSION['user_id']==1)
	echo "<p>" . $output . "</p>";
}
//Debug function that outputs data in a readable manner if the user is logged in with user id 1(Me) and halts execution
function ed($output)
{
	if($_SESSION['user_id']==1)
	echo "<p>" . $output . "</p>";
	die();
}

//Function I created for searching through a file line by line to find a snippet of text
function move_the_stream($file_handle, $search_text)
{
	$pos1 = false;
	if(is_resource($file_handle))
	{
		while ((!feof($file_handle)) && ($pos1 === false)) 
		{
			$contents = fgets($file_handle);
			$pos1 = stripos($contents, $search_text);
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
	else
	{
		die('Not a file');	
	}
}

//A scraper function I use to move the file pointer ahead a specific number of lines
function read_ahead($file_handle, $count)
{
	for($i=1;$i<=$count;$i++)
	{
		if(!feof($file_handle))
			$data = fgets($file_handle);
	}
	return $data;

}	
//Combined version of the existing strip_tags and trim functions
function strip_data($input)
{
	if($input<>"")
		return(trim(strip_tags($input)));	
}
//For comparision of English and Latin American names this function returns an English character set version of a Latin American name
function convert_name($name)
{
	$latin_chars = array('Á' => "A", 'É' => "E", 'Í' => "I", 'Ó' => "O", 'Ú' => "U", 'á' => "a", 'é' => "e", 'í' => "i", 'ó' => "o", 'ú' => "u", 'Ñ' => "N", 'ñ' => "n", );
	return strtr($name, $latin_chars);
}
?>