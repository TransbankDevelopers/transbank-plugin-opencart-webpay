[![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/transbankdevelopers/transbank-plugin-opencart-webpay)](https://github.com/TransbankDevelopers/transbank-plugin-opencart-webpay/releases/tag/2.5.2)
[![GitHub](https://img.shields.io/github/license/transbankdevelopers/transbank-plugin-opencart-webpay)](LICENSE)
[![GitHub contributors](https://img.shields.io/github/contributors/transbankdevelopers/transbank-plugin-opencart-webpay)](https://github.com/TransbankDevelopers/transbank-plugin-opencart-webpay/graphs/contributors)
[![Build Status](https://travis-ci.org/TransbankDevelopers/transbank-plugin-opencart-webpay.svg?branch=master)](https://travis-ci.org/TransbankDevelopers/transbank-plugin-opencart-webpay)

# Transbank Opencart 3.x Webpay Plugin

Plugin oficial de Webpay para OpenCart 3.x

## Descripción

Este plugin **oficial** de Transbank te permite integrar Webpay fácilmente en tu sitio OpenCart. Está desarrollado en base al [SDK oficial de PHP](https://github.com/TransbankDevelopers/transbank-sdk-php)

### ¿Cómo instalar?
Puedes ver las instrucciones de instalación y su documentación completa en [transbankdevelopers.cl/plugin/opencart/](https://www.transbankdevelopers.cl/plugin/opencart/)


### Paso a producción
Al instalar el plugin, este vendrá configurado para funcionar en modo **integración** (en el ambiente de pruebas de Transbank). Para poder operar con dinero real (ambiente de **producción**), debes:

1. Tener tu propio código de comercio. Si no lo tienes, solicita Webpay Plus en [transbank.cl](https://transbank.cl)
2. Debes [generar tus credenciales](https://www.transbankdevelopers.cl/documentacion/como_empezar#credenciales-en-webpay)  (llave privada y llave pública) usando tu código de comercio. 
3. Enviar [esta planilla de integración](https://transbankdevelopers.cl/files/evidencia-integracion-webpay-plugins.docx) a soporte@transbank.cl, junto con la llave pública (generada en el paso anterior) y tu **logo (130x59 pixeles en formato GIF)**. 
4. Cuando Transbank confirme que ha cargado tu certificado público y logo, debes entrar a la pantalla de configuración del plugin dentro de Prestashop y colocar tu código de comercio, llave privada, llave pública y poner el ambiente de 'Producción'. 
5. Debes hacer una compra de $10 en el ambiente de producción para confirmar el correcto funcionamiento. 

Puedes ver más información sobre este proceso en [este link](https://www.transbankdevelopers.cl/documentacion/como_empezar#puesta-en-produccion).

# Desarrollo
A continuación, encontrarás información necesaria para el desarrollo de este plugin. 

## Dependencias

* transbank/transbank-sdk
* fpdf

## Nota  
- La versión del sdk de php se encuentra en el archivo `config.sh`

**NOTA:** La versión del sdk de php se encuentra en el script config.sh

## Preparar el proyecto para bajar dependencias

    ./config.sh

## Crear una versión del plugin empaquetado 

    ./package.sh

## Desarrollo

Para apoyar el levantamiento rápido de un ambiente de desarrollo, hemos creado la especificación de contenedores a través de Docker Compose.

Para usarlo seguir el siguiente [README Opencart 3.x](./docker-opencart3)

## Instalación del plugin para un comercio

El manual de instalación para el usuario final se encuentra disponible [acá](docs/INSTALLATION.md) o en PDF [acá](https://github.com/TransbankDevelopers/transbank-plugin-opencart-webpay/blob/master/docs/INSTALLATION.pdf)
