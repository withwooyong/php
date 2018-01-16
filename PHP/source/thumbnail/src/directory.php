<?php
require_once( "config.php" );

function get_image_directory( $base_path, $service, $path, $width, $height, $color, $type, $quality )
{
	$image_directory = NULL;

	$parameter_path = $base_path . "/" . $service . "/" . $width . "_" . $height ;
	if( $type )
	{
		$parameter_path .= "_" . $color . $type ;
		if( $quality )
		{
			$parameter_path .= "_" . $quality ;
		}
	}
	//echo("parameter_path: " . $parameter_path ."\n");

	$parseurl = parse_url( $path );
	if( isset($parseurl["scheme"]) && $parseurl["scheme"] == "http" )
	{
		$parameter_path = $parameter_path . "/" . $parseurl["scheme"] . "/" . $parseurl[ "host" ] . "/" . $parseurl[ "path" ];
	}

	$image_directory = $parameter_path . "/" . $path;

	return $image_directory;
}

function make_directory( $path )
{
	$dirname = null;
	$permission = 0770;

	$info = pathinfo( $path );
	if( !isset($info["dirname"]) )
	{
		return false;
	}
	$dirname = $info["dirname"];

	@mkdir( $dirname, $permission, true);

	return true;
}
/*
function unLinkFile( $filepath )
{
	//echo( "  unlink filepath: " . $filepath . "\n");
	@unlink( $filepath );
	removeDirectory( $filepath );
}

function unLinkFiles( $filePathList )
{
	while( ($filepath = array_pop( $filePathList )) != NULL )
	{
		unLinkFile( $filepath );
	}
}

function removeDirectory( $path )
{
	global $BASE_PATH;

	$info = pathinfo( $path );
	$dirname = $info["dirname"];

	$token_list = Array();
	$token = strtok( $dirname, "/" );
	while( $token )
	{
		array_push( $token_list, $token );
		$token = strtok("/");
	}
	$count = count($token_list);
	while( $count )
	{
		$dirname = "";
		for( $i = 0; $i < $count; $i++ )
		{
			$dirname = $dirname . "/" . $token_list[$i];
		}

		if( strcmp( $dirname, $BASE_PATH ) == 0 )	return;

		if( @rmdir( $dirname ) == FALSE )        break;
		//echo("    remove_dir: " . $dirname . "\n");

		$count--;
	}
}

function fileSearch( $path, &$List )
{
	if ($dir = opendir($path)) 
	{
		while ($file = readdir($dir))
		{
			if ($file != '.' && $file != '..') 
			{
				$filepath = $path . "/" . $file;
				if( is_dir($filepath) )
				{
					fileSearch( $filepath, $List );
				}
				else
				{
					array_push( $List, $filepath );
					//echo "    $filepath\n";
				}
			}
		}
	}
}

function fileSearch2( $path )
{
	$files = Array();

	if($dir = @opendir ($path))
	{
		while($file = readdir ($dir))
		{
			if($file[0] != '.')
			{
				$filepath = $path . "/" . $file;
				if( is_dir($filepath) )
				{
					$in_files = fileSearch2 ($path . "/" . $file);
					if(is_array ($in_files)) $files = array_merge ($files , $in_files);
				}
				else
				{
					array_push ($files , $path . "/" . $file);
				}
			}
		}

		closedir ($dir);
	}
	return $files;
}*/
?>