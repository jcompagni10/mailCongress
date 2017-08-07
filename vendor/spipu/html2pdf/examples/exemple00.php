<?php
/**
 * HTML2PDF Library - example
 *
 * HTML => PDF convertor
 * distributed under the LGPL License
 *
 * @package   Html2pdf
 * @author    Laurent MINGUET <webmaster@html2pdf.fr>
 * @copyright 2016 Laurent MINGUET
 *
 * isset($_GET['vuehtml']) is not mandatory
 * it allow to display the result in the HTML format
 */
     date_default_timezone_set('America/Los_Angeles');

for ($i = 0; $i < 1000000; $i++){
echo uniqid() .  "<br>";
}
$date =  date('F j, Y');
$repName = $_POST['repName'];
$template = "<div class='container' style='font-size:15px'>
	<div class='date' style='text-align:right;'>echo date('F lS, Y') </div>
	<br>
	<br>
	<div class='repAddress'>
		<span class='repName'>$name</span><br>
		<span class='body'>US Senate</span><br>
		<span class='repAddress1'>1520 Pennsylvania Dr.</span><br>
		<span class='repCity'>Washington</span>, <span class='repState'>DC</span> <span class='repZip'>94611</span><br>
		<br>
	</div> <br>
	<div class='messageBody'>
		Dear <span class='repName'>$repName</span>,<br>
		<p class='messageBody'>
		$message
		</p>
	</div>

	<div class='signature' style='float: left; margin: 100px 0 0 50px;' >
		<b>Sincerely, </b><br><br>
		<span class='userName'>Julian Portis</span><br>
		<span class='userAddress1'>3821 SE Main St.</span><br>
		<span class='userCity'>Portland</span>, <span class='userState'>OR</span> <span class='userZip'>97214</span><br>
		<span class='userEmail'>jcompagni@gmail.com</span>
	</div>
</div>";

    // get the HTML

 	 ob_start();
    include('file:///Users/julian/Sites/mailCongress/letterTemplate.html');
    $content = ob_get_clean();
    require_once(dirname(__FILE__).'/../../../autoload.php');
    try
    {
        $html2pdf = new HTML2PDF('P', 'P4', 'en', true, 'UTF-8', array(25,25,25,35));
//      $html2pdf->setModeDebug();
        $html2pdf->setDefaultFont('Arial');
        $html2pdf->writeHTML($template, isset($_GET['vuehtml']));
        $html2pdf->Output(dirname(__FILE__).'/output.pdf', 'F');
    }
    catch(HTML2PDF_exception $e) {
        echo $e;
        exit;
    }
