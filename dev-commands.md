## Comandos para Symfony y Frontend

### Instalación de dependencias

- `composer install`  
  Instala las dependencias de PHP definidas en `composer.json`.

- `yarn install`  
  Instala las dependencias de JavaScript definidas en `package.json`.

### Compilación y desarrollo frontend

- `yarn build`  
  Compila los assets para producción.

- `yarn watch`  
  Compila los assets en modo desarrollo y observa cambios en tiempo real.

### Servidor de desarrollo Symfony

- `symfony serve`  
  Inicia el servidor de desarrollo de Symfony.

- `symfony server:start`  
  Inicia el servidor Symfony manualmente.

- `symfony server:stop`  
  Detiene el servidor Symfony.

### Generación y gestión de entidades

- `php bin/console make:entity`  
  Crea una nueva entidad Doctrine.

- `php bin/console make:entity --regenerate`  
  Regenera las entidades existentes.

- `php bin/console make:crud EntityName`  
  Genera un CRUD completo para una entidad.

### Gestión de usuarios y seguridad

- `php bin/console make:user`  
  Crea una nueva clase de usuario.

- `php bin/console security:hash-password`  
  Genera un hash para una contraseña.

### Base de datos y migraciones

- `php bin/console doctrine:database:create`  
  Crea la base de datos.

- `php bin/console doctrine:schema:validate`  
  Valida el esquema de la base de datos.

- `php bin/console doctrine:schema:update --force`  
  Actualiza el esquema de la base de datos.

### Migraciones

- `php bin/console make:migration`  
  Genera una nueva migración.

- `php bin/console doctrine:migrations:migrate`  
  Ejecuta las migraciones pendientes.

### Controladores

- `php bin/console make:controller`  
  Crea un nuevo controlador.

### Event Listeners y Subscribers

- `php bin/console make:listener`  
  Crea un nuevo event listener para escuchar eventos específicos de Symfony.

- `php bin/console make:subscriber`  
  Crea un event subscriber que puede suscribirse a múltiples eventos.

### Comandos de consola personalizados

- `php bin/console make:command`  
  Crea un nuevo comando de consola personalizado.

### Otros comandos útiles

- `php bin/console cache:clear`  
  Limpia la caché de la aplicación.

- `php bin/console debug:router`  
  Muestra información sobre las rutas definidas.

- `php bin/console debug:container`  
  Muestra información sobre los servicios registrados en el contenedor.

- `php bin/console debug:config`  
  Muestra la configuración actual de un paquete o extensión.

- `php bin/console about`  
  Muestra información sobre la instalación de Symfony.

