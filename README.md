# Web App Symfony

App básica de Symfony utilizada dentro de los proyectos de la SDI

## 🛠 Instalación

Proceso de instalación en un nuevo servidor, pensado para ambientes de desarrollo o producción inicial.

### 📋 Requisitos

Asegúrese de tener instaladas las siguientes herramientas:

- [Symfony CLI](https://symfony.com/download)
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/)
- [Git](https://git-scm.com/)
- [Yarn](https://yarnpkg.com/)

---

### 📦 Pasos de instalación

Para instalar la plataforma en un nuevo servidor, siga los pasos descritos a continuación:

1. Preparar el entorno de desarrollo:

   Clone el repositorio y configure las dependencias necesarias:

   ```bash
   yarn install && yarn build    # Instala y compila recursos frontend
   composer install              # Instala dependencias PHP
   cp .env .env.local            # Copia el archivo de entorno base
   ```
   > Asegúrese de tener Symfony 7.3 configurado como versión activa en el proyecto, y de contar con PHP 8.4 o una versión superior. Luego, actualice el archivo `.env.local` con las credenciales de base de datos, mailer, y otras variables necesarias para su entorno.

2. Crear una base de datos:

   Cree la base de datos y genere su esquema base:

   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:schema:update --force
   ```

## Contenido de la App

### 👦 Manejo de usuarios

La app cuenta con un usuario base, con login mediante Siding y CAS

### 🔩 Servicios

La app cuenta con los siguientes servicios:
- DataTableService: facilita la ultilización de la librería DataTables en modo serverside
- Siding: se comunica con el WSDL de Siding para consultar información
- Sid: ejemplo de query para consultar información de la base de datos Sid
- NotificationService: centralización de correos enviados por la app


## 📎 Anexos técnicos

### 🔧 Servicios Personalizados

Para una explicación detallada del funcionamiento de los servicios de la plataforma, revisa el siguiente archivo:

[👉 Ver documentación de servicios](src/Service/README.md)

### 🖥️ Comandos de desarrollo

Consulta la lista completa y explicaciones de los comandos útiles para desarrollo en Symfony y frontend en el siguiente archivo:

[👉 Ver referencia de comandos de desarrollo](dev-commands.md)

## 📄 To Do
- Mejor manejo de las constraseñas guardadas de usuarios, para login por DB
- Mejorar el rendimiento de la applicación para que cumpla con LightHouse
- Utilizar BS4.6 en lugar de BS5 si se busca mayor compatibilidad con el kit digital