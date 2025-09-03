# UPBOT – Backend (Laravel)

Este proyecto es un **asistente para consultas académicas de la UPB**.  
Permite detectar si una consulta hecha por un alumno corresponde a información **pública** o **privada**.  
Cuando la información es privada, en vez de enviarla directamente, el sistema genera un **token de acceso único** que el alumno deberá validar en la plataforma web ingresando también su identidad (CI o correo). Una vez verificado, podrá acceder a la información protegida.

---

## 🚀 Requisitos previos

Antes de instalar y correr el proyecto, asegúrate de tener:

- **XAMPP** con PHP ≥ 8.2 y MySQL/MariaDB  
- **Composer** instalado (dependencias de Laravel)  
- **Git** (para clonar el repositorio)  
- **Postman** (opcional, para probar APIs)  
- Editor de texto recomendado: **Visual Studio Code**  

Extensiones PHP necesarias (XAMPP normalmente ya las incluye):

- `openssl`  
- `pdo_mysql`  
- `mbstring`  
- `tokenizer`  
- `xml`  
- `ctype`  
- `json`  

---

## ⚙️ Instalación

1. Abrir la carpeta `htdocs` de XAMPP:  

cd C:\xampp\htdocs

text

2. Clonar el repositorio:  

git clone <repo> upbot
cd upbot

text

3. Instalar dependencias de Laravel con **Composer**:  

composer install

text

4. Copiar el archivo de entorno:  

copy .env.example .env

text

5. Configurar `.env` con tus credenciales de base de datos y claves mínimas:  

APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=upbot
DB_USERNAME=root
DB_PASSWORD=tu_clave

SESSION_DRIVER=database
MAIL_MAILER=log

UPBOT_API_KEY=supersecreto

text

6. Generar la clave de la aplicación:  

php artisan key:generate

text

7. Ejecutar migraciones y seeders:  

php artisan migrate --seed

text

---

## 👤 Usuarios de prueba

El sistema incluye un usuario demo:

- **Correo:** `alumno1@upb.edu`  
- **Contraseña:** `password123`  
- **CI:** `12345678`  

---

## 🔑 Flujo básico

1. El bot (**n8n**) recibe una consulta de un alumno.  
2. Si la info es **pública**, la devuelve directamente.  
3. Si es **privada**, el backend genera un **token único** y lo envía al bot.  
4. El alumno recibe un mensaje como:  
> “Tu información está lista. Ingresa este código en la web: `XZ91-K3LM`”  
5. El alumno entra a [`/token`](http://localhost/token), ingresa el código y verifica identidad.  
6. Si todo es correcto → se muestra la información protegida.  

---

## 🌐 Rutas principales

### Web
- `/login` → acceso con correo institucional `@upb.edu`  
- `/token` → ingreso del código de acceso  
- `/verify` → verificación de identidad  
- `/response/{payload}` → muestra información protegida  

### API
- `POST /api/interactions` → registra una interacción (usado por **n8n**)  
- `POST /api/public-response` → respuesta pública  
- `POST /api/private-response` → respuesta privada con token  

---

## 🔒 Seguridad

- Acceso solo a correos `@upb.edu`  
- API protegida con **API Key** en el header:  

Authorization: Bearer supersecreto

text

- Los tokens:
- tienen **expiración**  
- se guardan **encriptados**  
- Límite de intentos en `/token/verify`: **10/minuto**  
- Documentos privados se almacenan en `storage/app/protected` (nunca en público).  

---

## 🧪 Cómo probar

1. Generar un token de prueba:  

php artisan upbot:test-token

text

Ejemplo: `ABCD-1234`  

2. Entrar en navegador a:  
[http://localhost/login](http://localhost/login)  
Usuario demo: `alumno1@upb.edu / password123`  

3. Ir a `/token`, ingresar el código generado.  
4. Verificar con CI: `12345678`.  
5. Ver información protegida ✅  

---

## 📌 Próximos pasos recomendados

- [ ] Agregar **rate limiting** en todas las rutas API  
- [ ] Activar descarga segura de **PDFs**  
- [ ] Añadir validaciones estrictas en los controladores  
- [ ] Guardar logs en la tabla de **auditoría**  
- [ ] Implementar **OTP por correo como segundo factor**  

---

## 🛠️ Comandos útiles

- Refrescar autoload y caches:  

composer dump-autoload
php artisan optimize:clear

text

- Migrar desde cero:  

php artisan migrate:fresh --seed

text

- Generar token de prueba:  

php artisan upbot:test-token

text

---

## 📖 Estado del proyecto

✅ Listo para **demo**.  
⚡ Faltan capas adicionales de seguridad y pruebas en Postman/navegador.  