# UPBOT – Backend (Laravel)

Este proyecto es un asistente para consultas de la UPB.  
El sistema está pensado para que un bot (por ejemplo con n8n) pueda detectar si una consulta de un alumno es **información pública** o **información privada**.  
Cuando la información es privada, el bot no la entrega directamente, sino que genera un **token de acceso único**. El alumno debe ingresar este token en la plataforma web, verificar su identidad (por ejemplo con su CI), y recién ahí puede ver su información protegida.

---

## Requisitos

Antes de instalar y correr el proyecto, asegúrate de tener:

- XAMPP con PHP 8.2 o superior y MySQL/MariaDB.  
- Composer instalado en tu máquina (para manejar dependencias de Laravel).  
- Git instalado (para clonar el repositorio si es necesario).  
- Postman (opcional, para probar las APIs).  
- Editor de texto recomendado: Visual Studio Code.

Extensiones de PHP necesarias (normalmente XAMPP ya las trae):  
- openssl  
- pdo_mysql  
- mbstring  
- tokenizer  
- xml  
- ctype  
- json  

---

## Instalación

1. Ir a la carpeta de XAMPP donde se guardan los proyectos web:
   ```bash
   cd C:\xampp\htdocs
````

2. Clonar el repositorio o copiar el proyecto dentro de `htdocs`.
   Ejemplo si usas git:

   ```bash
   git clone <repo> upbot
   cd upbot
   ```

3. Instalar dependencias con Composer:

   ```bash
   composer install
   ```

4. Copiar el archivo de configuración de entorno:

   ```bash
   copy .env.example .env
   ```

5. Editar el archivo `.env` con la configuración de tu base de datos y claves.
   Ejemplo mínimo:

   ```
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://localhost

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=upbot
   DB_USERNAME=root
   DB_PASSWORD=#tu clave

   SESSION_DRIVER=database

   MAIL_MAILER=log
   UPBOT_API_KEY=supersecreto
   ```

6. Generar la clave de la aplicación:

   ```bash
   php artisan key:generate
   ```

7. Ejecutar las migraciones y seeders para crear la base de datos con datos de ejemplo:

   ```bash
   php artisan migrate --seed
   ```

---

## Usuarios de prueba

El sistema incluye un alumno de prueba para hacer las primeras pruebas:

* Correo: `alumno1@upb.edu`
* Contraseña: `password123`
* CI: `12345678`

---

## Flujo básico del sistema

1. El bot (n8n) recibe una consulta del alumno.
2. Si la información es sensible, el bot solicita al backend generar un **token de acceso**.
3. El backend crea el token y lo devuelve.
4. El bot le responde al alumno con un mensaje del tipo:
   “Tu información está lista, por favor entra a la página con el siguiente código: XZ91-K3LM”.
5. El alumno va al sitio web (`/token`), ingresa el código y verifica su identidad (con CI o email).
6. Si todo es correcto, el sistema muestra el documento o la información protegida.

---

## Rutas principales

### Web

* `/login` → acceso con correo institucional (@upb.edu).
* `/token` → formulario para ingresar código de acceso.
* `/verify` → formulario para verificar identidad.
* `/response/{payload}` → muestra la información protegida.

### API

* `/api/interactions` → registra la interacción (n8n).
* `/api/public-response` → genera respuesta pública.
* `/api/private-response` → genera respuesta privada con token.

---

## Seguridad

* Solo se permite acceso web a correos institucionales (`@upb.edu`).
* La API está protegida con un API Key (`Authorization: Bearer supersecreto`).
* Los tokens de acceso tienen expiración y se guardan encriptados.
* Se agregó límite de intentos en `/token/verify`: máximo 10 por minuto.
* Los documentos privados deben guardarse en `storage/app/protected` y nunca en la carpeta pública.

---

## Cómo probar el sistema

1. Generar un token de prueba:

   ```bash
   php artisan upbot:test-token
   ```

   Esto devolverá un código como `ABCD-1234`.

2. Entrar al navegador en `http://localhost/login` con el usuario demo (`alumno1@upb.edu` / `password123`).

3. Ir a `/token` e ingresar el código.

4. Luego ingresar el CI (`12345678`).

5. Si todo es correcto, se mostrará la información protegida.

---

## Próximos pasos recomendados

* Agregar rate limiting a todas las rutas del API (`/api`).
* Habilitar la descarga segura de PDFs en las vistas.
* Añadir validaciones más estrictas en los controladores de API.
* Registrar acciones importantes en la tabla de auditoría (ejemplo: cuando alguien redime un token o descarga un documento).
* Implementar OTP por correo como doble factor de seguridad.

---

## Comandos útiles

* Refrescar autoload y caches:

  ```bash
  composer dump-autoload
  php artisan optimize:clear
  ```

* Migrar desde cero:

  ```bash
  php artisan migrate:fresh --seed
  ```

* Generar token de prueba:

  ```bash
  php artisan upbot:test-token
  ```

---

Este backend ya está **listo para demo**. Lo que queda pendiente son capas adicionales de seguridad y pruebas con Postman/navegador para asegurarse de que todo funciona correctamente.
