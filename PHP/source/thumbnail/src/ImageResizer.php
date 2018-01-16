<?
/*
	use case :

	$Image = new RImage();
	$Image->LoadImage( ... );

	$ImageResizer = new RImageResizer( $Image );
	$thumbnail = $ImageResizer->Resize( ... ); // return value is image object( is not class RImage )

	...

	$Image->DestroyImage();
*/
class RImageResizer
{
    var $m_Image = NULL;
    var $m_hasAlpha = false;

    function RImageResizer( $Image )
    {
    	$this->m_Image = $Image;
    }

    function Resize( $width, $height, $type, $bgcolor )
    {
		$COLOR_WHITE	= "F";
		$COLOR_BLACK 	= "0";
		$COLOR_ALPHA    = "A";
		$COLOR_BLUR     = "B";

		$TYPE_MASK		= 0xF0;
		$POSITION_MASK	= 0x0F;
		$TYPE_RESIZE	= 0x10;
		$TYPE_CROP		= 0x20;
		$TYPE_EXTENSION		= 0x30;
		$POSITION_CENTER	= 0x00;
		$POSITION_TOP		= 0x01;
		$POSITION_BOTTOM	= 0x02;
		$POSITION_LEFT		= 0x04;
		$POSITION_RIGHT		= 0x08;

		//echo( "width: " . $width . ", height: " . $height . ", type: " . $type . ", bgcolor: " . $bgcolor . "<br/>");

		$oLeft = 0;
		$oTop = 0;
		$oWidth = $this->m_Image->GetWidth();
		$oHeight = $this->m_Image->GetHeight();

		$dLeft = 0;
		$dTop = 0;

		$dWidth = $width;
		$dHeight = $height;

		if( $width == 0 && $height == 0 )
		{
			$dWidth = $oWidth;
			$width =  $oWidth;

			$dHeight = $oHeight;
			$height = $oHeight;
		}
		else if( $width == 0 )
		{
			$width = intval( ( $oWidth*$dHeight ) / $oHeight );
			$dWidth = $width;
		}
		else if( $height == 0 )
		{
			$height = intval( ( $dWidth*$oHeight ) / $oWidth );
			$dHeight = $height;
		}

		$cropType = ( $TYPE_MASK&hexdec("0x".$type) ); //$cropType = GetCropType( $type );
		switch( $cropType )
		{
		case $TYPE_RESIZE:
			{
				// Do nothing
			}
			break;

		case $TYPE_EXTENSION:
			{
				$position = ( $POSITION_MASK&hexdec("0x".$type) );
				if( $dWidth*$oHeight < $oWidth*$dHeight )
				{
					$tHeight = intval( ( $dWidth*$oHeight ) / $oWidth );
					if( ($position&$POSITION_TOP) == $POSITION_TOP )
					{
						$dTop = 0;
					}
					else if( ($position&$POSITION_BOTTOM) == $POSITION_BOTTOM )
					{
						$dTop = intval( ( $dHeight-$tHeight ) );
					}
					else if( ($position&$POSITION_CENTER) == $POSITION_CENTER )
					{
						$dTop = intval( ( $dHeight-$tHeight ) / 2 );
					}

					$dHeight = $tHeight;
				}
				else
				{
					$tWidth = intval( ( $oWidth*$dHeight ) / $oHeight );
					if( ($position&$POSITION_LEFT) == $POSITION_LEFT )
					{
						$dLeft = 0;
					}
					else if( ($position&$POSITION_RIGHT) == $POSITION_RIGHT )
					{
						$dLeft = intval( ( $dWidth-$tWidth ) );
					}
					else if( ($position&$POSITION_CENTER) == $POSITION_CENTER )
					{
						$dLeft = intval( ( $dWidth-$tWidth ) / 2 );
					}
					$dWidth = $tWidth;
				}
			}
			break;

		default: // case $TYPE_CROP:
			{
				$position = ( $POSITION_MASK&hexdec("0x".$type) );
				if( $dWidth*$oHeight > $oWidth*$dHeight )
				{
					$tHeight = intval( ( $oWidth*$dHeight ) / $dWidth );

					if( ($position&$POSITION_TOP) == $POSITION_TOP )
					{
						$oTop = 0;
					}
					else if( ($position&$POSITION_BOTTOM) == $POSITION_BOTTOM )
					{
						$oTop = intval( ( $oHeight-$tHeight ) );
					}
					else if( ($position&$POSITION_CENTER) == $POSITION_CENTER )
					{
						$oTop = intval( ( $oHeight-$tHeight ) / 2 );
					}

					$oHeight = $tHeight;
				}
				else
				{
					$tWidth = intval( ( $oHeight*$dWidth ) / $dHeight );

					if( ($position&$POSITION_LEFT) == $POSITION_LEFT )
					{
						$oLeft = 0;
					}
					else if( ($position&$POSITION_RIGHT) == $POSITION_RIGHT )
					{
						$oLeft = intval( ( $oWidth-$tWidth )  );
					}
					else if( ($position&$POSITION_CENTER) == $POSITION_CENTER )
					{
						$oLeft = intval( ( $oWidth-$tWidth ) / 2 );	// center aline
					}

					$oWidth = $tWidth;
				}
			}
		}
		//echo( "dLeft: " . $dLeft . ", dTop: " . $dTop . ", oLeft: " . $oLeft . ", oTop: " . $oTop . "<br/>");
		//echo( "dWidth: " . $dWidth . ", dHeight: " . $dHeight . ", oWidth: " . $oWidth . ", oHeight: " . $oHeight . "<br/>");

		$alpha = 0;
		$blur = 0;

		$red = 0;
		$green = 0;
		$blue = 0;
		$len = strlen( $bgcolor );

		if( $len == 2 )
                {
			$alpha = 17 * hexdec($bgcolor[0]);
			$alpha = (int)($alpha/2);

			if( strcmp( $bgcolor[1], $COLOR_WHITE ) == 0 )
			{
				$red = 255;
				$green = 255;
				$blue = 255;
			}
		}
		else if( $len == 3 )
		{
			$red   = 17 * hexdec($bgcolor[0]);
			$green = 17 * hexdec($bgcolor[1]);
			$blue  = 17 * hexdec($bgcolor[2]);
		}
		else if( $len == 4 )
		{
			$alpha = 17 * hexdec($bgcolor[0]);
			$alpha = (int)($alpha/2);

			$red   = 17 * hexdec($bgcolor[1]);
			$green = 17 * hexdec($bgcolor[2]);
			$blue  = 17 * hexdec($bgcolor[3]);
		}
		else if( $len == 6 )
		{
			$red   = hexdec( substr($bgcolor, 0, 2) );
			$green = hexdec( substr($bgcolor, 2, 2) );
			$blue  = hexdec( substr($bgcolor, 4, 2) );
		}
		else if( $len == 8 )
		{
			$alpha = hexdec( substr($bgcolor, 0, 2) );
			$alpha = (int)($alpha/2);

			$red   = hexdec( substr($bgcolor, 0, 2) );
			$green = hexdec( substr($bgcolor, 2, 2) );
			$blue  = hexdec( substr($bgcolor, 4, 2) );
		}
		else
		{
			if( strcmp( $bgcolor, $COLOR_WHITE ) == 0 )
			{
				$red = 255;
				$green = 255;
				$blue = 255;
			}
			else if( strcmp( $bgcolor, $COLOR_ALPHA) == 0 )
			{
				$alpha = 127;
			}
			else if( strcmp( $bgcolor, $COLOR_BLUR) == 0 )
			{
				$blur = 1;
			}
		}

		$thumbnail = @imageCreateTrueColor( $width, $height );
		if( $alpha > 0 )
		{
			imageSaveAlpha( $thumbnail, true );

			$fillColor = @imageColorAllocateAlpha( $thumbnail, $red, $green, $blue, $alpha );
			@imageFill( $thumbnail, 0, 0, $fillColor );

			$this->m_hasAlpha = true;
		}
		else
		{
			$fillColor = @imageColorAllocate( $thumbnail, $red, $green, $blue );
			@imageFill( $thumbnail, 0, 0, $fillColor );

			$this->m_hasAlpha = false;
		}

		if( !@imageCopyResampled( $thumbnail, $this->m_Image->GetImage(), $dLeft, $dTop, $oLeft, $oTop, $dWidth, $dHeight, $oWidth, $oHeight) )
		{
			@imageDestroy( $thumbnail );
			return NULL;
		}

		if( $blur )
		{
			imagefilter( $thumbnail, IMG_FILTER_COLORIZE, -64, -64, -64 );
			imageFilter( $thumbnail, IMG_FILTER_GAUSSIAN_BLUR );
		}

		return $thumbnail;
    }

    function HasAlpha()
    {
        return $this->m_hasAlpha;
    }
}


?>