# 📋 Guía de Instalación PHP 8.2+ para Proyecto Cisternas

## 🎯 **Diagnóstico Actual**
- **PHP instalado**: 7.3.7 (❌ Incompatible)
- **PHP requerido**: 8.2+ (✅ Laravel 12.0)

---

## 🚀 **Opción 1: Instalar con XAMPP (Recomendado para Windows)**

### Paso 1: Descargar XAMPP
```
📥 Descargar desde: https://www.apachefriends.org/es/download.html
🎯 Seleccionar versión con PHP 8.2+ o superior
```

### Paso 2: Instalar XAMPP
1. Ejecutar el instalador descargado
2. Seleccionar componentes: Apache, MySQL, PHP, phpMyAdmin
3. Instalar en ruta sin espacios (ej: `C:\xampp`)

### Paso 3: Configurar PHP 8.2+ para el Proyecto

#### A. Agregar PHP al PATH del sistema
```cmd
# Abrir CMD como Administrador y ejecutar:
setx PATH "%PATH%;C:\xampp\php" /M

# O manualmente:
# 1. Buscar "Variables de entorno" en Windows
# 2. Click en "Variables de entorno"
# 3. Editar "Path" del sistema
# 4. Agregar: C:\xampp\php
```

#### B. Verificar instalación
```cmd
# Reiniciar CMD/PowerShell y verificar:
php --version
# Debe mostrar: PHP 8.2.x o superior
```

---

## 🚀 **Opción 2: Instalar PHP Puro (Avanzado)**

### Paso 1: Descargar PHP Binaries
```
📥 Descargar desde: https://windows.php.net/download/
🎯 Seleccionar VS16 x64 Thread Safe
📂 Descomprimir en: C:\php82
```

### Paso 2: Configurar PHP
1. Renombrar `php.ini-development` a `php.ini`
2. Editar `php.ini` y habilitar extensiones:
```ini
extension_dir = "ext"
extension=curl
extension=fileinfo
extension=gd
extension=mbstring
extension=openssl
extension=pdo_mysql
extension=pdo_sqlite
```

### Paso 3: Agregar al PATH
```cmd
setx PATH "%PATH%;C:\php82" /M
```

---

## 🔧 **Configurar Proyecto Cisternas**

### Paso 1: Navegar al proyecto
```cmd
cd d:\proyectos\cisternas-8.0
```

### Paso 2: Verificar versión de PHP
```cmd
php --version
# Debe mostrar 8.2.x o superior
```

### Paso 3: Instalar dependencias
```cmd
composer install
```

### Paso 4: Configurar entorno
```cmd
copy .env.example .env
php artisan key:generate
```

### Paso 5: Configurar base de datos
```cmd
# Editar .env con tus datos de MySQL
php artisan migrate
```

---

## 🎯 **Comandos Esenciales del Proyecto**

### Instalación completa
```cmd
composer run setup
```

### Desarrollo
```cmd
composer run dev-simple
```

### Tests
```cmd
composer run test
```

### Limpieza
```cmd
composer run clear
```

---

## ✅ **Verificación Final**

Ejecutar estos comandos en el proyecto:

```cmd
# 1. Verificar PHP
php --version

# 2. Verificar Composer
composer --version

# 3. Verificar Laravel
php artisan --version

# 4. Probar servidor
php artisan serve
```

### Resultado esperado:
```
PHP 8.2.x (✅)
Composer 2.x (✅)
Laravel Framework 12.x (✅)
Servidor iniciado en http://127.0.0.1:8000 (✅)
```

---

## 🚨 **Solución de Problemas**

### Error: "php no reconocido"
```cmd
# Reiniciar terminal después de agregar al PATH
# O usar ruta completa:
C:\xampp\php\php --version
```

### Error: "Extensiones faltantes"
```cmd
# Editar php.ini y descomentar extensiones necesarias
extension=mbstring
extension=pdo_mysql
extension=curl
```

### Error: "Composer no encuentra PHP"
```cmd
# Forzar a Composer a usar PHP específico:
composer config platform.php 8.2
```

---

## 📞 **Soporte**

Si tienes problemas:
1. 📧 Revisa que PHP 8.2+ esté en el PATH
2. 🔍 Verifica que las extensiones necesarias estén habilitadas
3. 🗃️ Asegúrate de que MySQL esté corriendo
4. 📝 Revisa el archivo `.env` esté configurado

---

*Guía creada para el proyecto Sistema de Gestión de Cisternas v8.0*
