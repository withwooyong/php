<?

function http_get_contents( $url, $repeat = 3 )  
{
        $html = @file_get_contents( $url );

        if( @$http_response_header != null )
        {
                $response = explode( " ", trim($http_response_header[0]) );
                if( $response[1] == 301 && $repeat > 0  )
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

                        $html = http_get_contents( $location, $repeat-1 );
                }
        }

        return $html;
}

class RImage
{
    var $m_Filename = "";
    var $m_ImageType = -1; // 1: GIF 2: JPG 3:PNG
    var $m_Image = NULL;

    function RImage( $ImageData )
    {
    	$this->SetImageData($ImageData);
    }

    function CreateImage( $Width, $Height, $Depth = 24 )
    {
        if( $Depth == 24 )
        {
            $this->m_Image = ImageCreateTrueColor( $Width, $Height );
        }
        else
        {
            $this->m_Image = ImageCreate( $Width, $Height );
        }
    }

    function DestroyImage()
    {
        if( $this->m_Image )
        {
            ImageDestroy( $this->m_Image );
            $m_ImageType = -1;
        }
    }

    function LoadImage( $Filename, $ImageType = -1 )
    {
        $this->m_Filename = $Filename;

        $data = http_get_contents( $Filename );
        if( $data == FALSE )
        {
                return FALSE;
        }

        return $this->SetImageData( $data );
    }

    function SetImageData( $data )
    {
        if( !$data )
        {
            return FALSE;
        }

        if( bin2hex($data[0]) == 'ff' && bin2hex($data[1]) == 'd8' ) // jpeg
        {
                $this->m_ImageType = 2;
        }
        else if( bin2hex($data[0]) == '89' && $data[1] == 'P' && $data[2] == 'N' && $data[3] == 'G' ) // png
        {
                $this->m_ImageType = 3;
        }
        else
        {
                $this->m_ImageType = 2;
        }

        $this->m_Image = @ImageCreateFromString( $data );
        if( $this->m_Image )
        {
                return TRUE;
        }

        return FALSE;
    }

    function SaveImage( $Filename, $Quality = 100 )
    {
        if( !$this->m_Image ) return FALSE;

        if( $this->m_ImageType == 1 )
        {
            ImageGIF( $this->m_Image, "$Filename" );
        }
        else if( $this->m_ImageType == 2 )
        {
            ImageJPEG( $this->m_Image, "$Filename", $Quality );
        }
        else if( $this->m_ImageType == 3 )
        {
            ImagePNG( $this->m_Image, "$Filename" );
        }
/*
	else if( $this->m_ImageType == 4 )
	{
	    ImageWBMP( $this->m_Image, "$Filename" );
	}
*/
    }

    function GetWidth()
    {
        if( !$this->m_Image ) return -1;
        return ImageSX( $this->m_Image );
    }

    function GetHeight()
    {
        if( !$this->m_Image ) return -1;
        return ImageSY( $this->m_Image );
    }

    function GetImageType()
    {
        return $this->m_ImageType;
    }

    function GetImage()
    {
        return $this->m_Image;
    }

    function SetImage( $Image, $Type )
    {
        $this->m_Image = $Image;
        $this->m_ImageType = $Type;
    }

    function Resize( $Width, $Height )
    {
        $Image = NULL;
        if( imageistruecolor($this->m_Image) )
        {
            $Image = ImageCreateTrueColor( $Width, $Height );
        }
        else
        {
            $Image = ImageCreate( $Width, $Height );
        }
        ImageCopyResampled( $Image, $this->m_Image, 0, 0, 0, 0, $Width,
            $Height, $this->GetWidth(), $this->GetHeight() );

        ImageDestroy( $this->m_Image );
        $this->m_Image = $Image;
    }

    function Resize2( $Width, $Height, $sx, $sy, $sw, $sh )
    {
        $Image = NULL;
        if( imageistruecolor($this->m_Image) )
        {
            $Image = ImageCreateTrueColor( $Width, $Height );
        }
        else
        {
            $Image = ImageCreate( $Width, $Height );
        }
        ImageCopyResampled( $Image, $this->m_Image, 0, 0, $sx, $sy, $Width,
            $Height, $sw, $sh );

        ImageDestroy( $this->m_Image );
        $this->m_Image = $Image;
    }

    function Draw()
    {
    }

}

//------------------------------------------------------------------------------
?>