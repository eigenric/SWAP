---
title: "Servidores Web de Altas Prestaciones. Práctica 1"
author: ["Ricardo Ruiz Fernández de Alba"]
date: "28/04/2023"
subject: "Servidores Web de Altas Prestaciones"
keywords: ["Apache", "Ubuntu Server"]
subtitle: "Introducción y Preparación de Herramientas."
titlepage: true
toc: true
toc-own-page: true
---

# Introducción

## Software de Virtualización

Utilizaremos VMWare Fusion para virtualizar varias máquinas con Ubuntu 22.04 LTS
que configuraremos para ir definiendo la estructura de granja web. Descargamos
Ubuntu 22.04 LTS con arquitectura ARM pues trabajamos desde MacOS Ventura con Apple Silicon M2.

La nombramos m1-ricardoruiz. La máquina se ha instalado con una configuración de 4GB de RAM y 20GB de disco duro. Modificamos el tamaño del disco duro a 10GB
mediante 

- Máquina Virtual > Disco duro NVMe > Configuración de Disco Duro (NVMe).

Iniciamos el programa y creamos una nueva máquina virtual con Inicio > Nuevo.
Pulsamos en instalar desde disco o imagen y seleccionamos la imagen descargada.

![](Practica1/assets/Figura1.png)


## Instalación de Ubuntu Server

1. Iniciamos la máquina virtual y procedemos a instalar Ubuntu Server.  
2. Arrancamos con Install Ubuntu Server e iniciamos en español.
3. En la configuración de perfil añadimos el nombre:
   1. Ricardo Ruiz Fernández de Alba
   2. Nombre de servidor m1-ricardoruiz
   3. Usuario: ricardoruiz
   4. Contraseña: Swap1234

![](Practica1/assets/Figura3-2.png)

Pulsamos la opcion de instalar OpenSSH. Repetimos la misma instalación con la máquina m2-ricardoruiz.


![](Practica1/assets/Figura4.png)

Sin hacer ninguna configuración, las direcciones IP de las máquinas son:

- m1-ricardoruiz: 172.16.21.132
- m2-ricardoruiz: 172.16.21.130

## Tareas a realizar

Necesitamos efectuar las siguientes tareas.

1. Acceder por ssh de una máquina a otra
2. Acceder mediante la herramienta curl desde una máquina a la otra
3. Mostrar configuraciones de red y opciones de netplan
4. Crear web básica (swap.html) y mostrar funcionamiento de las máquinas M1 y M2

# Tarea 1. Acceder por SSH de M1 a M2

Al instalar las máquinas, ya pulsamos la opción de instalar OpenSSH en ellas.

Necesitamos asegurar que ambas máquinas virtuales estén en la misma red "host-only".

## Añadir y Configurar Adaptadores de Red

Añadimos dos adaptadores de red en VMWare Fusion, uno de tipo **Uso compartido de Internet** que corresponde a modo NAT y otro Personalizado de tipo **Privado para mi mac** que corresponde a modo host-only.

![Adaptador modo NAT](Practica1/assets/Figura6.png)

![Adaptador host-only](Practica1/assets/Figura7.png)

### Configuración de IP y Puertas de enlace

Abrimos el archivo de configuración de Netplan `/etc/netplan/00-installer-config.yaml` y lo editamos con la siguiente configuración:

Para **M1**
```yaml
   network:
     version: 2
     renderer: networkd
     ethernets:
       ens160:
         addresses:
           - 192.168.1.10/24
         gateway4: 192.168.1.1
         nameservers:
           addresses: [8.8.8.8, 8.8.4.4]
       ens256:
         addresses:
           - 192.168.2.10/24
```

Para **M2**
```yaml
   network:
     version: 2
     renderer: networkd
     ethernets:
       ens160:
         addresses:
           - 192.168.1.20/24
         gateway4: 192.168.1.1
         nameservers:
           addresses: [8.8.8.8, 8.8.4.4]
       ens256:
         addresses:
           - 192.168.2.20/24
```

Aplicamos la configuración de Netplan con

```shell
$ sudo netplan generate
$ sudo netplan apply
```

Y verificamos que las interfaces de red tienen la configuración correcta con `ip addr show`.

De manera que

- La interfaz `ens160` correspondiente al adaptador en **modo NAT** se configura con la dirección ip estática `192.168.1.10/24` en M1 y `192.168.1.20/24`en M2, la puerta de enlace (`192.168.1.1`) y servidores DNS.

- La interfaz `ens256` correspondiente al adptador en **modo host-only** se configura con una dirección IP estática `192.168.2.10/24` en la máquina M1 y `192.168.2.20/24`en la máquina M2.

## Demostración de la tarea

Podemos realizar entonces la conexión SSH, desde la M1 (máquina de origen) a M2 (la máquina destino)

Desde M1:

```shell
$ ssh ricardoruiz@192.168.2.20
```

![Conexion ssh de M1 a M2](Practica1/assets/Figura8.png)

Análogamente, de M2 a M1:

```shell
$ ssh ricardoruiz@192.168.2.10
```

![Conexion ssh de M2 a M1](Practica1/assets/Figura9.png)


### Acceso sin Contraseña

### Autenticación de Clave Pública y Privada

# Tarea 2. Acceder mediante curl de M1 a M2.

Necesitamos realizar una instalación de Apache+MySQL (LAMP) para poder acceder mediante `curl`.

## Instalación LAMP 

```shell
$ sudo apt install apache2 mysql-server mysql-client
```

Comprobamos la versión:
```shell
$ apache2 -v
  Server version: Apache/2.4.52 (Ubuntu)
  Server built: 2023-03-01T22:43:55
```

Y lo iniciamos mediante

```shell
$ sudo systemctl enable apache2
$ sudo systemctl start apache2
```

Y comprobamos que los dos servicios están activos:

![](Practica1/assets/Figura5.png)


## Demostración de la tarea. 

Tras instalar curl con `sudo apt-get install curl`, realizamos 

```shell
$ curl 192.168.2.20/index.html
```

![](Practica1/assets/Figura12.png)

```shell
curl -o imagen.png https://www.google.es/images/srpr/logo3w.png
```

![](Practica1/assets/Figura11.png)

## Uso avanzado de Apache

### Directorios virtuales 
  
Para crear un directorio virtual en Apache, utiliza la directiva `Alias` en el archivo de configuración de Apache (`/etc/apache2/apache2.conf` o archivos en `/etc/apache2/sites-available/`). Aquí tienes un ejemplo resumido:

```
Alias /virtual /var/html
<Directory /var/html>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```



2. Redireccionar puertos:
   Para redireccionar puertos en Apache, puedes utilizar la directiva `ProxyPass` en el archivo de configuración. Aquí tienes un ejemplo resumido:

   ```
   ProxyPass / http://localhost:80/
   ProxyPassReverse / http://localhost:80/
   ```

   Esto redireccionará todas las solicitudes recibidas a través del puerto 8080 a `http://localhost:80/`. Puedes ajustar los puertos y la URL según tus requerimientos.

Recuerda reiniciar el servicio de Apache después de realizar cambios en la configuración con el siguiente comando:

```shell
sudo systemctl restart apache2
```

Estos son ejemplos básicos de configuraciones en Apache para crear directorios virtuales y redirigir puertos en Ubuntu Server 22.04. Ten en cuenta que la configuración específica puede variar dependiendo de tu caso de uso y los archivos de configuración presentes en tu sistema. Consulta la documentación oficial de Apache y los recursos específicos de Ubuntu para obtener más detalles y opciones avanzadas.

## Uso avanzado de CURL: 

Curl también proporciona opciones avanzadas para trabajar con HTTP, como cookies, peticiones GET/POST personalizadas y especificación de puertos. A continuación, se muestran algunas de las opciones más comunes:

### Cookies

Usando la opción `b`o `cookies`
   
```shell
$ curl -b "cookie1=value1; cookie2=value2" http://example.com
```

### Peticiones POST

Mediante `-X` o `--request` y `d`

```shell
$ curl -X POST -d "username=admin&password=12345" http://example.com
```

### Puertos

Se pueden especificar con `-p`o `--port`:

```shell
$ curl -p 8080 http://example.com
```

# Tarea 3. Mostrar configuraciones de red y opciones de netplan

En efecto, ya configuramos Netplan durante la tarea 1 para
disponer de IPs fáciles de recordar.

Netplan ofrece varias opciones avanzadas para configurar la puerta de enlace (gateway), servidores DNS y máscaras de red. 

## Configuración de la puerta de enlace (gateway):

El apartado
```routes
  - to: 
    via: <IP puerta de enlace>
```
de la configruación yaml, permite modificar la puerta de enlace (gateway)

## Configuración de servidores DNS:

El apartado 

```yaml
nameservers:
  addresses: [<DNS1>, <DNS2>]
```

de la configruación yaml, permite modificar los servidores DNS.

## Configuración de máscaras de red:

El apartado

```yaml
[...]
  addresses:
      - <IP>/<prefijo de red>

```

permite modificar la máscara de red. El prefijo de red indica el número de bits y define el tamaño de la red
En nuestro caso, hemos utilizado una máscara de red de 24 bits.


# Tarea 4. Página web de ejemplo

## Creación del archivo en el servidor apache de M2

Creamos el archivo `swap.html` en M2 en `/var/www/html`.

**swap.html**

```html
<HTML>
  <BODY>
    Web de ejemplo de "ricardoruiz" para SWAP 
    Email: ricardoruiz@correo.ugr.es
  </BODY>
</HTML>
```

## Acceso mediante curl desde M1

```shell
$ curl 192.168.2.20/swap.html
```

obteniendo

![](Practica1/assets/Figura10.png)


# Bibliografía

1. **VMware Fusion**
   - Sitio web: [VMware Fusion](https://www.vmware.com/products/fusion.html)

2. **Ubuntu Server**
   - Sitio web: [Ubuntu Server Documentation](https://ubuntu.com/server/docs)

3. **Netplan**
   - Sitio web: [Netplan](https://netplan.io/)
   - Ejemplos de configuración: [Netplan Examples](https://netplan.io/examples)

4. **Apache HTTP Server**
   - Sitio web: [Apache HTTP Server Documentation](https://httpd.apache.org/docs/)
   - Guía de configuración de Apache: [Apache HTTP Server Configuration](https://httpd.apache.org/docs/2.4/configuring.html)

5. **cURL**
   - Sitio web: [cURL Documentation](https://curl.se/docs/)
   - Comandos y opciones de cURL: [cURL Manual](https://curl.se/docs/manpage.html)
