<?php
require('../libs/fpdf.php');
$outputfolder = '../output';
if (!is_dir($outputfolder)) mkdir($outputfolder, 0777, true);

$json = file_get_contents('php://input');
$values = json_decode($json, true);

// Sorting values by string length
usort($values, fn($v1, $v2) => strlen($v2) <=> strlen($v1) );

// Adding dashes to the strings so that each string has a length exactly one greater than the following one
$q = -1;
for($i = 0; $i < count($values); $i++) {
    $q = max($q, strlen($values[$i]) + $i);
}
for($i = 0; $i < count($values); $i++) {
    $values[$i] .= str_repeat('-', $q - strlen($values[$i]) - $i);
}

// Adding dashes to the strings so that each string has different length
// for($i = count($values) - 1; $i > 0; $i--) {
//     $diff = strlen($values[$i]) - strlen($values[$i - 1]);
//     if($diff >= 0) {
//         $values[$i - 1] .= str_repeat('-', strlen($values[$i]) - strlen($values[$i - 1]) + 1);
//     }
// }

function getnextdirection($direction) {
    if($direction[0] != 0) return [$direction[1], $direction[0]];
    if($direction[1] != 0) return [-$direction[1], $direction[0]];
}

$maxlength = (count($values) > 0) ? strlen($values[0]) : 1;

try {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica','');
    
    $startx = 5;
    $starty = 10;
    $startdirection = [1,0];
    $offset = 5;
    $fontsize = 12;

    // If the strings are too long for a page, adjust the offset and the font size
    if((2 * $startx) + $offset * $maxlength >= 200) {
        $val = floor( (200 - 2 * $startx) / $maxlength );
        $offset = $val > 0 ? $val : 1;
        $fontsize = ceil( $offset * 2.5);
    }

    $pdf->SetFontSize($fontsize);
    $pdf->SetXY($startx, $starty);

    $x = $startx;
    $y = $starty;
    $direction = $startdirection;
    for ($i = 0; $i < count($values); $i++) {
        $cur = $values[$i];
        for($j = 0; $j < strlen($cur); $j++) {
            $pdf->SetXY($x += $offset * $direction[0], $y += $offset * $direction[1]);
            $pdf->Write($offset, $cur[$j]);
        }
        $direction = getnextdirection($direction);
    }
    
    $filename = $outputfolder . '/testitmnf_' . time() . '.pdf';
    $pdf->Output('F', $filename);

    echo "<a href=\"{$filename}\" target=\"_blank\">Download generated PDF</a>";
} catch (Exception $e) {
    echo $e->getMessage();
}

?>