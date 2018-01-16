<?
$path = "phar://thumbnail.phar/thumbnail.php";

if( file_exists("thumbnail.phar") == false )
{
	$path = "thumbnail.php";
}
require_once( $path );

main( parse_parameter($_GET) );
?>