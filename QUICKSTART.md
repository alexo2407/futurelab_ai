# FutureLab AI - Guía Rápida

## 🚀 Inicio Rápido

### 1. Importar Base de Datos
```bash
mysql -u root -p < futurelab_ai.sql
mysql -u root -p futurelab_ai < futurelab_ai_passwords.sql
```

### 2. Configurar API Key
Edita `config/config.php`:
```php
define('GEMINI_API_KEY', 'TU_API_KEY_AQUI');
```

### 3. Permisos
```bash
chmod -R 777 storage/
```

### 4. Acceder
http://localhost/futurelab-ai/

**Usuarios:**
- `admin` / `secret123` ⭐
- `operador` / `oper123`
- `viewer` / `view123`

## 📍 URLs Principales

| URL | Descripción | Requiere Login |
|-----|-------------|----------------|
| `/login` | Login | No |
| `/wall` | Muro público | No |
| `/admin/generate` | Generar participante | Sí (admin/operator) |
| `/admin/participants` | Lista participantes | Sí (admin/operator) |

## ⚙️ Ejecutar Worker

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/futurelab-ai
php -f config/worker.php
```

## 📝 Credenciales Predeterminadas

| Usuario | Password | Rol | Permisos |
|---------|----------|-----|----------|
| admin | secret123 | admin | Todo incluido delete |
| operador | oper123 | operator | Generar y ver |
| viewer | view123 | viewer | Solo muro público |

## 🗂️ Estructura de Archivos

```
/futurelab-ai
├── config/          # Configuración y helpers
├── controlador/     # Lógica de negocio
├── modelo/          # Acceso a datos
├── vista/           # Interfaces de usuario
├── storage/         # Archivos subidos/generados
│   ├── uploads/     # Fotos originales
│   ├── results/     # Imágenes generadas
│   └── qr/          # Códigos QR
└── index.php        # Router principal
```

## 🔄 Flujo del Sistema

1. **Operador** captura foto + datos → status `queued`
2. **Worker** procesa → status `processing`
3. Llama **Gemini API** → genera imagen
4. Actualiza → status `done`
5. **Muro** muestra automáticamente

## ⚠️ Troubleshooting

**Cámara no funciona**
→ Usa `localhost` o HTTPS

**Error 404**
→ Habilita `mod_rewrite` en Apache

**Imágenes no se guardan**
→ `chmod -R 777 storage/`

**QR no genera**
→ Verifica conexión a internet

## 📊 Características

✅ Autenticación multi-rol
✅ Captura con cámara web
✅ Generación de QR
✅ Worker de procesamiento
✅ Muro con carrusel (auto-refresh 5s)
✅ Panel admin con DataTables
✅ Diseño premium

## 🎨 Personalizar

**Colores:**
```css
/* En cada vista */
--primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

**Tiempo de carrusel:**
```javascript
/* En vista/wall.php */
const CAROUSEL_INTERVAL = 5000; // ms
```

**Agregar carrera:**
```sql
INSERT INTO careers (name, category, is_active, sort_order)
VALUES ('Nueva Carrera', 'Categoría', 1, 100);
```

---

Ver [README.md](file:///Applications/XAMPP/xamppfiles/htdocs/futurelab-ai/README.md) para documentación completa.
