<?php
//namespace Sicouk\EplPrinter;

class EplPrinter
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Label From: http://nicholas.piasecki.name/blog/2009/03/sending-raw-epl2-directly-to-a-zebra-lp2844-via-c/
        /*$label = 'N' . PHP_EOL;
        //$label .= 'q609' . PHP_EOL; // DO NOT USE
        //$label .= 'Q203,26' . PHP_EOL; // DO NOT USE
        $label .= 'B26,26,0,UA0,2,2,152,B,"603679025109"' . PHP_EOL;
        $label .= 'A253,26,0,3,1,1,N,"SKU 6205518 MFG 6354"' . PHP_EOL;
        $label .= 'A253,56,0,3,1,1,N,"2XIST TROPICAL BEACH"' . PHP_EOL;
        $label .= 'A253,86,0,3,1,1,N,"STRIPE SQUARE CUT TRUNK"' . PHP_EOL;
        $label .= 'A253,116,0,3,1,1,N,"BRICK"' . PHP_EOL;
        $label .= 'A253,146,0,3,1,1,N,"LARGE"' . PHP_EOL;
        $label .= 'P1,1' . PHP_EOL;
        
        // Create a temp file
        echo $file = tempnam(sys_get_temp_dir(), 'lbl');
        
        // Open the file for writing
        $handle = fopen($file, "w");
        fwrite($handle, $label);
        fclose($handle); // Close the file
        
        // Print the file
        echo exec('print /d:"\\\%COMPUTERNAME%\Zebra LP2844" "' . $file . '"');
        
        // Delete the file
        unlink($file);*/
    }
    
    public function send($label, $printer)
    {
        echo $label;
        
        // Create a temp file
        $file = tempnam(sys_get_temp_dir(), 'lbl');
        
        // Open the file for writing
        $handle = fopen($file, "w");
        fwrite($handle, $label);
        fclose($handle); // Close the file
        
        // Print the file
        exec('print /d:"\\\%COMPUTERNAME%\\' . $printer . '" "' . $file . '"');
        
        // Delete the file
        unlink($file);
    }
    
    /**
     * Add the label header and footer details
     * 
     * @param string $data The label data
     * @param int $quantity The quantity of labels to print
     * @return string
     */
    public function compile($data, $quantity = 1)
    {
        // Create the label header
        $compiled = '' . $this->_eol();
        $compiled .= 'N' . $this->_eol();
        
        //[q] Set the label width to 609 dots (3 inch label x 203 dpi = 609 dots wide).
        //$compiled = 'q812' . $this->_eol();
        
        // Append the data
        $compiled .= $data;

        // Append the label footer
        $compiled .= 'P1,' . (int) $quantity . $this->_eol();
        
        return $compiled;
    }
    
    /**
     * Write a string of ascii characters
     * 
     * @param string $value The string of text
     * @param int $xStart The horizontal start position
     * @param int $yStart The vertical start position
     * @param int $font The font selection (1-5)
     * @param boolean $reverse Wether to reverse the text to white on black
     * @param int $rotation The rotation of the text
     *                      0 = Normal (No rotation)
     *                      1 = 90 Degrees
     *                      2 = 180 Degrees
     *                      3 = 270 Degrees
     * @param int $xMultiplier
     * @param int $yMultiplier
     * @return string
     */
    public function writeString($value, $xStart, $yStart, $font, $reverse = false, $rotation = 0, $xMultiplier = 1, $yMultiplier = 1)
    {
        $command = 'A';

        // Check for the reverse parameter
        // Reverse = N (Normal) or R (Reversed)
        $style = 'N';
        if ((bool) $reverse) {
            $style = 'R';
        }

        return $this->writeLine($command, array(
            (int) $xStart,
            (int) $yStart,
            (int) $rotation,
            $font,
            (int) $xMultiplier,
            (int) $yMultiplier,
            $style,
            '"' . $value . '"',
        ));
    }

    /**
     * Write a barcode
     * 
     * @param string $value The barcode string
     * @param int $xStart The horizontal start position
     * @param int $yStart The vertical start position
     * @param int $height
     * @param string $type
     * @param boolean $readable
     * @param int $rotation
     * @param int $narrowBar
     * @param int $wideBar
     * @return string
     */
    public function drawBarcode($value, $xStart, $yStart, $height, $type = 3, $readable = true, $rotation = 0, $narrowBar = 2, $wideBar = 3)
    {
        $command = 'B';

        // Check for the human readable parameter
        // Human Readable = B (Yes) or N (No)
        $humanReadable = 'B';
        if (!(bool) $readable) {
            $humanReadable = 'N';
        }

        return $this->writeLine($command, array(
            (int) $xStart,
            (int) $yStart,
            (int) $rotation,
            $type,
            (int) $narrowBar,
            (int) $wideBar,
            (int) $height,
            $humanReadable,
            '"' . $value . '"',
        ));
    }
    
    /**
     * Draw a black box
     * 
     * @param int $xStart The horizontal start position
     * @param int $yStart The vertical start position
     * @param int $xEnd The horizontal end position
     * @param int $yEnd The vertical end position
     * @param int $thickness The thickness in dots
     * @return string
     */
    public function drawBox($xStart, $yStart, $xEnd, $yEnd, $thickness = 2)
    {
        $command = 'X';

        return $this->writeLine($command, array(
            (int) $xStart,
            (int) $yStart,
            (int) $thickness,
            (int) $xEnd,
            (int) $yEnd,
        ));
    }

    /**
     * Draw a line in either black or white
     * 
     * @see $this->line()
     * @param int $xStart The horizontal start position
     * @param int $yStart The vertical start position
     * @param int $length The length of the line in dots
     * @param int $thickness The thickness of the line in dots
     * @param int $orientation The orientation of the line [vertical|horizontal]
     * @param boolean $black Whether to print a black or white line (white only appears when printing over another line)
     * @param boolean $exclude Whether to exclude colour when overlapping other objects (i.e. when printing over another black line this line will be white)
     * @return string
     */
    public function drawLine($xStart, $yStart, $length, $thickness = 2, $orientation = 'vertical', $black = true, $exclude = false)
    {
        // Check the orientation and create the correct thickness and length
        if ('horizontal' == $orientation) {
            $xLength = $length;
            $yLength = $thickness;
        } else {
            $yLength = $length;
            $xLength = $thickness;
        }

        // Determine the correct line drawing method
        // TODO: Need to work on Black and white and exclusive lines.
        $command = 'LO';
        if ($exclude) {
            $command = 'LE';
        } elseif (!$black) {
            $command = 'LW';
        }

        // Create the line
        return $this->writeLine($command, array(
            (int) $xStart,
            (int) $yStart,
            (int) $xLength,
            (int) $yLength,
        ));
    }

    /**
     * Draw a diagonal line
     * 
     * @param int $xStart The horizontal start position
     * @param int $yStart The vertical start position
     * @param int $yEnd The vertical end position
     * @param int $length The length of the line in dots
     * @param int $thickness The thickness of the line in dots
     * @return string
     */
    public function drawDiagonalLine($xStart, $yStart, $yEnd, $length, $thickness = 2)
    {

    }

    /**
     * Create a line of code
     * 
     * @param string $command The command
     * @param array $options The options to write
     * @return string
     */
    protected function writeLine($command, $options)
    {
        return $command . implode(',', $options) . $this->_eol();
    }

    /**
     * Return the correct end of line characters
     * 
     * @return string
     */
    protected function _eol()
    {
        return PHP_EOL;
    }
}