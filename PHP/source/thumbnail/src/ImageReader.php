<?
/*

$ImageReader = new RImageReader( URL );
$ImageDate = $ImageReader->GetDate();

$Image = new RImage( $ImageReader->GetData() );

class RImageReader
{
	var $m_FileHandle;
	var $m_FileModifyDate = -1;

	RImageRead( $url, $retry = 3 )
	{
		file://
		$m_FileHandle;
		$m_FileModifyDate;
	}
	
	GetImage();
	GetImageDate();
	{
		//local;
	}
}
*/
class RImageReader
{
	var $m_FileData;
	var $m_FileModifyDate = -1;

	function RImageReader( $url )
	{
		$this->m_FileData = null;
		$this->m_FileModifyDate = -1;

		$data = $this->GetFile( $url );
		if( $data !== false )
		{
			$this->m_FileData = $data;
		}
	}

	function GetFile( $path, $retry = 3 )
	{
		$file = @file_get_contents( $path );

		if( @$http_response_header != null )	// remote file
		{
			$response = explode( " ", trim($http_response_header[0]) );
			if( $response[1] == 301 && $retry > 0  )
			{
				foreach( $http_response_header as $line )
				{
					$temp = explode( ":", trim($line) );
					if( strcasecmp($temp[0], "Location") == 0 )
					{
						$location = trim( substr(strstr($line,':'), 1) );
						break;
					}
				}

				$file = $this->GetFile( $location, $retry-1 );
			}
			else if( $response[1] == 200 )
			{
				foreach( $http_response_header as $line )
				{
					$temp = explode( ":", trim($line) );
					if( strcasecmp($temp[0], "Last-Modified") == 0 )
					{
						$last_modified = trim( substr(strstr($line,':'), 1) );
						$this->m_FileModifyDate = strtotime($last_modified);
						break;
					}
				}
			}
		}
		else if( $file !== false )	// local file
		{
			$modified_time = filemtime($path);
			if( $modified_time == 0 )	$modified_time = filectime($path);

			$this->m_FileModifyDate = $modified_time;
		}

		return $file;
	}

	function GetData()
	{
		return $this->m_FileData;
	}

	function GetDate()
	{
		return $this->m_FileModifyDate;
	}
}
?>