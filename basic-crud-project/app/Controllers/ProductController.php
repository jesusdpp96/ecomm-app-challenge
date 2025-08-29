<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ProductModel;
use App\Libraries\ResponseFormatter;
use App\Libraries\ErrorHandler;
use App\Libraries\DTOs\ProductResponse;
use App\Entities\Product;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\ProductValidationException;
use App\Exceptions\ProductStorageException;
use App\Traits\ErrorHandlingTrait;
use App\Traits\CrudLoggingTrait;
use Respect\Validation\Exceptions\ValidationException;

/**
 * Complete Product Controller with CRUD operations, validation, and API endpoints
 */
class ProductController extends BaseController
{
    use ErrorHandlingTrait;
    use CrudLoggingTrait;
    
    protected ProductModel $productModel;

    protected const DEFAULT_PAGE = 1;
    protected const DEFAULT_PER_PAGE = 7;
    

    public function __construct()
    {
        $this->productModel = new ProductModel();
    }

    // ==================== VIEW METHODS ====================

    /**
     * Display products list view
     *
     * @return string
     */
    public function index(): string
    {
        try {
            $page = (int)($this->request->getGet('page') ?? self::DEFAULT_PAGE);
            $perPage = (int)($this->request->getGet('per_page') ?? self::DEFAULT_PER_PAGE);
            $filters = $this->getFiltersFromRequest();

            $result = $this->productModel->getAllProducts($filters, $page, $perPage);
            
            $data = [
                'products' => $result['products'],
                'pagination' => $result['pagination'],
                'filters' => $filters
            ];

            $this->logCrudOperation('list', 'product', null, ['page' => $page, 'per_page' => $perPage]);

            return view('products/index', $data);
        } catch (\Exception $e) {
            $errors = ErrorHandler::handleGenericError($e);
            $this->logError('Error loading products index: ' . $e->getMessage());
            return view('errors/500', ['errors' => $errors]);
        }
    }

    /**
     * Display create product form
     *
     * @return string
     */
    public function create(): string
    {
        return view('products/create');
    }

    /**
     * Display edit product form
     *
     * @param int $id
     * @return string
     */
    public function edit(int $id): string
    {
        try {
            $product = $this->productModel->getProductById($id);
            
            if (!$product) {
                throw new ProductNotFoundException($id);
            }

            $data = ['product' => $product];
            return view('products/edit', $data);
        } catch (ProductNotFoundException $e) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        } catch (\Exception $e) {
            $this->logError('Error loading product edit form: ' . $e->getMessage(), ['product_id' => $id]);
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }

    /**
     * Display single product view
     *
     * @param int $id
     * @return string
     */
    public function show(int $id): string
    {
        try {
            $product = $this->productModel->getProductById($id);
            
            if (!$product) {
                throw new ProductNotFoundException($id);
            }

            $this->logCrudOperation('read', 'product', $id);
            
            $data = ['product' => $product];
            return view('products/show', $data);
        } catch (ProductNotFoundException $e) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        } catch (\Exception $e) {
            $this->logError('Error loading product view: ' . $e->getMessage(), ['product_id' => $id]);
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }

    // ==================== API METHODS ====================

    /**
     * API: Get all products with pagination and filters
     *
     * @return ResponseInterface
     */
    public function apiIndex(): ResponseInterface
    {
        try {
            $page = (int)($this->request->getGet('page') ?? 1);
            $perPage = min(100, (int)($this->request->getGet('per_page') ?? 10));
            $filters = $this->getFiltersFromRequest();

            log_message('info', "ProductController::apiIndex called with filters: " . json_encode($filters));

            $result = $this->productModel->getAllProducts($filters, $page, $perPage);
            
            // Format products using ProductResponse DTO
            $formattedProducts = ProductResponse::fromCollection($result['products']);
            
            $response = ResponseFormatter::paginated(
                $formattedProducts,
                $result['pagination']['total_items'],
                $page,
                $perPage
            );

            $this->logCrudOperation('list', 'product', null, ['page' => $page, 'per_page' => $perPage, 'api' => true]);

            return $this->response->setJSON($response);
        } catch (\Exception $e) {
            $errors = ErrorHandler::handleGenericError($e);
            $response = ResponseFormatter::error($errors, 'Failed to retrieve products', 500);
            return $this->response->setStatusCode(500)->setJSON($response);
        }
    }

    /**
     * API: Create new product
     *
     * @return ResponseInterface
     */
    public function store(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        return $this->executeWithErrorHandling(function() {
            $isLoggedIn = session()->get('is_logged_in');

            $this->logDebug('User is logged in: ' . ($isLoggedIn ? 'Yes' : 'No'));
            
            $rawData = $this->request->getPost();

            $this->logDebug('Raw data received in store()', is_array($rawData) ? $rawData : []);

            # Placeholder for next id
            $rawData['id'] = 1;
            
            // Validate raw input using Product Entity
            $validationErrors = Product::validateRawInput($rawData);

            if (!empty($validationErrors)) {
                throw new ProductValidationException($validationErrors);
            }

            // Create product using sanitized data from Entity
            $product = $this->productModel->createProduct($rawData);

            if (!$product) {
                throw new ProductStorageException('create');
            }

            // Format response
            $productResponse = ProductResponse::fromModel($product);
            $response = ResponseFormatter::created($productResponse->toArray(), 'Product created successfully');

            // Add new CSRF token to the response for form reuse
            $response['csrf_token'] = csrf_hash();

            $this->logCrudOperation('create', 'product', $product->id, $rawData);

            return $this->response->setStatusCode(201)->setJSON($response);
        }, 'No se pudo crear el producto');
    }

    /**
     * API: Update existing product
     *
     * @param int $id
     * @return ResponseInterface
     */
    public function update(int $id): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }
        
        return $this->executeWithErrorHandling(function() use ($id) {
            // Check if product exists
            if (!$this->productModel->productExists($id)) {
                throw new ProductNotFoundException($id);
            }

            // Get and validate raw input data
            $rawData = $this->request->getPost();

            $rawData['id'] = $id;
            // Validate raw input using Product Entity
            $validationErrors = Product::validateRawInput($rawData);

            if (!empty($validationErrors)) {
                throw new ProductValidationException($validationErrors);
            }

            // Update product using sanitized data from Entity
            $product = $this->productModel->updateProduct($id, $rawData);

            if (!$product) {
                throw new ProductStorageException('update', $id);
            }

            // Format response
            $productResponse = ProductResponse::fromModel($product);
            $response = ResponseFormatter::updated($productResponse->toArray(), 'Product updated successfully');

            // Add new CSRF token to the response for form reuse
            $response['csrf_token'] = csrf_hash();

            $this->logCrudOperation('update', 'product', $id, $rawData);

            return $this->response->setJSON($response);
        }, 'No se pudo actualizar el producto');
    }

    /**
     * API: Delete product
     *
     * @param int $id
     * @return ResponseInterface
     */
    public function delete(int $id): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }
        
        return $this->executeWithErrorHandling(function() use ($id) {
            // Check if product exists
            if (!$this->productModel->productExists($id)) {
                throw new ProductNotFoundException($id);
            }

            // Delete product
            $deleted = $this->productModel->deleteProduct($id);

            if (!$deleted) {
                throw new ProductStorageException('delete', $id);
            }

            $response = ResponseFormatter::deleted('Product deleted successfully');

            $this->logCrudOperation('delete', 'product', $id);

            return $this->response->setJSON($response);
        }, 'No se pudo eliminar el producto');
    }

    /**
     * API: Get single product
     *
     * @param int $id
     * @return ResponseInterface
     */
    public function apiShow(int $id): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }
        
        return $this->executeWithErrorHandling(function() use ($id) {
            $product = $this->productModel->getProductById($id);
            
            if (!$product) {
                throw new ProductNotFoundException($id);
            }

            $productResponse = ProductResponse::fromModel($product);
            $response = ResponseFormatter::success($productResponse->toArray(), 'Product retrieved successfully');

            $this->logCrudOperation('read', 'product', $id, ['api' => true]);
            
            return $this->response->setJSON($response);
        }, 'No se pudo obtener el producto');
    }

    /**
     * API: Search products
     *
     * @return ResponseInterface
     */
    public function search(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }
        
        return $this->executeWithErrorHandling(function() {
            $query = $this->request->getGet('q') ?? '';
            $query = Product::sanitizeStringStatic($query);

            $products = $this->productModel->searchProducts($query);
            $formattedProducts = ProductResponse::fromCollection($products);

            $response = ResponseFormatter::success($formattedProducts, 'Search completed');

            $this->logCrudOperation('search', 'product', null, ['query' => $query, 'results_count' => count($products)]);

            return $this->response->setJSON($response);
        }, 'No se pudo realizar la busqueda');
    }

    // ==================== HELPER METHODS ====================


    /**
     * Get filters from request parameters
     *
     * @return array
     */
    private function getFiltersFromRequest(): array
    {
        $filters = [];

        // Price range filters
        if ($minPrice = $this->request->getGet('min_price')) {
            $filters['min_price'] = Product::sanitizeNumericStatic($minPrice);
        }

        if ($maxPrice = $this->request->getGet('max_price')) {
            $filters['max_price'] = Product::sanitizeNumericStatic($maxPrice);
        }

        // Search filter
        if ($search = $this->request->getGet('search')) {
            $filters['search'] = Product::sanitizeStringStatic($search);
        }

        // Date filters
        if ($dateFrom = $this->request->getGet('date_from')) {
            $filters['date_from'] = Product::sanitizeStringStatic($dateFrom);
        }

        if ($dateTo = $this->request->getGet('date_to')) {
            $filters['date_to'] = Product::sanitizeStringStatic($dateTo);
        }

        // Sorting
        $filters['sort_by'] = Product::sanitizeStringStatic($this->request->getGet('sort_by') ?? 'id');
        $filters['order'] = Product::sanitizeStringStatic($this->request->getGet('order') ?? 'desc');

        return $filters;
    }

}
