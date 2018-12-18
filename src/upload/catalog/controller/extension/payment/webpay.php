<?php
require_once(DIR_SYSTEM . 'library/TransbankSdkWebpay.php');
require_once('libwebpay/LogHandler.php');

class ControllerExtensionPaymentWebpay extends Controller {

    private $transbankSdkWebpay = null;

    private function loadResources() {
        $this->load->language('extension/payment/webpay');
        $this->load->model('setting/setting'); //load model in: $this->model_setting_setting
        $this->load->model('localisation/order_status'); //load model in: $this->model_localisation_order_status
        $this->load->model('checkout/order'); //load model in: $this->model_checkout_order
    }

    private function getTransbankSdkWebpay() {
        $this->loadResources();
        return new TransbankSdkWebpay($this->getConfig(), new LogHandler());
    }

    private function getConfig() {
        $urlFinal = $this->url->link('extension/payment/webpay/authorize', '', 'SSL');
        $urlReturn = $this->url->link('extension/payment/webpay/authorize', '', 'SSL');
        $config = array(
            "ECOMMERCE" => "opencart",
            "MODO" => $this->config->get('payment_webpay_test_mode'),
            "PRIVATE_KEY" => $this->config->get('payment_webpay_private_key'),
            "PUBLIC_CERT" => $this->config->get('payment_webpay_public_cert'),
            "WEBPAY_CERT" => $this->config->get('payment_webpay_webpay_cert'),
            "COMMERCE_CODE" => $this->config->get('payment_webpay_commerce_code'),
            "URL_FINAL" => $urlFinal,
            "URL_RETURN" => $urlReturn,
            "VENTA_DESC" => array(
                "VD" => "Venta Deb&iacute;to",
                "VN" => "Venta Normal",
                "VC" => "Venta en cuotas",
                "SI" => "3 cuotas sin inter&eacute;s",
                "S2" => "2 cuotas sin inter&eacute;s",
                "NC" => "N cuotas sin inter&eacute;s",
            )
        );
        return $config;
    }

    public function index() {

        $this->transbankSdkOnepay = $this->getTransbankSdkWebpay();

        $config = $this->getConfig();

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $amount = (int)$order_info['total'];
        $sessionId = $this->session->data['order_id'].date('YmdHis');
        $buyOrder = $order_info['order_id'];
        $returnUrl = $config['URL_RETURN'];
        $finalUrl = $config['URL_FINAL'];

        $result = $this->transbankSdkOnepay->initTransaction($amount, $sessionId, $buyOrder, $returnUrl, $finalUrl);

        $data['url'] = $result['url'];
        $data['token_ws'] = $result['token_ws'];
        $data['button_confirm'] = $this->language->get('button_confirm');

        return $this->load->view('extension/payment/webpay', $data);
    }

    public function authorize() {

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $this->token = $this->request->post['token_ws'];
        }

        if (!isset($this->token)) {
            $result['error'] = $this->language->get('error_token');
            $this->response->setOutput($result);
        }

        $config = $this->getConfig();
        $this->transbankSdkOnepay = $this->getTransbankSdkWebpay();

        $result = $this->transbankSdkOnepay->commitTransaction($this->token);

        $order_id = $result->buyOrder;
        $order_info = $this->model_checkout_order->getOrder($order_id);
        $order_status_id = $this->config->get('config_order_status_id');
        $voucher = false;

        $this->session->data['webpay'] = json_decode(json_encode($result), true);

        if ($order_id && $order_info) {
            if (($result->VCI == "TSY" || $result->VCI == "A" || $result->VCI == "") && $result->detailOutput->responseCode == 0) {
                $voucher = true;
                $order_status_id = $this->config->get('payment_webpay_completed_order_status');
            } else {
                $order_status_id = $this->config->get('payment_webpay_rejected_order_status');
            }
        } else {
            $this->log->write($this->language->get('error_response').print_r($result, true));
        }

        if ($voucher) {
            $this->toRedirect($result->urlRedirection, array('token_ws' => $this->token));
        } else {
            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_webpay_rejected_order_status'), true);
            $this->toRedirect($this->config->get('payment_webpay_url_reject'), array(
                "token_ws" => $this->token,
                "code" => $result->detailOutput->responseCode,
                "description" => htmlentities($result->detailOutput->responseDescription),
                "fecha" => $result->transactionDate
            ));
        }
    }

    public function toRedirect($url, $data) {
        echo  "<form action='$url' method='POST' name='webpayForm'>";
        foreach ($data as $name => $value) {
            echo "<input type='hidden' name='".htmlentities($name)."' value='".htmlentities($value)."'>";
        }
        echo "</form>";
        echo "<script language='JavaScript'>"
            ."document.webpayForm.submit();"
            ."</script>";
    }

    public function finish() {

        $this->loadResources();

        $maindata = array('header', 'column_left', 'column_right', 'footer' );
        foreach ($maindata as $main) {
            $data[$main] = $this->load->controller('common/'.$main);
        }

        if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
            $data['base'] = $this->config->get('config_url');
        } else {
            $data['base'] = $this->config->get('config_ssl');
        }

        $data['language'] = $this->language->get('code');
        $data['direction'] = $this->language->get('direction');

        $data['title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

        $data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

        $data['text_success'] = $this->language->get('text_success');
        $data['text_success_wait'] = sprintf($this->language->get('text_success_wait'), $this->url->link('checkout/success', '', 'SSL'));

        $data['button_continue'] = $this->language->get('button_continue');
        $data['continue'] = $this->url->link('checkout/success');

        if (isset($this->session->data['webpay']) && isset($this->request->post['token_ws'])) {
            $webpayData = $this->session->data['webpay'];

            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        }

        if (isset($order_info) && $order_info && $webpayData["buyOrder"]) {
            $data['tbk_tipo_transaccion'] = 'Venta';
            $data['tbk_respuesta'] = "Aceptado";

            $data['tbk_nombre_comercio'] = $this->config->get('config_name');
            $data['tbk_url_comercio'] = $data['base'];
            $data['tbk_nombre_comprador'] = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
            $data['tbk_orden_compra'] = $webpayData["buyOrder"];
            $data['tbk_monto'] = $webpayData["detailOutput"]["amount"];
            $data['tbk_codigo_autorizacion'] = $webpayData["detailOutput"]["authorizationCode"];
            $data['tbk_fecha_contable'] = substr($webpayData["accountingDate"], 2, 2) . "-" . substr($webpayData["accountingDate"], 0, 2) . "-" . date('Y');
            $datetime = new DateTime($webpayData["transactionDate"]);
            $data['tbk_hora_transaccion'] = $datetime->format('H:i:s');
            $data['tbk_dia_transaccion'] = $datetime->format('d-m-Y');

            $data['tbk_final_numero_tarjeta'] = '************' . $webpayData["cardDetail"]["cardNumber"];

            $config = $this->getConfig();
            $data['tbk_tipo_pago'] = $config['VENTA_DESC'][$webpayData["detailOutput"]["paymentTypeCode"]];

            $data['tbk_tipo_cuotas'] = $webpayData["detailOutput"]["sharesNumber"];

            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_webpay_completed_order_status'), true);
        } else {
            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_webpay_canceled_order_status'), true);

            $this->response->toRedirect($this->url->link('checkout/cart'));
            return;
        }

        $this->response->setOutput($this->load->view('extension/payment/webpay_success', $data));
    }

    public function reject() {

        $this->loadResources();

        $maindata = array('header', 'column_left', 'column_right', 'footer' );
        foreach ($maindata as $main) {
            $data[$main] = $this->load->controller('common/'.$main);
        }

        if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
            $data['base'] = $this->config->get('config_url');
        } else {
            $data['base'] = $this->config->get('config_ssl');
        }

        $data['language'] = $this->language->get('code');
        $data['direction'] = $this->language->get('direction');

        $data['title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

        $data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

        $data['text_response'] = $this->language->get('text_response');

        $data['date'] = $this->request->post['fecha'];
        $data['text_razon'] = $this->request->post['description'];
        $data['text_failure'] = $this->language->get('text_failure');
        $data['text_failure_wait'] = sprintf($this->language->get('text_failure_wait'), $this->url->link('checkout/cart', '', 'SSL'));

        if (isset($this->request->post['data'])) {
            $webpayData = $this->session->data['payment_webpay'];
        }

        $data['button_continue'] = $this->language->get('button_continue');
        $data['continue'] = $this->url->link('checkout/cart');

        if (isset($this->session->data['order_id'])) {
            $data['orden_compra'] = $this->session->data['order_id'];
        } else {
            $data['orden_compra'] = $webpayData["buyOrder"];
        }
        $data['reject_time'] = date('H:i:s');
        $data['reject_data'] = date('d-m-Y');

        $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_webpay_rejected_order_status'), true);

        $this->response->setOutput($this->load->view('extension/payment/webpay_failure', $data));
    }
}
