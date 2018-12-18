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
        $urlFinal = $this->url->link('extension/payment/webpay/callback', '', 'SSL');
        $urlReturn = $this->url->link('extension/payment/webpay/callback', '', 'SSL');
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

        $transbankSdkOnepay = $this->getTransbankSdkWebpay();

        $config = $this->getConfig();

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $amount = (int)$order_info['total'];
        $sessionId = $this->session->data['order_id'].date('YmdHis');
        $buyOrder = $order_info['order_id'];
        $returnUrl = $config['URL_RETURN'];
        $finalUrl = $config['URL_FINAL'];

        $result = $transbankSdkOnepay->initTransaction($amount, $sessionId, $buyOrder, $returnUrl, $finalUrl);

        $data['url'] = $result['url'];
        $data['token_ws'] = $result['token_ws'];
        $data['button_confirm'] = $this->language->get('button_confirm');

        $this->session->data['paymentOk'] = 'WAITING';

        return $this->load->view('extension/payment/webpay', $data);
    }

    public function callback() {

        $tokenWs = null;

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $tokenWs = isset($this->request->post['token_ws']) ? $this->request->post['token_ws'] : null;
        }

        if (!isset($tokenWs)) {
            $this->errorView();
            return;
        }

        if ($this->session->data['paymentOk'] = 'WAITING') {

            $transbankSdkOnepay = $this->getTransbankSdkWebpay();

            $result = $transbankSdkOnepay->commitTransaction($tokenWs);

            $this->session->data['result'] = $result;

            if (isset($result->buyOrder) && isset($result->detailOutput) && $result->detailOutput->responseCode == 0) {

                $this->session->data['paymentOk'] = 'SUCCESS';

                $comment = array(
                    'buyOrder' => $result->buyOrder,
                    'sessionId' => $result->sessionId,
                    'responseCode' => $result->detailOutput->responseCode,
                    'authorizationCode' => $result->detailOutput->authorizationCode,
                    'paymentTypeCode' => $result->detailOutput->paymentTypeCode,
                    'vci' => $result->VCI
                );

                $order_status = $this->config->get('payment_webpay_completed_order_status');
                $order_comments = 'Pago exitoso: ' . json_encode($comment);

                $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $order_status, true);

                $this->toRedirect($result->urlRedirection, array('token_ws' => $tokenWs));
                die();

            } else {

                $this->session->data['paymentOk'] = 'FAIL';

                $comment = $result;

                //check if was return from webpay, then use only subset data
                if (isset($result->buyOrder)) {
                    $comment = array(
                        'buyOrder' => $result->buyOrder,
                        'sessionId' => $result->sessionId,
                        'responseCode' => $result->detailOutput->responseCode,
                        'responseDescription' => $result->detailOutput->responseDescription
                    );
                }

                $order_status = $this->config->get('payment_webpay_canceled_order_status');
                $order_comments = 'Pago fallido: ' . json_encode($comment);

                $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $order_status, true);

                $this->failView($result);
            }

        } else {

            $result = $session->get('result');

            if($this->session->data['paymentOk'] == 'SUCCESS') {

                $this->successView($result);

            } else if ($this->session->data['paymentOk'] == 'FAIL') {

                $this->failView($result);
            }
        }
    }

    private function errorView() {

        $this->loadResources();

        $maindata = array('header', 'column_left', 'column_right', 'footer' );
        foreach ($maindata as $main) {
            $data[$main] = $this->load->controller('common/'.$main);
        }

        $data['text_failure'] = $this->language->get('text_failure');
        $data['text_response'] = $this->language->get('error_token');
        $data['continue'] = $this->url->link('checkout/checkout');

        $this->response->setOutput($this->load->view('extension/payment/webpay_error', $data));
    }

    private function failView($result) {

        $this->loadResources();

        $maindata = array('header', 'column_left', 'column_right', 'footer' );
        foreach ($maindata as $main) {
            $data[$main] = $this->load->controller('common/'.$main);
        }

        $data['text_failure'] = $this->language->get('text_failure');
        $data['text_response'] = $this->language->get('text_response');
        $data['orden_compra'] = $this->session->data['order_id'];
        $data['reject_time'] = date('H:i:s');
        $data['reject_date'] = date('d-m-Y');
        $data['text_razon'] = '';

        if (isset($result->buyOrder)) {
            $data['text_razon'] = htmlentities($result->detailOutput->responseDescription);
        } else {
            $data['text_razon'] = $result['error'] . ', ' . $result['detail'];
        }

        $data['continue'] = $this->url->link('checkout/checkout');

        $this->response->setOutput($this->load->view('extension/payment/webpay_failure', $data));
    }

    private function successView($result) {

        $this->loadResources();

        $maindata = array('header', 'column_left', 'column_right', 'footer' );
        foreach ($maindata as $main) {
            $data[$main] = $this->load->controller('common/'.$main);
        }

        $config = $this->getConfig();

        $data['title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));
        $data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));
        $data['text_success'] = $this->language->get('text_success');
        $data['text_success_wait'] = sprintf($this->language->get('text_success_wait'), $this->url->link('checkout/success', '', 'SSL'));
        $data['continue'] = $this->url->link('checkout/success');

        $data['tbk_respuesta'] = "Aceptado";
        $data['tbk_orden_compra'] = $result->buyOrder;
        $data['tbk_codigo_autorizacion'] = $result->detailOutput->authorizationCode;
        $datetime = new DateTime($result->transactionDate);
        $data['tbk_hora_transaccion'] = $datetime->format('H:i:s');
        $data['tbk_dia_transaccion'] = $datetime->format('d-m-Y');
        $data['tbk_final_numero_tarjeta'] = '************' . $result->cardDetail->cardNumber;
        $data['tbk_tipo_pago'] = $config['VENTA_DESC'][$result->detailOutput->paymentTypeCode];
        $data['tbk_monto'] = $result->detailOutput->amount;
        $data['tbk_tipo_cuotas'] = $result->detailOutput->sharesNumber;

        $this->response->setOutput($this->load->view('extension/payment/webpay_success', $data));
    }

    private function toRedirect($url, $data) {
        echo  "<form action='$url' method='POST' name='webpayForm'>";
        foreach ($data as $name => $value) {
            echo "<input type='hidden' name='".htmlentities($name)."' value='".htmlentities($value)."'>";
        }
        echo "</form>";
        echo "<script language='JavaScript'>"
            ."document.webpayForm.submit();"
            ."</script>";
    }
    /*
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
    }*/
}
