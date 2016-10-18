<?php
//php ini setting, important for lengthy analyses
set_time_limit(30000);
ini_set("display_errors", 1);
ini_set("memory_limit", '1024M');

//define the DEFAULT of the datafolder, just the default
//the input instead can be selected by the user on the fly

define('DATAFOLDER', 'data62Btest');

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

function rtn($m){
	global $mkt, $rtn;
	
	$ret = 0.0;
	$prtsz = psz($m, $s1);
	foreach($s1 as $s){
		$sz = $size_set[$m][$s];
		$rt = $retn_set[$m][$s];
		$ret += $rt*$sz/$prtsz;
	}
	return($ret);	
}

function cst($sz){ // to decide on the level of costs of buying new stocks depending on the base
	global $CSC, $CSB;
	return(($sz <= $CSB[0])?$CSC[0]:(($sz <= $CSB[1])?$CSC[1] : (($sz <= $CSB[2])?$CSC[2]: (($sz <= $CSB[3])?$CSC[3]:(($sz <= $CSB[4])?$CSC[4]:$CSC[5])))));
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
	return(array_sum($s)/count($s1));
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

//get the output file ready
$SUMA = array('Date (MM/YY)', 'MKTCAP ($B)', 'Max MKTCAP ($M)', 'Min MKTCAP ($M)', 'Return', 'No. of Secs', 'Turnover', 'Est Txn cost (%AUM)');

if(!is_dir(ODIR . DATAFOLDER))
	mkdir(ODIR . DATAFOLDER);

$SF = DATAFOLDER . '.' . date("dmdHis") . '.' . (QTR==0?'M':'Q') . '.summary.' . CSV; //parameter file
$fpsf = fopen(ODIR . DATAFOLDER . '/' . $SF, 'w') or die ("Can't open file: " . ODIR . $TSF . '<br>');
$RF = DATAFOLDER . '.' . date("dmdHis") . '.' . (QTR==0?'M':'Q') . '.result.' . CSV; //parameter file
$fprf = fopen(ODIR . DATAFOLDER . '/' . $SF, 'w') or die ("Can't open file: " . ODIR . $TSF . '<br>');

//Now we will start the loop of reading in the input filesize

//var_dump($file_list);

$tck = array(); //[$pi][$row]investable universe for this time unit, treated as a 'constant' per time unit
$mkt = array(); //[$pi][$row]market cap
$rtn = array(); //[$pi][$row]return
$max = array(); //[$pi] max cap
$min = array(); //[$pi] min cap
$nor = array(); //[$pi] number of rows;
$trn = array(); //[$pi] turnover
$tst = array(); //[$pi] transaction cost

$pi = 0;

for($i = 0; $i < SEQ; $i+=GAP){ // per period (monthly or quarterly)
	//var_dump($file_list[$i]);

	$tck[$pi] = array();
	$mkt[$pi] = array();
	$rtn[$pi] = array();
	$max[$pi] = 0;
	$min[$pi] = 1000000000000.0;
	$nor[$pi] = 0;
	$trn[$pi] = 0;
	$tst[$pi] = 0.0;
	

	$fn = sprintf("%02d%02d", substr($file_list[$i], 4, 2), substr($file_list[$i], 2, 2));
		
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
        $mkt[$pi][] = $csv_line[MKT];
		$rtn[$pi][] = $csv_line[(QTR==0?R1M:R3M)];
		if($max[$pi] < $mkt[$pi][$nor[$pi]])
			$max[$pi] = $mkt[$pi][$nor[$pi]];
		if($min[$pi] > $mkt[$pi][$nor[$pi]])
			$min[$pi] = $mkt[$pi][$nor[$pi]];		
		$nor[$pi] ++;		
	}
	
	fclose($fp) or die('Cannot close file "' . $fn . '"...<br>');

	$pi++;
}

//var_dump($tck);
var_dump($mkt);
//var_dump($rtn);
var_dump($max);
var_dump($min);



?>