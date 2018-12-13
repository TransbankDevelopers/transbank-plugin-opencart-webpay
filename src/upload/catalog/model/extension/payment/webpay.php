<?php
class ModelExtensionPaymentWebpay extends Model {

	public function getMethod($address, $total) {

		$this->load->language('extension/payment/webpay');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_webpay_geo_zone') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        $status = false;

		if (intval($total) > 0) {
			$status = true;
		} else if (!$this->config->get('payment_webpay_geo_zone')) {
			$status = true;
		} else if ($query->num_rows) {
			$status = true;
		}

		$method_data = array();

		if ($status) {
			$method_data = array(
				'code' => 'webpay',
				'title' => $this->language->get('text_title'),
				'terms' => '',
				'sort_order' => $this->config->get('payment_webpay_sort_order')
			);
		}

		return $method_data;
	}
}
