<?php
//php ini setting, important for lengthy analyses
set_time_limit(30000);
ini_set("display_errors", 1);
ini_set("memory_limit", '1024M');

//define the DEFAULT of the datafolder, just the default
//the input instead can be selected by the user on the fly

define('DATAFOLDER', 'data62B');

define('IDIR', 'input/');  // The input folder which should be just ./input
define('ODIR', 'output/'); // The output folder which should be jsut ./output for all results with timestamps
define('Use_MKT', true);   // Use market cap or weights
define('Use_DVD', false);  //David's data format:  3 differences: 
                           //1. 'utility' identification this is a big difference
						   //2. book to market calculation, 
						   //3. no other value data

define('TKR', 1);    //ticker column position, all counted from 0
define('MKT', 4);    //market cap column position
define('R1M', 11);   //monthly return column position
define('R3M', 20);   //quarterly return column positio
define('WGT', 23);   //weight position, which is the holding weights, for Russell this comes with the holding not affected by selection

define('CSV', '.csv'); //extension

?>
<!--This style is just for the control's user interface of the page, some css stuff-->
<style type="text/css">
    input { text-align:right; }
	input[type=number]::-webkit-inner-spin-button, 
	input[type=number]::-webkit-outer-spin-button {  
			opacity: 1;
		}
	label { display: block; width: 160px; }
		
	.form-submit-button {
		background: #09CCCC;
		color: #fff;	
		border: 1px solid #eee;
		border-radius: 20px;
		box-shadow: 5px 5px 5px #eee;
		}

	.form-submit-button:hover {
		background: #016ABC;
		color: #fff;
		border: 1px solid #eee;
		border-radius: 20px;
		box-shadow: 5px 5px 5px #eee;
	}
	input, select, textarea{
		color: #ff0000;
		font-weight:bold;
	}
</style>

<!-- Top form part-->
<?php
$d = dir(IDIR . DATAFOLDER);
$file_list=array();

while (false !== ($fns = $d->read())) {
	
   if(substr($fns, -4) == '.csv' && strlen($fns) == 8){
	   $file_list[] = (substr($fns, 2, 2)>'50')?('19' . substr($fns, 2, 2)):('20' . substr($fns, 2, 2)) . substr($fns, 0, 2);
   }
}

sort($file_list);

$DSM = substr($file_list[0], 4, 2);
$DSY = substr($file_list[0], 0, 4);
$DEM = substr($file_list[sizeof($file_list)-1], 4, 2);
$DEY = substr($file_list[sizeof($file_list)-1], 0, 4);
$d->close();

function showValue($var, $default){
	echo isset($_GET[$var])?$_GET[$var]:$default;
}

function showOption($var, $value, $checked){
	echo isset($_GET[$var])?($_GET[$var]==$value?'checked':''):($checked?'checked':'');
}

?>

<center>
<form method="get" action="index.php">
	<table style="color: green; font-size: 16px; font-weight: bold">
		 <tr>
			<td align = "left">
				<label><u><?php echo DATAFOLDER; ?></u>
				<input type="hidden" name="datafolder"  
					value="<?php echo DATAFOLDER; ?>"></input>
				</label>
			</td>
			<td align = "right" >
				<label>Start Month
				<input type="number" name="sm" min="01" max="12" step="1" 
					value="<?php showValue('sm', $DSM); ?>"></input></label>
			</td>
			<td align = "left">
				<label>/Year
				<input type="number" name="sy" min="1995" max="2016" step="1" 
					value="<?php showValue('sy', $DSY); ?>"></input></label>
			</td> 
		</tr>
		 <tr>
			<td align = "left">
				<label>Mon/Qtr
					<input type="radio" name="qom" value="0"  <?php showOption('qom', 0, true); ?>>M
					<input type="radio" name="qom" value="1" <?php showOption('qom', 1, false); ?>>Q<br>
			</td>
			<td align = "right">
				<label>End Month
				<input type="number" name="em" min="01" max="12" step="1" 
					value="<?php showValue('em', $DEM); ?>"></input>
					</label>
			</td>
			<td align = "left">
				<label>/Year 
				<input type="number" name="ey" min="1995" max="2016" step="1" 
					value="<?php showValue('ey', $DEY); ?>"></input></label>
			</td>
		 </tr>
	 </table>
 <p>

<button type="submit" class="form-submit-button">Execute</button>

</form>
</center>
<hr>

<!-- Beginning of the data analysis -->
<?php
if(!isset($_GET['datafolder']))
	exit();
//Are we doing quarterly or monthly

define('QTR', $_GET['qom']);

define('SEQ', sizeof($file_list));
define('GAP', (QTR==0?1:3));

define('SM', $_GET['sm']);
define('SY', $_GET['sy']);
define('EM', $_GET['em']);
define('EY', $_GET['ey']);

function difference($s1, $s2){ // for two sets of securities, when treated as two sets, to yield the difference in s1 but not s2
	return(array_diff($s1, $s2));
}

function union($s1, $s2){ // s1 union s2, in either s1 or s2
	return(array_unique(array_merge($s2, $s2)));
}

function intersection($s1, $s2){ // si intersects with s2, in both s1 and s2.
	return(array_intersect($s1, $s2));
}

function rtn($p){
	global $mkt, $prt;
	
	$ret = 0.0;
	$tot = 0.0;
	foreach($mkt[$p] as $s => $m)
		$tot += $m;
	
	foreach($prt[$p] as $s => $r){
		$ret += $r*$mkt[$p][$s]/$tot;
	}
	return($ret);	
}

function cst($m){ // to decide on the level of costs of buying new stocks depending on the base
	global $CSC, $CSB;
	return(($m <= $CSB[0])?$CSC[0]:(($m <= $CSB[1])?$CSC[1] : (($m <= $CSB[2])?$CSC[2]: (($m <= $CSB[3])?$CSC[3]:(($m <= $CSB[4])?$CSC[4]:$CSC[5])))));
}

function standard_deviation($aValues, $bSample = false)  // sampling std definition if $bsample is true, otw, it is population
{
    $fMean = array_sum($aValues) / count($aValues);
    $fVariance = 0.0;
    foreach ($aValues as $i)
    {
        $fVariance += pow($i - $fMean, 2);
    }
    $fVariance /= ( $bSample ? count($aValues) - 1 : count($aValues) );
    return (float) sqrt($fVariance);
}

function std($s){  // std for a set of numbers in s
	return(standard_deviation($s, true));
}

function avg($s){ //average for a set of numbers in s
	return(array_sum($s)/count($s));
}

function mdn($s){ // median of a set of numbers in s
	$s1 = $s;
	$c = count($s);

	sort($s1);

	if(($c%2)==1)
		return($s1[($c-1)/2]);

	if($c == 0)
		return(0.0);
	return(($s1[$c/2-1] + $s1F[$c/2])/2.0);
}

$TB = 0.0016; //T-Bill Average

$CSC = array(0.0,(0.0134 / 2) + 0.001, (0.0056 / 2) + 0.001, (0.0025 / 2) + 0.001,(0.0014 / 2) + 0.001, (0.0004 / 2) + 0.001);
$CSB = array(0.0, 200.0, 500.0, 1500.0, 5000.0); // four intervals

//get the output file ready
$SUMA = array('Date (MM/YY)', 'MKTCAP ($B)', 'Max MKTCAP ($M)', 'Min MKTCAP ($M)', 'Return', 'No. of Secs', 'Turnover', 'Est Txn cost (%AUM)');

if(!is_dir(ODIR . DATAFOLDER))
	mkdir(ODIR . DATAFOLDER);

$SF = DATAFOLDER . '.' . date("dmdHis") . '.' . (QTR==0?'M':'Q') . '.summary' . CSV; //parameter file
$fpsf = fopen(ODIR . DATAFOLDER . '/' . $SF, 'w') or die ("Can't open file: " . ODIR . $TSF . '<br>');
$RF = DATAFOLDER . '.' . date("dmdHis") . '.' . (QTR==0?'M':'Q') . '.result' . CSV; //parameter file
$fprf = fopen(ODIR . DATAFOLDER . '/' . $RF, 'w') or die ("Can't open file: " . ODIR . $TSF . '<br>');

//Now we will start the loop of reading in the input filesize

//var_dump($file_list);

$tck = array(); //[$pi][$row]investable universe for this time unit, treated as a 'constant' per time unit
$mkt = array(); //[$pi][$row]market cap
$prt = array(); //[$pi][$row]periodical return
$tmk = array(); //[$pi] total market cap
$art = array(); //[$pi] aggregated periodical return
$ant = array(); //[$pi] aggregated periodical net return
$max = array(); //[$pi] max cap
$min = array(); //[$pi] min cap
$nor = array(); //[$pi] number of rows;
$trn = array(); //[$pi] turnover
$tst = array(); //[$pi] transaction cost

$fns = array(); //[$pi] file names in the periods

$pi = 0;

for($i = 0; $i < SEQ; $i+=GAP){ // per period (monthly or quarterly), after the loop $pi = period count
	//var_dump($file_list[$i]);

	$tck[$pi] = array();
	$mkt[$pi] = array();
	$rtn[$pi] = array();
	$tmk[$pi] = 0.0;
	$max[$pi] = 0;
	$min[$pi] = 1000000000000.0;
	$nor[$pi] = 0;
	$trn[$pi] = 0.0;
	$tst[$pi] = 0.0;

	$fn = sprintf("%02d%02d", substr($file_list[$i], 4, 2), substr($file_list[$i], 2, 2));
	//echo "working on $fn<br>";
	$fns[] = $file_list[$i];
		
	$fn = IDIR . DATAFOLDER . '/' . $fn . CSV;
	
	$fp = fopen($fn, 'r') or die('Cannot open file "' . $fn . '"...<br>');
	
	$csv_line = fgetcsv($fp); //skip title
	
	while($csv_line = fgetcsv($fp)){
		$t = $csv_line[TKR]; //echo ($t . "<br>"); the ticker

		if($csv_line[MKT]=='' || $csv_line[MKT] == 0.0) // skip tkr's that dont have a Market Cap
			continue;
		
		if($t == '') continue;//simply skip it
					
		if(isset($tck[$pi][$t])){
			echo 'Duplicated ticker!!! ' . $t . '<br>';
			break;
		}
		
		$tck[$pi][] = $t;	
        $mkt[$pi][$t] = $csv_line[MKT];
		$tmk[$pi] += $mkt[$pi][$t];
		$prt[$pi][$t] = $csv_line[(QTR==0?R1M:R3M)];
		if($max[$pi] < $mkt[$pi][$t])
			$max[$pi] = $mkt[$pi][$t];
		if($min[$pi] > $mkt[$pi][$t])
			$min[$pi] = $mkt[$pi][$t];		
		$nor[$pi] ++;		
	}
	
	fclose($fp) or die('Cannot close file "' . $fn . '"...<br>');

	$pi++;
}

//next we need to take care of turn over and transaction fee, starting 2nd period

for($p = 1; $p < $pi; $p++){

    //$tck from this period ($p) vs $tck last period ($p - 1)	
	
	$sll = 0.0;
	$buy = 0.0;
	
	$prev = $tck[$p - 1];
	$curr = $tck[$p];
	sort($prev);
	sort($curr);
	
	$sold = difference($prev, $curr);
	$bott = difference($curr, $prev);
	
	$tot = 0.0;
	
	foreach($mkt[$p - 1] as $t => $m) //? either last period or this period, from David's last month
		$tot += $m;
	
	foreach($sold as $s){
		$m = $mkt[$p - 1][$s];

		$sll += $m;
		$tst[$p] += $m*cst($m)/$tot;
	}
	
	$sll /= $tot;
	
	foreach($bott as $s){
		$m = $mkt[$p][$s];
		$buy += $m;
		$tst[$p] += $m*cst($m)/$tot;
	}
	$buy /= $tot;
				
	$trn[$p] = min($buy, $sll);	
}

for($p = 0; $p < $pi; $p++){
	$art[$p] = rtn($p);
	$ant[$p] = $art[$p] - $tst[$p];
}


//We got everything for result, now, need to prepare for the summaryRun Date	16/08/22

/*format	
Summary results for input/data62B	= IDIR . '.' . DATAFOLDER	
Run Date	16/08/22				= date()
Period	21							= $pi
Start	2011/07						= $fns[0]
End	2016/07							= $fns[$pi - 1]
Avg#Sec	3692						= avg($nor)
									= 
Average Returns	Gross	Net			= 
Monthly	4.17%	4.17%				= avg($art) and avg($ant)
Annual	16.68%	16.67%				= $avg($art) * 12/(1 or 4), $avg($ant) * (12/1 or 4)
Median	3.88%	3.88%				= mdn($art), mdn($ant)
Sample Std Dev						=
Monthly	6.38%						= std($art)
Annual	12.76%						= std($art) * sqrt(12/1 or 4)
Total Cpd Rtn	126.85%	126.77%		= $agr, $anr
Ann Cpd Rtn	59.69%	59.66%			= pow(1 + $agr, 12/$pi), pow(1 + $anr, 12/$pi)
									=
Sharpe Ratio	0.62854				= (avg($art) - $TB * (1 or 3))/std($art)
Ann Turnover	1.36%				= avg($trn)	* (12 or 4)
Txn cost(%AUM)	0.01%				= avg($sts) * (12 or 4)
									=
T-Bill	0.16%						= $TB
*/

$agr = 1.0; //aveage gross return
$anr = 1.0; //average net return

for($p = 0; $p < $pi; $p++){
	$agr *= 1.0 + $art[$p];
	$anr *= 1.0 + $ant[$p];
}
$agr -= 1.0;
$anr -= 1.0;	

//var_dump($agr);
//var_dump($anr);

//time to print out both result and summary

//result first to $fprf

//first line, the $SUMA array
//$SUMA = array('Date (MM/YY)', 'MKTCAP ($B)', 'Max MKTCAP ($M)', 'Min MKTCAP ($M)', 'Return', 'No. of Secs', 'Turnover', 'Est Txn cost (%AUM)');

for($i = 0; $i < 7; $i++)
	fprintf($fprf, "%s,", $SUMA[$i]);
fprintf($fprf, "%s\n", $SUMA[$i]);
	//fprintf($fprf, ($i < 6)?"%s,":"%s\n", $SUMA[$i]);

for($p = 0; $p < $pi; $p++){//only 8 columns
	fprintf($fprf, "%s,", substr($fns[$p], 4, 2) . '/' . substr($fns[$p], 2, 2)); // MM/YY
	fprintf($fprf, "%f,", $tmk[$p]/1000000000.0); // 
	fprintf($fprf, "%f,", $max[$p]/1000000.0); // 
	fprintf($fprf, "%f,", $min[$p]/1000000.0); // 
	fprintf($fprf, "%f,", $art[$p]); // 
	fprintf($fprf, "%d,", $nor[$p]); // 
	fprintf($fprf, "%f,", $trn[$p]); // 
	fprintf($fprf, "%f\n", $tst[$p]); // 
}
	
fclose($fprf);

//first line to $fpsf 
//*format	
fprintf($fpsf, "Summary for,%s\n", IDIR . DATAFOLDER);
fprintf($fpsf, "Run Date,%s\n",	date("Y-m-d H:i:s"));
fprintf($fpsf, "Period,%d\n", $pi);
fprintf($fpsf, "Start,%s\n", substr($fns[0], 4, 2) . '-' . substr($fns[0], 2, 2));
fprintf($fpsf, "End,%s\n", substr($fns[$pi - 1], 4, 2) . '-' . substr($fns[$pi - 1], 2, 2));
fprintf($fpsf, "Avg#Sec,%d\n", avg($nor));
fprintf($fpsf, "\n");
fprintf($fpsf, "Average Returns,Gross,Net\n");
fprintf($fpsf, "Monthly, %f%%, %f%%\n", avg($art)*100.0, avg($ant)*100.0);
fprintf($fpsf, "Annual, %f%%, %f%%\n", avg($art) *100.0* (QTR==0?12:4), avg($ant) *100.0 * (QTR==0?12:4));
fprintf($fpsf, "Median, %f%%, %f%%\n", mdn($art)*100.0, mdn($ant)*100.0);
fprintf($fpsf, "Sample Std Dev\n");
fprintf($fpsf, "Monthly, %f%%\n", std($art)*100.0);
fprintf($fpsf, "Annual, %f%%\n", std($art) * sqrt(QTR==0?12:4)*100.0);
fprintf($fpsf, "Total Cpd Rtn, %f%%, %f%%\n", $agr*100.0, $anr*100.0);
fprintf($fpsf, "Ann Cpd Rtn, %f%%, %f%%\n", (pow(1.0 + $agr, 12.0/$pi)-1.0)*100.0, (pow(1.0 + $anr, 12.0/$pi)-1.0)*100.0);
fprintf($fpsf, "\n");
fprintf($fpsf, "Sharpe Ratio, %f\n", (avg($art) - $TB * (QTR==0?1.0:3.0))/std($art));
fprintf($fpsf, "Ann Turnover, %f%%\n", avg($trn)*100.0	* (QTR==0?12:4));
fprintf($fpsf, "Txn cost(%%AUM), %f%%\n", avg($tst) * (QTR==0?12:4)*100.0);
fprintf($fpsf, "\n");
fprintf($fpsf, "T-Bill, %f\n", $TB);

fclose($fpsf);
echo "Done at" . date("Y-m-d H:i:s");
?>