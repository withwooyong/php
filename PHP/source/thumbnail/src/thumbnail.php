<?
require_once( "config.php" );
require_once( "parameter.php" );
require_once( "directory.php" );

require_once( "Image.php" );
require_once( "ImageReader.php" );
require_once( "ImageResizer.php" );

function main( $parameters )
{
	global $BASE_PATH;

	//....................................................................
	// parameters validation

	if( is_valid_parameter( $parameters ) == FALSE )
	{
		http_response_code( 400 );
		return 0;
	}

	//....................................................................
	// local cached image file open

	$local_image_path = get_image_directory( $BASE_PATH, $parameters["service"], $parameters["url"], $parameters[ "width" ], $parameters[ "height" ], @$_GET["color"], $_GET[ "type" ], $_GET[ "quality" ] );
	if( $local_image_path == NULL )
	{
		http_response_code( 400 );
		return 0;
	}

	$file = @fopen( $local_image_path, "rb" );
	if( $file !== FALSE )	// cache file exist !!
	{
		list(,,,,,,,$size,,$last_modified_time, $ctime) = @stat($imagePath);
		if( $last_modified_time == 0 )	$last_modified_time = $ctime;

		//....................................................................
		// if_modify_since 

		if( @$_SERVER["HTTP_IF_MODIFIED_SINCE"] != NULL )
		{
			if( gmstrftime("%a, %d %b %Y %T %Z", $lastModified) == $_SERVER["HTTP_IF_MODIFIED_SINCE"] )
			{
				header("HTTP/1.1 304 Not Modified");
				header("Pragma: no-cache");
				header("Cache-Control: no-cache" );
				header("Content-Type: image/jpeg" );
				return 0;
			}
		}

		//....................................................................
		// local cached image send

		$expires = 60*60*24*7;

		header( "Content-Type: image/jpeg" );
		header( "Content-Length: " . $size );
		header("Pragma: no-cache");
		header("Cache-Control: no-cache" ); //max-age=".$expires);
		header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
		header("Last-Modified: " . gmdate('D, d M Y H:i:s',$last_modified_time) . ' GMT');

		fpassthru( $file );
		fclose( $file );

		return 0;
	}

	//....................................................................
	// original image file open

	$content_type = "image/jpeg";
	$last_modified_time = time();
	$thumbnail = null;
	{
		//....................................................................
		// thumbnail service config load ( service's original source address )

		$config = json_decode(file_get_contents("../conf/thumbnail.conf"), true);
		if( $config == NULL )
		{
			http_response_code( 500 );
		}

		$source_list = $config["service"][$parameters["service"]]["source"];
		if( !isset($source_list) || count($source_list) == 0 )
		{
			http_response_code( 500 );
		}

		foreach( $source_list as $source_path )
		{
			$source_path .=  $parameters["url"];

			$reader = new RImageReader( $source_path );
			if( $reader->GetData() )
			{
				$source = new RImage( $reader->GetData() );
				$last_modified_time = $reader->GetDate();

				//....................................................................
				// image resize

				$image_resizer = new RImageResizer( $source );
				$resized_image = $image_resizer->Resize( $parameters["width"], $parameters["height"], $parameters["type"], $parameters["color"] );
				$source->DestroyImage();

				if( !$resized_image )
				{
					http_response_code( 500 );
					return 0;
				}

				//....................................................................
				// make thumbnail(resized) image

				ob_start();
				if( $source->GetImageType() == 3 && $image_resizer->HasAlpha() == true )
				{
					imagePng( $resized_image, NULL, (int)((100-$parameters["quality"])/10) );
					$content_type = "image/png";
				}
				else
				{
					imageJpeg( $resized_image, NULL, $parameters["quality"] );
				}
				$thumbnail = ob_get_contents();
				$thumbnail_length = ob_get_length();
				ob_end_clean();

				imageDestroy( $resized_image );
				break;
			}
		}
	}

	if( $thumbnail == null )
	{
		http_response_code( 404 );
		return 0;
	}

	//....................................................................
	// thumbnail send

	$expires = 60*60*24*7;

	header( $_SERVER["SERVER_PROTOCOL"] . " 200 OK");
	header( "Content-Type: " . $content_type );
	header( "Content-Length: " . $thumbnail_length );
	header( "Pragma: no-cache");
	header( "Cache-Control: no-cache"); //max-age=".$expires);
	header( 'Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
	header( 'Last-Modified: ' . gmdate('D, d M Y H:i:s',$last_modified_time) . ' GMT');

	echo( $thumbnail );

	//....................................................................
	// thumbnail save

	$save_path = get_image_directory( $BASE_PATH, $parameters["service"], $parameters["url"], $parameters[ "width" ], $parameters[ "height" ], @$_GET["color"], $_GET[ "type" ], $_GET[ "quality" ] );

	if( make_directory($save_path) == true )
	{
		$file = @fopen( $save_path, "w");
		if( $file !== FALSE )
		{
			fwrite( $file, $thumbnail );
			fclose( $file );
			touch( $save_path, $last_modified_time );
		}
	}

	return 0;
}
?>