---
title: "Servidores Web de Altas Prestaciones. Práctica 2"
author: ["Ricardo Ruiz Fernández de Alba"]
date: "22/05/2023"
subject: "Servidores Web de Altas Prestaciones"
keywords: ["SSH", "SCP", "Claves público-privada", "Rsync", "Tareas cron"]
subtitle: "Clonar la información de un Sitio Web."
titlepage: true
titlepage-background: "Practica2/background1.pdf"
toc: true
toc-own-page: true
# titlepage-color: "3C9F53"
# titlepage-text-color: "FFFFFF"
# titlepage-rule-color: "FFFFFF"
# titlepage-rule-height: 2
---

# Introducción

En esta práctica, se abordarán varios aspectos relacionados con el uso de SSH y la automatización de tareas. Los objetivos principales son aprender a copiar archivos mediante SSH, clonar contenido entre máquinas, configurar SSH para acceder a máquinas remotas sin contraseña y establecer tareas periódicas utilizando cron.

Realizaremos las siguientes tareas básicas: 

1. Probar la funcionalidad de copia de archivos a través de SSH.
2. Realizar el clonado de una carpeta entre las dos máquinas.
3. Configurar SSH para permitir el acceso sin solicitar contraseña.
4. Establecer una tarea programada en cron que se ejecute cada hora para mantener actualizado el contenido del directorio /var/www entre las dos máquinas.

# Tarea 1. Copiar archivos mediante SSH

## Usando tubería PIPE

Supongamos que no tenemos suficiente espacio en disco local para crear un archivo tar.gz. Podemos crearlo directamente en el equipo remoto mediante SSH.:

```shell
ricardoruiz@m1-ricardoruiz $ tar -czf - copia_local/ | ssh ricardoruiz@192.167.2.20 'cat > ~/archivo.tgz'
``` 

De esta manera, se creará el archivo tar.tgz en el equipo remoto.

![Copia mediante SSH](Practica2/assets/Figura1.png)

También es posible utilizar SCP, que utiliza SSH para realizar copias seguras y encriptadas de archivos o directorios. Podemos crear un archivo tar.gz localmente y luego copiarlo al equipo remoto utilizando SCP. Los comandos serían los siguientes:


## Usando SCP

También podemos realizar esta tarea usando SCP que utiliza SSH para hacer copias seguras y encriptadas de archivos o directorios.

```shell
ricardoruiz@m1-ricardoruiz $ tar -czvf archivo2.tgz copia_local
ricardoruiz@m1-ricardoruiz $ scp archivo.tgz ricardoruiz@192.168.2.20:~/archivo2.tgz
```

![Copia mediante SCP](Practica2/assets/Figura2.png)

O enviar el propio directorio sin comprimir:

```shell
$ scp -r copia_local ricardoruiz@m2-ricardoruiz:~/copia_local
```

![Copia del direcotorio mediante SCP](Practica2/assets/Figura3.png)


# Tarea 2. Clonar contenido entre máquinas

Para directorios de mayor tamaño, es mejor utiliza rsync. La herramienta rsync es una opción útil para realizar copias y sincronización de archivos. Lo instalamos:


```shell
ricardoruiz@m2-ricardoruiz $ sudo apt-get install rsync
```

\newpage 

Queremos clonar la carpeta que contiene el servidor web principal desde la máquina secundario. 
Creamos un archivo `m1.html` en `/var/www/html` para distinguir el servidor principal.

Es necesario que el usuario sea propietario de la carpeta que queremos sincronizar. 


```shell
ricardoruiz@m2-ricardoruiz $ sudo chown ricardoruiz:ricardoruiz -R /var/www
ricardoruiz@m2-ricardoruiz $ rsync -avz -e ssh 192.168.2.10:/var/www/ /var/www
```
Rsync nos pedirá la clave de usuario en M1 y comprobamos que se actualiza el contenido del directorio `/var/www/html` con el contenido del servidor principal (`m1.html`)

![Rsync](Practica2/assets/Figura4.png)

##  Configuraciones avanzadas de Rsync 

Podemos especificar qué directorios ignorar durante el proceso de copia. Por ejemplo, si queremos realiza rcopia de `/var/www`pero excluir los directorio `/var/www/error`, `/var/www/stats` y `/var/www/files/pictures` usamos `--exclude`.

En primer lugar, creamos la estructura de directorios en el servidor secundario, con los archivos `error.html`, `stats.html` y añadimos `img.jpg` a `/var/www/files/pictures`.

![Estructura de directorios](Practica2/assets/Figura5.png)

\newpage

```shell
ricardoruiz@m2-ricardoruiz $ rsync /var/www/ -avz --delete --exclude=**/stats --exclude=**/error --exclude=**/files/pictures -e ssh 192.168.2.10:/var/www
```
  
Así evitamos que los errores y estadísticas de la máquina 2 se sobreescriban con los de la principal.

![Opción exclude](Practica2/assets/Figura6.png)

![Opción exclude](Practica2/assets/Figura7.png)

La opción `--delete` indica que los archivos que se hayan eliminado en la máquina de origen también se eliminarán en la máquina de destino para asegurar una clonación idéntica.

Comprobémoslo eliminando el directorio files y volviendo a ejecutar `rsync`:

![Opción delete](Practica2/assets/Figura8.png)

![Opción delete](Practica2/assets/Figura9.png)

# Tarea 3. Configurar SSH para acceder sin contraseña

Para lograr una actualización automática sin intervención del administrador, es necesario utilizar autenticación con claves pública-privada.

De forma parecida como se hicimos en la configuración avanzada de la primer práctica, ejecutamos esta vez en M2:

```shell
ricardoruiz@m2-ricardoruiz $ ssh-keygen -b 4096 -t rsa
```

Esto generará, por defecto, el fichero `~/.ssh/id_rsa` para la clave privada y el ficher `~/.ssh/id_rsa.pub` para la clave pública. 

Este formato es válido para el protocolo 2 de SSH. Debemos copiar la clave pública al equipo remoto (máquina principal) en `~/.ssh/authorized_key` con permisos 60-s0.

Podemos realizarlo de forma sencilla utilizando el comando `ssh-copy-id`.

```shell
ricardoruiz@m2-ricardoruiz $ ssh-copy-id 192.168.2.10
```

Finalmente, podemos destacar la manera de ejecuta comandos en el equipo remoto,
esta vez sin solicitud de contraseña.

```shell
ricardoruiz@m2-ricardoruiz $ ssh 192.168.1.20 uname -a
```

![](Practica2/assets/Figura10.png)

Podemos ahora automatizar la sincronización de directorios con rsync utilizando cron.

\newpage

# Tarea 4. Establecer una tarea con Cron

Cron es un administrador de procesos en segundo plano que ejecuta tareas
programadas en momentos específicos. Está configurado mediante el archivo
`/etc/crontab`, que contiene las tareas a ejecutar junto con su frecuencia y el usuario que las ejecuta.

Las tareas se definen utilizando siete campos en cada línea del archivo crontab, que representan el minuto, hora, día del mes, mes, día de la semana, usuario y comando a ejecutar. 

Un asterisco representa la tarea se ejecutará en cada valor válido en esos campos.

Como ejemplo, la siguiente tarea apagará el ordenador cada día a las 00:30h:

```
30 0 * * * root /sbin/shutdown -h now
```

## Utilizar Cron para automatizar Rsync

Creamos la siguiente tarae de cron `crontab -e`

```
0 * * * * rsync /var/www/ -avz --exclude=**/stats --exclude=**/error --exclude=**/files/pictures -e ssh 192.168.2.10:/var/www
```

La línea  `0 * * * *` indica que la tarea se ejecutará en el minuto 0 de cada hora todos los días.

## Demostración

Los estados de las carpetas `/var/www` de las máquinas M2 y M1 respectivamente aproximadamente a las 11:48 / 11:49 son:

![Estado /var/www en M1](Practica2/assets/Figura11.png)

![Estado /var/www en M2](Practica2/assets/Figura12.png)

Configuramos la tarea de Crontab para que se ejecute cada hora:

![Tarea crontab en M2](Practica2/assets/Figura13.png)]

Esperamos a las 12:00 y comprobamos que se ha sincronizado en M1:

![Sincronización automática](Practica2/assets/Figura14.png)

\newpage

# Referencias

Aquí tienes la lista de referencias en el formato solicitado:

- **OpenSSH.** [https://www.openssh.com/](https://www.openssh.com/)
- **SCP (Secure Copy).**  [https://man.openbsd.org/scp](https://man.openbsd.org/scp)
- **Rsync**. [https://rsync.samba.org/](https://rsync.samba.org/)
- **SSH Keygen**. [https://man.openbsd.org/ssh-keygen](https://man.openbsd.org/ssh-keygen)
- **Cron.** [https://man7.org/linux/man-pages/man8/cron.8.html](https://man7.org/linux/man-pages/man8/cron.8.html)