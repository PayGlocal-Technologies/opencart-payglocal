<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require DIR_SYSTEM . 'vendor/autoload.php';

use Jose\Component\Encryption\Algorithm\ContentEncryption\A128CBCHS256;
use Jose\Component\Encryption\Algorithm\KeyEncryption\RSAOAEP256;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\Converter\StandardConverter;
use Jose\Component\Encryption\Algorithm\KeyEncryption\A256KW;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A256CBCHS512;
use Jose\Component\Encryption\Compression\CompressionMethodManager;
use Jose\Component\Encryption\Compression\Deflate;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Core\JWK;
use Jose\Component\Encryption\Serializer\CompactSerializer;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer as SigCompactSerializer;
use Jose\Component\Signature\Algorithm\RS256;

class ControllerExtensionPaymentPayglocal extends Controller {

	private $error = array(); 

	public function index() { 

		$this->language->load('extension/payment/payglocal');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payglocal', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_edit'] = $this->language->get('text_edit');
		$data['entry_order_status'] = $this->language->get('entry_order_status');		
		$data['entry_total'] = $this->language->get('entry_total');	
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_test'] = $this->language->get('entry_test');
		$data['entry_title'] = $this->language->get('entry_title');
		$data['entry_sandbox_merchant_id'] = $this->language->get('entry_sandbox_merchant_id');
		$data['entry_sandbox_public_kid'] = $this->language->get('entry_sandbox_public_kid');
		$data['entry_sandbox_private_kid'] = $this->language->get('entry_sandbox_private_kid');
		$data['entry_sandbox_public_pem'] = $this->language->get('entry_sandbox_public_pem');
		$data['entry_sandbox_private_pem'] = $this->language->get('entry_sandbox_private_pem');
		$data['entry_live_merchant_id'] = $this->language->get('entry_live_merchant_id');
		$data['entry_live_public_kid'] = $this->language->get('entry_live_public_kid');
		$data['entry_live_private_kid'] = $this->language->get('entry_live_private_kid');
		$data['entry_live_public_pem'] = $this->language->get('entry_live_public_pem');
		$data['entry_live_private_pem'] = $this->language->get('entry_live_private_pem');
		$data['entry_gateway_url'] = $this->language->get('entry_gateway_url');
		$data['entry_refund_url'] = $this->language->get('entry_refund_url');
		$data['entry_refund_required'] = $this->language->get('entry_refund_required');
		$data['help_total'] = $this->language->get('help_total');
		$data['button_upload'] = $this->language->get('button_upload');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['token'] = $this->session->data['token'];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true),
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true),
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/payglocal', 'token=' . $this->session->data['token'], true),
		);

		$data['action'] = $this->url->link('extension/payment/payglocal', 'token=' . $this->session->data['token'], true);

		$data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'] . '&type=payment', true);
		
		if (isset($this->request->post['payglocal_total'])) {
			$data['payglocal_total'] = $this->request->post['payglocal_total'];
		} else {
			$data['payglocal_total'] = $this->config->get('payglocal_total'); 
		}

		if (isset($this->request->post['payglocal_status'])) {
			$data['payglocal_status'] = $this->request->post['payglocal_status'];
		} else {
			$data['payglocal_status'] = $this->config->get('payglocal_status'); 
		}

		if (isset($this->request->post['payglocal_test'])) {
			$data['payglocal_test'] = $this->request->post['payglocal_test'];
		} else {
			$data['payglocal_test'] = $this->config->get('payglocal_test'); 
		}

		if (isset($this->request->post['payglocal_title'])) {
			$data['payglocal_title'] = $this->request->post['payglocal_title'];
		} else {
			$data['payglocal_title'] = $this->config->get('payglocal_title'); 
		}

		if (isset($this->request->post['payglocal_sandbox_merchant_id'])) {
			$data['payglocal_sandbox_merchant_id'] = $this->request->post['payglocal_sandbox_merchant_id'];
		} else {
			$data['payglocal_sandbox_merchant_id'] = $this->config->get('payglocal_sandbox_merchant_id'); 
		}

		if (isset($this->request->post['payglocal_sandbox_public_kid'])) {
			$data['payglocal_sandbox_public_kid'] = $this->request->post['payglocal_sandbox_public_kid'];
		} else {
			$data['payglocal_sandbox_public_kid'] = $this->config->get('payglocal_sandbox_public_kid'); 
		}

		if (isset($this->request->post['payglocal_sandbox_private_kid'])) {
			$data['payglocal_sandbox_private_kid'] = $this->request->post['payglocal_sandbox_private_kid'];
		} else {
			$data['payglocal_sandbox_private_kid'] = $this->config->get('payglocal_sandbox_private_kid'); 
		}

		if (isset($this->request->post['payglocal_sandbox_public_pem'])) {
			$data['payglocal_sandbox_public_pem'] = $this->request->post['payglocal_sandbox_public_pem'];
		} else {
			$data['payglocal_sandbox_public_pem'] = $this->config->get('payglocal_sandbox_public_pem'); 
		}

		if (isset($this->request->post['payglocal_sandbox_private_pem'])) {
			$data['payglocal_sandbox_private_pem'] = $this->request->post['payglocal_sandbox_private_pem'];
		} else {
			$data['payglocal_sandbox_private_pem'] = $this->config->get('payglocal_sandbox_private_pem'); 
		}

		if (isset($this->request->post['payglocal_sandbox_gateway_url'])) {
			$data['payglocal_sandbox_gateway_url'] = $this->request->post['payglocal_sandbox_gateway_url'];
		} else {
			$data['payglocal_sandbox_gateway_url'] = $this->config->get('payglocal_sandbox_gateway_url'); 
		}

		if (isset($this->request->post['payglocal_sandbox_refund_url'])) {
			$data['payglocal_sandbox_refund_url'] = $this->request->post['payglocal_sandbox_refund_url'];
		} else {
			$data['payglocal_sandbox_refund_url'] = $this->config->get('payglocal_sandbox_refund_url'); 
		}

		if (isset($this->request->post['payglocal_live_merchant_id'])) {
			$data['payglocal_live_merchant_id'] = $this->request->post['payglocal_live_merchant_id'];
		} else {
			$data['payglocal_live_merchant_id'] = $this->config->get('payglocal_live_merchant_id'); 
		}

		if (isset($this->request->post['payglocal_live_public_kid'])) {
			$data['payglocal_live_public_kid'] = $this->request->post['payglocal_live_public_kid'];
		} else {
			$data['payglocal_live_public_kid'] = $this->config->get('payglocal_live_public_kid'); 
		}

		if (isset($this->request->post['payglocal_live_private_kid'])) {
			$data['payglocal_live_private_kid'] = $this->request->post['payglocal_live_private_kid'];
		} else {
			$data['payglocal_live_private_kid'] = $this->config->get('payglocal_live_private_kid'); 
		}

		if (isset($this->request->post['payglocal_live_gateway_url'])) {
			$data['payglocal_live_gateway_url'] = $this->request->post['payglocal_live_gateway_url'];
		} else {
			$data['payglocal_live_gateway_url'] = $this->config->get('payglocal_live_gateway_url'); 
		}

		if (isset($this->request->post['payglocal_live_refund_url'])) {
			$data['payglocal_live_refund_url'] = $this->request->post['payglocal_live_refund_url'];
		} else {
			$data['payglocal_live_refund_url'] = $this->config->get('payglocal_live_refund_url'); 
		}

		if (isset($this->request->post['payglocal_refund'])) {
			$data['payglocal_refund'] = $this->request->post['payglocal_refund'];
		} else {
			$data['payglocal_refund'] = $this->config->get('payglocal_refund'); 
		}
		
		if (isset($this->request->post['payglocal_order_status_id'])) {
			$data['payglocal_order_status_id'] = $this->request->post['payglocal_order_status_id'];
		} else {
			$data['payglocal_order_status_id'] = $this->config->get('payglocal_order_status_id'); 
		} 

		$this->load->model('localisation/order_status');
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payglocal_geo_zone_id'])) {
			$data['payglocal_geo_zone_id'] = $this->request->post['payglocal_geo_zone_id'];
		} else {
			$data['payglocal_geo_zone_id'] = $this->config->get('payglocal_geo_zone_id'); 
		} 

		$this->load->model('localisation/geo_zone');						

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payglocal_status'])) {
			$data['payglocal_status'] = $this->request->post['payglocal_status'];
		} else {
			$data['payglocal_status'] = $this->config->get('payglocal_status');
		}

		if (isset($this->request->post['payglocal_sort_order'])) {
			$data['payglocal_sort_order'] = $this->request->post['payglocal_sort_order'];
		} else {
			$data['payglocal_sort_order'] = $this->config->get('payglocal_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');

		$data['column_left'] = $this->load->controller('common/column_left');

		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/payglocal', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/payglocal')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}

	public function upload() {

		$this->language->load('sale/order');

		$json = array();

		if (!$this->user->hasPermission('modify', 'catalog/download')) {
			$json['error'] = $this->language->get('error_permission');
		}
		
		$name = $this->request->get['name'];

		if (!isset($json['error'])) {
			if (!empty($this->request->files[$name]['name'])) {
				$filename = basename(html_entity_decode($this->request->files[$name]['name'], ENT_QUOTES, 'UTF-8'));

				if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 128)) {
					$json['error'] = $this->language->get('error_filename');
				}
				
				// Check to see if any PHP files are trying to be uploaded
				$content = file_get_contents($this->request->files[$name]['tmp_name']);

				if (preg_match('/\<\?php/i', $content)) {
					$json['error'] = $this->language->get('error_filetype');
				}

				if ($this->request->files[$name]['error'] != UPLOAD_ERR_OK) {
					$json['error'] = $this->language->get('error_upload_' . $this->request->files[$name]['error']);
				}
			} else {
				$json['error'] = $this->language->get('error_upload');
			}
		}

		if (!isset($json['error'])) {
			if (is_uploaded_file($this->request->files[$name]['tmp_name']) && file_exists($this->request->files[$name]['tmp_name'])) {
				$json['filename'] = $filename;
				move_uploaded_file($this->request->files[$name]['tmp_name'], DIR_DOWNLOAD . $filename);
			}

			$json['success'] = $this->language->get('text_upload');
		}

		$this->response->setOutput(json_encode($json));

	}

	public function refund(){

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

		$merchantUniqueId = $this->generateRandomString(16);

		if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
            $base = HTTP_SERVER;
        } else {
            $base = HTTPS_SERVER;
        }

		$this->load->model('sale/order');

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		$order_info = $this->model_sale_order->getOrder($order_id);
		//echo "<pre>"; print_r($order_info);exit;
		$page = 1;
		$histories = array();

		$results = $this->model_sale_order->getOrderHistories($order_id, ($page - 1) * 10, 10);
		$merchantTxnId = $gid = '';
		foreach ($results as $result) {
			if($result['status'] == 'Complete' && !empty($result['comment'])){
				$comment = nl2br($result['comment']);
				$array = explode(PHP_EOL, $result['comment']);
				$expl = explode(":", $array[0]);
				if(isset($expl[0]) && $expl[0] == 'merchantTxnId'){
					$merchantTxnId = isset($expl[1]) ? trim($expl[1]) : '';
				}
				$expl = explode(":", $array[1]);
				if(isset($expl[0]) && $expl[0] == 'GID'){
					$gid = isset($expl[1]) ? trim($expl[1]) : '';
				}
			}
		}
		
		$payload = json_encode([
			"merchantTxnId" => $merchantTxnId,
			"merchantUniqueId" => $merchantUniqueId,
			"refundType" => "F",
			"paymentData" => array(
				"totalAmount" => $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false),
				"txnCurrency" => $order_info['currency_code']
			),
			"merchantCallbackURL" => $base . 'index.php?route=payment/payglocal_callback' // this is our response url. Please check response.php
		]);
		
		$jwe = $jweBuilder
			->create()              // We want to create a new JWE
			->withPayload($payload) // We set the payload
			->withSharedProtectedHeader($header)
			->addRecipient($key)
			->build();
		
		
		$serializer = new CompactSerializer(); // The serializer
		$token = $serializer->serialize($jwe, 0); // We serialize the recipient at index 0 (we only have one recipient).
		
		//echo "JWE Token:\n" . print_r($token, true) . "\n";
		//echo '<br>';
		
		$algorithmManager = new AlgorithmManager([
			new RS256(),
		]);
		
		$jwsBuilder = new JWSBuilder(
			$algorithmManager
		);
		
		if($this->config->get('payglocal_test') == 1){
			$jwskey = JWKFactory::createFromKeyFile(
				DIR_DOWNLOAD . $this->config->get('payglocal_sandbox_private_pem'),
				// The filename
				null,
				[
					'kid' => $this->config->get('payglocal_sandbox_private_kid'),
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
		
		//echo '<br>';
		//print_r($hashedPayload) . "\n";
		//echo '<br>';
		
		$jwspayload = json_encode([
			'digest' => $hashedPayload,
			'digestAlgorithm' => "SHA-256",
			'exp' => 300000,
			'iat' => (string)round(microtime(true) * 1000)
		]);
		
		$jws = $jwsBuilder
			->create()              	// We want to create a new JWS
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
			$refund_url = str_replace("{{gid}}", $gid, $this->config->get('payglocal_sandbox_refund_url'));
		}else{
			$refund_url = str_replace("{{gid}}", $gid, $this->config->get('payglocal_live_refund_url'));
		}
		
		$curl = curl_init();
		
		curl_setopt_array($curl, array(
			CURLOPT_URL => $refund_url, //'https://api.dev.payglocal.in/gl/v1/payments/gl_a64a176f-9bac-4170-950d-4dc625a35ca9/refund',
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
		$data = json_decode($response, true);
		curl_close($curl);
		
		//echo '<br>';
		//echo "Response:\n";
		//echo '<br>';
		//echo $response;
		//echo '<br>';
		//echo "Data:\n";
		// echo '<br>';
		// print_r($data);
		// echo '<br>';

		// $json['gid'] = $data['gid'];
		// $json['status'] = $data['status'];
		// $json['message'] = $data['message'];

		// foreach(isset($data['errors']) as $key => $error){
		// 	$json['error'][$key] = $error;
		// }
		
		$this->response->setOutput(json_encode($data));
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
}
?>