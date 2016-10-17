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

define('Quarterly', $_GET['qom']);

define('SM', $_GET['sm']);
define('SY', $_GET['sy']);
define('EM', $_GET['em']);
define('EY', $_GET['ey']);

function difference($s1, $s2){
	return(array_diff($s1, $s2));
}

function union($s1, $s2){
	return(array_unique(array_merge($s2, $s2)));
}

function intersection($s1, $s2){
	return(array_intersect($s1, $s2));
}

function rtn($m){
}

?>