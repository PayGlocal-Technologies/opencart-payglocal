<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
ini_set('session.cookie_samesite', 'None');

class ControllerPaymentPayglocalCallback extends Controller {

	public function index(){

		if($this->config->get('payglocal_test')){
			$mode = 'sandbox';
			$site = 'dev';
		}else{
			$mode = 'live';
			$site = 'live';
		}

        $this->language->load('payment/payglocal');

        $this->data['title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

        if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
            $this->data['base'] = HTTP_SERVER;
        } else {
            $this->data['base'] = HTTPS_SERVER;
        }

        $this->data['language'] = $this->language->get('code');
        $this->data['direction'] = $this->language->get('direction');

        $this->data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

        $this->data['text_response'] = $this->language->get('text_response');
        $this->data['text_success_wait'] = sprintf($this->language->get('text_success_wait'), $this->url->link('checkout/success'));
        $this->data['text_failure_wait'] = sprintf($this->language->get('text_failure_wait'), $this->url->link('checkout/cart'));

		if (empty($_POST)){
			$this->redirect($this->url->link('checkout/cart'));
		}
		
		$token = $_POST['x-gl-token'];

		$data = explode('.', $token);

		$payload = base64_decode($data[1]);

		$response = json_decode($payload, true);

		// echo '<pre>';
		// print_r($response);
		// echo '</pre>';

		$fp = fopen(DIR_DOWNLOAD . $this->config->get('payglocal_'.$mode.'_private_pem'), "r");
		//Private Key path
		$priv_key = fread($fp, 8192);
		fclose($fp);
		$privateKey = openssl_get_privatekey($priv_key);
		
		$difPayload = '/gl/v1/payments/' . $response['merchantUniqueId'] . '/status';
		
		openssl_sign($difPayload, $signature, $privateKey, OPENSSL_ALGO_SHA256);
		
		$sign = base64_encode($signature);
		$metadata = json_encode([
			"mid" => "sitestmid", // Merchant ID
			"kid" => $this->config->get('payglocal_'.$mode.'_private_kid') // Private KEy ID
		]);

		$curl = curl_init();

		$url = 'https://api.'.$site.'.payglocal.in/gl/v1/payments/' . $response['merchantUniqueId'] . '/status';

		$detailedMessage = "";
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_HTTPHEADER => array(
				'x-gl-sign-external: ' . $sign,
				'x-gl-authn-metadata: ' . $metadata,
				'Content-Type: text/plain'
			),
		));

		$statusResponse = curl_exec($curl);

		$statusData = json_decode($statusResponse, true);

		$log = new Log('payglocal.log');
		$log->write('Response: ' . $statusResponse);

		curl_close($curl);

		// echo '<pre>';
		// print_r($statusData);
		// echo '</pre>';
		
		$detailedMessage = "";
		if (isset($response['statusUrl']) && !empty($response['statusUrl'])) {
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $response['statusUrl'],
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'GET'
			));

			$resp = curl_exec($curl); 

			$result = json_decode($resp, true);

			curl_close($curl);

			// echo '<br>';
			// echo "Response:\n";
			// echo '<br>';
			// echo "<pre>"; print_r($result);
			$detailedMessage = isset($result['data']['detailedMessage']) ? $result['data']['detailedMessage'] : '';
		}
		//echo $statusData['status']; exit;
		if (isset($statusData['status']) && $statusData['status'] == 'SENT_FOR_CAPTURE') {
			$message = '';
			
			if (isset($response['merchantTxnId'])) {
				$message .= 'merchantTxnId: ' . $response['merchantTxnId'] . "\n";
			}

			if (isset($response['gid'])) {
				$message .= 'GID: ' . $response['gid'] . "\n";
			}

			if (isset($response['status'])) {
				$message .= 'Status: ' . $response['status'] . "\n";
			}
			//echo $message;

			$this->load->model('checkout/order');
			$merchantTxnId = isset($response['merchantTxnId']) ? $response['merchantTxnId'] : 0;
			$ArrExp = explode("-", $merchantTxnId);
			
			$order_id = isset($ArrExp[1]) ? (int)$ArrExp[1] : 0;

			$this->model_checkout_order->confirm($order_id, $this->config->get('payglocal_order_status_id'));
			$this->model_checkout_order->update($order_id, $this->config->get('payglocal_order_status_id'), $message, false);
			// Save gid,status and statusUrl in database and show in order details
			// Process your order and redirect user to checkout success page

			$this->data['text_success'] = $detailedMessage;

			$this->data['continue'] = $this->url->link('checkout/success');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/payglocal_success.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/payment/payglocal_success.tpl';
			} else {
				$this->template = 'default/template/payment/payglocal_success.tpl';
			}

			$this->response->setOutput($this->render());

		} else {
			//$error = 'There is a processing error with your transaction ' . $statusData['status'];
			// Order is not completed because order status is not SENT_FOR_CAPTURE and redirect user to cart page

			$this->data['text_failure'] = $detailedMessage;

			$this->data['continue'] = $this->url->link('checkout/cart');

            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/payglocal_failure.tpl')) {
                $this->template = $this->config->get('config_template') . '/template/payment/payglocal_failure.tpl';
            } else {
                $this->template = 'default/template/payment/payglocal_failure.tpl';
            }

            $this->response->setOutput($this->render());
		}
	}
}
?>