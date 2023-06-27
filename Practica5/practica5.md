---
title: "Servidores Web de Altas Prestaciones. Práctica 5"
author: ["Ricardo Ruiz Fernández de Alba"]
date: "24/06/2023"
subject: "Servidores Web de Altas Prestaciones"
keywords: ["MySQL", "Replicación"]
subtitle: "Replicación de bases de datos MySQL"
titlepage: true
titlepage-background: "Practica5/background1.pdf"
toc: true
toc-own-page: true
# titlepage-color: "3C9F53"
# titlepage-text-color: "FFFFFF"
# titlepage-rule-color: "FFFFFF"
# titlepage-rule-height: 2
---

# Introducción

Para respaldar bases de datos MySQL, es común utilizar una réplica maestro-esclavo. El servidor en producción actúa como maestro y otro servidor funciona como respaldo, brindando mayor fiabilidad en caso de fallos o interrupciones permanentes. Tener un servidor de respaldo con MySQL como esclavo es una solución asequible que no afecta el rendimiento del sistema en producción.

# Tareas Básicas

En esta práctica se llevarán a cabo, como tareas básicas:

1. Crear una BD con al menos una tabla y algunos datos.
2. Realizar la copia de seguridad de la BD completa usando mysqldump en la máquina principal y copiar el archivo de copia de seguridad a la máquina secundaria.
3. Restaurar dicha copia de seguridad en la segunda máquina (clonado manual de
la BD), de forma que en ambas máquinas esté esa BD de forma idéntica.
4. Realizar la configuración maestro-esclavo de los servidores MySQL en M1 y M2
para que la replicación de datos se realice automáticamente. M1 (maestro) – M2
(esclavo).
5. Añadir regla IPTABLES para permitir tráfico al puerto 3306


## Tarea 1. Crear una BD con al menos una tabla y algunos datos.

Ejecutamos las siguiente ordenes en la máquina M1:

![Creación de la base de datos](Practica5/assets/Figura1.png)

## Tarea 2. Realizar la copia de seguridad de la BD completa.

Utilizaremos mysqldump en la máquina principal y copiaremos el archivo de copia de seguridad a la máquina secundaria.

Seguimos los siguiente pasos en la máquina M1:

- Conectar al servidor mysql para bloquear tablas
- Hacer copia de base de datos “estudiante” en archivo .sql
- Conectar al servidor mysql para desbloquear tablas

![Copia de seguridad de la base de datos](Practica5/assets/Figura2.png)

Ahora copiaremos el archivo de copia de seguridad a la máquina secundaria:

![Copia de seguridad de la base de datos](Practica5/assets/Figura3.png)

## Tarea 3. Restaurar dicha copia de seguridad en la segunda máquina.

Restauraremos la copia de seguridad en la segunda máquina (clonado manual de la BD), de forma que en ambas máquinas esté esa BD de forma idéntica.

![Restauración de la base de datos](Practica5/assets/Figura4.png)


## Tarea 4. Realizar la configuración maestro-esclavo de los servidores MySQL en M1 y M2 para que la replicación de datos se realice automáticamente.

MySQL tiene la opción de configurar el demonio para hacer replicación de las BD sobre un esclavo a partir de los datos que almacena el maestro.

Antes de nada, debemos desactivar las reglas de iptables.

```shell
ricardoruiz@m1-ricardoruiz $ iptables -F
ricardoruiz@m1-ricardoruiz $ iptables -X
```

En M1, como root editamos el archivo
`/etc/mysql/mysql.conf.d/mysqld.cnf`

```
[...]
#bind-address 127.0.0.1
log_error = /var/log/mysql/error.log
server-id = 1
log_bin = /var/log/mysql/mysql- bin.log
[...]
```

![Configuración MySQL](Practica5/assets/Figura5.png)

![Configuración MySQL](Practica5/assets/Figura6.png)

Reiniciamos el servicios y comprobamos que no hay errores

![Reinicio MySQL](Practica5/assets/Figura7.png)

Creamos un usuario esclavo, mostramos la configuración maestro y activamos las tablas:

![Usuario esclavo](Practica5/assets/Figura9.png)

Ahora en M2, como root, editar el archivo
`/etc/mysql/mysql.conf.d/mysqld.cnf`

```shell
#bind-address 127.0.0.1
log_error = /var/log/mysql/error.log
server-id = 2
log_bin = /var/log/mysql/mysql-bin.log
```

Reiniciamos el servicio y comprobamos que no hay errores

![Reinicio MySQL](Practica5/assets/Figura8.png)

Configuramos esclavo con los datos del maestro:
 
![Configuración esclavo](Practica5/assets/Figura10.png)

Y Comprobamos el estado del esclavo

![Estado esclavo](Practica5/assets/Figura11.png)

Y comprobamos que funciona añadiendo datos al maestro y viendo como se actualizan en el esclavo:

![Nuevos datos maestro](Practica5/assets/Figura12.png)

![Actualizacion esclavo](Practica5/assets/Figura13.png)