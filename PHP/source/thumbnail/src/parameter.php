<?php
require_once( "config.php" );

function parse_parameter( $parameters )
{
	global $APACHE_REWRITE_RULE;
	global $DEFAULT_QUALITY_VALUE;
	global $DEFAULT_TYPE_VALUE;
	global $DEFAULT_COLOR_VALUE;

	//print_r( $parameters );

	$result = Array();

	if( !(($parameters["width"]>=0)&&($parameters["height"]>=0)) )
	{
		$request = $parameters[ "url" ];
	
		$pos = strpos( $request, $APACHE_REWRITE_RULE ) + strlen( $APACHE_REWRITE_RULE );
		$request = substr( $request, $pos );

		$parameters = strtok( $request, "/" );
		$url = strtok( " " );

		$token_list = array();
		$token = strtok( $parameters, "_" );
		while( $token )
		{
			array_push( $token_list, $token );
			$token = strtok("_");
		}

		$token_count = count( $token_list );
		$type = $DEFAULT_TYPE_VALUE;
		$quality = $DEFAULT_QUALITY_VALUE;
		if( $token_count >= 2 )
		{
			$width = $token_list[0];
			$height = $token_list[1];
			if( $token_count > 2 )	$type = $token_list[2];
			if( $token_count > 3 )	$quality = $token_list[3];
		}
		//echo( "    width: " . $width . ", height: " . $height . ", type: " . $type . ", quality: " . $quality . "\n");

		$parameters = Array();

		$parameters[ "url" ] = $url;
		$parameters[ "width" ] = $width;
		$parameters[ "height" ] = $height;
		// Optional Parameter
		$parameters[ "type" ] = $type;
		$parameters[ "quality" ] = $quality;
	}

	$tempPath = substr( $_SERVER['REQUEST_URI'], 1 );

	$tempPath = substr( $tempPath, strpos($tempPath,'/')+1 );
	$tempPath = substr( $tempPath, strpos($tempPath,'/')+1 );

	// NOTE: hoppin Rule
	//if( $parameters["service"] != "hoppin" )
	//$pattern = '/^\/thumbnails\/([a-zA-Z_]+)\/([0-9]+)_([0-9]+)(?:_(([0-9a-fA-F]|[0-9a-fA-F]{2}|[0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})?[0-9][0-9a-fA-F]))?(?:_([0-9]|[1-9][0-9]|100))?\/([\w\W]+)$/';
	//if( preg_match($pattern,$_SERVER['REQUEST_URI'], $matches) == 1 )
	{
		$tempPath = substr( $tempPath, strpos($tempPath,'/')+1 );
	}

	$parameters[ "url" ] = $tempPath;

	$result[ "url" ] = $parameters[ "url" ];
	$result[ "width" ] = $parameters[ "width" ];
	$result[ "height" ] = $parameters[ "height" ];

	// Optional Parameter
	if( $parameters["quality"] )
	{
		$result[ "quality" ] = $parameters[ "quality" ];
	}
	else
	{
		$result[ "quality" ] = $DEFAULT_QUALITY_VALUE;
	}

	$pos = ( strlen($parameters["type"])-strlen($DEFAULT_TYPE_VALUE) );
	if( $pos > 0 )
	{
		$result[ "color" ] = substr( $parameters["type"], 0, $pos );
		$result[ "type" ] = substr( $parameters["type"], $pos );
	}
	else if( $pos == 0 )
	{
		$result[ "type" ] = $parameters[ "type" ];
		$result[ "color" ] = $DEFAULT_COLOR_VALUE;
	}
	else
	{
		$result[ "type" ] = $DEFAULT_TYPE_VALUE;
		$result[ "color" ] = $DEFAULT_COLOR_VALUE;
	}

	$result[ "service" ] = $parameters[ "service" ];

	/*
	echo( " service: " . $result[ "service" ] . "<br/>" );
	echo( " url: " . $result[ "url" ] . "<br/>" );
	echo( " width: " . $result[ "width" ] . "<br/>" );
	echo( " height: " . $result[ "height" ] . "<br/>" );
	echo( " type: " . $result[ "type" ] . "<br/>" );
	echo( " quality: " . $result[ "quality" ] . "<br/>" );
	//*/

	return $result;
}

function is_valid_parameter( $parameters )
{
	global $SERVER_MODE;
	global $SERVER_LIVE;
	global $WIDTH_MIN;
	global $WIDTH_MAX;
	global $HEIGHT_MIN;
	global $HEIGHT_MAX;
	global $QUALITY_MIN;
	global $QUALITY_MAX;

	if( $SERVER_MODE == $SERVER_LIVE )
	{
		$parameters[ "width" ]   = min( max($parameters[ "width" ], $WIDTH_MIN), $WIDTH_MAX );
		$parameters[ "height" ]  = min( max($parameters[ "height" ], $HEIGHT_MIN), $HEIGHT_MAX );
		$parameters[ "quality" ] = min( max($parameters[ "quality" ], $QUALITY_MIN), $QUALITY_MAX );
	}
	else
	{
		if( $parameters[ "width" ] < $WIDTH_MIN || $parameters[ "width" ] > $WIDTH_MAX ||
		    $parameters[ "height" ] < $HEIGHT_MIN || $parameters[ "height" ] > $HEIGHT_MAX ||
		    $parameters[ "quality" ] < $QUALITY_MIN || $parameters[ "quality" ] > $QUALITY_MAX )
		{
			return FALSE;
		}
	}

	return TRUE;
}
?>