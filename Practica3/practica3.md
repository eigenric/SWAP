---
title: "Servidores Web de Altas Prestaciones. Práctica 3"
author: ["Ricardo Ruiz Fernández de Alba"]
date: "25/05/2023"
subject: "Servidores Web de Altas Prestaciones"
keywords: ["Ubuntu Server", "Nginx"]
subtitle: "Balanceo de carga en un sitio web."
titlepage: true
titlepage-background: "Practica3/background1.pdf"
toc: true
toc-own-page: true
# titlepage-color: "3C9F53"
# titlepage-text-color: "FFFFFF"
# titlepage-rule-color: "FFFFFF"
# titlepage-rule-height: 2
---

# Introducción

En esta práctica, el objetivo es configurar las máquinas virtuales de forma que dos hagan de servidores web finales mientras que la tercera haga de balanceador de carga por
software. 

# Descripción de las tareas

En esta práctica se llevarán a cabo las **tareas básicas**:

1. Configurar una máquina e instalar nginx y haproxy como balanceadores de carga con el algoritmo round-robin
2. Someter la granja web a una alta carga con la herramienta Apache Benchmark a través de M3, considerando 2 opciones:
    a) nginx con round-robin
    b) haproxy con round-robin
3. Realizar un análisis comparativo de los resultados considerando el número de peticiones por unidad de tiempo


Como **opciones avanzadas**:


1. Configurar nginx y haproxy como balanceadores de carga con ponderación, suponiendo que M1 tiene el doble de capacidad que M2.

2. Habilitar el módulo de estadísticas en HAproxy con varias opciones y analizarlo.

3. Instalar y configurar otros balanceadores de carga (Gobetween, Zevenet, Pound, etc.).

4. Someter la granja web a una alta carga con la herramienta Apache Benchmark considerando los distintos balanceadores instalados y configurados.

5. Realizar un análisis comparativo de los resultados considerando el número de peticiones por unidad de tiempo

\newpage

# Tarea 1. Balanceo de carga con NGINX y HAProxy.

Creamos una nueva máquina virtual llamada m3-ricardoruiz con Ubuntu Server 22.04 LTS, a la que añadiremos el usuario
ricardoruiz con contraseña Swap12324.

![](Practica3/assets/Figura1.png)

## Balanceo de carga con NGINX

### Instalación de NGINX.

Seguiremos la guia de instalación de nginx para Ubuntu Server 22.04 de[Digital Ocean](https://www.digitalocean.com/community/tutorials/how-to-install-nginx-on-ubuntu-22-04).

```shell
ricardoruiz@m3-ricardoruiz $ sudo apt update
ricardoruiz@m3-ricardoruiz $ sudo apt install nginx
```

Antes de probar Nginx, es necesario configurar el firewall para permitir el acceso al servicio. Nginx se registra como un servicio en ufw durante la instalación, lo que facilita permitir el acceso a Nginx.

```shell
ricardoruiz@m3-ricardoruiz $ sudo ufw allow 'Nginx HTTP
```

Comprobamos que nginx está activo con `sudo systemctl status nginx`:

![Nginx](Practica3/assets/Figura2.png)

### Configuración de NGINX como balanceador de carga

Debemos deshabilitar la configuración por defecto de nginx como servidor web para que actúe como balanceador.

Para ello, comentamos la línea 

```
#include /etc/nginx/sites-enabled/*;
```

del fichero de configuración `/etc/nginx/nginx.conf`.

Creamos una nueva configuración en `/etc/nginx/conf.d/default.conf` 


Para definir la granja web de servidores apache escribimos la sección upstream con la IP de las M1 y M2. Es importante que este al principio del archivo de configuración, fuera de la sección server.


```
upstream balanceo_ricardoruiz { 
    server 192.168.2.10;
    server 192.168.2.20;
}
```

Debemos definir ahora la sección server para indicar a nginx que use el grupo definido anteriormente en upstream.
Para que el proxy_pass funcione correctamente , debemos indicar una conexión de tipo HTTP 1.1 asi como eliminar la cabecera `Connection` para evitar que se pase al servidor final la cabecer que indica el usuario.


```conf
[..]
server {
    listen 80;
    server_name balanceador_ricardoruiz;
    access_log /var/log/nginx/balanceador_ricardoruiz.access.log; 
    error_log /var/log/nginx/balanceador_ricardoruiz.error.log; 
    root /var/www/;
    location / {
        proxy_pass http://balanceo_ricardoruiz;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for; 
        proxy_http_version 1.1;
        proxy_set_header Connection "";
    }
}
```

Luego la configuración completa quedaría como sigue:

![](Practica3/assets/Figura3.png)

### Ejemplo de funcionamiento

Configuramos la IP de la máquina m3-ricardoruiz como IP estática en el fichero `/etc/netplan/00-installer-config.yaml`, añadiendo un nuevo adaptador de red Host-Only.

```yaml
   network:
     version: 2
     renderer: networkd
     ethernets:
       ens160:
         dhcp4: true
         addresses:
           - 192.168.1.30/24
         routes:
            - to: 0.0.0.0/0
              via: 192.168.1.1
              metric: 100
         nameservers:
           addresses: [8.8.8.8, 8.8.4.4]
       ens256:
         dhpc4: false
         addresses:
           - 192.168.2.30/24
```

La IP accesible desde fuera a M3 es `172.16.21.133`. 

Podemos comprobar el funcionamiento del balanceador con 

```shell
ricardoruiz@m3-ricardoruiz $ curl 172.16.21.133/swap.html
```

![](Practica3/assets/Figura4.png)

### Repartir carga en función de pesos

En caso de saber que alguna de las máquinas finales es más potente, podemos modificar la definición del “upstream” para pasarle más tráfico que al resto. Para ello, asignamos un valor numero al modificador "weight".

Por ejemplo, podemos hacer que cada tres peticiones que lleguen al balanceador, la máquina M2 atenderá dos y la máquina M1 atenderá una:

```conf
upstream balanceo_ricardoruiz {
  server 192.168.2.10 weight=1;
  server 192.168.2.20 weight=2;
}
```

Para comprobarlo, modificamos `swap.html` las máquinas finales para identificarlas. 

![swap.html en M1](Practica3/assets/Figura7.png)

![swap.html en M2](Practica3/assets/Figura8.png)

Desactivamos también la tarea cron de sincronización con rsync para evitar que se sobreescriban los cambios.

![Desactivación de la tarea cron](Practica3/assets/Figura6.png)

Realizamos tres peticiones y comprobamos que se sigue el **Algoritmo Round Robin**, acabando dos de ellas en M2:

![](Practica3/assets/Figura5.png)

## Balanceo de carga con HAProxy


# Tarea 2. Alta carga con Apache Benchmark

# Tarea 3. Análisis Comparativo

# Referencias