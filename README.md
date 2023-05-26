# Servidores Web De Altas Prestaciones

Repositorio de prácticas de la asignatura de Servidores Web de Altas Prestaciones del Doble Grado en Ingeniería Informática y Matemáticas de la Universidad de Granada para el curso 2022/2023.

- [Práctica 1 - Introducción y Preparación de Herramientas.](./Practica1/practica1.pdf)
- [Práctica 2 - Clonar la información de un Sitio Web.](./Practica2/practica2.pdf)
- [Práctica 3 - Balanceo de carga en un sitio web.](./Practica3/practica3.pdf)

## Compilación

Para la elaboración de las prácticas se ha utilizado [Pandoc](https://pandoc.org/) con la plantilla [Eisvogel](https://github.com/Wandmalfarbe/pandoc-latex-template).

```bash
$ pandoc practicax.md --template eisvogel -V lang=es --listings -o practicax.pdf
```
