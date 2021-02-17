<?php
	require_once('configs.php');

	$urlPagSeguro		= URL_PAGSEGURO;
	$emailPagSeguro 	= EMAIL_PAGSEGURO;
	$tokenPagSeguro 	= TOKEN_PAGSEGURO;

	$url = $urlPagSeguro . "transactions?email=$emailPagSeguro&token=$tokenPagSeguro";

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded; charset=UTF-8"));
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($curl);
	curl_close($curl);

	$xml = simplexml_load_string($data);
	echo json_encode($xml);
