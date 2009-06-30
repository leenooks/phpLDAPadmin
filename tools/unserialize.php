<?php

require '../lib/common.php';

echo '<head><link type="text/css" rel="stylesheet" href="../htdocs/css/style.css" /></head>';
echo '<body>'."\n";
$index = get_request('index','REQUEST');
if (! isset($_SESSION['backtrace'][$index]))
	die('No backtrace available...?');

$line = $_SESSION['backtrace'][$index];
echo '<table class="result_table">';
printf('<tr class="hightlight"><td colspan="2"><b><small>%s</small></b></td><td>%s (%s)</td></tr>',
	_('File'),isset($line['file']) ? $line['file'] : $last['file'],isset($line['line']) ? $line['line'] : '');

printf('<tr><td>&nbsp;</td><td><b><small>%s</small></b></td>',
	_('Function'),$line['function']);

echo '<td><small><pre>';
print_r($line['args']);
echo '</pre></small></td>';

echo '</tr>';
echo '</table>';
echo '</body>';
?>
