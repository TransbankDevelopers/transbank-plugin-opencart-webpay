# Manual de instalación para Plugin Opencart

## Descripción

Este plugin oficial ha sido creado para que puedas integrar Webpay fácilmente en tu comercio, basado en Opencart.

## Requisitos

Debes tener instalado previamente Opencart.

Habilitar los siguientes módulos / extensiones para PHP:
- Soap
- OpenSSL 1.0.1 o superior
- SimpleXML
- DOM 2.7.8 o superior

## Instalación de Plugin

1. Dirígete a [https://github.com/TransbankDevelopers/transbank-plugin-opencart-webpay/releases/latest](https://github.com/TransbankDevelopers/transbank-plugin-opencart-webpay/releases/latest), y descargue la última versión disponible del plugin.

  Una vez descargado el plugin, ingresa a la página de administración de Opencart (usualmente en _misitio.com_/admin), y dirígete a (Extensions / Intaller) indicado a continuación:

  ![Paso 1](img/paso1.png)
  
2. Haz click sobre el botón [Upload], selecciona el archivo del plugin y Opencart procederá a instalar el plugin. Una vez finalizado, se te indicará que el módulo fue instalado:

  ![Paso 2](img/paso2.png)

### Refrescar el sistema de modificaciones de OpenCart

1. Dirígete a (Extensions / Modifications) y selecciona el plugin "Transbank Webpay" indicado a continuación:

  ![Paso 3](img/paso3.png)

2. Con el plugin "Transbank Webpay" seleccionado presiona el botón "Refresh" ![save](img/mod_refresh.png) de la parte superior derecha.

OpenCart indicará que las modificaciones han sido exitosas sobre el plugin:

  ![Paso 4](img/paso4.png)

3. Dentro del sitio de administración dirígete a (Extensions / Extensions) y filtra por "Payments".

  ![Paso 5](img/paso5.png)

4. Busca hacia abajo el plugin "Webpay Plus".

  ![Paso 6](img/paso6.png)

5. Presiona el botón verde "+" para instalar el plugin.
   
  ![Paso 7](img/paso7.png)

6. Cambiará a color rojo.

  ![Paso 8](img/paso8.png)

## Configuración

Este plugin posee un sitio de configuración que te permitirá ingresar credenciales que Transbank te otorgará, y además podrás generar un documento de diagnóstico en caso que Transbank te lo pida.

Para acceder a la configuración, debes seguir los siguientes pasos:

1. Dirígete a la página de administración de Opencart (usualmente en _misitio.com_/admin), y luego dirígete a (Extensions / Extensions) , filtra por "Payments", busca "Webpay Plus" y presiona el botón "Edit" del plugin:

  ![Paso 8](img/paso8.png)

2. ¡Ya está! Estás en la pantalla de configuración del plugin, debes ingresar la siguiente información:

  * **Ambiente**: Ambiente hacia donde se realiza la transacción. 
  * **Código de comercio**: Es lo que te identifica como comercio.
  * **Llave Privada**: Llave secreta que te autoriza y valida a hacer transacciones.
  * **Certificado**: Llave publica que te autoriza y valida a hacer transacciones.
  * **Certificado Transbank**: Llave secreta de webpay que te autoriza y valida a hacer transacciones.

  Las opciones disponibles para _Ambiente_ son: "Integración" para realizar pruebas y certificar la instalación con Transbank, y "Producción" para hacer transacciones reales una vez que Transbank ha aprobado el comercio.

  Asegurate de configurar correctamente los estados de las ordenes:

  * **Estado completado**: Estado de una orden exitosa.
  * **Estado rechazado**: Estado de una orden rechazada.
  * **Estado cancelado**: Estado de una orden cancelada.
  
 ![Paso 9](img/paso9.png)

### Credenciales de Prueba

Para el ambiente de Integración, puedes utilizar las siguientes credenciales para realizar pruebas:

* Código de comercio: `597020000540`
* Llave Privada: Se puede encontrar [aquí - private_key](https://github.com/TransbankDevelopers/transbank-webpay-credenciales/blob/master/integracion/Webpay%20Plus%20-%20CLP/597020000540.key)
* Certificado Publico: Se puede encontrar [aquí - public_cert](https://github.com/TransbankDevelopers/transbank-webpay-credenciales/blob/master/integracion/Webpay%20Plus%20-%20CLP/597020000540.crt)
* Certificado Webpay: Se puede encontrar [aquí - webpay_cert](https://github.com/TransbankDevelopers/transbank-sdk-php/blob/master/lib/webpay/webpay.php#L39)

1. Guardar los cambios presionando el botón [Guardar]

2. Además, puedes generar un documento de diagnóstico en caso que Transbank te lo pida. Para ello, haz click en el botón "Información" ahí podrás descargar un pdf.

  ![Paso 10](img/paso10.png)

## Prueba de instalación con transacción

En ambiente de integración es posible realizar una prueba de transacción utilizando un emulador de pagos online.

* Ingresa al comercio y con la sesión iniciada, ingresa a cualquier sección para agregar productos

  ![demo1](img/demo1.png)


* Agrega al carro de compras un producto, selecciona el carro de compras y luego presiona el botón [Checkout]:

  ![demo2](img/demo2.png)

* Ingresa todos los datos requeridos:

  ![demo3](img/demo3.png)

* Selecciona método de pago "Pago con Tarjetas de Crédito o Redcompra":
  
  ![demo4](img/demo4.png)

* Presiona el botón [Continuar a Webpay]

  ![demo5](img/demo5.png)

* Una vez presionado el botón para iniciar la compra, se mostrará la ventana de pago Webpay y deberás seguir el proceso de pago.

Para pruebas puedes usar los siguientes datos:  

* Número de tarjeta: `4051885600446623`
* Rut: `11.111.111-1`
* Cvv: `123`
  
![demo6](img/demo6.png)

![demo7](img/demo7.png)

Para pruebas puedes usar los siguientes datos:  

* Rut: `11.111.111-1`
* Clave: `123`

![demo8](img/demo8.png)

Puedes aceptar o rechazar la transacción

![demo9](img/demo9.png)

![demo10](img/demo10.png)
  
* Serás redirigido a Opencart y podrás comprobar que el pago ha sido exitoso.

![demo11](img/demo11.png)

* Además si accedes al sitio de administración sección (Sales / Orders) se podrá ver la orden creada y el detalle de los datos entregados por Webpay.

 ![order1](img/order1.png)

 ![order2](img/order2.png)
