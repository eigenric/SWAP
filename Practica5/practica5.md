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
2. Realizar la copia de seguridad de la BD completa usando mysqldump en la
máquina principal y copiar el archivo de copia de seguridad a la máquina
secundaria.
3. Restaurar dicha copia de seguridad en la segunda máquina (clonado manual de
la BD), de forma que en ambas máquinas esté esa BD de forma idéntica.
4. Realizar la configuración maestro-esclavo de los servidores MySQL en M1 y M2
para que la replicación de datos se realice automáticamente. M1 (maestro) – M2
(esclavo)
5. Añadir regla IPTABLES para permitir tráfico al puerto 3306


