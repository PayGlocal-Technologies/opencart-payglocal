<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('session.cookie_samesite', 'None');

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\Converter\StandardConverter;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\JWSLoader;

class ControllerExtensionPaymentPayglocalCallback extends Controller {

	public function index(){

        $this->language->load('extension/payment/payglocal');

        $data['title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

        if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
            $data['base'] = HTTP_SERVER;
        } else {
            $data['base'] = HTTPS_SERVER;
        }

        $data['language'] = $this->language->get('code');
        $data['direction'] = $this->language->get('direction');

        $data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

        $data['text_response'] = $this->language->get('text_response');
        $data['text_success_wait'] = sprintf($this->language->get('text_success_wait'), $this->url->link('checkout/success'));
        $data['text_failure_wait'] = sprintf($this->language->get('text_failure_wait'), $this->url->link('checkout/cart'));

		if (empty($_POST)){
			$this->redirect($this->url->link('checkout/cart'));
		}

		$params = $_POST;

		// echo '<pre>';
		// print_r($params);
		// echo '</pre>';

		$token = $params['x-gl-token'];

		$algorithmManager = new AlgorithmManager([
			new RS256(),
		]);

		$jwsVerifier = new JWSVerifier(
			$algorithmManager
		);
		
		if($this->config->get('payment_payglocal_test') == 1){
			$jwk = JWKFactory::createFromKeyFile(
				DIR_DOWNLOAD . $this->config->get('payment_payglocal_sandbox_public_pem'),
				// The filename
				null,
				[
					'kid' => $this->config->get('payment_payglocal_sandbox_public_kid'),//Public Key KID,
					'use' => 'sig'
				]
			);
		}else{
			$jwk = JWKFactory::createFromKeyFile(
				DIR_DOWNLOAD . $this->config->get('payment_payglocal_live_public_pem'),
				// The filename
				null,
				[
					'kid' => $this->config->get('payment_payglocal_live_public_kid'),//Public Key KID,
					'use' => 'sig'
				]
			);
		}
		

		$serializerManager = new JWSSerializerManager([
			new CompactSerializer(),
		]);

		$jws = $serializerManager->unserialize($token);
		$isVerified = $jwsVerifier->verifyWithKey($jws, $jwk, 0);

		if ($isVerified) {
			$headerCheckerManager = $payload = null;

			try {
				$jwsLoader = new JWSLoader(
					$serializerManager,
					$jwsVerifier,
					$headerCheckerManager
				);
			} catch (\Exception $e) {
				throw new $e->getMessage();
			}

			$jws = $jwsLoader->loadAndVerifyWithKey($token, $jwk, $signature, $payload);

			$payload = json_decode($jws->getPayload(), true);

			// echo '<pre>';
			// print_r($payload);
			// echo '</pre>';
			// exit;

			$detailedMessage = "";
			if (isset($payload['statusUrl'])) {
				$curl = curl_init();
				curl_setopt_array($curl, array(
					CURLOPT_URL => $payload['statusUrl'],
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => '',
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 0,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => 'GET'
				));

				$response = curl_exec($curl); 

				$result = json_decode($response, true);

				curl_close($curl);

				// echo '<br>';
				// echo "Response:\n";
				// echo '<br>';
				// echo "<pre>"; print_r($result);
				$detailedMessage = isset($result['data']['detailedMessage']) ? $result['data']['detailedMessage'] : '';
			}

            $message = '';
			
			if (isset($payload['merchantTxnId'])) {
				$message .= 'merchantTxnId: ' . $payload['merchantTxnId'] . "\n";
			}

			if (isset($payload['gid'])) {
				$message .= 'GID: ' . $payload['gid'] . "\n";
			}

            if (isset($payload['status'])) {
				$message .= 'Status: ' . $payload['status'] . "\n";
			}

            $this->load->model('checkout/order');
            $merchantTxnId = isset($payload['merchantTxnId']) ? $payload['merchantTxnId'] : 0;
            //$ArrExp = explode("-", $merchantTxnId);
            //$order_id = isset($ArrExp[1]) ? (int)$ArrExp[1] : 0;
			$order_id = substr($merchantTxnId, 10);
			$failed = false;

			if (isset($payload['status']) && $payload['status'] == 'SENT_FOR_CAPTURE') {
		        //$this->model_checkout_order->confirm($order_id, $this->config->get('payment_payglocal_order_status_id'));
                $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_payglocal_order_status_id'), $message, false);
				// Save gid,status and statusUrl in database and show in order details
				// Process your order and redirect user to checkout success page

				$data['text_success'] = $detailedMessage;

                $data['continue'] = $this->url->link('checkout/success');

				$this->response->setOutput($this->load->view('extension/payment/payglocal_success', $data));
			} else {
				$failed = true;
			}
		} else {
			$failed = true;
		}

		if($failed){

			$data['text_failure'] = $detailedMessage;

			$data['continue'] = $this->url->link('checkout/cart');

			$this->response->setOutput($this->load->view('extension/payment/payglocal_failure', $data));
		}
	}
}
?>