---
title: "Servidores Web de Altas Prestaciones. Práctica 1"
author: ["Ricardo Ruiz Fernández de Alba"]
date: "28/04/2023"
subject: "Servidores Web de Altas Prestaciones"
keywords: ["Apache", "Ubuntu Server"]
subtitle: "Introducción y Preparación de Herramientas."
titlepage: true
titlepage-background: "Practica1/background1.pdf"
toc: true
toc-own-page: true
# titlepage-color: "3C9F53"
# titlepage-text-color: "FFFFFF"
# titlepage-rule-color: "FFFFFF"
# titlepage-rule-height: 2
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

En la anterior demostración, se nos pide la contraseña de usuario para acceder a la máquina destino. Para evitar esto, podemos configurar la autenticación mediante clave pública y privada.

#### Generar claves pública y privada

1. En M1, generamos un par de clavesun pública y privada utilizando el comando `ssh-keygen`. 

```shell
ricardoruiz@m1-ricardoruiz $ ssh-keygen
```

![Clave Pública y privada](Practica1/assets/Figura13.png)

2. Copiamos la clave pública generada (generalmente se encuentra en el archivo `~/.ssh/id_rsa.pub` o `~/.ssh/id_dsa.pub`) al archivo `authorized_keys` en la máquina remota. Para ello, podemos utilizar el comando `scp`

```shell
ricardoruiz@m1-ricardoruiz $ scp .ssh/id_rsa.pub ricardoruiz@192.168.2.20:/home/ricardo/.ssh
```

![Enviar clave publica](Practica1/assets/Figura14.png)

Que debe tener permisos de lectura y escritura:

```shell
ricardoruiz@m2-ricardoruiz $ mv .ssh/id_rsa.pub .ssh/authorized_keys
ricardoruiz@m2-ricardoruiz $ chmod 600 .ssh/authorized_keys
```

### Demostración acceso sin contraseña

En efecto, ahora ya no es necesario incluir la contraseña cuando realizamos la conexión:

![Conexion ssh de M1 a M2 sin contraseña](Practica1/assets/Figura15.png)

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
  
Para crear directorios virtuales en Apache, creamos archivos `.conf` en `/etc/apache2/sites-availables` y realizamos un enlace simbolico a estos archivos en `/etc/apache2/sites-enabled`.

Crearemos dos directorios virtuales, uno en el puerto 80 que apuntará al directorio `/var/www/html` y otro en el puerto 8000 que apuntará al directorio `/var/www/virtual`. Este tendrá un archivo `index.html` que indique que accedemos al directorio virtual.

`index.html`

```html
<HTML>
  <BODY>
    Directorio virtual de "ricardoruiz" para SWAP 
    Email: ricardoruiz@correo.ugr.es
  </BODY>
</HTML>
```

`default-html.conf`

```ApacheConf
<VirtualHost *:80>
    ServerName 192.168.2.20
    DocumentRoot /var/www/html

    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

`virtual.conf`

```ApacheConf
<VirtualHost *:8000>
    ServerName 192.168.2.20
    DocumentRoot /var/www/virtual

    <Directory /var/www/virtual>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted 
    </Directory>
</VirtualHost>
```

Enlazamos los archivos `.conf` en `/etc/apache2/sites-enabled`:

```shell
$ sudo ln -s /etc/apache2/sites-available/default-html.conf /etc/apache2/sites-enabled/default-html.conf
$ sudo ln -s /etc/apache2/sites-available/virtual.conf /etc/apache2/sites-enabled/virtual.conf
```

Necesitamos escuchar el puerto 8000, por lo que modificamos el archivo `/etc/apache2/ports.conf`, añadiendo `Listen 8000`

Y reiniciamos el servidor de Apache:

```shell 
$ sudo systemctl restart apache2
```

Podemos comprobar accediendo con curl:

![](Practica1/assets/Figura16.png)

### Redireccionar puertos

Para redireccionar puertos en Apache, se utilizan las directivas `ProxyPass` y `ProxyPassReverse`. Haremos una redireccion del puerto 80 al 8000. Para ello añadimos el apartado VirtualHost en `default-html.conf` lo siguiente:

```ApacheConf
[...]
ProxyPass /virtual http://192.168.2.20:8000/
ProxyPassReverse /virtual http://192.168.2.20:8000/
</VirtualHost>
```

Activamo el módulo de proxy de Apache y reiniciamos el servidor:

```shell
$ sudo a2enmod proxy
$ sudo a2enmod proxy_http
$ sudo systemctl restart apache2
```

De manera que podemos acceder a nuestro directorio virtual del puerto 8000 desde el puerto 80:

![Redireccionamiento de puertos](Practica1/assets/Figura17.png)

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

Se puede acceder mediante el formato de url `http://example.com:<puerto`.

```shell
$ curl http://example.com:8000
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

permite modificar la máscara de red. El prefijo de red indica el número de bits y define el tamaño de la red. En nuestro caso, hemos utilizado una máscara de red de 24 bits.


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


# Referencias


- **VMware Fusion**. Recuperado de [https://www.vmware.com/products/fusion.html](https://www.vmware.com/products/fusion.html)

- **Ubuntu**. Ubuntu Server Documentation. Recuperado de [https://ubuntu.com/server/docs](https://ubuntu.com/server/docs)

- **Netplan**. Recuperado de [https://netplan.io/](https://netplan.io/)
   - Ejemplos de configuración: Netplan. (s.f.). Recuperado de [https://netplan.io/examples](https://netplan.io/examples)
- **Apache**. Apache HTTP Server Documentation. Recuperado de [https://httpd.apache.org/docs/](https://httpd.apache.org/docs/)
   - Guía de configuración de Apache: Apache. (s.f.). Apache HTTP Server Configuration.

- **cURL**. Recuperado de [https://curl.se/docs/](https://curl.se/docs/)
   - Comandos y opciones de cURL: cURL. (s.f.). cURL Manual. Recuperado de [https://curl.se/docs/manpage.html](https://curl.se/docs/manpage.html)