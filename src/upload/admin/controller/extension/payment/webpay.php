<?php

/**
 *
 */
class ControllerExtensionPaymentWebpay extends Controller {

    private $error = array();

    private $sections = array('commerce_code', 'private_key', 'public_cert', 'webpay_cert', 'test_mode');

    private $transbankSdkWebpay = null;

    private function loadResources() {
        $this->load->language('extension/payment/webpay');
        $this->load->model('setting/setting'); //load model in: $this->model_setting_setting
        $this->load->model('localisation/order_status'); //load model in: $this->model_localisation_order_status
        //$this->load->model('checkout/order'); //load model in: $this->model_checkout_order
    }

    private function getTransbankSdkWebpay() {
        $this->loadResources();
        if (!class_exists('TransbankSdkWebpay')) {
            $this->load->library('TransbankSdkWebpay');
        }
        return new TransbankSdkWebpay($this->config);
    }

    public function index() {

        /*phpinfo();

        if (true) {
            return;
        }*/

        $this->transbankSdkWebpay = $this->getTransbankSdkWebpay();

        $this->document->setTitle($this->language->get('heading_title'));;

        $redirs = array('authorize', 'finish', 'error', 'reject');
        foreach ($redirs as $value) {
            $this->request->post['payment_webpay_url_'.$value] = HTTP_CATALOG . 'index.php?route=extension/payment/webpay/' .$value;
        }

        // validacion de modificaciones

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_webpay', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' .$this->session->data['user_token'] . '&type=payment', true));
        }

        // se imprimen errores si existen

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        foreach ($this->sections as $value) {
            if (isset($this->error['payment_webpay_'.$value])) {
                $data['error_'.$value] = $this->error['payment_webpay_'.$value];
            } else {
                $data['error_'.$value] = '';
            }
        }

        $vars = array(
            'entry_commerce_code',
            'entry_private_key',
            'entry_public_cert',
            'entry_webpay_cert',
            'entry_test_mode',
            'entry_total',
            'entry_geo_zone',
            'entry_status',
            'entry_sort_order',
            'entry_completed_order_status',
            'entry_rejected_order_status',
            'tab_settings',
            'entry_canceled_order_status'
        );

        foreach ($vars as $var) {
            $data[$var] = $this->language->get($var);
        }

        // se declaran los breadcrumbs (el menu de seguimiento)

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_webpay'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/webpay', 'user_token=' . $this->session->data['user_token'], true),
        );

        $data['action'] = $this->url->link('extension/payment/webpay', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        foreach ($this->sections as $value) {
            if (isset($this->request->post['payment_webpay_'.$value])) {
                $data['payment_webpay_'.$value] = $this->request->post['payment_webpay_'.$value];
            } elseif ($this->config->get('payment_webpay_'.$value)) {
                $data['payment_webpay_'.$value] = $this->config->get('payment_webpay_'.$value);
            }
        }

        $selects = array('total', 'completed_order_status', 'rejected_order_status', 'canceled_order_status', 'geo_zone', 'sort_order', 'status');

        foreach ($selects as $value) {
            if (isset($this->request->post['payment_webpay_'.$value])) {
                $data['payment_webpay_'.$value] = $this->request->post['payment_webpay_'.$value];
            } else {
                $data['payment_webpay_'.$value] = $this->config->get('payment_webpay_'.$value);
            }
        }

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();


        require_once DIR_CATALOG.'controller/extension/payment/libwebpay/healthcheck.php';
        require_once DIR_CATALOG.'controller/extension/payment/libwebpay/loghandler.php';

        // valores por defecto de integracion (WA-2.0.12) 10/31/2017, 3:07:59 PM

        $default_integration = array(
          'MODO' => "INTEGRACION",
          'COMMERCE_CODE' => "597020000541",
          'PRIVATE_KEY' => "-----BEGIN RSA PRIVATE KEY-----
MIIEpQIBAAKCAQEA0ClVcH8RC1u+KpCPUnzYSIcmyXI87REsBkQzaA1QJe4w/B7g
6KvKV9DaqfnNhMvd9/ypmGf0RDQPhlBbGlzymKz1xh0lQBD+9MZrg8Ju8/d1k0pI
b1QLQDnhRgR2T14ngXpP4PIQKtq7DsdHBybFU5vvAKVqdHvImZFzqexbZjXWxxhT
+/sGcD4Vs673fc6B+Xj2UrKF7QyV5pMDq0HCCLTMmafWAmNrHyl6imQM+bqC12gn
EEAEkrJiSO6P/21m9iDJs5KQanpJby0aGW8mocYRHDMHZjtTiIP0+JAJgL9KsH+r
Xdk2bT7aere7TzOK/bEwhkYEXnMMt/65vV6AfwIDAQABAoIBAHnIlOn6DTi99eXl
KVSzIb5dA747jZWMxFruL70ifM+UKSh30FGPoBP8ZtGnCiw1ManSMk6uEuSMKMEF
5iboVi4okqnTh2WSC/ec1m4BpPQqxKjlfrdTTjnHIxrZpXYNucMwkeci93569ZFR
2SY/8pZV1mBkZoG7ocLmq+qwE1EaBEL/sXMvuF/h08nJ71I4zcclpB8kN0yFrBCW
7scqOwTLiob2mmU2bFHOyyjTkGOlEsBQxhtVwVEt/0AFH/ucmMTP0vrKOA0HkhxM
oeR4k2z0qwTzZKXuEZtsau8a/9B3S3YcgoSOhRP/VdY1WL5hWDHeK8q1Nfq2eETX
jnQ4zjECgYEA7z2/biWe9nDyYDZM7SfHy1xF5Q3ocmv14NhTbt8iDlz2LsZ2JcPn
EMV++m88F3PYdFUOp4Zuw+eLJSrBqfuPYrTVNH0v/HdTqTS70R2YZCFb9g0ryaHV
TRwYovu/oQMV4LBSzrwdtCrcfUZDtqMYmmZfEkdjCWCEpEi36nlG0JMCgYEA3r49
o+soFIpDqLMei1tF+Ah/rm8oY5f4Wc82kmSgoPFCWnQEIW36i/GRaoQYsBp4loue
vyPuW+BzoZpVcJDuBmHY3UOLKr4ZldOn2KIj6sCQZ1mNKo5WuZ4YFeL5uyp9Hvio
TCPGeXghG0uIk4emSwolJVSbKSRi6SPsiANff+UCgYEAvNMRmlAbLQtsYb+565xw
NvO3PthBVL4dLL/Q6js21/tLWxPNAHWklDosxGCzHxeSCg9wJ40VM4425rjebdld
DF0Jwgnkq/FKmMxESQKA2tbxjDxNCTGv9tJsJ4dnch/LTrIcSYt0LlV9/WpN24LS
0lpmQzkQ07/YMQosDuZ1m/0CgYEAu9oHlEHTmJcO/qypmu/ML6XDQPKARpY5Hkzy
gj4ZdgJianSjsynUfsepUwK663I3twdjR2JfON8vxd+qJPgltf45bknziYWvgDtz
t/Duh6IFZxQQSQ6oN30MZRD6eo4X3dHp5eTaE0Fr8mAefAWQCoMw1q3m+ai1PlhM
uFzX4r0CgYEArx4TAq+Z4crVCdABBzAZ7GvvAXdxvBo0AhD9IddSWVTCza972wta
5J2rrS/ye9Tfu5j2IbTHaLDz14mwMXr1S4L39UX/NifLc93KHie/yjycCuu4uqNo
MtdweTnQt73lN2cnYedRUhw9UTfPzYu7jdXCUAyAD4IEjFQrswk2x04=
-----END RSA PRIVATE KEY-----",
          'PUBLIC_CERT' => "-----BEGIN CERTIFICATE-----
MIIDujCCAqICCQCZ42cY33KRTzANBgkqhkiG9w0BAQsFADCBnjELMAkGA1UEBhMC
Q0wxETAPBgNVBAgMCFNhbnRpYWdvMRIwEAYDVQQKDAlUcmFuc2JhbmsxETAPBgNV
BAcMCFNhbnRpYWdvMRUwEwYDVQQDDAw1OTcwMjAwMDA1NDExFzAVBgNVBAsMDkNh
bmFsZXNSZW1vdG9zMSUwIwYJKoZIhvcNAQkBFhZpbnRlZ3JhZG9yZXNAdmFyaW9z
LmNsMB4XDTE2MDYyMjIxMDkyN1oXDTI0MDYyMDIxMDkyN1owgZ4xCzAJBgNVBAYT
AkNMMREwDwYDVQQIDAhTYW50aWFnbzESMBAGA1UECgwJVHJhbnNiYW5rMREwDwYD
VQQHDAhTYW50aWFnbzEVMBMGA1UEAwwMNTk3MDIwMDAwNTQxMRcwFQYDVQQLDA5D
YW5hbGVzUmVtb3RvczElMCMGCSqGSIb3DQEJARYWaW50ZWdyYWRvcmVzQHZhcmlv
cy5jbDCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBANApVXB/EQtbviqQ
j1J82EiHJslyPO0RLAZEM2gNUCXuMPwe4OirylfQ2qn5zYTL3ff8qZhn9EQ0D4ZQ
Wxpc8pis9cYdJUAQ/vTGa4PCbvP3dZNKSG9UC0A54UYEdk9eJ4F6T+DyECrauw7H
RwcmxVOb7wClanR7yJmRc6nsW2Y11scYU/v7BnA+FbOu933Ogfl49lKyhe0MleaT
A6tBwgi0zJmn1gJjax8peopkDPm6gtdoJxBABJKyYkjuj/9tZvYgybOSkGp6SW8t
GhlvJqHGERwzB2Y7U4iD9PiQCYC/SrB/q13ZNm0+2nq3u08ziv2xMIZGBF5zDLf+
ub1egH8CAwEAATANBgkqhkiG9w0BAQsFAAOCAQEAdgNpIS2NZFx5PoYwJZf8faze
NmKQg73seDGuP8d8w/CZf1Py/gsJFNbh4CEySWZRCzlOKxzmtPTmyPdyhObjMA8E
Adps9DtgiN2ITSF1HUFmhMjI5V7U2L9LyEdpUaieYyPBfxiicdWz2YULVuOYDJHR
n05jlj/EjYa5bLKs/yggYiqMkZdIX8NiLL6ZTERIvBa6azDKs6yDsCsnE1M5tzQI
VVEkZtEfil6E1tz8v3yLZapLt+8jmPq1RCSx3Zh4fUkxBTpUW/9SWUNEXbKK7bB3
zfB3kGE55K5nxHKfQlrqdHLcIo+vdShATwYnmhUkGxUnM9qoCDlB8lYu3rFi9w==
-----END CERTIFICATE-----",
          'WEBPAY_CERT' => "-----BEGIN CERTIFICATE-----
MIIDKTCCAhECBFZl7uIwDQYJKoZIhvcNAQEFBQAwWTELMAkGA1UEBhMCQ0wxDjAMBgNVBAgMBUNo
aWxlMREwDwYDVQQHDAhTYW50aWFnbzEMMAoGA1UECgwDa2R1MQwwCgYDVQQLDANrZHUxCzAJBgNV
BAMMAjEwMB4XDTE1MTIwNzIwNDEwNloXDTE4MDkwMjIwNDEwNlowWTELMAkGA1UEBhMCQ0wxDjAM
BgNVBAgMBUNoaWxlMREwDwYDVQQHDAhTYW50aWFnbzEMMAoGA1UECgwDa2R1MQwwCgYDVQQLDANr
ZHUxCzAJBgNVBAMMAjEwMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAizJUWTDC7nfP
3jmZpWXFdG9oKyBrU0Bdl6fKif9a1GrwevThsU5Dq3wiRfYvomStNjFDYFXOs9pRIxqX2AWDybjA
X/+bdDTVbM+xXllA9stJY8s7hxAvwwO7IEuOmYDpmLKP7J+4KkNH7yxsKZyLL9trG3iSjV6Y6SO5
EEhUsdxoJFAow/h7qizJW0kOaWRcljf7kpqJAL3AadIuqV+hlf+Ts/64aMsfSJJA6xdbdp9ddgVF
oqUl1M8vpmd4glxlSrYmEkbYwdI9uF2d6bAeaneBPJFZr6KQqlbbrVyeJZqmMlEPy0qPco1TIxrd
EHlXgIFJLyyMRAyjX9i4l70xjwIDAQABMA0GCSqGSIb3DQEBBQUAA4IBAQBn3tUPS6e2USgMrPKp
sxU4OTfW64+mfD6QrVeBOh81f6aGHa67sMJn8FE/cG6jrUmX/FP1/Cpbpvkm5UUlFKpgaFfHv+Kg
CpEvgcRIv/OeIi6Jbuu3NrPdGPwzYkzlOQnmgio5RGb6GSs+OQ0mUWZ9J1+YtdZc+xTga0x7nsCT
5xNcUXsZKhyjoKhXtxJm3eyB3ysLNyuL/RHy/EyNEWiUhvt1SIePnW+Y4/cjQWYwNqSqMzTSW9TP
2QR2bX/W2H6ktRcLsgBK9mq7lE36p3q6c9DtZJE+xfA4NGCYWM9hd8pbusnoNO7AFxJZOuuvLZI7
JvD7YLhPvCYKry7N6x3l
-----END CERTIFICATE-----",
          'ECOMMERCE' => "opencart"
        );


        // si desde la instalacion inicial no toma los parametros por defecto

        if (isset($this->request->post['payment_webpay_commerce_code'])) {
            $args = array(
                'MODO' => $this->request->post['payment_webpay_test_mode'],
                'COMMERCE_CODE' => $this->request->post['payment_webpay_commerce_code'],
                'PRIVATE_KEY' => $this->request->post['payment_webpay_private_key'],
                'PUBLIC_CERT' => $this->request->post['payment_webpay_public_cert'],
                'WEBPAY_CERT' => $this->request->post['payment_webpay_webpay_cert'],
                'ECOMMERCE' => 'opencart'
            );
        } elseif ($this->config->get('payment_webpay_commerce_code')) {
            $args = array(
                'MODO' => $this->config->get('payment_webpay_test_mode'),
                'COMMERCE_CODE' => $this->config->get('payment_webpay_commerce_code'),
                'PRIVATE_KEY' => $this->config->get('payment_webpay_private_key'),
                'PUBLIC_CERT' => $this->config->get('payment_webpay_public_cert'),
                'WEBPAY_CERT' => $this->config->get('payment_webpay_webpay_cert'),
                'ECOMMERCE' => 'opencart'
            );
        } else {
            foreach ($default_integration as $key => $value) {
                $args[$key] = $value;
            }
        }

        $this->hc = new HealthCheck($args);
        $healthcheck = json_decode($this->hc->printFullResume(), true);
        //var_dump($healthcheck);
        $lh = new LogHandler($args['ECOMMERCE']);
        $loghandler = json_decode($lh->getResume(), true);
        // secciones y funciones de modal
        $data['hc_data'] = $this->hc->printFullResume();
        $data['healthcheck'] = $healthcheck;
        $data['lg_data'] = $lh->getResume();
        $data['loghandler'] = $loghandler;

        if ($healthcheck['validate_init_transaction']['status']['string'] == 'OK') {
            $data['response_init'] = "<tr><td><div title='URL entregada por Transbank para realizar la transacción' class='label label-info'>?</div> <b>URL: </b></td><td>{$healthcheck['validate_init_transaction']['response']['url']}</td></tr><tr><td><div title='Token entregada por Transbank para realizar la transacción' class='label label-info'>?</div> <b>Token: </b></td><td><code>{$healthcheck['validate_init_transaction']['response']['token_ws']}</code></td></tr>";
        } else {
            $data['response_init'] = "{$healthcheck['validate_init_transaction']['response']['error']}";
        }

        if ($loghandler['last_log']['log_content']) {
            $data['res_logcontent'] = json_encode($loghandler['last_log']['log_content']);
            $data['log_file'] = $loghandler['last_log']['log_file'];
            $data['log_file_weight'] = $loghandler['last_log']['log_weight'];
            $data['log_file_regs'] = $loghandler['last_log']['log_regs_lines'];
        } else {
            $data['res_logcontent'] = $loghandler['last_log'];
            $data['log_file'] = json_encode($data['res_logcontent']);
            $data['log_file_weight'] = $data['log_file'];
            $data['log_file_regs'] = $data['log_file'];
        }

        if ($loghandler['config']['status'] === false) {
            $data['estado_logs'] = "<span class='label label-warning'>Desactivado sistema de Registros</span>";
        } else {
            $data['estado_logs'] = "<span class='label label-success'>Activado sistema de Registros</span>";
        }

        $data['log_list'] = $loghandler['logs_list'];
        $data['log_dir'] = stripslashes(json_encode($loghandler['log_dir']));
        $data['log_count'] = json_encode($loghandler['logs_count']['log_count']);
        $data['tb_max_logs_days'] = $loghandler['config']['max_logs_days'];

        $data['tb_max_logs_weight'] = $loghandler['config']['max_log_weight'];

        $data['webpay_url_loadconfig'] = '../catalog/controller/extension/payment/webpay_functions/webpay_loadconfig.php';

        $data['webpay_url_makepdf'] = '../catalog/controller/extension/payment/webpay_functions/webpay_makepdf.php';

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/webpay', $data));
    }

    private function validate() {

        if (!$this->user->hasPermission('modify', 'extension/payment/webpay')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        foreach ($this->sections as $value) {
            if (!$this->request->post['payment_webpay_'.$value]) {
                $this->error[$value] = $this->language->get('error_'.$value);
            }
        }

        return !$this->error;
    }
}
