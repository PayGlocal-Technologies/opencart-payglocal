<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
ini_set('session.cookie_samesite', 'None');

class ControllerPaymentPayglocal extends Controller {
	protected function index() {

		if($this->config->get('payglocal_test')){
			$mode = 'sandbox';
		}else{
			$mode = 'live';
		}

		$fp = fopen(DIR_DOWNLOAD . $this->config->get('payglocal_'.$mode.'_private_pem'), "r");

		$priv_key = fread($fp, 8192);
		fclose($fp);
		$privateKey = openssl_get_privatekey($priv_key);

		$merchantUniqueId = $this->generateRandomString(16);

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$prodData = array();

		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$prodData[] = array(
				'productDescription' => $product['name'],
				'productSKU' => $product['model'],
				'productType' => $product['name'],
				'itemUnitPrice' => $this->currency->format($product['price'], $order_info['currency_code'], $order_info['currency_value'], false),
				'itemQuantity' => $product['quantity']
			);
		}

		$merchantAssignedCustomerId = 0;
		$customerAccountCreationDate = date('Ymd');
		if ($this->customer->isLogged()) {
			$merchantAssignedCustomerId = $this->customer->getId();
			$this->load->model('account/customer');
			$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
			if($customer_info){
				$customerAccountCreationDate = date('Ymd',strtotime($customer_info['date_added']));
			}
		}

		$payload = json_encode([
			"merchantTxnId" => $this->generateRandomString(10) . "-" . $this->session->data['order_id'], // Order Increment ID
			"merchantUniqueId" => $merchantUniqueId, // Unique Random key and must be 16 digit long
			"paymentData" => array(
				"totalAmount" => $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false),
				"txnCurrency" => $order_info['currency_code'],
				"billingData" => array(
					"firstName" => html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8'),
					"lastName" => html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8'),
					"addressStreet1" => html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8'),
					"addressStreet2" => html_entity_decode($order_info['payment_address_2'], ENT_QUOTES, 'UTF-8'),
					"addressCity" => html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8'),
					"addressState" => html_entity_decode($order_info['payment_zone'], ENT_QUOTES, 'UTF-8'),
					"addressPostalCode" => html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8'),
					"addressCountry" => $order_info['payment_iso_code_2'],
					"emailId" => $order_info['email']
				)
			),
			'clientPlatformDetails' => array(
				"platformName" => 'Opencart',
				"platformVersion" => '1.5'
			),
			'riskData' => array(
				'orderData' => $prodData,
				'customerData' => array(
					'merchantAssignedCustomerId' => str_pad($merchantAssignedCustomerId, 8, '0', STR_PAD_LEFT),
					'customerAccountType' => 1,
					'customerSuccessOrderCount' => '0',
					'customerAccountCreationDate' => $customerAccountCreationDate,
					'ipAddress' => $this->get_client_ip(),
					'httpAccept' => $_SERVER['HTTP_ACCEPT'], 
					'httpUserAgent' => $_SERVER['HTTP_USER_AGENT']
				),
				'shippingData' => array(
					"firstName" => html_entity_decode($order_info['shipping_firstname'], ENT_QUOTES, 'UTF-8'),
					"lastName" => html_entity_decode($order_info['shipping_lastname'], ENT_QUOTES, 'UTF-8'),
					"addressStreet1" => html_entity_decode($order_info['shipping_address_1'], ENT_QUOTES, 'UTF-8'),
					"addressStreet2" => html_entity_decode($order_info['shipping_address_2'], ENT_QUOTES, 'UTF-8'),
					"addressCity" => html_entity_decode($order_info['shipping_city'], ENT_QUOTES, 'UTF-8'),
					"addressState" => html_entity_decode($order_info['shipping_zone'], ENT_QUOTES, 'UTF-8'),
					"addressPostalCode" => html_entity_decode($order_info['shipping_postcode'], ENT_QUOTES, 'UTF-8'),
					"addressCountry" => $order_info['shipping_iso_code_2'],
					"emailId" => $order_info['email'],
					"callingCode" => '',
					"phoneNumber" => $order_info['telephone']
				),
			),
			"merchantCallbackURL" => $this->url->link('payment/payglocal_callback', 'order_id=' .$this->session->data['order_id']) // Response url. please check response.php for response handling
		]);

		//echo "<pre>"; print_r($payload);

		openssl_sign($payload, $signature, $privateKey, OPENSSL_ALGO_SHA256);

		$sign = base64_encode($signature);
		
		$metadata = json_encode([
			"mid" => "sitestmid", // Merchant ID
			"kid" => $this->config->get('payglocal_'.$mode.'_private_kid') // Private KEy ID
		]);

		$gateway_url = $this->config->get('payglocal_'.$mode.'_gateway_url');

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $gateway_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => $payload,
			CURLOPT_HTTPHEADER => array(
				'x-gl-sign-external: ' . $sign,
				'x-gl-authn-metadata: ' . $metadata,
				'Content-Type: text/plain'
			),
		));

		$response = curl_exec($curl);

		$result = json_decode($response, true);

		curl_close($curl);

		// echo '<br>';
		// echo "Response:\n";
		// echo '<br>';
		// echo $response;
		// echo '<br>';
		// echo "Data:\n";
		// echo '<br>';
		// print_r($result);
		// echo '<br>';

		$this->data['redirectUrl'] = isset($result['data']['redirectUrl']) ? $result['data']['redirectUrl'] : '';
		$this->data['button_confirm'] = $this->language->get('button_confirm');
		$this->data['continue'] = $this->url->link('checkout/success');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/payglocal.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/payglocal.tpl';
		} else {
			$this->template = 'default/template/payment/payglocal.tpl';
		}

		$this->render();
	}

	public function generateRandomString($length = 16){
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	// Function to get the client IP address
	public function get_client_ip() {
		$ipaddress = '';
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_X_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		else if(isset($_SERVER['REMOTE_ADDR']))
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		else
			$ipaddress = 'UNKNOWN';
		return $ipaddress;
	}
}
?>