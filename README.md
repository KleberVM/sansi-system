# 🎯 Sistema de Inscripción para las Olimpiadas Oh! SanSi

<p align="center"><a href="https://tecnocursosedu.com/olimpiadas-osansi-2024/" target="_blank"><img src="https://tecnocursosedu.com/wp-content/uploads/2024/10/ohsansi.jpg" width="400"></a></p>

## 🚀 Descripción del Proyecto

Este sistema permite gestionar el proceso de inscripción de estudiantes a las Olimpiadas Oh! SanSi. Incluye funcionalidades para que los administradores, tutores y estudiantes realicen sus tareas de manera eficiente y organizada.

---

## 🔧 Tecnologías Usadas

- **PHP 7.4.22**  
- **Laravel 8.83.29**  
- **MySQL 5.7**  
- **Apache 2.4.28**  
- **Visual Studio Code**  
- **PowerDesigner** *(para el modelado de base de datos)*  
- **StarUML** *(para diagramas UML)*  
- **GIMP 2.10** *(para edición gráfica)*  

---

## ✨ Características Principales

- **Gestión de Colegios:** Crear, editar, eliminar colegios y generar reportes detallados.  
- **Gestión de Convocatorias:** Crear y controlar las convocatorias para cada edición de las olimpiadas.  
- **Gestión de Áreas de Competencia, Niveles y Categorías:** Control sobre las diferentes áreas y sus categorías.  
- **Registro de Estudiantes y Tutores:** Permite que los tutores registren estudiantes bajo su responsabilidad.  
- **Inicio de Sesión y Recuperación de Contraseña:** Sistema seguro de autenticación.  
- **Inscripción de Estudiantes:** Registro y validación de la inscripción a las áreas de competencia.  
- **Reportes:** Exportación de reportes en PDF y Excel con filtros personalizables.

---

## 🔥 Instalación y Configuración

1. **Clonar el repositorio:**
```bash
    git clone https://github.com/KleberVM/sansi-system.git
```

2. **Instalar dependencias (Recomendable estar dentro de la carpeta del proyecto):**
```bash
    composer install
    npm install
```

3. **Configurar el archivo .env:**
```bash
    cp .env.example .env
    php artisan key:generate
```

4. **Configurar la base de datos (Puedes saltarte al paso 5):**
```bash
    php artisan migrate --seed
```

5. **Levantar el servidor local:**
```bash
    php artisan serve
```

## Comandos útiles para el proyecto

### 1. `composer require doctrine/dbal`
Este comando instala el paquete **Doctrine DBAL**, el cual es necesario para realizar cambios en las columnas de las tablas existentes, como cambiar el tipo de datos de una columna (por ejemplo, de `string` a `integer`).

**Ejemplo de uso**:
Cuando quieras cambiar el tipo de una columna en una migración, como cambiar de `string` a `integer`, necesitas instalar este paquete primero, ya que Laravel requiere Doctrine DBAL para gestionar esos cambios.

```bash
composer require doctrine/dbal
```

### 2. `php artisan make:migration update_telefono_in_delegacion --table=delegacions`
Este comando crea una nueva migración para modificar la tabla especificada (`delegacions` en este caso). El parámetro `--table=delegacions` indica que la migración se aplicará a la tabla existente `delegacions`.

**Ejemplo de uso**:
Cuando necesites modificar una tabla existente (como cambiar el tipo de datos de una columna), puedes crear una nueva migración utilizando este comando. Posteriormente, podrás editar la migración para agregar los cambios específicos a la columna que deseas modificar.

```bash
php artisan make:migration update_telefono_in_delegacion --table=delegacions
```

Este comando hace dos cosas:

migrate:fresh Elimina todas las tablas de la base de datos.Luego, ejecuta todas las migraciones nuevamente desde cero.

--seed: Después de migrar la base de datos, ejecuta los seeders definidos en database/seeders/DatabaseSeeder.php. Sirve para poblar la base de datos con datos iniciales (como usuarios de prueba, roles, configuraciones, etc.).

```bash
php artisan migrate:fresh --seed 
```

Recuerda que, después de crear la migración, debes editar el archivo generado en `database/migrations` para realizar los cambios que deseas en la tabla.

---

## Extenciones que deben estar habilitadas en php.ini 

- **extension=zip**
- **extension=fileinfo**
- **extension=openssl**
- **extension=pdo_mysql (importante)**
- **extension=mysqli (importante)**
- **extension=pdo_sqlite**
- **extension=sqlite3**
- **extension_dir = "ext" (importante)**
- **extension_dir = mbstring (importante)**

---
## 🧠 Desarolladores

- **Altamirano Vargas Orlando**  
- **Cespedes Valencia Leyton**  
- **Cayola Cayo Yahir Leonardo**  
- **Luizaga Merino Gustavo**  
- **Paredes Lovera Guilder**  
- **Velasco Muruchi Kleber**  

---

## Porque Laravel ?

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

## 📚 Aprendiendo Laravel

Laravel tiene la documentación más extensa y completa entre los frameworks modernos. Puedes consultarla [aquí](https://laravel.com/docs).

Si prefieres aprender viendo videos, [Laracasts](https://laracasts.com) ofrece más de 1500 tutoriales sobre Laravel, PHP moderno, pruebas unitarias y JavaScript.

---

## 💪 Contribuir

Puedes seguir la guía de contribuciones en la [documentación de Laravel](https://laravel.com/docs/contributions).

---

## 🔒 Vulnerabilidades de Seguridad

Si descubres alguna vulnerabilidad de seguridad, por favor contacta a [taylor@laravel.com](mailto:taylor@laravel.com). Todas las vulnerabilidades serán tratadas de inmediato.

---

## 🏅 Licencia

El framework Laravel es software de código abierto licenciado bajo la [licencia MIT](https://opensource.org/licenses/MIT).

