<?php

require_once("../../../wp-config.php");

$postdata['zip'] = $_POST['zip'];

$rawdata = post_form('http://csls.diamondcomics.com/default.asp', $postdata);
$data = parseraw( $rawdata );
if (!is_array($data)) {
	switch ($data) {
		case 1:
			$message = "The code you entered does not appear to be valid.";
			break;
		case 2:
			$message = "We're sorry, but we found no stores in your vicinity.";
			break;
		case 3:
			$message = "We're sorry, but we've encountered an internal error. Please retry.";
	}
	echo "<span style='color: red'>$message</span>";

}
else {
	echo "<br /><strong>We found " . sizeof($data) . " results:</strong><br />\n";
	echo "<form action='http://csls.diamondcomics.com/default.asp' method='post' target='CSLS' id='CSFDetailsForm' >\n";
	echo "<input type='hidden' name='PassedIDNo' value='' id='CSFPassedIDNo' />\n";
	echo "<input type='hidden' name='source' value='dcd' />\n";
	echo "<input type='hidden' name='x' value='10' />\n";
	echo "<input type='hidden' name='y' value='8' />\n";
	echo "<input type='hidden' name='SearchPostalCode' value='". htmlspecialchars($_POST['zip'], ENT_QUOTES) . "' />\n";
	echo "<ul>";
	foreach ($data as $shop) {
		/*$result[$i]['id'] = $match[1][$i];
				$result[$i]['name'] = $match[2][$i];
				$result[$i]['addr1'] = $match[3][$i];
				$result[$i]['addr2'] = $match[4][$i];
				$result[$i]['phone'] = $match[5][$i];
				$result[$i]['have_details'] = strpos($match[6][$i],'type="image"');*/
		echo "<li><strong>";
		// FIXME: can we do this? Or are they blocking our requests?
		if ( false && $shop['have_details'] ) {
			echo "<a href='javascript:void(0)' onclick='CSFopenDetails({$shop['id']})'>{$shop['name']}</a>";
		}
		else {
			echo $shop['name'];
		}
		
		echo "</strong><br />\n";
		echo "<span style='font-size: 80%;line-height: 100%'>{$shop['addr1']}<br />{$shop['addr2']}<br />{$shop['phone']}</span>";
		echo "</li>";
	}
	echo "</ul>";
	echo "</form>";
}






function post_form( $url, $postdata, $timeout = 120 ) {
	$parts = parse_url( $url );
	if ( !$parts ) return false;
	
	$response = false;
	if (function_exists('curl_init')) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		/* Currently redirection support is not absolutely necessary, so it's OK
		if this line fails due to safemode restrictions */
		
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		
		curl_setopt($ch, CURLOPT_POST, true);
		
		$poststring = "";
		foreach ($postdata as $key => $value) {
			$poststring[] = "$key=$value";
		}
		$poststring = implode( '&', $poststring );
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, $poststring);
		
		$response = curl_exec($ch);
		curl_close($ch);
	} else if ( file_exists( ABSPATH . 'wp-includes/class-snoopy.php' ) ) {
		require_once( ABSPATH . 'wp-includes/class-snoopy.php' );
		$snoopy = new Snoopy();
		$snoopy->submit( $url, $postdata );

		if( $snoopy->status == '200' ) {
			$response = $snoopy->results;
		}
	} else if ( ini_get( 'allow_url_fopen' ) && ( ( $rh = fopen( $url, 'rb' ) ) !== FALSE ) ) { 
		echo "fopen() mode: TO BE DONE";return;
		$response = '';
		while ( !feof( $rh ) ) {
				$response .= fread( $rh, 1024 );
		}
		fclose( $rh );
	} else {
		return false;
	}
	return $response;	
}

function parseraw( $string ) {
	// not valid code
	if ( strpos( $string, 'does not appear to be valid' ) ) return 1;
	
	// no results
	if ( strpos( $string, 'We\'re sorry, but we found no' ) ) return 2;
	
	$string = str_replace( "\n", '', $string);
	$string = str_replace( "\r", '', $string);
	$string = str_replace( "\t", ' ', $string);
	
	$expression = '/<form method="POST" action="profile.asp" id="profile_form">.*?PassedIDNo" value="(.*?)".*?<strong>(.*?)<\/strong>.*?size=1>(.*?)<\/font>.*?size=1>(.*?)<\/font>.*?size=1>(.*?)<\/font>(.*?)<\/form>/';
	if (preg_match_all($expression, $string, $match) ) {
/*			echo "MATCHO\n";
		var_dump($match);
*/		
		
		$result = array();
		$num_results = sizeof($match[0]);
		for ($i = 0;$i<$num_results;$i++) {
			$result[$i]['id'] = $match[1][$i];
			$result[$i]['name'] = $match[2][$i];
			$result[$i]['addr1'] = $match[3][$i];
			$result[$i]['addr2'] = $match[4][$i];
			$result[$i]['phone'] = $match[5][$i];
			$result[$i]['have_details'] = strpos($match[6][$i],'type="image"');
		}
		return $result;
	}
	else {
		// Has the format changed?
		//echo "Doesn't match";
		//echo $string;
		return 3;
	}
}


?>
