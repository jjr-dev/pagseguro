<?php
	require_once('configs.php');

	if($_POST['notificationType'] && $_POST['notificationCode']) {
		$urlPagSeguro		= URL_PAGSEGURO;
		$emailPagSeguro 	= EMAIL_PAGSEGURO;
		$tokenPagSeguro 	= TOKEN_PAGSEGURO;

		$notifyCode = $_POST['notificationCode'];

		$url = $urlPagSeguro . "transactions/notifications/$notifyCode?email=$emailPagSeguro&token=$tokenPagSeguro";

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($curl);
		curl_close($curl);
		
		$dataXML 	= simplexml_load_string($data);
		$dataArray 	= get_object_vars($dataXML);

		if($data) {
			$code_pay 	= $dataArray['code'];
			$status 	= $dataArray['status'];
		} else {
			// Error
		}
	} else {
		// Error
	}
