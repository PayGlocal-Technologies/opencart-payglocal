<?php 
class ModelPaymentPayglocal extends Model {

	public function getMethod($address, $total) {

		$this->language->load('payment/payglocal');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payglocal_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if ($this->config->get('payglocal_total') > 0 && $this->config->get('payglocal_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('payglocal_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
            $base = HTTP_SERVER;
        } else {
            $base = HTTPS_SERVER;
        }

		$method_data = array();

		if ($status) {  
			$method_data = array(
				'code' => 'payglocal',
				'title' => $this->config->get('payglocal_title'),
				'sort_order' => $this->config->get('payglocal_sort_order')
			);

			//'<img src="' . $base . "admin/view/image/payment/payment">' .
		}

		return $method_data;
	}
}

?>