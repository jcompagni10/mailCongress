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


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Los_Angeles');



$repInfo = $_POST['repInfo'];
//$repInfo = json_decode("{ name: 'Jeff Merkley', body: 'US Senate', address1: '313 Hart Senate Office Building', city: 'Washington', state: 'DC', zip: '20510', photo: 'http://bioguide.congress.gov/biogui…', fullTitle: 'Sen. Jeff Merkley', letterUrl: '061496897389966' }");
$userInfo = $_POST['userInfo'];
//$userInfo = json_decode("{ name: 'sd', address1: '3821 SE Main St.', address2: '', city: 'Portland', state: 'OR', zip: '97214', email: '3@df' }");
$fileName = $_POST['fName'];
$message = $_POST['message'];
//$message = "test";
$response = array(
	"success" => false,
	"errors" => ""	
);

//$date =  date('F j, Y');


$css= "  <style>                  #safe-area {   position: absolute;       width: 5.875in;       height: 3.8in;       left: 0.1875in;       top: 0.075in;     }          #ink-free {       position: absolute;       width: 3in;       height: 2.7in;       left: 3.55in;       bottom: 0.3in;       font-size: 12px;       padding-left: 20px;      }          #stamp{     top: -0.5in;     height: .9in;     width: .9in;     position: absolute;     right: 0.3in;     } img{ height: 100%; width: 100%;}          .text {    margin: 0px;  padding-right: 0.1in;      width: 3.2in;       height: 100%;       font-weight: 400;       font-size: 15px;       color: black;       border-right: 2px solid #000;     }     #returnAddress{      position:absolute;   }          #address{       	font-weight: bold;    		font-size: 15px;    		position: absolute;    		bottom: 50px;     }          .salutation {       margin-left: 20px;  } #wrapper{ height: 4in; width: 6in; overflow: hidden; position: absolute; }  #front{ width: 6.25in; position: absolute; top: -.125in; } </style>";

$body= "<page> <div id='safe-area'><div class='text'>
	Dear ". $repInfo['fullTitle'] . ", <br> <br>" . $message . "<br><br>
	<div class='salutation'>
      	Sincerely, <br>" . $userInfo['name'] . "<br>
        <span class='userEmail'>" . $userInfo['email'] . "</span>
    </div>
      <div id='ink-free'>
      	<div id='returnAddress'>
			<span class='userName'>" . $userInfo['name'] . "</span><br>
			<span class='userAddress1'>" . $userInfo['address1'] . " ". $userInfo['address2'] . "</span><br>
			<span class='userCity'>" . $userInfo['city'] . "</span>, <span class='userState'>" . $userInfo['state'] . "</span> <span class='userZip'>" . $userInfo['zip'] . "</span><br>
      	</div>
      	<div id='stamp'> 	
	      	<img id ='this' src='../img/stamp.png'>
    	</div>
    	<div id='address'>
    	<span class='repName'>" . $repInfo['fullTitle'] . "</span><br>
		<span class='body'>" . $repInfo['body'] . "</span><br>
		<span class='repAddress1'>" . $repInfo['address1'] . "</span><br>
		<span class='repCity'>" . $repInfo['city'] . "</span>, <span class='repState'>" . $repInfo['state'] . "</span> <span class='repZip'>" . $repInfo['zip'] . "</span><br>
		</div>
      </div>
    </div>
    </div>
    </page>";

$template = $css . $body;

    $content = ob_get_clean();
    require_once(dirname(__FILE__).'/../vendor/autoload.php');
    try
    {
		ob_start();
    	$letterFile = fopen('letters/' . $fileName .".pdf", 'w');
        $html2pdf = new HTML2PDF('L', array(176, 112), 'en', true, 'UTF-8');
//     $html2pdf->setModeDebug();
       // $html2pdf->setDefaultFont('Arial');
        $html2pdf->writeHTML($template, isset($_GET['vuehtml']));
        $html2pdf->Output(dirname(__FILE__).'/letters/'.$fileName.'.pdf', 'F');
        $response['success'] = true;
    }

    catch(Exception $e) {
       echo "$template";

        $response['errors'] = $e;
        echo $e->getMessage();
        exit;
    };
    
      
$output =  dirname(__FILE__) . "/letters/" . $fileName . "f.pdf"; 
$front = dirname(__FILE__) . "/../img/POSTCARDS/" . $userInfo['image'] . ".pdf";
$back = dirname(__FILE__).'/letters/'.$fileName.'.pdf';
$fxn = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=$output $front $back 2>&1"; 
$rsp = shell_exec($fxn);
$del = unlink (dirname(__FILE__) . '/letters/'. $fileName . '.pdf');
echo json_encode(array("pdf" => $response, "join" => $rsp ));
