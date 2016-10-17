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

function rtn($m){ // return for the month $m, TBD 
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

//$ODIR = sprintf("%s-out/%s-%02d-%02d/", substr(IDIR,0, -1), (BM==5)?"BM":((BM==12)?"EP":((BM==13)?"SP":((BM==14)?"OCTP":((BM==15)?"EBIDA":((BM==16)?"LCR1":((BM==17)?"LCR2":"UKN")))))), SP * 100.0, VP * 100.0);

if(!is_dir(ODIR . DATAFOLDER))
	mkdir(ODIR . DATAFOLDER);

$SF = DATAFOLDER . '.' . date("dmdHis") . '.' . (QTR==0?'M':'Q') . '.summary.' . CSV; //parameter file
$fpsf = fopen(ODIR . DATAFOLDER . '/' . $SF, 'w') or die ("Can't open file: " . ODIR . $TSF . '<br>');
$RF = DATAFOLDER . '.' . date("dmdHis") . '.' . (QTR==0?'M':'Q') . '.result.' . CSV; //parameter file
$fprf = fopen(ODIR . DATAFOLDER . '/' . $SF, 'w') or die ("Can't open file: " . ODIR . $TSF . '<br>');

//Now we will start the loop of reading in the input filesize

//echo sizeof($file_list) . '<br>';
var_dump($file_list);
for($i = 0; $i < SEQ; $i+=GAP){
/*
		$mn = sprintf("%02d%02d", $m, substr($y,2));
		
		$size_set[$mi] = array(); //initialize for the new time period
		$valu_set[$mi] = array();
		$retn_set[$mi] = array();
		$util_set[$mi] = array();
		
		$IU[$mi] = array(); //universe
		$US[$mi] = array();
		$NU[$mi] = array();
		$TT[$mi] = 0.0;
		$XS[$mi] = -10000000.0;
		$NS[$mi] = 10000000.0;
		$XV[$mi] = -10000000.0;
		$NV[$mi] = 10000000.0;
		
		$fn = IDIR . $mn . CSV;
		
		$fp = fopen($fn, 'r') or die('Cannot open file "' . $fn . '"...<br>');
		
		$csv_line = fgetcsv($fp); //skip title
		
		if($csv_line[STS] == "status_code")
			$thirdParty = true;

		while($csv_line = fgetcsv($fp)){
			$t = $csv_line[TKR]; //echo ($t . "<br>"); the ticker

			if($csv_line[TMC]=='' || $csv_line[TMC] == 0.0) // skip tkr's that dont have a Market Cap
				continue;
			
			if($t == '') continue;//simply skip it
						
			if(isset($IU[$mi][$t])){
				echo 'Duplicated ticker!!! ' . $t . '<br>';
				break;
			}
			
			$IU[$mi][] = $t;
			
			if($thirdParty){
				$utility = false;
				switch($csv_line[STS]){
					case "ERN":	case "IPO": case "AIB": case "LP": case "LLC": case "NO": case "M": 
					case "3M": case "RP": case "AEB": case "REIT": case "Ltd Part": case "Private Comp":
					case "MLP": case "Tracking Stk": case "ADR":
					  $utility = true;
					  break;
					default:
						break;
				}
			}
			else{
				$utility = ($csv_line[STS] == 'Utilities');				
			}
		

			if(!$thirdParty && $csv_line[BM]=='')
				$csv_line[BM] = -1000.0; // for covering the situation when no BM is there
			if(!$thirdParty){
				$csv_line[TMC] /= 1000000.0;
			}

			$size_set[$mi][$t] = $csv_line[TMC] * 1.0;
			if(BM == 16){ //LCR1, linear combination of R1W....
				$valu_set[$mi][$t] = $lp1 * $csv_line[R1W] + $lp2 * $csv_line[R1M] + $lp3 * $csv_line[R3M] + $lp4 * $csv_line[R6M] + $lp5 * $csv_line[R1Y];
			}else if(BM == 17){
				$valu_set[$mi][$t] = $lp1 * $csv_line[MA5] + $lp2 * $csv_line[MA10] + $lp3 * $csv_line[MA20] + $lp4 * $csv_line[MA50] + $lp5 * $csv_line[MA100] + $lp6 * $csv_line[MA200];				
			}else
				$valu_set[$mi][$t] = $csv_line[BM] * 1.0; // can change in the future
			if($thirdParty){
				$valu_set[$mi][$t] = $csv_line[BM]/$csv_line[TMC]; // can change in the future				
			}
			if($thirdParty)
				$retn_set[$mi][$t] = $csv_line[RTN]/100.0;
			else
				$retn_set[$mi][$t] = $csv_line[RTN];
			$util_set[$mi][$t] = $utility;
			
			if($utility)
				$US[$mi][] = $t;
			else
				$NU[$mi][] = $t;
			
			$TT[$mi] += $size_set[$mi][$t];
			
			$sz = $size_set[$mi][$t];
			$vl = $valu_set[$mi][$t];
			
			if($sz > $XS[$mi])
				$XS[$mi] = $sz;
			if($sz < $NS[$mi])
				$NS[$mi] = $sz;
			if($vl > $XV[$mi])
				$XV[$mi] = $vl;
			if($vl < $NV[$mi])
				$NV[$mi] = $vl;			
		}			
		
		fclose($fp) or die('Cannot close file "' . $fn . '"...<br>');

		$mi++;
		if(($m == EM)&&($y==EY))break;
	}

	if(($m == EM)&&($y==EY))break;
	if($m == 13) $m=1;
*/	
}



?>