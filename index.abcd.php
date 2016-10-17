<?php

set_time_limit(30000);
define('Quarterly', false);
define('Use_Russell_MC', false);
define('SZ', true);
define('VL', false);
define('FULLPRINT', false);
define('SR12', sqrt(12.0));

ini_set("display_errors", 1);
ini_set("memory_limit", '1024M');
$thirdParty = false; //using 3rd party, three differences 1. for the utility identification 2. book to market calculatioin, 3. no other values

define('TKR', 1); //ticker column

//Important this is where the "SIZE" field is located
if(Use_Russell_MC)
	define('TMC', 20); 
else
	define('TMC', 4); //size column
define('STS', 7); //status column

if(Quarterly)
	define('RTN', 20);//return column	
else
	define('RTN', 11);//return column

define('R1W', 18);
define('R1M', 19);
define('R3M', 20);
define('R6M', 21);
define('R1Y', 22);
define('MA5', 23);
define('MA10', 24);
define('MA20', 25);
define('MA50', 26);
define('MA100', 27);
define('MA200', 28);

$retWgts1w = array(0.0);
$retWgts1m = array(1.0);
$retWgts3m = array(0.0);
$retWgts6m = array(0.0);
$retWgts1y = array(0.0);

$maWgt5 = array(0.0);
$maWgt10 = array(0.0);
$maWgt20 = array(1.0);
$maWgt50 = array(0.0);
$maWgt100 = array(0.0);
$maWgt200 = array(0.0);


//define('SF', 'summary.csv'); //parameter file
//define('RF', 'result');  //result file
define('CSV', '.csv');

define('IDIR', 'data60B3mMA/');
?>
<!--This style is just for the control area of the page-->
<style type="text/css">
    input { text-align:right; }
	input[type=number]::-webkit-inner-spin-button, 
	input[type=number]::-webkit-outer-spin-button {  
			opacity: 1;
		}
	label { display: block; width: 200px; }
		
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
$d = dir(IDIR);
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
	<table border="0" style="color: green; font-size: 16px; font-weight: bold">
		 <tr>
			<td align = "right">
				<label>Size%
					<input type="number" name="sp" min="0.5" max="100" step="0.5" 
						value="<?php showValue('sp', '10'); ?>">
				</label>
			</td>
			<td align = "right">
				<label>Start Month
				<input type="number" name="sm" min="01" max="12" step="1" 
					value="<?php showValue('sm', $DSM); ?>"></label>
			</td>
			<td align = "right">
				<label>End Month
				<input type="number" name="em" min="01" max="12" step="1" 
					value="<?php showValue('em', $DEM); ?>"></label>
			</td>
			<td rowspan="2" align="right" valign="top">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Value
			</td>
			<td rowspan="2" align="left">
				<label>
				<table  style="border:solid 1px #060; color: #FF0000; font-size: 12px; font-weight: bold" cellpadding="2" cellspacing="1">
				<tr>
					<td valign="top">
					<input type="radio" name="tp" value="5"  <?php showOption('tp', 5, false); ?>>BTM<br>
					<input type="radio" name="tp" value="12" <?php showOption('tp', 12, false); ?>>EP<br>
					<input type="radio" name="tp" value="16" <?php showOption('tp', 16, false); ?>>LCR1<br>
					<input type="radio" name="tp" value="13" <?php showOption('tp', 13, false); ?>>SP<br></small>
					</td>
					<td valign="top">
					<input type="radio" name="tp" value="14" <?php showOption('tp', 14, false); ?>>OCFP<br>
					<input type="radio" name="tp" value="15" <?php showOption('tp', 15, false); ?>>EBIDA<br></small>
					<!--input type="radio" name="tp" value="17" < ?php showOption('tp', 15, false); ? >><br></small-->
					<input type="radio" name="tp" value="17" <?php showOption('tp', 17, true); ?>>LCR2<br></small>
					</td>
				</tr>
				</table>
				</label>
			</td> 
		</tr>
		 <tr>
			<td align = "right">
				<label>Value%
				<input type="number" name="vp" min="20.0" max="100.0" step="1.0" 
					value="<?php echo showValue('vp', '30'); ?>"></label>
			</td>
			<td align = "right">
				<label>Year
				<input type="number" name="sy" min="1995" max="2016" step="1" 
					value="<?php showValue('sy', $DSY); ?>"></label>
			</td>
			<td align = "right">
				<label>Year 
				<input type="number" name="ey" min="1995" max="2016" step="1" 
					value="<?php showValue('ey', $DEY); ?>"></label>
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

//if neither sp nor vp is set then quit
if(!isset($_GET['sp'])||!isset($_GET['vp']))
	exit();

define('SP', '0.' . str_replace('.', '', $_GET['sp']));
define('VP', '0.' . $_GET['vp']);
define('SM', $_GET['sm']);
define('SY', $_GET['sy']);
define('EM', $_GET['em']);
define('EY', $_GET['ey']);

define('BM', $_GET['tp']); //very important, this indicates how where the "VALUE" field is coming from 

//the concept of a function COP (size or value cut off point) of stock set, an order indicator, and a percentile
//and a function of VCOP (value cut off point) of stock set, an order indicator, and a percentile

//float function c(array s, boolean sov = SZ, float p) s is a set, sov is either SZ for size or VL for value, p is the percentile
//array function f(array s, float sp, vp) is a function to remove elements from a set which is either over sp or under vp

//T1(sp, vp) = F(IU, C(IU, ‘S’, sp), C(F(IU, C(IU, ‘S’, sp), minv)-US, ‘V’, vp))
//T2(sp, vp) = F(IU, C(F(IU, maxs, C(IU-US, ‘V’, vp))+US, ‘S’, sp), C(IU-US, ‘V’, vp))
//T3(sp, vp) = F(IU, max_s, C(IU-US, ‘V’, vp)) ∩ F(IU, C(IU, ‘S’, sp), min_v)

function c($m, $ss, $sov, $p){//size or valu cutoff
	//sort $s by either SZ for size or VL for value first
	global $size_set, $valu_set;
	
	$rr = array();
	
	$ts = 0.0;
	foreach($ss as $s){
		$sz = $size_set[$m][$s];
		$rr[$s] = ($sov == SZ)?$sz:$valu_set[$m][$s];
		$ts += $sz;
	}
	
	asort($rr); //descending sort along value or size
	
	$ap = 0.0; //accumulative percentage
	
	if($sov != SZ)
		$p = 1.0 - $p;
	
	foreach($rr as $r=>$num){
		$sz = $size_set[$m][$r];
		$vl = $valu_set[$m][$r];
		$ap += $sz / $ts;
		if($ap > $p)
			break;
	}
	
	return (($sov == SZ)?$sz:$vl);	
}

function f($m, $ss, $sp, $vp){ //remove the stocks that are outside the cut-off points
	global $size_set, $valu_set;
	
	$rr = array();
	
	foreach($ss as $s)
		if(($size_set[$m][$s] <= $sp) && ($valu_set[$m][$s] >= $vp))
			$rr[] = $s;
		
	return $rr;
}

function difference($s1, $s2){
	return(array_diff($s1, $s2));
}

function union($s1, $s2){
	return(array_unique(array_merge($s2, $s2)));
}

function intersection($s1, $s2){
	return(array_intersect($s1, $s2));
}

function psz($m, $s1){
	global $size_set;
	
	$ret = 0.0;
	foreach($s1 as $s)
		$ret += $size_set[$m][$s];
	return($ret);
}

function wsz($m, $s1){
	global $size_set;
	
	$ret = 0.0;
	$prtsz = psz($m, $s1);
	foreach($s1 as $s){
		$sz = $size_set[$m][$s];
		$ret += $sz*$sz/$prtsz;
	}
	return($ret);	
}

function wvl($m, $s1){
	global $size_set, $valu_set;
	
	$ret = 0.0;
	$prtsz = psz($m, $s1);
	foreach($s1 as $s){
		$sz = $size_set[$m][$s];
		$vl = $valu_set[$m][$s];
		$ret += $vl*$sz/$prtsz;
	}
	return($ret);	
}

function xsz($m, $s1){
	global $size_set;
	
	$max = -100000000.0;
	foreach($s1 as $s){
		$sz = $size_set[$m][$s];
		if($sz > $max)
			$max = $sz;
	}
	return($max);	
}

function nsz($m, $s1){
	global $size_set;
	
	$min = 100000000.0;
	foreach($s1 as $s){
		$sz = $size_set[$m][$s];
		if($sz < $min)
			$min = $sz;
	}
	return($min);	
}

function nvl($m, $s1){
	global $valu_set;
	
	$min = 100000000.0;
	foreach($s1 as $s){
		$vl = $valu_set[$m][$s];
		if($vl < $min)
			$min = $vl;
	}
	return($min);	
}

function rtn($m, $s1){
	global $size_set, $retn_set;
	
	$ret = 0.0;
	$prtsz = psz($m, $s1);
	foreach($s1 as $s){
		$sz = $size_set[$m][$s];
		$rt = $retn_set[$m][$s];
		$ret += $rt*$sz/$prtsz;
	}
	return($ret);	
}


function cst($sz){
	global $CSC, $CSB;
	return(($sz <= $CSB[0])?$CSC[0]:(($sz <= $CSB[1])?$CSC[1] : (($sz <= $CSB[2])?$CSC[2]: (($sz <= $CSB[3])?$CSC[3]:(($sz <= $CSB[4])?$CSC[4]:$CSC[5])))));
}

function standard_deviation($aValues, $bSample = false)
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

function std($s1){
	return(standard_deviation($s1, true));
}

function avg($s1){
	return(array_sum($s1)/count($s1));
}

function mdn($s1){
	$s = $s1;
	$c = count($s1);

	sort($s);

	if(($c%2)==1)
		return($s[($c-1)/2]);

	if($c == 0)
		return(0.0);
	return(($s[$c/2-1] + $s[$c/2])/2.0);
}

$TB = 0.0016; //T-Bill Average


//fprintf($fps, "%s, %d, %s, %s\n", date('m/d/y'), $PS, SM . '/01/20' . SY, EM . '/01/20' . EY);

$SUMA = array('Size Cut', 'Value Cut', 'Avg # Secs', 'Avg Returns (Gross)', 'Monthly', 'Annual', 'Median', 'Sample Std Dev', 'Monthly', 'Annual', 'Total Cpd Rtn', 'Ann Cpd Rtn', 'Avg Returns (Net)', 'Monthly', 'Annual', 'Median', 'Total Cpd Rtn (Net)', 'Ann Cpd Rtn','Sharpe Ratio', 'Ann Turnover', 'Txn Cost (%AUM)');
$t = $_GET['tp'];


$ODIR = sprintf("%s-out/%s-%02d-%02d/", substr(IDIR,0, -1), (BM==5)?"BM":((BM==12)?"EP":((BM==13)?"SP":((BM==14)?"OCTP":((BM==15)?"EBIDA":((BM==16)?"LCR1":((BM==17)?"LCR2":"UKN")))))), SP * 100.0, VP * 100.0);

if(!is_dir($ODIR))
	mkdir($ODIR);

$TSF = str_replace('/','-', $ODIR) . 'total_summary.' . date("dmdHis") . '.csv'; //parameter file
$fpts = fopen($ODIR . $TSF, 'w') or die ("Can't open file: " . $ODIR . $TSF . '<br>');

//Title line for total summary for LCR1 and LCR2 only
if(BM == 16){
	fprintf($fpts, "%s", "LCR1");
	fprintf($fpts, ",%s", "1W");
	fprintf($fpts, ",%s", "1M");
	fprintf($fpts, ",%s", "3M");
	fprintf($fpts, ",%s", "6M");
	fprintf($fpts, ",%s", "1Y");		
	fprintf($fpts, ",%s", "");		
}elseif(BM == 17){
	fprintf($fpts, "%s", "LCR2");
	fprintf($fpts, ",%s", "MA5");
	fprintf($fpts, ",%s", "MA10");
	fprintf($fpts, ",%s", "MA20");
	fprintf($fpts, ",%s", "MA50");
	fprintf($fpts, ",%s", "MA100");		
	fprintf($fpts, ",%s", "MA200");			
}

$k = 0;
foreach($SUMA as $sss){
	$k++;
	if($k == (count($SUMA) - 2))
		fprintf($fpts, ",%s", "");
	fprintf($fpts, ",%s", $sss);
}

fprintf($fpts, "\n");

if(BM == 16){
	$loop1 = $retWgts1w;
	$loop2 = $retWgts1m;
	$loop3 = $retWgts3m;
	$loop4 = $retWgts6m;	
	$loop5 = $retWgts1y;
	$loop6 = array(0);	
}elseif(BM == 17){
	$loop1 = $maWgt5;
	$loop2 = $maWgt10;
	$loop3 = $maWgt20;
	$loop4 = $maWgt50;
	$loop5 = $maWgt100;
	$loop6 = $maWgt200;	
}else{
	$loop1 = array(0);
	$loop2 = array(0);
	$loop3 = array(0);
	$loop4 = array(0);
	$loop5 = array(0);
	$loop6 = array(0);
}
	
foreach($loop1 as $lp1)
foreach($loop2 as $lp2)
foreach($loop3 as $lp3)
foreach($loop4 as $lp4)
foreach($loop5 as $lp5)
foreach($loop6 as $lp6)
{
$retMark = $lp1 . $lp2 . $lp3 . $lp4 . $lp5 . $lp6;

if(!in_array(SY . SM, $file_list) || !in_array(EY . EM, $file_list)){
	echo "<center><h2><font color='red'>Starting or ending month-year out of range!</font></h2></center><br>";
	exit();
}

if(SY . SM == EY . EM){
	echo "<center><h2><font color='green'>We need at least two months, please.</font></h2></center><br>";
	exit();
}


$timestamp = date("dmdHis");
$SF = str_replace('/','-', $ODIR) . $retMark . '-' . 'summary.' . $timestamp . '.csv'; //parameter file
$RF = str_replace('/','-', $ODIR) . $retMark . '-' . 'result.' . $timestamp;  //result file

//change log 01-21-16 changed to get Corey's modified 0116.csv whose Return is wrong from David.


$CSC = array(0.0,(0.0134 / 2) + 0.001, (0.0056 / 2) + 0.001, (0.0025 / 2) + 0.001,(0.0014 / 2) + 0.001, (0.0004 / 2) + 0.001);
$CSB = array(0.0, 200.0, 500.0, 1500.0, 5000.0); // four intervals


$ATTR = array('cid', 'tkr', 'cnt', 'pri', 'tmc', 'btm', 'dpb', 'sts', 'typ', 'med', 'ffa', 'rtn', 'vep', 'vsp', 'ocp', 'evp');
$PORT = array('Port Date',	'Cusip/ID',	'Ticker',	'Mkt Cap', 'BM', 'MCAP Tgt', 'Port_Wt', 'Return',	'Alert (U/D)');
$RESL = array('Date (MM/YY)', 'Universe MCAP ($B)', 'Port MCAP ($B)', 'Wtd MCAP ($M)', 'Wtd BM', 'Min MCAP ($M)', 'Max MCAP ($M)', 'Min BM', 'Return', 'No. of Secs', 'Turnover', 'Est Txn cost (%AUM)', 'Size Cut-off', 'Value Cut-off');

$size_set = array(); //size of a stock by ticker by time unit
$valu_set = array(); //value of a stock by ticker by time unit
$retn_set = array(); //return of a stock by ticker by time unit
$util_set = array(); //boolean set

$IU = array(); //investable universe for this time unit, treated as a 'constant' per time unit
$TT = array(); //total size of universe per time unit
$US = array(); //utility stocks for this time unit, treated as a 'constant' per time unit
$NU = array(); //non-utility stocks for this time unit, treated as a 'constant' per time unit $NU = $IU - $US
$XS = array(); //max size per time unit
$NS = array(); //min size per time unit
$XV = array(); //max value per time unit
$NV = array(); //min value per time unit

$PS; //total number of time units

$first = true;

if((SY > EY) || ((SY == EY) && (SM > EM)))
   die("Date Range Issue: starting date is later than ending date...</br>");
   
if(SY == EY){
	$PS = EM - SM + 1;
}else{
	$PS = 12 - SM + 1 + EM + 12 * (EY - SY - 1);
}

$fps = fopen($ODIR . $SF, 'w') or die ("Can't open file: " . $ODIR . $SF . '<br>');

if(FULLPRINT){
	foreach(array(1, 2, 3) as $i){ //print header for the result file
		foreach(array(1, 2, 3) as $j){
			$fvn = 'frp' . $i . $j;
			
			if((($i == 1) || ($i == 2)) && ($j == 3))
				break;
			
			$$fvn = fopen($ODIR . $RF . '.' . $i . $j . CSV, 'w') or die ("Can't open file: " . $ODIR . $RF . $i . '<br>');		
			$line = '';
			foreach($RESL as $R)
				$line .= $R . ',';
			$line = substr($line, 0, -1);
			fprintf($$fvn, "%s\n", $line);
		}
	}		
}

$mi = 0; //time unit index
$m = SM; //it should iterate from 0 to $PS-1

for($y = SY; $y <= EY; $y++){	
	for(; $m <= 12; $m++){				
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
}



foreach(array('11', '12', '21', '22', '31', '32', '33') as $i){
	${'t' . $i . 's'} = array();
	${'t' . $i . 'v'} = array();
	${'t' . $i . 'p'} = array();
	
	${'t' . $i . 'pap'} = 0.0;
	${'t' . $i . 'pcr'} = 1.0;
	${'t' . $i . 'prt'} = array();
	${'t' . $i . 'pnr'} = array();
	${'t' . $i . 'pcn'} = 1.0;	
	${'t' . $i . 'tto'} = array();
	${'t' . $i . 'tto'}[0] = 0.0;	
	${'t' . $i . 'txn'} = array();
	${'t' . $i . 'txn'}[0] = 0.0;
	${'t' . $i . 'sll'} = array();
	${'t' . $i . 'buy'} = array();
}


//the analysis procedure

$mi = 0; //month index
$m = SM;


for($y = SY; $y <= EY; $y++){	
	for(; $m <= 12; $m++){		
		//t1
		$t11s[$mi] = c($mi, $IU[$mi], SZ, SP);
		$t11v[$mi] = $NV[$mi];
		$t11p[$mi] = f($mi, $IU[$mi], $t11s[$mi], $t11v[$mi]);		

		$t12v[$mi] = c($mi, difference($t11p[$mi], $US[$mi]), VL, VP);
		$t12s[$mi] = $t11s[$mi];		
		$t12p[$mi] = f($mi, $IU[$mi], $t12s[$mi], $t12v[$mi]);
		
		//t2
		$t21v[$mi] = c($mi, $NU[$mi], VL, VP);
		$t21s[$mi] = $XS[$mi];
		$t21p[$mi] = f($mi, $IU[$mi], $t21s[$mi], $t21v[$mi]);
		
		$t22s[$mi] = c($mi, $t21p[$mi], SZ, SP);
		$t22v[$mi] = $t21v[$mi];
		$t22p[$mi] = f($mi, $IU[$mi], $t22s[$mi], $t22v[$mi]);
		
		//t3
		$t31v[$mi] = c($mi, $NU[$mi], VL, VP);
		$t31s[$mi] = $XS[$mi];
		$t31p[$mi] = f($mi, $IU[$mi], $t31s[$mi], $t31v[$mi]);
		
		$t32s[$mi] = c($mi, $IU[$mi], SZ, SP);
		$t32v[$mi] = $NV[$mi];		
		$t32p[$mi] = f($mi, $IU[$mi], $t32s[$mi], $t32v[$mi]);
		
		$t33v[$mi] = $t31v[$mi];
		$t33s[$mi] = $t32s[$mi];		
		$t33p[$mi] = intersection($t31p[$mi], $t32p[$mi]);

		$mi++;
		if(($m == EM)&&($y==EY))break;
	}

	if(($m == EM)&&($y==EY))break;
	if($m == 13) $m=1;
}


for($mi = 0; $mi < $PS; $mi++){
	foreach(array('11', '12', '21', '22', '31', '32', '33') as $i){
		${'t' . $i . 'tto'}[$mi] = 0.0;
		${'t' . $i . 'txn'}[$mi] = 0.0;
	}
}

$mi = 0; //month index
$m = SM;



for($y = SY; $y <= EY; $y++){	
	for(; $m <= 12; $m++){				
		
		foreach(array('11', '12', '21', '22', '31', '32', '33') as $i){
			if($mi == 0){
				${'t' . $i . 'tto'}[$mi] = 0.0;
				${'t' . $i . 'txn'}[$mi] = 0.0;
			}
			
			${'t' . $i . 'sll'}[$mi] = 0.0;
			${'t' . $i . 'buy'}[$mi] = 0.0;
			$flag = false;
			
			$prt = ${'t' . $i . 'p'}[$mi];
			$prt1 = ${'t' . $i . 'p'}[$mi+1];
			sort($prt);
			sort($prt1);
			
			$sll = difference($prt, $prt1);
			$buy = difference($prt1, $prt);
			$psz0 = psz($mi, $prt);
			//$psz1 = psz($mi+1, $prt1); using Davids prior month NAV
			
			foreach($sll as $p){
				$sz = $size_set[$mi][$p];
				if($first){
					//echo $mi . ':' . $sz . ',' . $p . ',' . cst($sz) . ',' . psz($mi, $prt) . ',' . $sz*cst($sz)/psz($mi, $prt) . '<br>';
					$first = false;
				}
				${'t' . $i . 'sll'}[$mi] += $sz;
				${'t' . $i . 'txn'}[$mi+1] += $sz*cst($sz)/$psz0;
			}
			${'t' . $i . 'sll'}[$mi] /= $psz0;
			
			foreach($buy as $p){
				$sz = $size_set[$mi+1][$p];
				${'t' . $i . 'buy'}[$mi] += $sz;
				${'t' . $i . 'txn'}[$mi+1] += $sz*cst($sz)/$psz0;
			}
			${'t' . $i . 'buy'}[$mi] /= $psz0;
						
			${'t' . $i . 'tto'}[$mi+1] = min(${'t' . $i . 'buy'}[$mi], ${'t' . $i . 'sll'}[$mi]);
		}
		$mi++;
		if($mi == ($PS-1)) break;
	}

	if($mi == ($PS-1))break;
	if($m == 13) $m=1;
}

//making the result reports

$mi = 0; //month index
$m = SM;

for($y = SY; $y <= EY; $y++){	
	for(; $m <= 12; $m++){		
		$mn = sprintf("%02d/01/%02d", $m, substr($y, 2));
		foreach(array('11', '12', '21', '22', '31', '32', '33') as $i){
			$prt = ${'t' . $i . 'p'}[$mi];
			if(FULLPRINT)
				fprintf(${'frp'.$i}, "%5s, %f, %f, %f, %f, %f, %f, %f, %f, %d, %f, %f, %f, %f\n", $mn, $TT[$mi]/1000.0, psz($mi, $prt)/1000.0, wsz($mi, $prt), wvl($mi, $prt), nsz($mi, $prt), xsz($mi, $prt), nvl($mi, $prt), rtn($mi, $prt), count($prt), ${'t'.$i.'tto'}[$mi], ${'t'.$i.'txn'}[$mi], ${'t'.$i.'s'}[$mi], ${'t'.$i.'v'}[$mi]);
			${'t' . $i . 'pap'} += count($prt)/$PS;
			${'t' . $i . 'pcr'} *= (1.0 + rtn($mi, $prt));			
			${'t' . $i . 'prt'}[$mi] = rtn($mi, $prt);
			${'t' . $i . 'pnr'}[$mi] = rtn($mi, $prt) - ${'t'.$i.'txn'}[$mi];
			${'t' . $i . 'pcn'} *= (1.0 + ${'t' . $i . 'pnr'}[$mi]);			
		}
		$mi++;
		if(($m == EM)&&($y==EY))break;
	}

	if(($m == EM)&&($y==EY))break;
	if($m == 13) $m=1;
}

$FC = 12.0/$PS;

fprintf($fps, "%s, %s\n\n", 'Value Type:', $t==5?'BM':($t==12?'EP':($t==13?'SP':($t==14?'OCFP':($t==15?'EBITDA':($t==16?'LCR1':'LCR2'))))));
fprintf($fps, "%s, %.1f%%\n\n", 'Size:', $_GET['sp']);
fprintf($fps, "%s, %.1f%%\n\n", 'Value:', $_GET['vp']);
fprintf($fps, "%s, %02d/%02d\n", 'Start:', SM, SY);
fprintf($fps, "%s, %02d/%02d\n", 'End:', EM, EY);
fprintf($fps, "%s, %d\n", 'Months:', $PS);
fprintf($fps, "%s, %s\n", 'TBill:', '0.16%');
fprintf($fps, "%s, %s\n\n", 'Date:', date('m/d/y'));



$suma = 0;
//prep for the G function: the turnover and transaction
if(FULLPRINT){
	for($mi = 0; $mi < $PS; $mi++){
		foreach(array('12', '22', '33') as $i){
			echo $mi . ':' . 'T' . substr($i,0,1) . '|' . sprintf("%.3f", ${'t' . $i . 's'}[$mi]) . '|' . sprintf("%.3f", ${'t' . $i . 'v'}[$mi]) . '<br>';
		}
	}	
}

fprintf($fps, "%s,%s,%s,%s,%s,%s,%s,%s\n", '', 'T11', 'T12', 'T21', 'T22', 'T31', 'T32', 'T33');
fprintf($fps, "%s,%f,%f,%f,%f,%f,%f,%f\n", $SUMA[$suma++], $t11s[$PS-1], $t12s[$PS-1], $t21s[$PS-1], $t22s[$PS-1], $t31s[$PS-1], $t32s[$PS-1], $t33s[$PS-1]);
fprintf($fps, "%s,%f,%f,%f,%f,%f,%f,%f\n", $SUMA[$suma++], $t11v[$PS-1], $t12v[$PS-1], $t21v[$PS-1], $t22v[$PS-1], $t31v[$PS-1], $t32v[$PS-1], $t33v[$PS-1]);
fprintf($fps, "%s,%f,%f,%f,%f,%f,%f,%f\n", $SUMA[$suma++], $t11pap, $t12pap, $t21pap, $t22pap, $t31pap, $t32pap, $t33pap);
fprintf($fps, "%s,%s,%s,%s,%s,%s,%s,%s\n", $SUMA[$suma++], '', '', '', '', '', '', '');
fprintf($fps, "%s,%f,%f,%f,%f,%f,%f,%f\n", $SUMA[$suma++], avg($t11prt), avg($t12prt), avg($t21prt), avg($t22prt), avg($t31prt), avg($t32prt), avg($t33prt));
fprintf($fps, "%s,%f,%f,%f,%f,%f,%f,%f\n", $SUMA[$suma++], avg($t11prt)*12.0, avg($t12prt)*12.0, avg($t21prt)*12.0, avg($t22prt)*12.0, avg($t31prt)*12.0, avg($t32prt)*12.0, avg($t33prt)*12.0);
fprintf($fps, "%s,%f,%f,%f,%f,%f,%f,%f\n", $SUMA[$suma++], mdn($t11prt), mdn($t12prt), mdn($t21prt), mdn($t22prt), mdn($t31prt), mdn($t32prt), mdn($t33prt));
fprintf($fps, "%s,%s,%s,%s,%s,%s,%s,%s\n", $SUMA[$suma++], '', '', '', '', '', '', '');
fprintf($fps, "%s,%f,%f,%f,%f,%f,%f,%f\n", $SUMA[$suma++], std($t11prt), std($t12prt), std($t21prt), std($t22prt), std($t31prt), std($t32prt), std($t33prt));
fprintf($fps, "%s,%f,%f,%f,%f,%f,%f,%f\n", $SUMA[$suma++], std($t11prt)*SR12, std($t12prt)*SR12, std($t21prt)*SR12, std($t22prt)*SR12, std($t31prt)*SR12, std($t32prt)*SR12, std($t33prt)*SR12);
fprintf($fps, "%s,%f,%f,%f,%f,%f,%f,%f\n", $SUMA[$suma++], $t11pcr-1.0, $t12pcr-1.0, $t21pcr-1.0, $t22pcr-1.0, $t31pcr-1.0, $t32pcr-1.0, $t33pcr-1.0);
fprintf($fps, "%s,%f,%f,%f,%f,%f,%f,%f\n", $SUMA[$suma++], pow($t11pcr, $FC)-1.0, pow($t12pcr, $FC)-1.0, pow($t21pcr, $FC)-1.0, pow($t22pcr, $FC)-1.0, pow($t31pcr, $FC)-1.0, pow($t32pcr, $FC)-1.0, pow($t33pcr, $FC)-1.0);
fprintf($fps, "%s,%s,%s,%s,%s,%s,%s,%s\n", $SUMA[$suma++], '', '', '', '', '', '', '');
fprintf($fps, "%s,%f,%f,%f,%f,%f,%f,%f\n", $SUMA[$suma++], avg($t11pnr), avg($t12pnr), avg($t21pnr), avg($t22pnr), avg($t31pnr), avg($t32pnr), avg($t33pnr));
fprintf($fps, "%s,%f,%f,%f,%f,%f,%f,%f\n", $SUMA[$suma++], avg($t11pnr)*12.0, avg($t12pnr)*12.0, avg($t21pnr)*12.0, avg($t22pnr)*12.0, avg($t31pnr)*12.0, avg($t32pnr)*12.0, avg($t33pnr)*12.0);
fprintf($fps, "%s,%f,%f,%f,%f,%f,%f,%f\n", $SUMA[$suma++], mdn($t11pnr), mdn($t12pnr), mdn($t21pnr), mdn($t22pnr), mdn($t31pnr), mdn($t32pnr), mdn($t33pnr));
fprintf($fps, "%s,%f,%f,%f,%f,%f,%f,%f\n", $SUMA[$suma++], $t11pcn-1.0, $t12pcn-1.0, $t21pcn-1.0, $t22pcn-1.0, $t31pcn-1.0, $t32pcn-1.0, $t33pcn-1.0);
fprintf($fps, "%s,%f,%f,%f,%f,%f,%f,%f\n", $SUMA[$suma++], pow($t11pcn, $FC)-1.0, pow($t12pcn, $FC)-1.0, pow($t21pcn, $FC)-1.0, pow($t22pcn, $FC)-1.0, pow($t31pcn, $FC)-1.0, pow($t32pcn, $FC)-1.0, pow($t33pcn, $FC)-1.0);
fprintf($fps, "%s,%s,%s,%s,%s,%s,%s,%s\n", '', '', '', '', '', '', '', '');
fprintf($fps, "%s,%f,%f,%f,%f,%f,%f,%f\n", $SUMA[$suma++], (avg($t11prt)-$TB)/std($t11prt), (avg($t12prt)-$TB)/std($t12prt), (avg($t21prt)-$TB)/std($t21prt), (avg($t22prt)-$TB)/std($t22prt), (avg($t31prt)-$TB)/std($t31prt), (avg($t32prt)-$TB)/std($t32prt), (avg($t33prt)-$TB)/std($t33prt));
fprintf($fps, "%s,%f,%f,%f,%f,%f,%f,%f\n", $SUMA[$suma++], avg($t11tto)*12.0, avg($t12tto)*12.0, avg($t21tto)*12.0, avg($t22tto)*12.0, avg($t31tto)*12.0, avg($t32tto)*12.0, avg($t33tto)*12.0);
fprintf($fps, "%s,%f,%f,%f,%f,%f,%f,%f\n", $SUMA[$suma++], avg($t11txn)*12.0, avg($t12txn)*12.0, avg($t21txn)*12.0, avg($t22txn)*12.0, avg($t31txn)*12.0, avg($t32txn)*12.0, avg($t33txn)*12.0);


if(FULLPRINT){
	fclose($frp11);
	fclose($frp12);
	fclose($frp21);
	fclose($frp22);
	fclose($frp31);
	fclose($frp32);
	fclose($frp33);
	fclose($fps);	
}

if(FULLPRINT){
	$_GET['s'] = substr(sprintf("%4.2f", SP), 2);
	$_GET['v'] = substr(sprintf("%4.2f", VP), 2);

	$ins = $_GET['s'];


	$inv = $_GET['v'];


	$fp = fopen($ODIR . $SF, 'r') or die ("Can't open file: " . $ODIR .  $SF . '<br>');	
	$lid = 0;
	echo '<table align="center"><tr>';
		
		while($csv_line = fgetcsv($fp)){
			if($lid > 31)
				break;
			if(count($csv_line)<2)
				continue;
			$lid++;
			
			if($lid < 9){
				echo '<td align="rihgt" ><b><font color="blue">' . $csv_line[0] . '</font></b></td><td align="right">' . $csv_line[1] . '&nbsp;</td>';
			}else if ($lid == 9) {
				echo '</tr></table><br><font color="blue"><table border="1" align="center"><tr>';
				foreach($csv_line as $field){
					echo '<td align="center" width="80"><b><font color="blue">' . $field . '</font></b></td>';
				}
				echo '</tr>';
			}else{
				echo '<tr>';
				foreach($csv_line as $i=>$field){
					if($field != '')
					echo ($i == 0)?'<td align="right" width="150"><b><font color="blue">' . $field . '</font></b></td>':'<td align="right" width="80">' . ((($i==2)||($i==4)||($i==7))?'<b><font color = "blue">':'') . (($lid==10 || $lid==11 || $lid==12 || $lid==29)?sprintf("%5.2f", $field):sprintf("%8.2f%%", $field*100.0)) . ((($i==2)||($i==4)||($i==7))?'</font></b>':''). '</td>';
				}
				echo '</tr>';
			}			
		}
	echo '</table></font>';
		
	fclose($fp);	
	
}else{//just print t33 column as to total_summary as a row
	echo 'Completed ' . date('m/d/y H:i:s') . '</br>';
}
//print the t33 from fps
if((BM == 16) || (BM == 17)){
	fprintf($fpts, "c(%s),", $retMark);
	fprintf($fpts, "%d,", $lp1);
	fprintf($fpts, "%d,", $lp2);
	fprintf($fpts, "%d,", $lp3);
	fprintf($fpts, "%d,", $lp4);
	fprintf($fpts, "%d,", $lp5);
	fprintf($fpts, "%d,", $lp6);
}
fprintf($fpts, "%s,", $t33s[$PS-1]);
fprintf($fpts, "%s,", $t33v[$PS-1]);
fprintf($fpts, "%s,", $t33pap);
fprintf($fpts, "%s,", "");
fprintf($fpts, "%s,", avg($t33prt));
fprintf($fpts, "%s,", avg($t33prt)*12.0);
fprintf($fpts, "%s,", mdn($t33prt));
fprintf($fpts, "%s,", "");
fprintf($fpts, "%s,", std($t33prt));
fprintf($fpts, "%s,", std($t33prt)*SR12);
fprintf($fpts, "%s,", $t33pcr-1.0);
fprintf($fpts, "%s,", pow($t33pcr, $FC)-1.0);
fprintf($fpts, "%s,", "");
fprintf($fpts, "%s,", avg($t33pnr));
fprintf($fpts, "%s,", avg($t33pnr)*12.0);
fprintf($fpts, "%s,", mdn($t33pnr));
fprintf($fpts, "%s,", $t33pcn-1.0);
fprintf($fpts, "%s,", pow($t33pcn, $FC)-1.0);
fprintf($fpts, "%s,", "");
fprintf($fpts, "%s,", (avg($t33prt)-$TB)/std($t33prt));
fprintf($fpts, "%s,", avg($t33tto)*12.0);
fprintf($fpts, "%s\n", avg($t33txn)*12.0);
}
fclose($fpts);

?>

