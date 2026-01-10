# 🔧 Módulo de Configuración - Guía Rápida

## 📦 Instalación

### 1. Crear la tabla de configuración

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/futurelab-ai
mysql -u root -p futurelab_ai < system_config_table.sql
```

### 2. Acceder al módulo

Una vez importada la tabla, accede a:

```
http://localhost/futurelab-ai/admin/config
```

## ✨ Características

El módulo de configuración incluye:

### 🤖 Configuración de Gemini API

- **API Key**: Guarda tu API key de forma segura
- **Modelo**: Selecciona entre:
  - Gemini 1.5 Flash (Rápido) ⚡
  - Gemini 1.5 Pro (Avanzado) 🚀
  - Gemini Nano (Ligero) 💫

- **Habilitar/Deshabilitar**: Toggle para activar o desactivar la integración

### 🧪 Prueba de Conexión

Botón "Probar Conexión" que valida:
- ✅ API Key es válida
- ✅ Conectividad con Google Gemini
- ✅ Lista de modelos disponibles

### 💾 Guardado

- Guarda en base de datos (`system_config`)
- También actualiza `config/config.php` automáticamente
- Registro de auditoría

## 📍 Rutas Nuevas

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/admin/config` | GET | Panel de configuración |
| `/api/config/save` | POST | Guardar configuración |
| `/api/config/test-gemini` | GET | Probar conexión API |

## 🎨 Menú Integrado

El enlace de configuración aparece en:
- Vista de generación de participantes
- Panel de lista de participantes  
- Panel de configuración (para navegar a otras secciones)

## 🔐 Permisos

Solo usuarios con rol **admin** pueden:
- Ver el panel de configuración
- Guardar cambios
- Probar la API

## 📝 Uso

1. **Login como admin** (admin / secret123)

2. **Ir a Configuración**:
   - Desde cualquier vista admin, click en <i class="bi bi-gear"></i> Configuración

3. **Ingresar API Key**:
   - Pega tu API key de Google Gemini
   - Selecciona el modelo deseado
   - Activa/desactiva la integración

4. **Guardar**:
   - Click en "Guardar Configuración"
   - Verás mensaje de confirmación

5. **Probar** (opcional):
   - Click en "Probar Conexión"
   - Verifica que aparezca "Conexión exitosa"

## 🎯 Obtener API Key de Gemini

1. Ve a: https://makersuite.google.com/app/apikey
2. Inicia sesión con tu cuenta Google
3. Click en "Create API Key"
4. Copia la key generada
5. Pégala en el panel de configuración

## 🔄 Actualización Automática

Al guardar la configuración:
1. Se guarda en la tabla `system_config`
2. Se actualiza el archivo `config/config.php`
3. Se registra en `audit_log`
4. Los cambios aplican inmediatamente

## 📊 Información del Sistema

El panel también muestra:
- Versión de PHP
- Directorio de storage
- Base URL
- Tamaño máximo de upload

---

**¡Listo!** Ahora tienes un panel completo para gestionar la configuración de Gemini API sin editar archivos manualmente.
