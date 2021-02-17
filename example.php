<?php
	// Requires
	require_once('configs.php');
	require_once('generateXML.php');

	// Credentials
	$urlPagseguro		= URL_PAGSEGURO;
	$emailPagSeguro 	= EMAIL_PAGSEGURO;
	$tokenPagSeguro 	= TOKEN_PAGSEGURO;
	$notificationURL 	= URL_NOTIFICATION;

	// Basic Infos
	$client = array(
		"code" 			=> '001',
		"name" 			=> 'Julimar Gomes da Silva Junior',
		"cpf" 			=> str_replace('.', '', str_replace('-', '', '797.710.720-13')),
		"ddd"			=> '11',
		"phone" 		=> '912341234',
		"hash"    		=> '123456789',
		"email" 		=> 'contato@julimarjunior.com.br',
		"cep" 			=> '78040-290',
		"city" 			=> 'Cuiabá',
		"state" 		=> 'MT',
		"district" 		=> 'Santa Rosa',
		"address" 		=> 'Rua Polônia',
		"number" 		=> '123',
		"complement"	=> ''
	);

	// Credit Card
	$client['card'] = array(
		"token"     	=> '123456789',
		"quantity"    	=> '18',
		"value"    		=> '20.00',
		"valueTotal"    => '360.00',
		"name"			=> 'Julimar Gomes da Silva Junior',
		"cpf" 			=> str_replace('.', '', str_replace('-', '', '797.710.720-13')),
		"birthDate"		=> '2001-07-10',
		"ddd"			=> '11',
		"phone"			=> '912341234',
		"cep" 			=> '78040-290',
		"city" 			=> 'Cuiabá',
		"state" 		=> 'MT',
		"district" 		=> 'Santa Rosa',
		"address" 		=> 'Rua Polônia',
		"number" 		=> '123',
		"complement"	=> ''
	);

	// Products
	$products = array(
		0 => array(
			"code"			=> '001',
			"name"			=> 'Nome produto 01',
			"amount"		=> '2.00',
			"quantity"		=> '10'
		),
		1 => array(
			"code"			=> '002',
			"name"			=> 'Nome produto 02',
			"amount"		=> '6.00',
			"quantity"		=> '5'
		)
	);

	// Shipping Value
	$shipping = "20.00";

	// XML
	$xml = generateXML($products, $client, $shipping, $notificationURL);

	// cURL
	$url = $urlPagseguro . "transactions?email=$emailPagseguro&token=$tokenPagseguro";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml;charset=UTF-8'));

	$data = curl_exec($ch);
	if($data && $data != "Internal Server Error") {
		$dataXML = simplexml_load_string($data);
		$dataArray = get_object_vars($dataXML);
		if($dataXML->error) {
			// Error
			var_dump($dataXML->error);
			exit;
		} else {
			// Success
		}
	}

	curl_close($ch);
