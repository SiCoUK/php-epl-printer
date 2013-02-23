<?php
//namespace Sicouk\EplPrinter;

class EplPrinter
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $label = 'N' . PHP_EOL;
        $label .= 'q609' . PHP_EOL;
        $label .= 'Q203,26' . PHP_EOL;
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
        unlink($file);
    }
    
}