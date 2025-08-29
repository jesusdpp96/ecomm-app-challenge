/* INSTRUCCIONES PARA EL CHALLENGE TÉCNICO DE ECOMM-APP */

En este desafío técnico se busca implementar un CRUD básico de una sola entidad (Producto).

**Requisitos:**
1) Iniciar un proyecto en algún framework PHP (Por ejemplo Codeigniter es ideal por su rapida implementación)
2) El CRUD debe ser implementado bajo el patrón MVC (modelo-vista-controlador).
3) La entidad que se debe crear, consultar, modificar o eliminar se llama "producto" y tiene la siguiente estructura:

```json
{
    "id": 1,
    "title": "Producto del Challenge",
    "price": 2000,
    "created_at": "2022-12-13 10:41"
}
```

**Aclaración importante:**
No se requiere conexión con base de datos; el repositorio de datos puede ser un archivo local, por ejemplo, `productos.json`, y debe ser leído y modificado desde el modelo de la entidad.

4) En el frontend, es válido utilizar simplemente una tabla HTML. El renderizado debe realizarse mediante JavaScript, y el enlace para eliminar y modificar debe hacerse mediante data attributes del DOM utilizando jQuery o JavaScript vanilla (es optativo).

5) **Validación de Datos:**
   - Implementa una validación de datos en el lado del servidor antes de realizar cualquier operación CRUD. Asegúrate de que los datos del producto sean válidos antes de agregarlos o modificarlos.

6) **Manejo de Errores:**
   - Implementa un manejo adecuado de errores. Por ejemplo, si la lectura o escritura del archivo JSON falla, muestra un mensaje de error claro en el frontend.

7) **Paginación y Búsqueda:**
   - Agrega funcionalidades de paginación para mostrar solo un número limitado de productos por página.
   - Implementa una funcionalidad de búsqueda para filtrar los productos según su título, precio o fecha de creación.

8) **Seguridad:**
   - Realiza validación y escape de los datos de entrada para evitar ataques de inyección.
   - Protege contra CSRF (Cross-Site Request Forgery) utilizando tokens CSRF.

9) **Logs:**
   - Agrega la capacidad de realizar un registro (log) de las acciones CRUD, incluyendo la fecha y la descripción de la operación realizada.

10) **Pruebas Unitarias:**
    - Crea pruebas unitarias para al menos algunas funciones clave del controlador y modelo. Esto puede ayudar a evaluar la calidad del código.

11) **Operaciones Asíncronas:**
    - Implementa operaciones CRUD asíncronas utilizando AJAX para mejorar la experiencia del usuario y evitar recargas completas de la página.

12) **Control de Acceso:**
    - Implementa un sistema básico de control de acceso para restringir ciertas operaciones a usuarios autorizados (esto significa que todas las operaciones del CRUD deben ser asíncronas, desde la creación, edición hasta la paginación y filtrado).


Happy coding!!