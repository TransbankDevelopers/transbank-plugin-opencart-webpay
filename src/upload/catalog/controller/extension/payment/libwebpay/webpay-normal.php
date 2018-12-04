<?php
/**
 * @author     Allware Ltda. (http://www.allware.cl)
 * @copyright  2018 Transbank S.A. (http://www.transbank.cl)
 * @date       May 2018
 * @license    GNU LGPL
 * @version    2.0.23
 */
require_once('soap/soap-wsse.php');
require_once('soap/soap-validation.php');
require_once('soap/soapclient.php');
require_once('loghandler.php');


class getTransactionResult
{
    public $tokenInput;
}
class getTransactionResultResponse
{
    public $return;
}
class transactionResultOutput
{
    public $accountingDate;
    public $buyOrder;
    public $cardDetail;
    public $detailOutput;
    public $sessionId;
    public $transactionDate;
    public $urlRedirection;
    public $VCI;
}
class cardDetail
{
    public $cardNumber;
    public $cardExpirationDate;
}
class wsTransactionDetailOutput
{
    public $authorizationCode;
    public $paymentTypeCode;
    public $responseCode;
}
class wsTransactionDetail
{
    public $sharesAmount;
    public $sharesNumber;
    public $amount;
    public $commerceCode;
    public $buyOrder;
}
class acknowledgeTransaction
{
    public $tokenInput;
}
class acknowledgeTransactionResponse
{
}
class initTransaction
{
    public $wsInitTransactionInput;
}
class wsInitTransactionInput
{
    public $wSTransactionType;
    public $commerceId;
    public $buyOrder;
    public $sessionId;
    public $returnURL;
    public $finalURL;
    public $transactionDetails;
    public $wPMDetail;
}
class wpmDetailInput
{
    public $serviceId;
    public $cardHolderId;
    public $cardHolderName;
    public $cardHolderLastName1;
    public $cardHolderLastName2;
    public $cardHolderMail;
    public $cellPhoneNumber;
    public $expirationDate;
    public $commerceMail;
    public $ufFlag;
}
class initTransactionResponse
{
    public $return;
}
class wsInitTransactionOutput
{
    public $token;
    public $url;
}

class WebPayNormal
{
    public $config;
    public $soapClient;
    private static $WSDL_URL_NORMAL = array(
            "INTEGRACION"   => "https://webpay3gint.transbank.cl/WSWebpayTransaction/cxf/WSWebpayService?wsdl",
            // "CERTIFICACION" => "https://webpay3gint.transbank.cl/WSWebpayTransaction/cxf/WSWebpayService?wsdl",
            "PRODUCCION"    => "https://webpay3g.transbank.cl/WSWebpayTransaction/cxf/WSWebpayService?wsdl",
    );

    private static $RESULT_CODES = array(
         "0" => "Transacción aprobada",
        "-1" => "Rechazo de transacción",
        "-2" => "Transacción debe reintentarse",
        "-3" => "Error en transacción",
        "-4" => "Rechazo de transacción",
        "-5" => "Rechazo por error de tasa",
        "-6" => "Excede cupo máximo mensual",
        "-7" => "Excede límite diario por transacción",
        "-8" => "Rubro no autorizado",
    );

    private static $classmap = array('getTransactionResult' => 'getTransactionResult', 'getTransactionResultResponse' => 'getTransactionResultResponse', 'transactionResultOutput' => 'transactionResultOutput', 'cardDetail' => 'cardDetail', 'wsTransactionDetailOutput' => 'wsTransactionDetailOutput', 'wsTransactionDetail' => 'wsTransactionDetail', 'acknowledgeTransaction' => 'acknowledgeTransaction', 'acknowledgeTransactionResponse' => 'acknowledgeTransactionResponse', 'initTransaction' => 'initTransaction', 'wsInitTransactionInput' => 'wsInitTransactionInput', 'wpmDetailInput' => 'wpmDetailInput', 'initTransactionResponse' => 'initTransactionResponse', 'wsInitTransactionOutput' => 'wsInitTransactionOutput');
    public $logger;


    public function __construct($config)
    {
        $this->config = $config;
        $privateKey = $this->config->getParam("PRIVATE_KEY");
        $publicCert = $this->config->getParam("PUBLIC_CERT");
        $comercio = $this->config->getParam("ECOMMERCE");
        $this->logger = new LogHandler($comercio);

        $modo = $this->config->getModo();
        $url = WebPayNormal::$WSDL_URL_NORMAL[$modo];
        ini_set("default_socket_timeout", 15);
        $this->soapClient = new WSSecuritySoapClient($url, $privateKey, $publicCert, array(
          //  "classmap" => self::$classmap,
            "trace" => true,
            "exceptions" => true,
            "conection_timeout" => 0
        ));
    }

    public function _getTransactionResult($getTransactionResult)
    {
        $getTransactionResultResponse = $this->soapClient->getTransactionResult($getTransactionResult);
        return $getTransactionResultResponse;
    }

    public function _acknowledgeTransaction($acknowledgeTransaction)
    {
        $acknowledgeTransactionResponse = $this->soapClient->acknowledgeTransaction($acknowledgeTransaction);
        return $acknowledgeTransactionResponse;
    }

    public function _initTransaction($initTransaction)
    {
        $initTransactionResponse = $this->soapClient->initTransaction($initTransaction);
        return $initTransactionResponse;
    }

    public function _getReason($code)
    {
        return WebPayNormal::$RESULT_CODES[$code];
    }

    public function initTransaction($amount, $sessionId="", $ordenCompra="0", $urlFinal)
    {
        try {
            $error = array();
            $wsInitTransactionInput = new wsInitTransactionInput();

            $wsInitTransactionInput->wSTransactionType = "TR_NORMAL_WS";
            $wsInitTransactionInput->sessionId = $sessionId;
            $wsInitTransactionInput->buyOrder = $ordenCompra;
            $wsInitTransactionInput->returnURL = $this->config->getParam("URL_RETURN");
            $wsInitTransactionInput->finalURL = $urlFinal;

            $wsTransactionDetail = new wsTransactionDetail();
            $wsTransactionDetail->commerceCode = $this->config->getParam("COMMERCE_CODE");
            $wsTransactionDetail->buyOrder = $ordenCompra;
            $wsTransactionDetail->amount = $amount;

            $wsInitTransactionInput->transactionDetails = $wsTransactionDetail;

            $initTransactionResponse = $this->_initTransaction(
                array("wsInitTransactionInput" => $wsInitTransactionInput)
            );
            $xmlResponse = $this->soapClient->__getLastResponse();

            $soapValidation = new SoapValidation($xmlResponse, $this->config->getParam("WEBPAY_CERT"));

            $validationResult = $soapValidation->getValidationResult();



            if ($validationResult === true) {
                $wsInitTransactionOutput = $initTransactionResponse->return;
                $this->logger->writeLog('initTransaction', $wsTransactionDetail->buyOrder, $wsTransactionDetail, $wsInitTransactionOutput, true);
                return array(
                    "url" => $wsInitTransactionOutput->url,
                    "token_ws" => $wsInitTransactionOutput->token
                );
            } else {
                $error["error"] = "Error validando conexión a Webpay";
                $error["detail"] = "No se puede validar la respuesta usando certificado " . WebPaySOAP::getConfig("WEBPAY_CERT");
            }
            $this->logger->writeLog('initTransaction', $wsTransactionDetail->buyOrder, $wsTransactionDetail, $error, false);
        } catch (Exception $e) {
            $error["error"] = "Error conectando a Webpay";
            $error["detail"] = $e->getMessage();
        }
        $this->logger->writeLog('initTransaction', $wsTransactionDetail->buyOrder, $wsTransactionDetail, $error, false);
        return $error;
    }

    public function getTransactionResult($token)
    {
        $getTransactionResult = new getTransactionResult();
        $getTransactionResult->tokenInput = $token;
        $getTransactionResultResponse = $this->_getTransactionResult($getTransactionResult);

        $xmlResponse = $this->soapClient->__getLastResponse();
        $soapValidation = new SoapValidation($xmlResponse, $this->config->getParam("WEBPAY_CERT"));
        $validationResult = $soapValidation->getValidationResult();
        if ($validationResult === true) {
            $result = $getTransactionResultResponse->return;

            if ($this->acknowledgeTransaction($token)) {
                $resultCode = $result->detailOutput->responseCode;
                if (($result->VCI == "TSY" || $result->VCI == "A" || $result->VCI == "") && $resultCode == 0) {
                    $this->logger->writeLog('getTransactionResult', $result->buyOrder, $token, $result->detailOutput, true);
                    return $result;
                } else {
                    $result->detailOutput->responseDescription = $this->_getReason($resultCode);
                    $this->logger->writeLog('getTransactionResult', $result->buyOrder, $token, $result->detailOutput, true);
                    return $result;
                }
            } else {
                $this->logger->writeLog('getTransactionResult', $result->buyOrder, $token, "Error eviando ACK a Webpay", false);
                error_log("getTransactionResult "."Error eviando ACK a Webpay", 0);
                return array("error" => "Error eviando ACK a Webpay");
            }
        }
        $this->logger->writeLog('getTransactionResult', $result->buyOrder, $token, "Error validando transacción en Webpay", false);
        return array("error" => "Error validando transacción en Webpay");
    }


    public function acknowledgeTransaction($token)
    {
        $acknowledgeTransaction = new acknowledgeTransaction();
        $acknowledgeTransaction->tokenInput = $token;
        $acknowledgeTransactionResponse = $this->_acknowledgeTransaction($acknowledgeTransaction);

        $xmlResponse = $this->soapClient->__getLastResponse();
        $soapValidation = new SoapValidation($xmlResponse, $this->config->getParam("WEBPAY_CERT"));
        $validationResult = $soapValidation->getValidationResult();
        $this->logger->writeLog('acknowledgeTransaction', null, $token, $validationResult, true);
        return $validationResult === true;
    }
}
