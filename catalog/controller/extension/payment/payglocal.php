<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('session.cookie_samesite', 'None');

require DIR_SYSTEM . 'vendor/autoload.php';

use Jose\Component\Encryption\Algorithm\ContentEncryption\A128CBCHS256;
use Jose\Component\Encryption\Algorithm\KeyEncryption\RSAOAEP256;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\Converter\StandardConverter;
use Jose\Component\Encryption\Compression\CompressionMethodManager;
use Jose\Component\Encryption\Compression\Deflate;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\Serializer\CompactSerializer;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Algorithm\RS256;

class ControllerExtensionPaymentPayglocal extends Controller {
	public function index() {

		$this->language->load('extension/payment/payglocal');

		$keyEncryptionAlgorithmManager = new AlgorithmManager([
			new RSAOAEP256(),
		]);

		$contentEncryptionAlgorithmManager = new AlgorithmManager([
			new A128CBCHS256(),
		]);

		$compressionMethodManager = new CompressionMethodManager([
			new Deflate(),
		]);

		$jweBuilder = new JWEBuilder(
			$keyEncryptionAlgorithmManager,
			$contentEncryptionAlgorithmManager,
			$compressionMethodManager
		);

		if($this->config->get('payglocal_test') == 1){
			$key = JWKFactory::createFromKeyFile(
				DIR_DOWNLOAD . $this->config->get('payglocal_sandbox_public_pem'),
				// The filename
				null,
				[
					'kid' => $this->config->get('payglocal_sandbox_public_kid'),//Public Key KID
					'use' => 'enc',
					'alg' => 'RSA-OAEP-256',
				]
			);
			
			//print_r($key);
			
			$header = [
				'issued-by' => 'magentodemo',//Merchant ID
				'enc' => 'A128CBC-HS256',
				'exp' => 30000,
				'iat' => (string)round(microtime(true) * 1000),
				'alg' => 'RSA-OAEP-256',
				'kid' => $this->config->get('payglocal_sandbox_public_kid')//Public Key KID
			];
		}else{
			$key = JWKFactory::createFromKeyFile(
				DIR_DOWNLOAD . $this->config->get('payglocal_live_public_pem'),
				// The filename
				null,
				[
					'kid' => $this->config->get('payglocal_live_public_kid'),//Public Key KID
					'use' => 'enc',
					'alg' => 'RSA-OAEP-256',
				]
			);

			$header = [
				'issued-by' => $this->config->get('payglocal_live_merchant_id'),//Merchant ID
				'enc' => 'A128CBC-HS256',
				'exp' => 30000,
				'iat' => (string)round(microtime(true) * 1000),
				'alg' => 'RSA-OAEP-256',
				'kid' => $this->config->get('payglocal_live_public_kid')//Public Key KID
			];
		}

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

		$merchantUniqueId = $this->generateRandomString(16);

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
				"platformVersion" => '2.x'
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
			"merchantCallbackURL" => $this->url->link('extension/payment/payglocal_callback', 'order_id=' .$this->session->data['order_id']) // Response url. please check response.php for response handling
		]);

		//echo "<pre>"; print_r($payload);exit;

		$jwe = $jweBuilder
			->create()              // We want to create a new JWE
			->withPayload($payload) // We set the payload
			->withSharedProtectedHeader($header)
			->addRecipient($key)
			->build();

		$serializer = new CompactSerializer(); // The serializer
		$token = $serializer->serialize($jwe, 0); // We serialize the recipient at index 0 (we only have one recipient).

		// echo "JWE Token:\n" . print_r($token, true) . "\n";
		// echo '<br>';

		$algorithmManager = new AlgorithmManager([
			new RS256(),
		]);

		$jwsBuilder = new JWSBuilder(
			$algorithmManager
		);

		if($this->config->get('payglocal_test') == 1){
			$jwskey = JWKFactory::createFromKeyFile(
				DIR_DOWNLOAD . '4969a3d1-c840-4e05-8320-408a785c3e5f_magentodemo.pem',
				// The filename
				null,
				[
					'kid' => '4969a3d1-c840-4e05-8320-408a785c3e5f',
					'use' => 'sig'
					//'alg' => 'RSA-OAEP-256',
				]
			);
			
			$jwsheader = [
				'issued-by' => 'magentodemo', // Merchant ID
				'is-digested' => 'true',
				'alg' => 'RS256',
				'x-gl-enc' => 'true',
				'x-gl-merchantId' => 'magentodemo',
				'kid' => '4969a3d1-c840-4e05-8320-408a785c3e5f'
			];
		}else{
			$jwskey = JWKFactory::createFromKeyFile(
				DIR_DOWNLOAD . $this->config->get('payglocal_live_private_pem'),
				// The filename
				null,
				[
					'kid' => $this->config->get('payglocal_live_public_kid'),
					'use' => 'sig'
					//'alg' => 'RSA-OAEP-256',
				]
			);

			$jwsheader = [
				'issued-by' => $this->config->get('payglocal_live_merchant_id'),//Merchant ID
				'is-digested' => 'true',
				'alg' => 'RS256',
				'x-gl-enc' => 'true',
				'x-gl-merchantId' => $this->config->get('payglocal_live_merchant_id'),
				'kid' => $this->config->get('payglocal_live_public_kid')
			];
		}

		$hashedPayload = base64_encode(hash('sha256', $token, $BinaryOutputMode = true));

		// echo '<br>';
		// print_r($hashedPayload) . "\n";
		// echo '<br>';

		$jwspayload = json_encode([
			'digest' => $hashedPayload,
			'digestAlgorithm' => "SHA-256",
			'exp' => 300000,
			'iat' => (string)round(microtime(true) * 1000)
		]);

		$jws = $jwsBuilder
			->create()              // We want to create a new JWS
			->withPayload($jwspayload) // We set the payload
			->addSignature($jwskey, $jwsheader)
			->build();

		//print_r($jws);

		$jwsserializer = new \Jose\Component\Signature\Serializer\CompactSerializer(); // The serializer
		$jwstoken = $jwsserializer->serialize($jws, 0); // We serialize the recipient at index 0 (we only have one recipient).

		//echo '<br>';
		//echo "JWSToken:\n" . print_r($jwstoken, true) . "\n";
		//echo '<br>';

		if($this->config->get('payglocal_test') == 1){
			$gateway_url = $this->config->get('payglocal_sandbox_gateway_url');
		}else{
			$gateway_url = $this->config->get('payglocal_live_gateway_url');
		}

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
			CURLOPT_POSTFIELDS => $token,
			CURLOPT_HTTPHEADER => array(
				'x-gl-token-external: ' . $jwstoken,
				'Content-Type: text/plain'
			),
		));

		$response = curl_exec($curl);

		$result = json_decode($response, true);

		curl_close($curl);

		// echo '<br>';
		// echo '<pre>';
		// echo "Response:\n";
		// echo '<br>';
		// echo $response;
		// echo '<br>';
		// echo "Data:\n";
		// echo '<br>';
		// print_r($result);
		// echo '<br>';

		$data['redirectUrl'] = isset($result['data']['redirectUrl']) ? $result['data']['redirectUrl'] : '';
		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['continue'] = $this->url->link('checkout/success');

		return $this->load->view('extension/payment/payglocal', $data);
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