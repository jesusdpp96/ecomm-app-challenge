/* INSTRUCCIONES PARA EL CHALLENGE TÉCNICO DE ECOMM-APP */

En este desafío técnico se busca implementar un CRUD básico de una sola entidad (Producto).

**Requisitos:**
1) Iniciar un proyecto en algún framework PHP (Por ejemplo Codeigniter es ideal por su rapida implementación)

   ✅ Se creo el proyecto con Codeigniter 4
   
   
2) El CRUD debe ser implementado bajo el patrón MVC (modelo-vista-controlador).

   ✅ Controlador: ProductController, LoginController
   ✅ Modelo: ProductModel (usa JSON para persistencia)
   ✅ Vista principales: 
      - products/index: lista de productos
      - products/create: formulario de creacion de producto
      - products/edit: formulario de edicion de producto
      - products/show: detalle de un producto


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


   ✅ Se creo la Entidad Product
   ✅ Se creo la clase JSONStorage para manejar la persistencia de los productos en un archivo JSON


4) En el frontend, es válido utilizar simplemente una tabla HTML. El renderizado debe realizarse mediante JavaScript, y el enlace para eliminar y modificar debe hacerse mediante data attributes del DOM utilizando jQuery o JavaScript vanilla (es optativo).

   ✅ Se uso Javascript vanilla para renderizar los productos en la vista
   ✅ Se crearon varios archivos .js separando en cada uno las diferentes responsabilidades

5) **Validación de Datos:**

   - Implementa una validación de datos en el lado del servidor antes de realizar cualquier operación CRUD. Asegúrate de que los datos del producto sean válidos antes de agregarlos o modificarlos.

   ✅ Se creo la entidad Product que contiene las reglas de negocio para un producto (validaciones)
   ✅ Tanto ProductController como ProductModel hacen uso de la entidad Product para validar los datos del producto


6) **Manejo de Errores:**
   - Implementa un manejo adecuado de errores. Por ejemplo, si la lectura o escritura del archivo JSON falla, muestra un mensaje de error claro en el frontend.

   ✅ Se creo la clase ErrorHandler que maneja los errores de manera centralizada
   ✅ Se crearon excepciones personalizadas para manejar los errores con los productos y el json-storage

7) **Paginación y Búsqueda:**
   - Agrega funcionalidades de paginación para mostrar solo un número limitado de productos por página.
   - Implementa una funcionalidad de búsqueda para filtrar los productos según su título, precio o fecha de creación.

   ✅ Se soporta filtrado basado en query params
   ✅ Se soporta paginación basada en query params
   ✅ Se soporta ordenamiento basado en query params
   ✅ Se soportan todos los filtros en AJAX request (sin recargar la página)
   ✅ Se soporta la paginación en AJAX request (sin recargar la página)
   ✅ Se creo la clase Javascript ProductFilter para manejar los filtros

8) **Seguridad:**
   - Realiza validación y escape de los datos de entrada para evitar ataques de inyección.
   - Protege contra CSRF (Cross-Site Request Forgery) utilizando tokens CSRF.

   ✅ Se activo protección contra CSRF
   ✅ Se agrega el campo hidden con el token CSRF en los formulario
   ✅ Se envian tokens CSRF en las peticiones AJAX

9) **Logs:**
   - Agrega la capacidad de realizar un registro (log) de las acciones CRUD, incluyendo la fecha y la descripción de la operación realizada.

   ✅ Se creo el Trait CrudLoggingTrait que maneja los logs de manera centralizada y con un formato json para mantenerlos formato estructurado
   
10) **Pruebas Unitarias:**
    - Crea pruebas unitarias para al menos algunas funciones clave del controlador y modelo. Esto puede ayudar a evaluar la calidad del código.

   ✅ Se crearon pruebas unitarias para la clase que maneja la persistencia de los productos en un archivo JSON (JSONStorage)

   Ejecute el comando:
   
      ./vendor/bin/phpunit ./tests/unit/Libraries/JSONStorageTest.php

   ✅ Se crearon pruebas unitarias para la entidad Product
   
   Ejecute el comando:
   
      ./vendor/bin/phpunit ./tests/unit/Entities/ProductTest.php
      
   ✅ Se crearon pruebas unitarias para el modelo ProductModel
   
   Ejecute el comando:

      ./vendor/bin/phpunit ./tests/unit/Models/ProductModelTest.php

11) **Operaciones Asíncronas:**
    - Implementa operaciones CRUD asíncronas utilizando AJAX para mejorar la experiencia del usuario y evitar recargas completas de la página.

    ✅ La accion de crear un producto se realiza de manera asíncrona con un AJAX request
    ✅ La accion de editar un producto se realiza de manera asíncrona con un AJAX request
    ✅ La accion de eliminar un producto se realiza de manera asíncrona con un AJAX request
    ✅ Las acciones de filtrado se realizan de manera asíncrona con un AJAX request
    

12) **Control de Acceso:**
    - Implementa un sistema básico de control de acceso para restringir ciertas operaciones a usuarios autorizados (esto significa que todas las operaciones del CRUD deben ser asíncronas, desde la creación, edición hasta la paginación y filtrado).

    ✅ Se implemento control de acceso con un login basico y dos roles de usuario (user y admin)
    ✅ Para los usuarios con rol user se les deniega la accion de eliminar un producto. Pueden crear y editar productos
      - Si intentan eliminar un producto, se les muestra un mensaje de error
      - El boton permanece visible para estos usuarios para que en pruebas de uso se pueda verificar que no se puede eliminar un producto

    ✅ Para los usuarios con rol admin pueden crear, editar y eliminar productos
    


Happy coding!!