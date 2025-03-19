# 🎯 Sistema de Inscripción para las Olimpiadas Oh! SanSi

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

## 🚀 Descripción del Proyecto

Este sistema permite gestionar el proceso de inscripción de estudiantes a las Olimpiadas Oh! SanSi. Incluye funcionalidades para que los administradores, tutores y estudiantes realicen sus tareas de manera eficiente y organizada.

---

## 🔧 Tecnologías Usadas

- **PHP 7.4.22**  
- **Laravel 8**  
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

1️⃣ **Clonar el repositorio:**
```bash
    git clone https://github.com/tu_usuario/ohsansi-inscripciones.git
```

2️⃣ **Instalar dependencias:**
```bash
    composer install
    npm install
```

3️⃣ **Configurar el archivo .env:**
```bash
    cp .env.example .env
    php artisan key:generate
```

4️⃣ **Configurar la base de datos:**
```bash
    php artisan migrate --seed
```

5️⃣ **Levantar el servidor local:**
```bash
    php artisan serve
```

---

## 👥 Roles principales del sistema

- **🛠️ Administrador:** Gestiona colegios, áreas, convocatorias y genera reportes.  
- **👨‍🏫 Tutor:** Registra estudiantes y supervisa sus inscripciones.  
- **🎓 Estudiante:** Se inscribe en las áreas correspondientes según la convocatoria.  

---

## 📌 Estado del Proyecto

- 🚀 **Gestión de Colegios:** En progreso  
- 🛠️ **Gestión de Convocatorias:** En progreso  
- 📍 **Inscripción de Estudiantes:** En progreso  
- 📊 **Reportes:** En progreso *(falta validación final)*  

---

## 🔮 Próximas Mejoras

- 📩 Implementación de notificaciones por email.  
- 🎨 Mejora en la UI/UX para el proceso de inscripción.  
- 🔒 Autenticación de dos factores (2FA) para administradores.  

---

## 📚 Aprendiendo Laravel

Laravel tiene la documentación más extensa y completa entre los frameworks modernos. Puedes consultarla [aquí](https://laravel.com/docs).

Si prefieres aprender viendo videos, [Laracasts](https://laracasts.com) ofrece más de 1500 tutoriales sobre Laravel, PHP moderno, pruebas unitarias y JavaScript.

---

## 💪 Contribuir

¡Gracias por considerar contribuir al proyecto! Puedes seguir la guía de contribuciones en la [documentación de Laravel](https://laravel.com/docs/contributions).

---

## 🧠 Autores

- **Altamirano Vargas Orlando**  
- **Cespedes Valencia Leyton**  
- **Cayola Cayo Yahir Leonardo**  
- **Luizaga Merino Gustavo**  
- **Paredes Lovera Guilder**  
- **Velasco Muruchi Kleber**  

---

## 🔒 Vulnerabilidades de Seguridad

Si descubres alguna vulnerabilidad de seguridad, por favor contacta a [taylor@laravel.com](mailto:taylor@laravel.com). Todas las vulnerabilidades serán tratadas de inmediato.

---

## 🏅 Licencia

El framework Laravel es software de código abierto licenciado bajo la [licencia MIT](https://opensource.org/licenses/MIT).

