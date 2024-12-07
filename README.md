# gepaIDESA
Desafio IDESA

# Laravel Project Setup

Este documento detalla los pasos para clonar y montar este proyecto Laravel desde GitHub. Puedes configurarlo utilizando Docker o sin Docker, según tus necesidades.

---

## Clonar el repositorio

1. Asegúrate de tener instalado Git en tu sistema.
2. Clona el repositorio ejecutando el siguiente comando en tu terminal:
   ```bash
   git clone git@github.com:gepa89/gepaIDESA.git
   ```
3. Navega al directorio del proyecto:
   ```bash
   cd gepaIDESA
   ```

---

## Configuración con Docker

### Requisitos previos

- Docker y Docker Compose instalados.

### Pasos

1. Copia el archivo `.env.example` y renómbralo a `.env` ubicado en el directorio `gepaIDESA/src`:
   ```bash
   cp .env.example .env
   ```

2. Edita el archivo `.env` según tus necesidades. Asegúrate de configurar los siguientes valores:
   - **Base de datos**:
     ```env
     DB_CONNECTION=mysql
     DB_HOST=db
     DB_PORT=3306
     DB_DATABASE=library_db
     DB_USERNAME=laravel
     DB_PASSWORD=secret
     ```

3. Construye y levanta los contenedores:
   ```bash
   docker-compose up --build -d
   ```

4. Accede al contenedor de la aplicación:
   ```bash
   docker exec -it laravel-app bash
   ```

5. Navega al directorio `/var/www/html/src/` dentro del contenedor:
   ```bash
   cd /var/www/html/src/
   ```

6. Instala las dependencias del proyecto:
   ```bash
   composer install
   ```

7. Genera la clave de la aplicación:
   ```bash
   php artisan key:generate
   ```

8. Ejecuta las migraciones para configurar la base de datos:
   ```bash
   php artisan migrate
   ```

9. El proyecto estará disponible en [http://localhost:8084](http://localhost:8084).

---

## Configuración sin Docker

### Requisitos previos

- PHP (8.1+ recomendado).
- Composer.
- MySQL.
- Servidor web como Apache o Nginx.

### Pasos

1. Copia el archivo `.env.example` y renómbralo a `.env` ubicado en el directorio `gepaIDESA/src`:
   ```bash
   cp .env.example .env
   ```

2. Edita el archivo `.env` según tus necesidades. Configura los valores para la base de datos que utilizarás localmente:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=library_db
   DB_USERNAME=laravel
   DB_PASSWORD=secret
   ```

3. Navega al directorio `src` del proyecto:
   ```bash
   cd src
   ```

4. Instala las dependencias de Laravel:
   ```bash
   composer install
   ```

5. Genera la clave de la aplicación:
   ```bash
   php artisan key:generate
   ```

6. Ejecuta las migraciones para configurar la base de datos:
   ```bash
   php artisan migrate
   ```

7. Configura tu servidor web para que el directorio raíz apunte a `src/public`.

8. El proyecto estará disponible en el dominio o dirección configurada en tu servidor web.

---

## Notas

- Si encuentras problemas de permisos con la carpeta `storage`, asegúrate de ejecutar:
  ```bash
  chmod -R 775 storage bootstrap/cache
  ```
- Si usas Docker y necesitas revisar los logs, ejecuta:
  ```bash
  docker-compose logs -f
  ```
- El archivo docker-compose.yml configura el puerto 8084 para acceder a la aplicación. Puedes abrir http://localhost:8084 en tu navegador para verificar el funcionamiento.
  
- En caso de montar el proyecto sin docker, se utiliza la configuración del servidor web que utilices.
  
---

## Dependencias del Proyecto

Este proyecto utiliza las siguientes dependencias, administradas por Composer:

### Framework principal
- **laravel/framework**: El marco de trabajo principal de PHP utilizado para este proyecto.

### Dependencias de desarrollo
- **knuckleswtf/scribe**: Generador automático de documentación para APIs en Laravel.
- **phpunit/phpunit**: Framework para pruebas unitarias.
- **nunomaduro/collision**: Manejo de errores para aplicaciones en la línea de comandos.
- **laravel/sail**: Configuración de Docker para aplicaciones Laravel.
- **laravel/pint**: Formateador de código para PHP.
- **laravel/tinker**: REPL para Laravel, utilizado para interactuar con la aplicación desde la línea de comandos.

### Librerías de utilidades
- **fakerphp/faker**: Generador de datos ficticios, utilizado principalmente en los factories para pruebas.
- **nesbot/carbon**: Extensión de API para DateTime con soporte multi-idioma.
- **guzzlehttp/guzzle**: Cliente HTTP para realizar peticiones externas.
- **symfony/console**: Herramientas para crear interfaces de línea de comandos.
- **symfony/mailer**: Librería para el envío de correos electrónicos.
- **fruitcake/php-cors**: Implementación de CORS para solicitudes HTTP.
- **league/flysystem**: Abstracción para el manejo de sistemas de archivos.

### Dependencias para la documentación
- **erusev/parsedown**: Parser para Markdown, utilizado en la generación de documentación.

### Herramientas de depuración
- **filp/whoops**: Manejo de errores con una interfaz amigable.
- **spatie/laravel-ignition**: Página de errores mejorada para Laravel.

### Otras dependencias clave
- **psr/http-client**, **psr/http-message**, **psr/container**: Interfaces estándar para HTTP, contenedores y mensajes en PHP.
- **vlucas/phpdotenv**: Manejo de variables de entorno desde archivos `.env`.

### Consultar todas las dependencias
Para obtener la lista completa de dependencias instaladas y sus versiones actuales, puedes ejecutar:
```bash
composer show
```


# Guía para Ejecutar las Pruebas

El siguiente documento explica cómo ejecutar las pruebas unitarias de este proyecto y su propósito.

---

## Requisitos previos
Asegúrate de tener los siguientes requisitos instalados y configurados en tu entorno:
1. **PHP**: Versión compatible con Laravel (ver `composer.json`).
2. **Composer**: Administrador de dependencias para PHP.
4. **Migraciones**: Deben ejecutarse antes de las pruebas (se detalla más adelante).

---

## Preparación del entorno de pruebas

### 1. Instala las dependencias
Asegúrate de que todas las dependencias estén instaladas ejecutando:
```bash
composer install
```

---

## Ejecución de pruebas

Asegurate de tener las tablas y su contenido cargado utilizando el siguiente comando:
```bash
php artisan migrate:fresh --seed
```
Puedes ejecutar todas las pruebas del proyecto utilizando el siguiente comando:
```bash
php artisan test
```

### Ejecución detallada
Si deseas filtrar las pruebas por una clase o método específico, utiliza el flag `--filter`:
```bash
php artisan test --filter=AuthControllerTest
```

Esto ejecutará únicamente las pruebas definidas en `AuthControllerTest`.

### Salida esperada
Cuando las pruebas son exitosas, deberías ver algo similar a esto:
```plaintext
Tests:    23 passed (279 assertions)
Duration: 1.59s
```

---

## Significado de las pruebas

### **AuthControllerTest**
1. **Registers user successfully**  
   Valida que un usuario pueda registrarse correctamente y recibir un token de acceso en la respuesta.

2. **Registration fails with invalid data**  
   Verifica que el registro de usuario falle si se envían datos inválidos, devolviendo los errores correspondientes.

3. **Logs in user successfully**  
   Comprueba que un usuario pueda iniciar sesión correctamente utilizando credenciales válidas, obteniendo un token de acceso.

4. **Login fails with invalid credentials**  
   Valida que el intento de inicio de sesión falle si las credenciales proporcionadas no son válidas, devolviendo el código de error `401`.

5. **Revokes existing tokens on login**  
   Garantiza que todos los tokens de acceso previos de un usuario sean revocados cuando inicie sesión nuevamente.

---

### **AuthorControllerTest**
1. **Can fetch paginated authors**  
   Comprueba que la lista de autores se pueda obtener de forma paginada.

2. **Can fetch author by id**  
   Valida que un autor pueda ser consultado individualmente por su ID.

3. **Fetching non-existent author returns 404**  
   Verifica que al intentar consultar un autor que no existe, se devuelva un error `404`.

4. **Can create author**  
   Valida que se pueda crear un nuevo autor enviando los datos correctos.

5. **Creating author with invalid data returns errors**  
   Comprueba que al intentar crear un autor con datos inválidos, se devuelvan los errores correspondientes.

6. **Can update author**  
   Verifica que un autor existente pueda ser actualizado correctamente.

7. **Updating non-existent author returns 404**  
   Comprueba que al intentar actualizar un autor que no existe, se devuelva un error `404`.

8. **Can delete author**  
   Valida que un autor pueda ser eliminado correctamente.

9. **Deleting non-existent author returns 404**  
   Garantiza que al intentar eliminar un autor que no existe, se devuelva un error `404`.

---

### **BookControllerTest**
1. **Can fetch paginated books**  
   Comprueba que la lista de libros se pueda obtener de forma paginada.

2. **Can fetch book by id**  
   Valida que un libro pueda ser consultado individualmente por su ID.

3. **Fetching non-existent book returns 404**  
   Verifica que al intentar consultar un libro que no existe, se devuelva un error `404`.

4. **Can create book**  
   Valida que se pueda crear un nuevo libro enviando los datos correctos.

5. **Creating book with invalid data returns errors**  
   Comprueba que al intentar crear un libro con datos inválidos, se devuelvan los errores correspondientes.

6. **Can update book**  
   Verifica que un libro existente pueda ser actualizado correctamente.

7. **Updating non-existent book returns 404**  
   Comprueba que al intentar actualizar un libro que no existe, se devuelva un error `404`.

8. **Can delete book**  
   Valida que un libro pueda ser eliminado correctamente.

9. **Deleting non-existent book returns 404**  
   Garantiza que al intentar eliminar un libro que no existe, se devuelva un error `404`.

---

## Resolución de problemas comunes

1. **Error: Dependencias faltantes**
   - Solución: Instala todas las dependencias necesarias ejecutando:
     ```bash
     composer install
     ```

2. **Pruebas fallidas**
   - Verifica el mensaje de error detallado en la salida de los tests para identificar posibles problemas.

---

Con esta guía, deberías poder ejecutar y comprender las pruebas del proyecto correctamente. Si tienes problemas adicionales, revisa los logs detallados o contacta al equipo de desarrollo.

---
---
Si tienes dudas, no dudes en consultar la documentación generada por Scribe. Puedes acceder a ella localmente desde [http://localhost:8084/docs](http://localhost:8084/docs), dependiendo de tu instalación.
