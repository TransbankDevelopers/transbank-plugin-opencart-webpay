<?php

// Heading
$_['heading_title'] = 'Webpay Plus';

// Text
$_['text_extension'] = 'Extensions';
$_['text_success'] = '&Eacute;xito: ¡Se han modificado los detalles de la cuenta Webpay satisfactoriamente!';
$_['text_edit'] = 'Editar Webpay Plus';
$_['text_webpay'] = '<a href="http://www.transbank.cl" target="_blank"><img src="https://www.transbank.cl/public/img/Logo_Webpay3-01-50x50.png" alt="WebPay" title="WebPay" style="border: 1px solid #EEEEEE;" /></a>';
$_['text_authorization'] = 'Autorizaci&oacute;n';
$_['text_sale'] = 'Oferta';
$_['text_options'] = 'Informaci&oacute;n';
$_['text_enabled'] = 'Habilitado';
$_['text_disabled'] = 'Deshabilitado';
$_['text_all_zones'] = 'Todas las &Aacute;reas';
// entry menu
$_['entry_commerce_code'] = 'C&oacute;digo de Comercio:';
$_['entry_private_key'] = 'Llave Privada';
$_['entry_public_cert'] = 'Certificado:';
$_['entry_webpay_cert'] = 'Certificado Transbank:';
$_['entry_test_mode'] = 'Ambiente a utilizar:';

$_['tab_settings'] = 'Ajustes';

// Entry

$_['entry_total'] = 'Total';
$_['entry_order_status'] = 'Estado de Orden';
$_['entry_geo_zone'] = 'Zona Geogr&aacute;fica';
$_['entry_status'] = 'Estado';
$_['entry_sort_order'] = 'Orden';
$_['entry_completed_order_status'] = 'Estado completado:';
$_['entry_rejected_order_status']  = 'Estado rechazado:';
$_['entry_canceled_order_status']  = 'Estado cancelado:';


// placeholder
$_['webpay_commerce_code_placeholder'] = 'C&oacute;digo del comercio';
$_['webpay_private_key_placeholder'] = 'Llave Privada';
$_['webpay_public_cert_placeholder'] = 'Certificado P&uacute;blico';
$_['webpay_webpay_cert_placeholder'] = 'Certificado P&uacute;blico Webpay';
$_['webpay_total_placeholder'] = 'Monto minimo';
$_['webpay_sort_order_placeholder'] = 'Orden';

// Help
$_['help_commerce_code'] = 'C&oacute;digo de comercio &uacute;nico entregado por Transbank';
$_['help_private_key'] = 'Llave privada SSL del Servidor';
$_['help_public_cert'] = 'Certificado Publico del Servidor';
$_['help_webpay_cert'] = 'Certificado Publico de Webpay Transbank';
$_['help_test_mode'] = 'Ambiente de Integraci&oacute;n o Producci&oacute;n de para realizar Transacciones';
$_['help_total'] = 'Cantidad minima para activar este medio de pago';

// Error
$_['error_permission'] = 'Advertencia: ¡No tienes permiso para modificar el motor de pago Webpay!';
$_['error_commerce_code'] = '¡Se requiere el c&oacute;digo de comercio!';
$_['error_private_key'] = '¡Se requiere la llave privada!';
$_['error_public_cert'] = '¡Se requiere el certificado!';
$_['error_webpay_cert'] = '¡Se requiere el certificado de transbank!';


// modal-content

$_['label_ecommerce_name'] = 'Nombre del E-commerce instalado en el servidor';
$_['label_ecommerce_version'] = 'Versi&oacute;n de E-commerce instalada en el servidor';
$_['label_ecommerce_webpay'] = 'Versi&oacute;n de Plugin Webpay Instalada';
$_['label_ecommerce_last'] = '&Uacute;ltima versi&oacute;n disponible del plugin para descargar';

$_['label_consistency'] = 'Informa si las llaves ingresadas por el usuario corresponden al certificado entregado por Transbank';
$_['label_commerce_code_validate'] = 'Informa si el c&oacute;digo de comercio ingresado por el usuario corresponde al certificado entregado por Transbank';
$_['label_common_name'] = 'CN (common name) dentro del certificado, en este caso corresponde al c&oacute;digo de comercio emitido por Transbank';
$_['label_cert_version'] = 'Versi&oacute;n del certificado emitido por Transbank';
$_['label_cert_vigency'] = 'Informa si el certificado est&aacute; vigente actualmente';
$_['label_cert_from'] = 'Fecha desde la cual el certificado es v&aacute;lido';
$_['label_cert_to'] = 'Fecha hasta la cual el certificado es v&aacute;lido';
$_['label_webserver'] = 'Descripci&oacute;n del Servidor Web instalado';
$_['label_php_version_validate'] = 'Informa si la versi&oacute;n de PHP instalada en el servidor es compatible con el plugin de Webpay';
$_['label_php_version'] = 'Versi&oacute;n de PHP instalada en el servidor';
$_['label_conection_status'] = 'Informa el estado de la comunicaci&oacute;n con Transbank mediante m&eacute;todo init_transaction';
$_['label_active_logs'] = 'Al activar esta opci&oacute;n se habilita que se guarden los datos de cada compra mediante Webpay';
$_['label_logs_days'] = 'Cantidad de d&iacute;as que se conservan los datos de cada compra mediante Webpay';
$_['label_logs_weight'] = 'Peso m&aacute;ximo (en Megabytes) de cada archivo que guarda los datos de las compras mediante Webpay';
$_['label_last_log_name'] = 'Nombre del &uacute;timo archivo de registro creado';
$_['label_last_log_weight'] = 'Peso del &uacute;ltimo archivo de registro creado';
$_['label_last_log_regs'] = 'Cantidad de l&iacute;neas que posee el &uacute;ltimo archivo de registro creado';
$_['label_regs_status'] = 'Informa si actualmente se guarda la información de cada compra mediante Webpay';
$_['label_regs_dir'] = 'Carpeta en el servidor en donde se guardan los archivos con la informacón de cada compra mediante Webpay';
$_['label_regs_count'] = 'Cantidad de archivos que guardan la información de cada compra mediante Webpay';
$_['label_regs_list'] = 'Lista los archivos archivos que guardan la información de cada compra mediante Webpay';


$_['l_ecommerce'] = 'Software E-commerce';
$_['l_version'] = 'Version E-commerce';
$_['l_webpay'] = 'Version Plugin Webpay Instalada';
$_['l_last_webpay'] = 'Ultima Version de Plugin Disponible';
$_['l_consistency'] = 'Consistencias con llaves';
$_['l_cc_validate'] = 'Validaci&oacute;n C&oacute;digo de commercio';
$_['l_cn'] = 'C&oacute;digo de Comercio V&aacute;lido';
$_['l_cert_version'] = 'Version certificado';
$_['l_cert_vigency'] = 'Vigencia';
$_['l_cert_from'] = 'V&aacute;lido desde';
$_['l_cert_to'] = 'V&aacute;lido hasta';
$_['l_webserver'] = 'Software Servidor';
$_['l_php_validate'] = 'Estado';
$_['l_php_version'] = 'Versi&oacute;n';
$_['l_con_status'] = 'Estado';
$_['l_logs_active'] = 'Activar Registro';
$_['l_ldays'] = 'Cantidad de Dias a Registrar';
$_['l_lweight'] = 'Peso maximo de Registros';
$_['l_lastname'] = 'Ultimo Documento';
$_['l_lastweight'] = 'Peso de Documento';
$_['l_lastregs'] = 'Cantidad de Lineas';
$_['l_regs_status'] = 'Estado de Registros';
$_['l_regs_dir'] = 'Directorio de Registros';
$_['l_regs_count'] = 'Cantidad de registros en directorio';
$_['l_regs_list'] = 'Listado de registros';
$_['l_last_log'] = 'Contenido Ultimo Log';

$_['btn_params'] = 'Actualizar Par&aacute;metros';
$_['btn_close'] = 'Cerrar';
$_['btn_pdfmain'] = 'Crear Reporte';
$_['button_pdfinfo'] = 'Crear PHP info';
