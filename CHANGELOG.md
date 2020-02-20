# Changelog
Todos los cambios notables a este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
y este proyecto adhiere a [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [2.2.0] - 2020-02-20
### Added
- Agrega métricas de uso cuando se pasa a producción en la configuración el plugin.


## [2.1.6] - 2019-07-09
### Fixed
- Resuelve error generando nuevo zip para instalación del plugin.

## [2.1.5] - 2019-05-08
### Fixed
- Elimina referencias al certificado de Webpay, resuelve error al activar el plugin

## [2.1.4] - 2019-04-17
### Fixed
- Corrige configuración, Ya no es necesario incluir el certificado de Webpay

## [2.1.3] - 2019-04-04
###Fixed
- Corrige despliegue de información en el detalle de la transacción realizada, ahora se visualiza toda la información

## [2.1.2] - 2019-01-17
### Changed
- Se elimina la condición de VCI == "TSY" || VCI == "" para evaluar la respuesta de getTransactionResult debido a que
esto podría traer problemas con transacciones usando tarjetas internacionales.

## [2.1.1] - 2018-12-27
### Fixed
- Corrige creación de url para webpay.
### Added
- Agrega logs de transacciones para poder obtener los datos como token, orden de compra, etc.. necesarios para el proceso de certificación.

## [2.1.0] - 2018-12-24
### Changed
- Se corrigen varios problemas internos del plugin para entregar una mejor experiencia en opencart con Webpay
- Se mejoran las validaciones internas del proceso de pago.
- Se mejora la creación del pdf de diagnóstico.
- Se elimina la comprobación de la extensión mcrypt dado que ya no es necesaria por el plugin.
- Ahora soporta php 7.1.25

## [2.0.23] - 2018-11-05
### Fixed
- Corrección de la herramienta de diagnóstico para registrar las versiones de Ecommerce y plugin.

## [2.0.21] - 2018-01-01
### Changed
- Se modifica certificado de servidor para ambiente de integración.

## [2.0.20] - 2018-01-01
### Added
- Se agrega archivo 'changelog' para mantener orden de cambios realizados plugin.

## [2.0.19] - 2018-01-01
### Added
- Incorpora herramientas de diagnóstico de ambiente y conectividad, además de activar log.

## [1.1.1] - 2018-01-01
### Changed
- Actualización endpoint de integración

## [1.1.0] - 2018-01-01
### Changed
- Cambio de tiendas y endpoint, apuntando al nuevo ambiente.

## [1.0.0] - 2018-01-01
### Added
- Versión Inicial.
