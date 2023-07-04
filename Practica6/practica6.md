---
title: "Servidores Web de Altas Prestaciones. Práctica 6"
author: ["Ricardo Ruiz Fernández de Alba"]
date: "27/06/2023"
subject: "Servidores Web de Altas Prestaciones"
keywords: ["MySQL", "Replicación"]
subtitle: "Servidor de disco NFS"
titlepage: true
titlepage-background: "Practica6/background1.pdf"
toc: true
toc-own-page: true
# titlepage-color: "3C9F53"
# titlepage-text-color: "FFFFFF"
# titlepage-rule-color: "FFFFFF"
# titlepage-rule-height: 2
---

# Introducción

El objetivo principal de esta práctica es configurar un servidor NFS para exportar un espacio en disco a los servidores finales (que actuarán como clientes-NFS).

# Tareas básicas

Hay que llevar a cabo las siguientes tareas básicas:
1. Configurar una máquina como servidor de disco NFS y exportar una carpeta a
los clientes.
2. Montar en las máquinas cliente la carpeta exportada por el servidor.
3. Comprobar que todas las máquinas pueden acceder a los archivos almacenados
en la carpeta compartida.

## Tarea 1. Configurar una máquina como servidor de disco NFS y exportar una carpeta a los clientes.

Creamos una nueva máquina virtual con Ubuntu Server que llamaremos NFS-ricardoruiz al igual que hicimos en la práctica 1. Debemos añadir adaptadores de red NAT y Solo-Anfitrión.

![Creación de la máquina NFS-ricardoruiz](Practica6/assets/Figura1.png)


Instalaremos las siguientes herramientas para utilizarla como servidor NFS:

```shell
ricardoruiz@nfs-ricardoruiz $ sudo apt-get install nfs-kernel-server nfs-common rpcbind
ricardoruiz@nfs-ricardoruiz $ sudo mkdir /datos/compartido
ricardoruiz@nfs-ricardoruiz $ sudo chown nobody:nogroup /datos/compartido/ $ sudo chmod -R 777 /datos/compartido/
```

![Instalación de herramientas](Practica6/assets/Figura2.png)

Para dar permiso de acceso a las máquinas clientes (M1 y M2), debemos añadir las IP correspondientes en el archivo de configuración `/etc/exports`

![Configuración de permisos](Practica6/assets/Figura3.png)


Finalmente, debemos reiniciar el servicio y comprobar que todo está correcto:

```shell
ricardoruiz@nfs-ricardoruiz $ sudo service nfs-kernel-server restart
ricardoruiz@nfs-ricardoruiz $ sudo service nfs-kernel-server status
```

![Comprobación de configuración](Practica6/assets/Figura4.png)


## Tarea 2. Montar en las máquinas cliente la carpeta exportada por el servidor.

En los clientes (M1 y M2) debemos instalar los paquetes necesarios y crear el punto de montaje (el directorio "datos" en cada máquina cliente):

```shell
ricardoruiz@m1-ricardoruiz $ sudo apt-get install nfs-common rpcbind 
ricardoruiz@m1-ricardoruiz $ cd /home/usuario
ricardoruiz@m1-ricardoruiz $ mkdir datos
ricardoruiz@m1-ricardoruiz $ chmod -R 777 datos
```

Ahora ya podemos montar la carpeta remota (la exportada en el servidor NFS) sobre el directorio recién creado:

```shell
ricardoruiz@m1-ricardoruiz $ sudo mount 192.168.2.40:/datos/compartido datos
```

En este punto podemos comprobar que se pueden leer y escribir los archivos que haya almacenados en la carpeta compartida:

```shell
ricardoruiz@m1-ricardoruiz $ ls datos
ricardoruiz@m1-ricardoruiz $ touch datos/archivo1.txt
```


## Tarea 3. Comprobar que todas las máquinas pueden acceder a los archivos almacenados en la carpeta compartida.

![Montaje de la carpeta remota en M1](Practica6/assets/Figura5.png)

![Montaje de la carpeta remota en M2](Practica6/assets/Figura6.png)

Y se compruebamos como desde las tres máquinas podemos acceder a todos los archivo que modificamos en la carpeta compartida, tanto para lectura como para escritura.

## Tarea Avanzada. Configuración permanente

Para hacer la configuración permanente, debemos añadir una línea al archivo `/etc/fstab` para que la carpeta compartida se monte al arrancar el sistema:

![Configuración permanente en M1](Practica6/assets/Figura7.png)

![Configuración permanente en M2](Practica6/assets/Figura8.png)


Tras reiniciar el sistema, la carpeta se monta de forma automática.

\newpage

# Referencias

**Ayuda de la Comunidad de Ubuntu: Guía de Configuración de NFS**: 
[https://help.ubuntu.com/community/SettingUpNFSHowTo](https://help.ubuntu.com/community/SettingUpNFSHowTo)

**Guía del Servidor Ubuntu: Sistema de Archivos en Red (Network File System, NFS)**: [https://help.ubuntu.com/lts/serverguide/network-file-system.html.en](https://help.ubuntu.com/lts/serverguide/network-file-system.html.en)

**DigitalOcean: Cómo Configurar un Montaje NFS en Ubuntu 16.04**: [https://www.digitalocean.com/community/tutorials/how-to-set-up-an-nfs-mount-on-ubuntu-16-04](https://www.digitalocean.com/community/tutorials/how-to-set-up-an-nfs-mount-on-ubuntu-16-04)

**Website for Students: Configurar Montajes NFS en Servidores Ubuntu 16.04 LTS**: [https://websiteforstudents.com/setup-nfs-mounts-on-ubuntu-16-04-lts-servers-for-client-computers-to-access/](https://websiteforstudents.com/setup-nfs-mounts-on-ubuntu-16-04-lts-servers-for-client-computers-to-access/)