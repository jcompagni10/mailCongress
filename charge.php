<?php

/////////////////
/**Functions **/
//////////////

function sendMail($charge_id, $userInfo, $repInfo, $message){
	$message1 = substr($message, 0, 450);
	$message2 ="&nbsp";
	if(strlen($message) > 450){
		$message2 = substr($message, 450);
	}
	
	require 'vendor/autoload.php';
	$resp = array(
		'success' => false,
		'errors' => ''
	);
	
	try{
		$apiKey = 'live_ea0e3fcb3b37756f0fc8f7b0f3fbddb710f';
		$lob = new \Lob\Lob($apiKey);
		for ($i = 0; $i < count($repInfo); $i++){
			$lobResp = $lob->postcards()->create(array(
			  'description' 		  => $repInfo[$i]['letterUrl'],
			  'to[name]'              => $repInfo[$i]['fullTitle'],
			  'to[address_line1]'     => $repInfo[$i]['address1'],
			  'to[address_city]'      => $repInfo[$i]['city'],
			  'to[address_zip]'       => $repInfo[$i]['zip'],
			  'to[address_state]'     => $repInfo[$i]['state'],
			  'to[address_country]'   => 'US',
			  'from[name]'            => $userInfo['name'],
			  'from[address_line1]'   => $userInfo['address1'],
			  'from[address_line2]'   => $userInfo['address2'],
			  'from[address_city]'    => $userInfo['city'],
			  'from[address_zip]'     => $userInfo['zip'],
			  'from[address_state]'   => $userInfo['state'],
			  'from[address_country]' => 'US',
			  'front'   		      => 'tmpl_664ac80fea4ec33',
			  'back'   		  	      => 'tmpl_6119d9cccf9e043',
			  'data[userName]' 		  => $userInfo['name'],
			  'data[fullTitle]' 	  => $repInfo[$i]['fullTitle'],
			  'data[message1]'  	  => $message1,
			  'data[message2]'  	  => $message2,
			  'data[image]'           => $userInfo['image'],
			  'metadata[userEmail]'	  => $userInfo['email'],
			  'metadata[stripe_id]' => $charge_id
			  ));
			  if (!empty($lobResp['id'])){
				$resp['success'] = true;
			  }
			  else{
				throw exception('ERROR');
			  }
		  }

	}
	catch(exception $e){
		$code =  $e->getCode();
		if ($code == 429){
			$resp['errors'] =  "ERROR: We are proccessing too much mail. You have not been Charged. Try again later";
		}
		else {
			$resp['errors'] = $e -> getMessage();
		}
	}
	return $resp;
}

function stripeCharge($qty){	
	$response = array(
		"success" => false,
		"errors" => "",
		"charge_id" => ""
	);
	
	//Check for token
	if(!($token = $_POST['stripeToken'])){
		$response['errors'] = "Your order cannot be processed. You have not been charged. Please confirm that you have JavaScript enabled and try again.";
		return $response;
	}
	else{
		$unitCost = .95;
		$amount = $unitCost * $qty * 100;
		$data = array(
			"amount" => $amount,
			"currency" => 'usd',
			"description" => "Mail Congress",
			"source" => $token
		);
		
		$encodedData = http_build_query($data);
		$curl = curl_init('https://api.stripe.com/v1/charges');
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($curl, CURLOPT_USERPWD, "sk_live_VVPWGv29KM9314DNK3KjA5id");
		curl_setopt($curl, CURLOPT_POSTFIELDS, $encodedData);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_VERBOSE, true);
		curl_setopt($curl, CURLOPT_CAINFO, "/etc/pki/tls/certs/ca-bundle.crt.og");
		//$verbose = fopen('output.txt', 'w');
		//curl_setopt($curl, CURLOPT_STDERR, $verbose);
		$server_output = curl_exec($curl);
		$responseData = json_decode($server_output, true);
		
		//Payment Success	
		if( empty($responseData['error']['message'])){
			if ($responseData['paid']){
				$response['success'] = true;
				$response['charge_id'] = $responseData['id'];
				return $response;
			}
			//No Error but no Paid (shouldn't happen)
			else { 
				$response['errors'] = "Your order cannot be processed. You have not been charged. Please try again later.";
				return $response;
			}
		}
		//Stripe Returns Error
		else{
			//Card Error
			if ( $responseData['error']['type'] == "card_error"){
				$response['errors'] = $responseData['error']['message'];
			}
			//System Error
			else{
				$response['errors'] = $responseData['error']['message'] ;
			}
			return $response;
		}
	}
}

	

//////////////
/** Main **/
///////////
$qty = $_POST['qty'];
$userInfo = json_decode($_POST['userInfo'],true);
$repInfo = json_decode($_POST['repInfo'],true);
$message = str_replace("\\", "/",str_replace("\"","'",$_POST['message']));

// Check for qty data mismatch tampering 
if ($qty != count($repInfo)){
	echo json_encode(array("errors" =>"Quantity Mismatch: Your order cannot be processed. You have not been charged. Please try again later"));
}
else {			
	$stripeResp = stripeCharge($qty);
	if ($stripeResp['success']){
		$mailResp = sendMail($stripeResp['charge_id'], $userInfo, $repInfo, $message);
		//Don't show lob error after Payment
		//echo json_encode($mailResp);
		echo json_encode($stripeResp);
	}
	else{
		echo json_encode($stripeResp);
	}
}

?>


