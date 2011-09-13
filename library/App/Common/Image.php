<?php
class App_Common_Image
{
	/**
	 * list($lines, $lineHeight) = wordWrapAnnotation($image, $draw, $msg, 140);
	 * for($i = 0; $i < count($lines); $i++)
	 *     $image->annotateImage($draw, $xpos, $ypos + $i*$lineHeight, 0, $lines[$i]);  
	 */
	public static function wordWrapAnnotation(&$image, &$draw, $text, $maxWidth)
	{
	    $words = explode(" ", $text);
	    $lines = array();
	    $i = 0;
	    $lineHeight = 0;
	    while($i < count($words) )
	    {
	        $currentLine = $words[$i];
	        if($i+1 >= count($words))
	        {
	            $lines[] = $currentLine;
	            break;
	        }
	        //Check to see if we can add another word to this line
	        $metrics = $image->queryFontMetrics($draw, $currentLine . ' ' . $words[$i+1]);
	        while($metrics['textWidth'] <= $maxWidth)
	        {
	            //If so, do it and keep doing it!
	            $currentLine .= ' ' . $words[++$i];
	            if($i+1 >= count($words))
	                break;
	            $metrics = $image->queryFontMetrics($draw, $currentLine . ' ' . $words[$i+1]);
	        }
	        //We can't add the next word to this line, so loop to the next line
	        $lines[] = $currentLine;
	        $i++;
	        //Finally, update line height
	        if($metrics['textHeight'] > $lineHeight)
	            $lineHeight = $metrics['textHeight'];
	    }
	    return array($lines, $lineHeight);
	}
}