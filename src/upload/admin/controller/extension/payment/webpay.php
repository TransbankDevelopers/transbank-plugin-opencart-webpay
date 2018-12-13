<?php

require_once(DIR_CATALOG.'controller/extension/payment/libwebpay/HealthCheck.php');
require_once(DIR_CATALOG.'controller/extension/payment/libwebpay/LogHandler.php');

class ControllerExtensionPaymentWebpay extends Controller {

    private $error = array();

    private $default_integration = array(
        'MODO' => "INTEGRACION",
        'COMMERCE_CODE' => "597020000540",
        'PRIVATE_KEY' => "-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEAvuNgBxMAOBlNI7Fw5sHGY1p6DB6EMK83SL4b1ZILSJs/8/MC
X8Pkys3CvJmSIiKU7fnWkgXchEdqXJV+tzgoED/y99tXgoMssi0ma+u9YtPvpT7B
a5rk5HpLuaFNeuE3l+mpkXDZZKFSZJ1fV/Hyn3A1Zz+7+X2qiGrAWWdjeGsIkz4r
uuMFLQVdPVrdAxEWoDRybEUhraQJ1kwmx92HFfRlsbNAmEljG9ngx/+/JLA28cs9
oULy4/M7fVUzioKsBJmjRJd6s4rI2YIDpul6dmgloWgEfzfLNnAsZhJryJNBr2Wb
E6DL5x/U2XQchjishMbDIPjmDgS0HLLMjRCMpQIDAQABAoIBAEkSwa/zliHjjaQc
SRwNEeT2vcHl7LS2XnN6Uy1uuuMQi2rXnBEM7Ii2O9X28/odQuXWvk0n8UKyFAVd
NSTuWmfeEyTO0rEjhfivUAYAOH+coiCf5WtL4FOWfWaSWRaxIJcG2+LRUGc1WlUp
6VXBSR+/1LGxtEPN13phY0DWUz3FEfGBd4CCPLpzq7HyZWEHUvbaw89xZJSr/Zwh
BDZZyTbuwSHc9X9LlQsbaDuW/EyOMmDvSxmSRJO10FRMxyg8qbE4edtUK4jd61i0
kGFqdDu9sj5k8pDxOsN2F270SMlIwejZ1uunB87w9ezIcR9YLq9aa22cT8BZdOxb
uZ3PAAECgYEA6xfgRtcvpJUBWBVNsxrSg6Ktx2848eQne9NnbWHdZuNjH8OyN7SW
Fn0r4HsTw59/NJ1L5F3co5L5baEtRbRLWRpD72xjrXsQSsoKliCik1xgDIplMvOh
teA2GdeSv9wglqnotGcj5B/8+vn3tEzMjy+UUsyFn0fIaDC3zK3W2qUCgYEAz90g
va+FCcU8cnykb5Yn1u1izdK1c6S++v1bQFf6590ZMNy3p0uGrwAk/MzuBkJ421GK
p4pInUvO/Mb2BCcoHtr3ON3v0DCLl6Ae2Gb7lG0dLgcZ1EK7MDpMvKCqNHAv8Qu8
QBZOA08L8buVkkRt7jxJrPuOFDI5JAaWCmMOSgECgYEA3GvzfZgu9Go862B2DJL+
hCuYMiCHTM01c/UfyT/z/Y7/ln2+8FniS02rQPtE6ar28tb0nDahM8EPGon/T5ae
+vkUbzy6LKLxAJ501JPeurnm2Hs+LUqe+U8yioJD9p2m9Hx0UglOborLgGm0pRlI
xou+zu8x7ci5D292NXNcun0CgYAVKV378bKJnBrbTPUwpwjHSMOWUK1IaK1IwCJa
GprgoBHAd7f6wCWmC024ruRMntfO/C4xgFKEMQORmG/TXGkpOwGQOIgBme+cMCDz
xwg1xCYEWZS3l1OXRVgqm/C4BfPbhmZT3/FxRMrigUZo7a6DYn/drH56b+KBWGpO
BGegAQKBgGY7Ikdw288DShbEVi6BFjHKDej3hUfsTwncRhD4IAgALzaatuta7JFW
NrGTVGeK/rE6utA/DPlP0H2EgkUAzt8x3N0MuVoBl/Ow7y5sqIQKfEI7h0aRdXH5
ecefOL6iiJWQqX2+237NOd0fJ4E1+BCMu/+HnyCX+cFM2FgoE6tC
-----END RSA PRIVATE KEY-----",
        'PUBLIC_CERT' => "-----BEGIN CERTIFICATE-----
MIIDeDCCAmACCQDjtGVIe/aeCTANBgkqhkiG9w0BAQsFADB+MQswCQYDVQQGEwJj
bDENMAsGA1UECAwEc3RnbzENMAsGA1UEBwwEc3RnbzEMMAoGA1UECgwDdGJrMQ0w
CwYDVQQLDARjY3JyMRUwEwYDVQQDDAw1OTcwMjAwMDA1NDAxHTAbBgkqhkiG9w0B
CQEWDmNjcnJAZ21haWwuY29tMB4XDTE4MDYwODEzNDYwNloXDTIyMDYwNzEzNDYw
NlowfjELMAkGA1UEBhMCY2wxDTALBgNVBAgMBHN0Z28xDTALBgNVBAcMBHN0Z28x
DDAKBgNVBAoMA3RiazENMAsGA1UECwwEY2NycjEVMBMGA1UEAwwMNTk3MDIwMDAw
NTQwMR0wGwYJKoZIhvcNAQkBFg5jY3JyQGdtYWlsLmNvbTCCASIwDQYJKoZIhvcN
AQEBBQADggEPADCCAQoCggEBAL7jYAcTADgZTSOxcObBxmNaegwehDCvN0i+G9WS
C0ibP/PzAl/D5MrNwryZkiIilO351pIF3IRHalyVfrc4KBA/8vfbV4KDLLItJmvr
vWLT76U+wWua5OR6S7mhTXrhN5fpqZFw2WShUmSdX1fx8p9wNWc/u/l9qohqwFln
Y3hrCJM+K7rjBS0FXT1a3QMRFqA0cmxFIa2kCdZMJsfdhxX0ZbGzQJhJYxvZ4Mf/
vySwNvHLPaFC8uPzO31VM4qCrASZo0SXerOKyNmCA6bpenZoJaFoBH83yzZwLGYS
a8iTQa9lmxOgy+cf1Nl0HIY4rITGwyD45g4EtByyzI0QjKUCAwEAATANBgkqhkiG
9w0BAQsFAAOCAQEAhX2/fZ6+lyoY3jSU9QFmbL6ONoDS6wBU7izpjdihnWt7oIME
a51CNssla7ZnMSoBiWUPIegischx6rh8M1q5SjyWYTvnd3v+/rbGa6d40yZW3m+W
p/3Sb1e9FABJhZkAQU2KGMot/b/ncePKHvfSBzQCwbuXWPzrF+B/4ZxGMAkgxtmK
WnWrkcr2qakpHzERn8irKBPhvlifW5sdMH4tz/4SLVwkek24Sp8CVmIIgQR3nyR9
8hi1+Iz4O1FcIQtx17OvhWDXhfEsG0HWygc5KyTqCkVBClVsJPRvoCSTORvukcuW
18gbYO3VlxwXnvzLk4aptC7/8Jq83XY8o0fn+A==
-----END CERTIFICATE-----",
        'WEBPAY_CERT' => "-----BEGIN CERTIFICATE-----
MIIEDzCCAvegAwIBAgIJAMaH4DFTKdnJMA0GCSqGSIb3DQEBCwUAMIGdMQswCQYD
VQQGEwJDTDERMA8GA1UECAwIU2FudGlhZ28xETAPBgNVBAcMCFNhbnRpYWdvMRcw
FQYDVQQKDA5UUkFOU0JBTksgUy5BLjESMBAGA1UECwwJU2VndXJpZGFkMQswCQYD
VQQDDAIyMDEuMCwGCSqGSIb3DQEJARYfc2VndXJpZGFkb3BlcmF0aXZhQHRyYW5z
YmFuay5jbDAeFw0xODA4MjQxOTU2MDlaFw0yMTA4MjMxOTU2MDlaMIGdMQswCQYD
VQQGEwJDTDERMA8GA1UECAwIU2FudGlhZ28xETAPBgNVBAcMCFNhbnRpYWdvMRcw
FQYDVQQKDA5UUkFOU0JBTksgUy5BLjESMBAGA1UECwwJU2VndXJpZGFkMQswCQYD
VQQDDAIyMDEuMCwGCSqGSIb3DQEJARYfc2VndXJpZGFkb3BlcmF0aXZhQHRyYW5z
YmFuay5jbDCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAJN+OJgQQqMb
iRZDb3x+JoTfSjyYsRc5k2CWvLpTPFxXuhDyp6mbdIpWIiNYEC4vufVZo5A3THar
cbnJRlW/4NVv5QM3gHN9WJ4QeIsrTLtvcIPlfUJNPLNeDqy84zum2YqAFmX5LWsp
SF1Ls6n7el8KNJAceaU+2ooN8QZdFZ3RnMc2vrHY7EU6wYGmf/VCEaDZCKqY6ElY
mt6/9b2lkhpQLdBn01IqqFpGrD+5DLmYrQur4/1BDVtdNLggX0K7kPk/mkPDq4ME
ytkc9/RI5HfJWoQ4EDQF6qcqPqxlMFDf5KEaoLVL230EdwOl0UyvlF25S9ubRyHy
mKWIEFSSXe0CAwEAAaNQME4wHQYDVR0OBBYEFP3nYSPX3YKF11RArC09hxjEMMBv
MB8GA1UdIwQYMBaAFP3nYSPX3YKF11RArC09hxjEMMBvMAwGA1UdEwQFMAMBAf8w
DQYJKoZIhvcNAQELBQADggEBAFHqOPGeg5IpeKz9LviiBGsJDReGVkQECXHp1QP4
8RpWDdXBKQqKUi7As97wmVksweaasnGlgL4YHShtJVPFbYG9COB+ElAaaiOoELsy
kjF3tyb0EgZ0Z3QIKabwxsxdBXmVyHjd13w6XGheca9QFane4GaqVhPVJJIH/zD2
mSc1boVSpaRc1f0oiMtiZf/rcY1/IyMXA9RVxtOtNs87Wjnwq6AiMjB15fLHfT7d
R48O6P0ZpWLlZwScyqDWcsg/4wNCL5Kaa5VgM03SKM6XoWTzkT7p0t0FPZVoGCyG
MX5lzVXafBH/sPd545fBH2J3xAY3jtP764G4M8JayOFzGB0=
-----END CERTIFICATE-----",
        'ECOMMERCE' => "opencart"
    );

    private $sections = array('commerce_code', 'private_key', 'public_cert', 'webpay_cert', 'test_mode');

    private $transbankSdkWebpay = null;

    private function loadResources() {
        $this->load->language('extension/payment/webpay');
        $this->load->model('setting/setting'); //load model in: $this->model_setting_setting
        $this->load->model('localisation/order_status'); //load model in: $this->model_localisation_order_status
        //$this->load->model('checkout/order'); //load model in: $this->model_checkout_order
    }

    /*
    private function getTransbankSdkWebpay() {
        $this->loadResources();
        if (!class_exists('TransbankSdkWebpay')) {
            $this->load->library('TransbankSdkWebpay');
        }
        return new TransbankSdkWebpay($this->config);
    }*/

    public function index() {

        /*phpinfo();

        if (true) {
            return;
        }*/

        //$this->transbankSdkWebpay = $this->getTransbankSdkWebpay();

        $this->loadResources();

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
        } else if ($this->config->get('payment_webpay_commerce_code')) {
            $args = array(
                'MODO' => $this->config->get('payment_webpay_test_mode'),
                'COMMERCE_CODE' => $this->config->get('payment_webpay_commerce_code'),
                'PRIVATE_KEY' => $this->config->get('payment_webpay_private_key'),
                'PUBLIC_CERT' => $this->config->get('payment_webpay_public_cert'),
                'WEBPAY_CERT' => $this->config->get('payment_webpay_webpay_cert'),
                'ECOMMERCE' => 'opencart'
            );
        } else {
            foreach ($this->default_integration as $key => $value) {
                $args[$key] = $value;
            }
        }

        $this->hc = new HealthCheck($args);
        $healthcheck = json_decode($this->hc->printFullResume(), true);

        $lh = new LogHandler();
        $loghandler = json_decode($lh->getResume(), true);
        // secciones y funciones de modal
        $data['hc_data'] = $this->hc->printFullResume();
        $data['healthcheck'] = $healthcheck;
        $data['lg_data'] = $lh->getResume();
        $data['loghandler'] = $loghandler;

        /*
        if ($healthcheck['validate_init_transaction']['status']['string'] == 'OK') {
            $data['response_init'] = "<tr><td><div title='URL entregada por Transbank para realizar la transacción' class='label label-info'>?</div> <b>URL: </b></td><td>{$healthcheck['validate_init_transaction']['response']['url']}</td></tr><tr><td><div title='Token entregada por Transbank para realizar la transacción' class='label label-info'>?</div> <b>Token: </b></td><td><code>{$healthcheck['validate_init_transaction']['response']['token_ws']}</code></td></tr>";
        } else {
            $data['response_init'] = "{$healthcheck['validate_init_transaction']['response']['error']}";
        }*/

        $data['response_init'] = '';

        if (isset($loghandler['last_log']['log_content'])) {
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

        $data['webpay_url_loadconfig'] = '../catalog/controller/extension/payment/UpdateConfigLog.php';

        $data['webpay_url_makepdf'] = '../catalog/controller/extension/payment/webpay_functions/CreatePdf.php';

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
