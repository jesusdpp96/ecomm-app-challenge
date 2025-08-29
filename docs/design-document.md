# Design Document - ECOMM-APP Challenge Técnico Challenge Técnico

## 1. Resumen Ejecutivo

### Objetivo
Desarrollar una aplicación web CRUD básica para gestión de productos utilizando el patrón MVC, sin conexión a base de datos, con funcionalidades avanzadas de seguridad, búsqueda y paginación.

### Stack Tecnológico Propuesto
- **Backend**: CodeIgniter 4 (PHP Framework)
- **Frontend**: HTML5, CSS3, JavaScript Vanilla
- **Almacenamiento**: Archivo JSON local
- **Testing**: PHPUnit

## 2. Análisis de Requisitos

### Requisitos Funcionales

#### 2.1 CRUD Básico
- **Create**: Crear nuevos productos
- **Read**: Listar productos con paginación
- **Update**: Modificar productos existentes
- **Delete**: Eliminar productos

#### 2.2 Modelo de Datos - Producto
```json
{
    "id": "integer (auto-incrementable)",
    "title": "string (requerido, min 3 chars, max 100 chars)",
    "price": "decimal (requerido, > 0)",
    "created_at": "datetime (ISO 8601 format)"
}
```

#### 2.3 Funcionalidades Adicionales
- **Búsqueda**: Por título, precio y fecha
- **Paginación**: Configurable (default: 10 items por página)
- **Validación**: Server-side y client-side
- **Logs**: Registro de operaciones CRUD
- **Autenticación básica**: Control de acceso
- **Operaciones asíncronas**: AJAX para todas las operaciones

### Requisitos No Funcionales
- **Seguridad**: Validación, escape de datos, protección CSRF
- **Mantenibilidad**: Patrón MVC, código limpio, aplicación patrones SOLID
- **Testeable**: Cobertura de pruebas unitarias
- **Performance**: Respuesta rápida, carga asíncrona

## 3. Arquitectura del Sistema

### 3.1 Estructura del Proyecto (CodeIgniter 4)
```
ecomm-app/
├── app/
│   ├── Controllers/
│   │   ├── BaseController.php
│   │   ├── ProductController.php
│   │   └── AuthController.php
│   ├── Models/
│   │   ├── ProductModel.php
│   │   └── LogModel.php
│   ├── Views/
│   │   ├── layouts/
│   │   │   └── main.php
│   │   ├── products/
│   │   │   ├── index.php
│   │   │   ├── create.php
│   │   │   └── edit.php
│   │   └── auth/
│   │       └── login.php
│   ├── Filters/
│   │   └── AuthFilter.php
│   └── Libraries/
│       ├── JSONStorage.php
│       └── Logger.php
├── public/
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   └── index.php
├── writable/
│   ├── data/
│   │   └── productos.json
│   └── logs/
└── tests/
    ├── unit/
    └── integration/
```

### 3.2 Patrón MVC Implementado

#### Model (ProductModel.php)
- Gestión del archivo JSON
- Validaciones de datos
- Operaciones CRUD sobre el almacenamiento
- Manejo de errores de I/O

#### View (Templates PHP)
- Renderizado de HTML
- Integración con JavaScript para funcionalidad asíncrona
- Formularios con validación client-side

#### Controller (ProductController.php)
- Manejo de requests HTTP
- Validación server-side
- Coordinación entre Model y View
- Respuestas JSON para AJAX

## 4. Especificaciones Técnicas Detalladas

### 4.1 Almacenamiento de Datos

#### Estructura del archivo productos.json
```json
{
  "products": [
    {
      "id": 1,
      "title": "Producto del Challenge",
      "price": 2000.00,
      "created_at": "2022-12-13T10:41:00"
    }
  ],
  "next_id": 2
}
```

#### Operaciones sobre el archivo JSON
- **Lectura**: Validación de formato, manejo de archivo inexistente
- **Escritura**: Backup automático, validación de integridad
- **Concurrencia**: Lock de archivo durante escritura

### 4.2 API Endpoints

#### Productos
- `GET /products` - Listar productos (con paginación y filtros)
- `POST /products` - Crear producto
- `GET /products/{id}` - Obtener producto específico
- `PUT /products/{id}` - Actualizar producto
- `DELETE /products/{id}` - Eliminar producto

#### Autenticación
- `POST /login` - Iniciar sesión
- `POST /logout` - Cerrar sesión

### 4.3 Validaciones

#### Server-Side (PHP)
```php
$validation_rules = [
    'title' => 'required|min_length[3]|max_length[100]|alpha_numeric_space',
    'price' => 'required|decimal|greater_than[0]|less_than[999999.99]'
];
```

#### Client-Side (JavaScript)
- Validación en tiempo real
- Mensajes de error dinámicos
- Prevención de envío de formularios inválidos

### 4.4 Sistema de Búsqueda y Paginación

#### Parámetros de búsqueda
- `search`: Búsqueda por título (LIKE)
- `price_min`: Precio mínimo
- `price_max`: Precio máximo
- `date_from`: Fecha desde
- `date_to`: Fecha hasta

#### Parámetros de paginación
- `page`: Número de página (default: 1)
- `per_page`: Items por página (default: 10, max: 50)
- `sort_by`: Campo de ordenamiento (title, price, created_at)
- `sort_order`: Dirección (asc, desc)

### 4.5 Seguridad

#### Protección CSRF
- Token único por sesión
- Validación en todas las operaciones POST/PUT/DELETE
- Regeneración automática de tokens

#### Validación y Escape
```php
// Escape de salida
echo esc($product['title'], 'html');

// Validación de entrada
$input = $this->request->getPost();
$validated = $this->validation->run($input, 'product_rules');
```

#### Control de Acceso
- Sistema de sesiones simple
- Middleware de autenticación
- Redirección automática para usuarios no autenticados

### 4.6 Sistema de Logs

#### Estructura del Log
```php
[
    'timestamp' => '2024-01-15T10:30:00',
    'action' => 'CREATE',
    'entity' => 'product',
    'entity_id' => 123,
    'user' => 'admin',
    'details' => 'Created product: Laptop Dell',
    'ip' => '192.168.1.1'
]
```

#### Tipos de Acciones
- CREATE, READ, UPDATE, DELETE
- LOGIN, LOGOUT
- ERROR (para excepciones)

## 5. Frontend - Especificaciones UX/UI

### 5.1 Interfaz Principal

#### Layout Responsivo
- Header con navegación y logout
- Sidebar con filtros de búsqueda
- Área principal con tabla de productos
- Footer con paginación

#### Tabla de Productos
```html
<table id="products-table">
  <thead>
    <tr>
      <th data-sort="id">ID</th>
      <th data-sort="title">Título</th>
      <th data-sort="price">Precio</th>
      <th data-sort="created_at">Fecha Creación</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody id="products-tbody">
    <!-- Contenido dinámico via AJAX -->
  </tbody>
</table>
```

### 5.2 Funcionalidad JavaScript

#### Operaciones AJAX
```javascript
class ProductManager {
    constructor() {
        this.csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    }
    
    async loadProducts(page = 1, filters = {}) {
        // Cargar productos con filtros y paginación
    }
    
    async createProduct(data) {
        // Crear nuevo producto
    }
    
    async updateProduct(id, data) {
        // Actualizar producto existente
    }
    
    async deleteProduct(id) {
        // Eliminar producto
    }
}
```

#### Manejo de Errores
- Notificaciones toast para errores/éxitos
- Validación en tiempo real de formularios
- Loading states durante operaciones AJAX

## 6. Testing Strategy

### 6.1 Pruebas Unitarias (PHPUnit)

#### ProductModel Tests
```php
class ProductModelTest extends TestCase {
    public function testCreateProduct()
    public function testReadProducts()
    public function testUpdateProduct()
    public function testDeleteProduct()
    public function testValidation()
    public function testFileOperations()
}
```

#### ProductController Tests
```php
class ProductControllerTest extends TestCase {
    public function testIndexWithPagination()
    public function testStoreWithValidation()
    public function testUpdateWithAuthorization()
    public function testDestroyWithLogging()
}
```

### 6.2 Cobertura de Testing
- **Models**: 90% cobertura mínima
- **Controllers**: 80% cobertura mínima
- **Libraries**: 85% cobertura mínima

## 7. Plan de Implementación

### Phase 1: Setup y Estructura Base
1. Instalación de CodeIgniter 4
2. Configuración del entorno de desarrollo
3. Creación de la estructura MVC básica
4. Setup de testing con PHPUnit

### Phase 2: CRUD Básico
1. Implementación del ProductModel con operaciones JSON
2. ProductController con endpoints básicos
3. Views básicas para CRUD
4. Validaciones server-side

### Phase 3: Frontend Interactivo
1. Implementación de AJAX para todas las operaciones
2. Interfaz responsive con Bootstrap/CSS Grid
3. Sistema de notificaciones
4. Validación client-side

### Phase 4: Funcionalidades Avanzadas
1. Sistema de búsqueda y filtros
2. Paginación dinámica
3. Control de acceso y autenticación
4. Sistema de logs

### Phase 5: Seguridad y Testing
1. Implementación de protección CSRF
2. Escape de datos y validaciones adicionales
3. Pruebas unitarias completas
4. Testing de integración

### Phase 6: Refinamiento y Documentación
1. Optimizaciones de performance
2. Manejo de errores mejorado
3. Documentación de API
4. Testing final y deployment

## 8. Consideraciones Técnicas Adicionales

### 8.1 Manejo de Errores
- Try-catch en todas las operaciones de archivo
- Logs detallados de errores
- Mensajes de error user-friendly
- Rollback automático en operaciones fallidas

### 8.2 Performance
- Lazy loading de productos
- Caching de búsquedas frecuentes
- Optimización de operaciones JSON
- Debouncing en búsquedas

### 8.3 Mantenibilidad
- Código documentado con PHPDoc
- Separación clara de responsabilidades
- Configuración centralizada
- Versionado de la estructura de datos

## 9. Criterios de Éxito

### Funcionalidad
- ✅ CRUD completo funcionando
- ✅ Búsqueda y paginación operativas
- ✅ Todas las operaciones son asíncronas
- ✅ Control de acceso implementado

### Calidad de Código
- ✅ Patrón MVC correctamente implementado
- ✅ Validaciones comprehensivas
- ✅ Manejo de errores robusto
- ✅ Cobertura de tests >80%

### Seguridad
- ✅ Protección CSRF activa
- ✅ Validación y escape de datos
- ✅ Control de acceso funcional
- ✅ Logs de seguridad implementados

### UX/UI
- ✅ Interfaz intuitiva y responsive
- ✅ Feedback visual apropiado
- ✅ Operaciones fluidas sin recargas
- ✅ Manejo de estados de carga
