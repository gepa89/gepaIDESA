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


Si tienes dudas, no dudes en consultar la documentación generada por Scribe. Puedes acceder a ella localmente desde [http://localhost:8084/docs](http://localhost:8084/docs), dependiendo de tu instalación.
