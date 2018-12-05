<?php

require_once(DIR_SYSTEM.'library/transbank-sdk-php/init.php');

class TransbankSdkWebpay {

    const PLUGIN_VERSION = '1.0.0'; //version of plugin payment
    const PLUGIN_CODE = 'transbank_webpay'; //code of plugin for opencart

    public function __construct($config) {
        $this->config = $config;
    }
}
