<?php
class ModelExtensionPaymentWebpay extends Model {

	public function getMethod($address, $total) {

        $this->load->language('extension/payment/webpay');
        $this->load->model('setting/setting');

        $status = false;

		if (intval($total) > 0) {
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
