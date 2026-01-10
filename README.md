# FutureLab AI - Sistema de Eventos con IA

Sistema completo de captura de participantes con cámara web, generación de imágenes con Gemini AI, códigos QR, muro público con carrusel auto-refresh, y panel administrativo con DataTables.

## 📋 Características

- ✅ **Autenticación multi-rol** (admin, operator, viewer)
- 📸 **Captura de fotos** con cámara web (getUserMedia API)
- 🤖 **Integración con Gemini AI** para procesamiento de imágenes
- #️⃣ **Generación automática** de códigos QR
- 🖼️ **Muro público** con carrusel automático (auto-refresh cada 5s)
- 📊 **Panel administrativo** con DataTables server-side
- 🔄 **Worker de cola** para procesamiento asíncrono
- 🎨 **Diseño moderno** con gradientes y animaciones

## 🛠️ Requisitos

- XAMPP (PHP 7.4+ con extensiones cURL, GD, PDO MySQL)
- MySQL/MariaDB
- Navegador moderno con soporte para getUserMedia
- API Key de Google Gemini (opcional para generación de imágenes)

## 📦 Instalación

### 1. Importar Base de Datos

```bash
# Opción A: Desde phpMyAdmin
# 1. Abre http://localhost/phpmyadmin
# 2. Crea una base de datos llamada 'futurelab_ai'
# 3. Importa el archivo futurelab_ai.sql

# Opción B: Desde terminal
mysql -u root -p < futurelab_ai.sql
mysql -u root -p futurelab_ai < futurelab_ai_passwords.sql
```

### 2. Configurar el Proyecto

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/futurelab-ai
```

Edita `config/config.php` y actualiza:

```php
// Tu API Key de Gemini (obtén una en https://makersuite.google.com/app/apikey)
define('GEMINI_API_KEY', 'TU_API_KEY_REAL_AQUI');

// Verifica que la base de datos sea correcta
define('DB_SCHEMA', 'futurelab_ai');
```

### 3. Dar Permisos a las Carpetas de Storage

```bash
chmod -R 777 storage/
```

> **Nota:** En producción, usa permisos más restrictivos (755 para directorios, 644 para archivos) y asegúrate de que el usuario de Apache tenga acceso.

### 4. Habilitar mod_rewrite en Apache (XAMPP)

Edita `/Applications/XAMPP/xamppfiles/etc/httpd.conf`:

```apache
# Busca esta línea y descomenta (quita el #)
LoadModule rewrite_module modules/mod_rewrite.so

# Busca AllowOverride None y cámbialo a:
AllowOverride All
```

Reinicia Apache desde el panel de XAMPP.

## 🚀 Uso

### Acceso al Sistema

1. **Login**: http://localhost/futurelab-ai/login

**Credenciales de prueba:**
- **Admin**: `admin` / `admin123` (acceso total)
- **Operador**: `operador` / `oper123` (puede generar participantes)
- **Viewer**: `viewer` / `view123` (solo puede ver el muro)

### Generar Participantes

1. Login como admin u operador
2. Ve a: http://localhost/futurelab-ai/admin/generate
3. Haz clic en "Iniciar Cámara" y permite el acceso
4. Captura la foto
5. Llena nombre, apellido y selecciona carrera
6. Haz clic en "Generar Participante"
7. Se mostrará un QR grande para que el participante lo escanee
8. El participante queda en cola para procesamiento

### Ejecutar el Worker

El worker procesa la cola de participantes y genera las imágenes con Gemini:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/futurelab-ai
php -f config/worker.php
```

> **Tip:** Para procesamiento continuo, puedes configurar un cron job o ejecutar el worker en loop:
> 
> ```bash
> # Ejecutar cada 2 minutos con cron
> */2 * * * * cd /Applications/XAMPP/xamppfiles/htdocs/futurelab-ai && php -f config/worker.php
> ```

### Ver el Muro Público

1. Abre: http://localhost/futurelab-ai/wall
2. El muro es **público** (no requiere login)
3. Las imágenes cambian automáticamente cada 5 segundos
4. Nuevos participantes aparecen automáticamente (polling cada 5s)

### Panel de Administración

1. Login como admin u operador
2. Ve a: http://localhost/futurelab-ai/admin/participants
3. Verás una tabla con todos los participantes
4. Puedes:
   - **Buscar** por nombre, código, carrera, etc.
   - **Ordenar** por cualquier columna
   - **Ver detalle** de un participante
   - **Reintentar** procesamiento si hubo error
   - **Eliminar** participantes (solo admin)

## 📁 Estructura del Proyecto

```
/futurelab-ai
├── config/
│   ├── config.php           # Configuración principal
│   ├── auth.php             # Helpers de autenticación
│   ├── GeminiClient.php     # Cliente API de Gemini
│   ├── phpqrcode.php        # Librería QR
│   └── worker.php           # Worker para procesar cola
├── controlador/
│   ├── AuthControlador.php
│   ├── ParticipanteControlador.php
│   ├── WallControlador.php
│   └── AdminParticipantesControlador.php
├── modelo/
│   ├── conexion.php
│   ├── UsuarioModel.php
│   ├── RolModel.php
│   ├── CarreraModel.php
│   ├── ParticipanteModel.php
│   └── AuditLogModel.php
├── vista/
│   ├── login.php
│   ├── wall.php
│   └── admin/
│       ├── generate.php
│       └── participants.php
├── storage/
│   ├── uploads/             # Fotos originales
│   ├── results/             # Imágenes generadas
│   └── qr/                  # Códigos QR
├── index.php                # Front controller y router
├── .htaccess                # URL rewriting
└── futurelab_ai.sql         # Base de datos

```

## 🔄 Flujo del Sistema

1. **Operador** captura foto del participante + datos
2. Sistema guarda foto, genera QR y crea registro con `status='queued'`
3. **Worker** toma participantes en cola cada X tiempo
4. Worker marca como `processing`, llama a Gemini, guarda resultado
5. Worker actualiza registro a `status='done'`
6. **Muro público** muestra automáticamente nuevos participantes
7. **Panel admin** permite ver, buscar y gestionar participantes

## 🐛 Troubleshooting

### La cámara no funciona
- Verifica que estés usando HTTPS o `localhost` (getUserMedia requiere contexto seguro)
- Revisa permisos del navegador para acceder a la cámara

### Error "API Key no configurada"
- Edita `config/config.php` y reemplaza `TU_API_KEY_AQUI` con tu API key real de Gemini

### Las imágenes no se guardan
- Verifica permisos de la carpeta `storage/`: `chmod -R 777 storage/`

### El QR no se genera
- El sistema usa una API externa como fallback
- Verifica conectividad a internet
- Opcionalmente, instala `chillerlan/php-qrcode` con Composer

### Los passwords no funcionan
- Ejecuta el archivo `futurelab_ai_passwords.sql` para actualizar los hashes
- Los passwords deben estar hasheados con `password_hash()`

### Error 404 en las rutas
- Verifica que `mod_rewrite` esté habilitado en Apache
- Revisa el archivo `.htaccess` en la raíz del proyecto
- Asegúrate de que `AllowOverride All` esté configurado en Apache

## 📝 Notas Importantes

### Sobre Gemini API

La versión actual de Gemini 1.5 Flash **NO genera imágenes**, solo analiza y describe. El worker está configurado para:

1. Analizar la foto con Gemini (genera descripción)
2. Por ahora, guardar la foto original como "resultado"

**Para integrar generación real de imágenes:**
- Usa otro modelo como DALL-E, Stable Diffusion, o Midjourney
- O integra servicios como Replicate, RunPod, etc.
- Modifica `config/GeminiClient.php` según el servicio elegido

### Seguridad en Producción

Para poner en producción:
1. Cambia las credenciales de usuario
2. Usa HTTPS obligatorio
3. Configura permisos de archivos correctamente
4. Sanitiza todas las entradas
5. Habilita logs de errores
6. Considera rate limiting para las APIs

## 🎨 Personalización

### Cambiar colores del tema

Edita las variables CSS en cada vista:

```css
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
```

### Modificar tiempo del carrusel

En `vista/wall.php`, cambia:

```javascript
const CAROUSEL_INTERVAL = 5000; // milisegundos
const POLLING_INTERVAL = 5000;  // milisegundos
```

### Agregar más carreras

Inserta en la tabla `careers`:

```sql
INSERT INTO careers (name, category, is_active, sort_order)
VALUES ('Nueva Carrera', 'Categoría', 1, 100);
```

## 📄 Licencia

Este proyecto fue creado específicamente para FutureLab AI Event System.

## 🤝 Soporte

Para dudas o problemas:
1. Revisa la sección de Troubleshooting
2. Verifica los logs de PHP en XAMPP
3. Inspecciona la consola del navegador para errores JavaScript

---

**© 2026 FutureLab AI - Sistema de Eventos Interactivos**
