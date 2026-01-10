# INSTRUCCIONES DE ACCESO - FutureLab AI

## 🔑 CREDENCIALES ACTUALIZADAS

**Usuario:** admin
**Password:** secret123

## 🌐 ACCESO AL SISTEMA

### IMPORTANTE: Usa URLs con `index.php` explícito

Por si mod_rewrite da problemas, usa estas URLs:

**Login:**
```
http://localhost/futurelab-ai/index.php?ruta=login
```

O simplemente:
```
http://localhost/futurelab-ai/
```

## 📋 URLS COMPLETAS (con index.php)

```
Login:          http://localhost/futurelab-ai/index.php
Muro Público:   http://localhost/futurelab-ai/index.php (luego navega a /wall)
Generar:        http://localhost/futurelab-ai/index.php (accede después de login)
Admin Panel:    http://localhost/futurelab-ai/index.php (accede después de login)
```

## ⚠️ SOLUCIÓN SI NO PUEDES ACCEDER

### Opción 1: Acceso Directo sin mod_rewrite

1. Ve a: `http://localhost/futurelab-ai/`
2. Debería mostrarte el login automáticamente
3. Usuario: `admin`
4. Password: `secret123`

### Opción 2: Actualizar Password desde phpMyAdmin

1. Abre http://localhost/phpmyadmin
2. Selecciona base de datos `futurelab_ai`
3. Ve a la tabla `users`
4. Ejecuta este SQL:

```sql
UPDATE users 
SET password_hash = '$2y$10$tW.m5wv7iAukUlEiYe4G/OBYKfOuAu2CHxgiI9KieQGxeErtlLByK' 
WHERE username = 'admin';
```

O importa el archivo: `update_admin_password.sql`

### Opción 3: Si mod_rewrite no funciona

Reemplaza `.htaccess` con `.htaccess-simple`:

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/futurelab-ai
mv .htaccess .htaccess-backup
mv .htaccess-simple .htaccess
```

Luego accede con: `http://localhost/futurelab-ai/`

## 🔧 VERIFICAR QUE APACHE FUNCIONA

1. Abre: http://localhost/
2. Si NO abre, inicia Apache desde el panel de XAMPP
3. Si abre, prueba: http://localhost/futurelab-ai/

## 📝 NOTAS

- El sistema redirige `/` automáticamente a `/login`
- No necesitas escribir `/login` manualmente
- Después de login, te redirige según tu rol
- Admin va a `/admin/generate`

## ✅ CHECKLIST DE VERIFICACIÓN

- [ ] Apache está corriendo en XAMPP
- [ ] Base de datos `futurelab_ai` existe
- [ ] Password actualizado (ejecutar update_admin_password.sql)
- [ ] Carpeta storage tiene permisos (chmod 777)
- [ ] Accedes a http://localhost/futurelab-ai/
- [ ] Ves el formulario de login
- [ ] Usas admin / secret123

---

**Si sigues sin poder acceder, verifica:**
1. Que XAMPP esté corriendo (Apache y MySQL)
2. Que la base de datos esté importada
3. Que no haya errores en el log de Apache
