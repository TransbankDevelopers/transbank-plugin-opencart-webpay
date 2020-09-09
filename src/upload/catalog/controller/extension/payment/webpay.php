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
        return new TransbankSdkWebpay($this->getConfig(), new LogHandler());
    }

    private function getConfig() {
        $config = array(
            "ECOMMERCE" => "opencart",
            "MODO" => $this->config->get('payment_webpay_test_mode'),
            "PRIVATE_KEY" => $this->config->get('payment_webpay_private_key'),
            "PUBLIC_CERT" => $this->config->get('payment_webpay_public_cert'),
            "COMMERCE_CODE" => $this->config->get('payment_webpay_commerce_code'),
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

        $this->loadResources();

        $transbankSdk = $this->getTransbankSdkWebpay();

        $config = $this->getConfig();

        foreach ($this->cart->getProducts() as $product) {
            $itemsId[] = $product['product_id'];
        }

        $orderId = $this->session->data['order_id'];
        $orderInfo = $this->model_checkout_order->getOrder($orderId);
        $amount = intval($orderInfo['total']);
        $sessionId = $this->session->data['order_id'].date('YmdHis');

        //patch for error with parallels carts
        $dataPaymentHash = $amount . $orderId. json_encode($itemsId);
        $paymentHash = md5($dataPaymentHash);

        $url = $this->url->link('extension/payment/webpay/callback', '', 'SSL');
        $url .= (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . ('ph_=' . $paymentHash);
        $url .= '&session_id=' . $this->session->getId();
        $returnUrl = $url;
        $finalUrl = $url;

        $result = $transbankSdk->initTransaction($amount, $sessionId, $orderId, $returnUrl, $finalUrl);

        $data['url'] = $result['url'];
        $data['token_ws'] = $result['token_ws'];
        $data['button_confirm'] = $this->language->get('button_confirm');

        $this->session->data['paymentOk'] = 'WAITING';

        return $this->load->view('extension/payment/webpay', $data);
    }

    public function callback() {

        $sessionId = $_GET['session_id'];
        $this->session->start($sessionId);

        $this->loadResources();

        $tokenWs = null;

        $orderId = $this->session->data['order_id'];

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $tokenWs = isset($this->request->post['token_ws']) ? $this->request->post['token_ws'] : null;
        }

        if (!isset($tokenWs)) {

            $comment = array(
                'error' => 'Compra cancelada',
                'detail' => 'El token no ha sido enviado'
            );

            $orderStatusId = $this->config->get('payment_webpay_canceled_order_status');
            $orderComment = 'Pago cancelado: ' . json_encode($comment);
            $orderNotifyToUser = false;

            $this->model_checkout_order->addOrderHistory($orderId, $orderStatusId, $orderComment, $orderNotifyToUser);

            $this->errorView('error_token');
            return;
        }

        foreach ($this->cart->getProducts() as $product) {
            $itemsId[] = $product['product_id'];
        }

        $orderInfo = $this->model_checkout_order->getOrder($orderId);
        $amount = intval($orderInfo['total']);

        //patch for error with parallels carts
        $dataPaymentHash = $amount . $orderId. json_encode($itemsId);
        $paymentHash = md5($dataPaymentHash);
        $dataPaymentHashOriginal = $_GET['ph_'];

        //patch for error with parallels carts
        if ($dataPaymentHashOriginal != $paymentHash) {

            $comment = array(
                'error' => 'Error en el pago',
                'detail' => 'Carro inválido'
            );

            $orderStatusId = $this->config->get('payment_webpay_canceled_order_status');
            $orderComment = 'Pago cancelado: ' . json_encode($comment);
            $orderNotifyToUser = false;

            $this->model_checkout_order->addOrderHistory($orderId, $orderStatusId, $orderComment, $orderNotifyToUser);

            $this->errorView('error_invalid_cart');
            return;
        }

        if ($this->session->data['paymentOk'] == 'WAITING') {

            $transbankSdk = $this->getTransbankSdkWebpay();

            $result = $transbankSdk->commitTransaction($tokenWs);

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

                $orderStatusId = $this->config->get('payment_webpay_completed_order_status');
                $orderComment = 'Pago exitoso: ' . json_encode($comment);
                $orderNotifyToUser = true;

                $this->model_checkout_order->addOrderHistory($orderId, $orderStatusId, $orderComment, $orderNotifyToUser);

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

                $orderStatusId = $this->config->get('payment_webpay_rejected_order_status');
                $orderComment = 'Pago fallido: ' . json_encode($comment);
                $orderNotifyToUser = true;

                $this->model_checkout_order->addOrderHistory($orderId, $orderStatusId, $orderComment, $orderNotifyToUser);

                $rejectText = '';

                if (isset($result->buyOrder)) {
                    $rejectText = htmlentities($result->detailOutput->responseDescription);
                } else {
                    $rejectText = $result['error'] . ', ' . $result['detail'];
                }

                $this->session->data['reject_text'] = $rejectText;
                $this->session->data['reject_date'] = date('d-m-Y');
                $this->session->data['reject_time'] = date('H:i:s');

                $this->failView();
            }

        } else {

            if($this->session->data['paymentOk'] == 'SUCCESS') {

                $this->successView();

            } else if ($this->session->data['paymentOk'] == 'FAIL') {

                $this->failView();
            }
        }
    }

    private function errorView($error_id = 'error_token') {

        $this->loadResources();

        $maindata = array('header', 'column_left', 'column_right');
        foreach ($maindata as $main) {
            $data[$main] = $this->load->controller('common/'.$main);
        }

        $data['text_failure'] = $this->language->get('text_failure');
        $data['text_response'] = $this->language->get($error_id);
        $data['continue'] = $this->url->link('checkout/checkout');

        $this->response->setOutput($this->load->view('extension/payment/webpay_error', $data));
    }

    private function failView() {

        $this->loadResources();

        $maindata = array('header', 'column_left', 'column_right');
        foreach ($maindata as $main) {
            $data[$main] = $this->load->controller('common/'.$main);
        }

        $data['text_failure'] = $this->language->get('text_failure');
        $data['text_response'] = $this->language->get('text_response');
        $data['order_id'] = $this->session->data['order_id'];
        $data['reject_date'] = $this->session->data['reject_date'];
        $data['reject_time'] = $this->session->data['reject_time'];
        $data['reject_text'] = $this->session->data['reject_text'];
        $data['continue'] = $this->url->link('checkout/checkout');

        $this->response->setOutput($this->load->view('extension/payment/webpay_fail', $data));
    }

    private function successView() {

        $result = $this->session->data['result'];

        if (!is_array($result)) {
            $result = json_decode(json_encode($result));
        }

        $this->loadResources();

        $config = $this->getConfig();

        if($result['detailOutput']['paymentTypeCode'] == "SI" || $result['detailOutput']['paymentTypeCode'] == "S2" ||
            $result['detailOutput']['paymentTypeCode'] == "NC" || $result['detailOutput']['paymentTypeCode'] == "VC" ) {
            $installmentType = $config['VENTA_DESC'][$result['detailOutput']['paymentTypeCode']];
        } else {
            $installmentType = "Sin cuotas";
        }

        if($result['detailOutput']['paymentTypeCode'] == "VD"){
            $paymentType = "Débito";
        } else {
            $paymentType = "Crédito";
        }

        $data['title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));
        $data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));
        $data['text_success'] = $this->language->get('text_success');
        $data['text_success_wait'] = sprintf($this->language->get('text_success_wait'), $this->url->link('checkout/success', '', 'SSL'));
        $data['continue'] = $this->url->link('checkout/success');

        $data['tbk_respuesta'] = "Aceptado";
        $data['tbk_orden_compra'] = $result['buyOrder'];
        $data['tbk_codigo_autorizacion'] = $result['detailOutput']['authorizationCode'];
        $datetime = new DateTime($result['transactionDate']);
        $data['tbk_hora_transaccion'] = $datetime->format('H:i:s');
        $data['tbk_dia_transaccion'] = $datetime->format('d-m-Y');
        $data['tbk_final_numero_tarjeta'] = '************' . $result['cardDetail']['cardNumber'];
        $data['tbk_tipo_pago'] = $paymentType;
        $data['tbk_tipo_cuotas'] = $installmentType;
        $data['tbk_monto'] = $result['detailOutput']['amount'];
        $data['tbk_numero_cuotas'] = $result['detailOutput']['sharesNumber'];

        $this->session->data['transbank_webpay_result'] = $this->load->view('extension/payment/webpay_success', $data);
        $this->response->redirect($this->url->link('checkout/success', 'language=' . $this->config->get('config_language'), 'SSL'));
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
}
