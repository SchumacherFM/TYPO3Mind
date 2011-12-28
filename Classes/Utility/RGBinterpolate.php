<?php
/***************************************************************
 *  Copyright notice
*
*  (c) 2011 Cyrill Schumacher <Cyrill@Schumacher.fm>
*
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 3 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 *
 *
 * @package typo3mind
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
*
*/
/*
	Source
	http://stackoverflow.com/questions/1177826/simple-color-variation
	but wrapped in a class
*/
class Tx_Typo3mind_Utility_RGBinterpolate {
	// Input:
	//   $start as RGB color string,
	//   $end as RGB color string,
	//   $dist as float in [0.0 .. 1.0] being % distance between start and end colors
	// Output:
	//   array(int, int, int) being the resulting color in RGB)

	
	public function inverse($color){
		$color = str_replace('#', '', $color);
		if (strlen($color) != 6){ return '000000'; }
		$rgb = '';
		for ($x=0;$x<3;$x++){
			$c = 255 - hexdec(substr($color,(2*$x),2));
			$c = ($c < 0) ? 0 : dechex($c);
			$rgb .= (strlen($c) < 2) ? '0'.$c : $c;
		}
		return '#'.$rgb;
	}

	public function interpolate( $start, $end, $dist ){


		$hsl_start = $this->rgb2hsl( $this->getCol($start) );
		$hsl_end = $this->rgb2hsl( $this->getCol($end) );

		// choose the shorter arc of the hue wheel!
		if ($hsl_start[0] > $hsl_end[0]) {
			if ($hsl_start[0] > $hsl_end[0] + 0.5)
				$hsl_start[0] -= 1.0;
		}
		else {
			if ($hsl_end[0] > $hsl_start[0] + 0.5)
				$hsl_end[0] -= 1.0;
		}

		// do linear interpolation in hsl color space
		$hs = $this->interp( $hsl_start[0], $hsl_end[0], $dist );
		$ss = $this->interp( $hsl_start[1], $hsl_end[1], $dist );
		$ls = $this->interp( $hsl_start[2], $hsl_end[2], $dist );
		return $this->col2string( $this->hsl2rgb( array( $hs, $ss, $ls ) ) );
		// return $this->return;
	}



	// Input: start-value, end-value, % distance as float in [0.0 .. 1.0]
	// Output: result of interpolation as float
	private function interp($start, $end, $dist) {
			return $start + ($end - $start)*$dist;
	}


	// Input: string in one of the following formats:
	//  #XXXXXX		(standard hex code as used in CSS)
	//  0xXXXXXX	   (same thing written as C longint)
	//  #XXX		   (equivalent to each-digit-doubled, ie #abc is #aabbcc)
	//  000, 000, 000  (decimal triad, each value in 0..255)
	//  colorname	  (Netscape defined color names)
	// Output: array(int, int, int) for legal values, else default value
	private function getCol($str, $default=array(0,0,0)) {



		$str = trim($str);  // remove leading and trailing whitespace
		$hex = '[0-9a-z]';

			// attempt to match #XXXXXX
		$pat = "/(#)($hex{2})($hex{2})($hex{2})/i";
		if ((preg_match($pat, $str, $arr)) == 1) {
					$r = hexdec($arr[2]);
					$g = hexdec($arr[3]);
					$b = hexdec($arr[4]);

					return array($r, $g, $b);
		}

		/* attempt to match 0xXXXXXX
		$pat = "/(0x)($hex{2})($hex{2})($hex{2})/i";
		if ((preg_match($pat, $str, $arr)) == 1) {
					$r = hexdec($arr[2]);
					$g = hexdec($arr[3]);
					$b = hexdec($arr[4]);

					return array($r, $g, $b);
		} */

			// attempt to match #XXX
		$pat = "/(#)($hex)($hex)($hex)/i";
		if ((preg_match($pat, $str, $arr)) == 1) {
					$r = hexdec($arr[2]) * 17;
					$g = hexdec($arr[3]) * 17;
					$b = hexdec($arr[4]) * 17;

					return array($r, $g, $b);
		}

		/* attempt to match int, int, int
			$pat = '/(\d{1,3})\\s*,\\s*(\d{1,3})\\s*,\\s*(\d{1,3})/i';
		if ((preg_match($pat, $str, $arr)) == 1) {
					$r = 0 + $arr[2];	// implicit cast to int - make explicit?
					$g = 0 + $arr[3];
					$b = 0 + $arr[4];

					return array($r, $g, $b);
		} */

			// if none of the above worked, return default value
		return $default;
	}


	// Input: array(int,int,int) being RGB color in { [0..255], [0..255], [0..255] }
	// Output array(float,float,float) being HSL color in { [0.0 .. 1.0), [0.0 .. 1.0), [0.0 .. 1.0) }
	private function rgb2hsl($rgbtrio) {
		$r = $rgbtrio[0] / 256.0;   // Normalize {r,g,b} to [0.0 .. 1.0)
		$g = $rgbtrio[1] / 256.0;
		$b = $rgbtrio[2] / 256.0;

		$h = 0.0;
		$s = 0.0;
		$L = 0.0;

		$min = min($r, $g, $b);
		$max = max($r, $g, $b);
		$delta = $max - $min;
		$L = 0.5 * ( $max + $min );

		if ( $delta < 0.001 )	   // This is a gray, no chroma...
		{
			$h = 0.0;	   // ergo, hue and saturation are meaningless
			$s = 0.0;
		}
		else		// Chromatic data...
		{
			/*
			if ( $L < 0.5 )				 $s = $max / ( $max + $min );
			else									$s = $max / ( 2 - $max - $min );
			*/
			/* bug fix */
			if ( $L < 0.5 ) { $s = ($max - $min) / ( $max + $min ); }
			else { $s = ($max - $min) / ( 2 - $max - $min ); }

			$dr = ( (($max - $r) / 6.0) + ($max / 2.0) ) / $max;
			$dg = ( (($max - $g) / 6.0) + ($max / 2.0) ) / $max;
			$db = ( (($max - $b) / 6.0) + ($max / 2.0) ) / $max;

			if ($r == $max)	{			 $h = $db - $dg;}
			elseif ($g == $max) {			$h = (0.3333) + $dr - $db;}
			elseif ($b == $max)  {		   $h = (0.6666) + $dg - $dr;}

			if ( $h < 0.0 ){ $h += 1.0;}
			if ( $h > 1.0 ){ $h -= 1.0;}
		}

		return array($h, $s, $L);
	}


	private function Hue_2_RGB( $v1, $v2, $vH ) {
		$v1 = 0.0+$v1;
		$v2 = 0.0+$v2;
		$vH = 0.0+$vH;

		if ( $vH < 0.0 )  {					  $vH += 1.0;}
		elseif ( $vH >= 1.0 )   {		$vH -= 1.0;}
		// 0.0 <= vH < 1.0

		if ( $vH < 0.1667 )  {			return ( $v1 + 6.0*$vH*($v2 - $v1) );}
		elseif ( $vH < 0.5 ) {			return ( $v2 );}
		elseif ( $vH < 0.6667 ){		return ( $v1 + (4.0-(6.0*$vH ))*($v2 - $v1) );}
		else   { 						return ( $v1 );}
	}

	// Input: array(float,float,float) being HSL color in { [0.0 .. 1.0), [0.0 .. 1.0), [0.0 .. 1.0) }
	// Output: array(int,int,int) being RGB color in { [0..255], [0..255], [0..255] }
	private function hsl2rgb($hsltrio) {
		$h = $hsltrio[0];
		$s = $hsltrio[1];
		$L = $hsltrio[2];

		if ( $s < 0.001 )					   //HSL from 0 to 1
		{
			$r = $L;
			$g = $L;
			$b = $L;
		}
		else
		{
			if ( $L < 0.5 )						 $j = $L * ( 1.0 + $s );
			else											$j = ($L + $s) - ($s * $L);

			$i = (2.0 * $L) - $j;

			$r = $this->Hue_2_RGB( $i, $j, $h + 0.3333 );
			$g = $this->Hue_2_RGB( $i, $j, $h );
			$b = $this->Hue_2_RGB( $i, $j, $h - 0.3333 );
		}

		return array( floor(256.0 * $r), floor(256.0 * $g), floor(256.0 * $b) );
	}


	private function col2string($rgbtrio) {

		$r = floor( $rgbtrio[0] );
		$g = floor( $rgbtrio[1] );
		$b = floor( $rgbtrio[2] );

		$str = sprintf('#%02x%02x%02x', $r, $g, $b);


		return $str;
	}

}