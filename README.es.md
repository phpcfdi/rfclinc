# phpcfdi/rfclinc

> RFC inscritos no cancelados [README.md](README.md)

Esta librería permite mantener un listado local de RFC inscritos no cancelados.

Los RFC que se encuentren en la lista de inscritos no cancelados son los RFC a los que se les puede
emitir un Comprobante Fiscal Digital por Internet (el receptor del CFDI).

El propósito de crear esta libería nace de la necesidad de mantener una copia actualizada de este listado
así como mantener un listado de los cambios en el mismo.

Actualmente el SAT provee una forma de consultar un RFC en la lista en
https://portalsat.plataforma.sat.gob.mx/ConsultaRFC/
El problema de este recurso es que se encuentra detrás de un captcha y no ha publicado su consumo
por servicios web, algún otro tipo de API, ni permite web scraping.

La librería no contiene en sí misma la información del SAT del Listado de RFC inscritos no cancelados.
Lo que contiene es una forma de automatizar su descarga desde el contenedor de Azure
https://cfdisat.blob.core.windows.net/lco

La lista se encuentra disponible desde 2016-01-08

Los pasos genéricos son:

1. A partir de una fecha establecida, por ejemplo 2018-02-11
2. Cargar el índice de blobs desde https://cfdisat.blob.core.windows.net/lco?restype=container&comp=list&prefix=l_RFC_2018_02_11
3. Por cada blob encontrado

Por cada blob

1. Descargar el blob
2. Verificar la descarga con su digestión md5
3. Descomprimir el listado
4. Desempaquetar (smime) el listadp
5. Procesar cada línea

Los pasos de descomprimir, desempaquetar y procesar se realizan en una sola pasada usando unix pipes.

Para generar esta tarea la librería depende de las utilerías `gunzip` para descomprimir,
`openssl` para desempaquetar e `iconv` para transformar de `iso8859-1` a `utf-8`

