<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ProductModel;
use App\Libraries\DataSanitizer;
use App\Libraries\AppLogger;
use App\Libraries\ResponseFormatter;
use App\Libraries\ErrorHandler;
use App\Libraries\DTOs\ProductRequest;
use App\Libraries\DTOs\ProductResponse;
use App\Entities\Product;
use Respect\Validation\Exceptions\ValidationException;

/**
 * Complete Product Controller with CRUD operations, validation, and API endpoints
 */
class ProductController extends BaseController
{
    protected ProductModel $productModel;
    protected DataSanitizer $sanitizer;
    protected AppLogger $appLogger;

    protected const DEFAULT_PAGE = 1;
    protected const DEFAULT_PER_PAGE = 7;
    

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->sanitizer = new DataSanitizer();
        $this->appLogger = new AppLogger('ProductController');
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

            $this->appLogger->logOperation('view_products_list', null, ['page' => $page, 'per_page' => $perPage]);

            return view('products/index', $data);
        } catch (\Exception $e) {
            $this->appLogger->logError('Error loading products index: ' . $e->getMessage());
            return view('errors/500');
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
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }

            $data = ['product' => $product];
            return view('products/edit', $data);
        } catch (\Exception $e) {
            $this->appLogger->logError('Error loading product edit form: ' . $e->getMessage(), ['product_id' => $id]);
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
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }

            $this->appLogger->logOperation('view_product', $id);
            
            $data = ['product' => $product];
            return view('products/show', $data);
        } catch (\Exception $e) {
            $this->appLogger->logError('Error loading product view: ' . $e->getMessage(), ['product_id' => $id]);
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

            $result = $this->productModel->getAllProducts($filters, $page, $perPage);
            
            // Format products using ProductResponse DTO
            $formattedProducts = ProductResponse::fromCollection($result['products']);
            
            $response = ResponseFormatter::paginated(
                $formattedProducts,
                $result['pagination']['total_items'],
                $page,
                $perPage
            );

            $this->appLogger->logOperation('api_list_products', null, ['page' => $page, 'per_page' => $perPage]);

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
        try {
            $isLoggedIn = session()->get('is_logged_in');

            log_message('debug', 'User is logged in: ' . ($isLoggedIn ? 'Yes' : 'No'));
            
            // Get and sanitize input data
            $contentType = $this->request->getHeaderLine('Content-Type');
            if (strpos($contentType, 'application/json') !== false) {
                $rawData = $this->request->getJSON(true);
            } else {
                $rawData = $this->request->getPost();
            }
            $this->appLogger->logDebug('Raw data received in store()', is_array($rawData) ? $rawData : []);

            $sanitizedData = $this->sanitizer->sanitizeArray($rawData, [
                'title' => 'string',
                'price' => 'numeric'
            ]);

            // Create and validate ProductRequest DTO
            $productRequest = ProductRequest::fromArray($sanitizedData);
            $validationErrors = $productRequest->validate();

            if (!empty($validationErrors)) {
                $errors = ErrorHandler::handleValidationErrors($validationErrors);
                $response = ResponseFormatter::error($errors, 'Validation failed', 400);
                return $this->response->setStatusCode(400)->setJSON($response);
            }

            // Create product using model
            $product = $this->productModel->createProduct($productRequest->getSanitizedData());

            if (!$product) {
                $errors = [['field' => 'product', 'message' => 'Failed to create product', 'type' => 'creation_error']];
                $response = ResponseFormatter::error($errors, 'Product creation failed', 500);
                return $this->response->setStatusCode(500)->setJSON($response);
            }

            // Format response
            $productResponse = ProductResponse::fromModel($product);
            $response = ResponseFormatter::created($productResponse->toArray(), 'Product created successfully');

            $this->appLogger->logOperation('create_product', $product->id, $sanitizedData);

            return $this->response->setStatusCode(201)->setJSON($response);
        } catch (ValidationException $e) {
            $errors = ErrorHandler::handleValidationErrors(['validation' => $e->getMessage()]);
            $response = ResponseFormatter::error($errors, 'Validation failed', 400);
            return $this->response->setStatusCode(400)->setJSON($response);
        } catch (\Exception $e) {
            $errors = ErrorHandler::handleGenericError($e);
            $response = ResponseFormatter::error($errors, 'Failed to create product', 500);
            return $this->response->setStatusCode(500)->setJSON($response);
        }
    }

    /**
     * API: Update existing product
     *
     * @param int $id
     * @return ResponseInterface
     */
    public function update(int $id): ResponseInterface
    {
        try {
            // Check if product exists
            if (!$this->productModel->productExists($id)) {
                $errors = ErrorHandler::handleNotFoundException('product', $id);
                $response = ResponseFormatter::error($errors, 'Product not found', 404);
                return $this->response->setStatusCode(404)->setJSON($response);
            }

            // Get and sanitize input data
            $rawData = $this->request->getJSON(true) ?? $this->request->getPost();
            $sanitizedData = $this->sanitizer->sanitizeArray($rawData, [
                'title' => 'string',
                'price' => 'numeric'
            ]);

            // Create and validate ProductRequest DTO
            $productRequest = ProductRequest::fromArray($sanitizedData);
            $validationErrors = $productRequest->validate();

            if (!empty($validationErrors)) {
                $errors = ErrorHandler::handleValidationErrors($validationErrors);
                $response = ResponseFormatter::error($errors, 'Validation failed', 400);
                return $this->response->setStatusCode(400)->setJSON($response);
            }

            // Update product using model
            $product = $this->productModel->updateProduct($id, $productRequest->getSanitizedData());

            if (!$product) {
                $errors = [['field' => 'product', 'message' => 'Failed to update product', 'type' => 'update_error']];
                $response = ResponseFormatter::error($errors, 'Product update failed', 500);
                return $this->response->setStatusCode(500)->setJSON($response);
            }

            // Format response
            $productResponse = ProductResponse::fromModel($product);
            $response = ResponseFormatter::updated($productResponse->toArray(), 'Product updated successfully');

            $this->appLogger->logOperation('update_product', $id, $sanitizedData);

            return $this->response->setJSON($response);
        } catch (ValidationException $e) {
            $errors = ErrorHandler::handleValidationErrors(['validation' => $e->getMessage()]);
            $response = ResponseFormatter::error($errors, 'Validation failed', 400);
            return $this->response->setStatusCode(400)->setJSON($response);
        } catch (\Exception $e) {
            $errors = ErrorHandler::handleGenericError($e);
            $response = ResponseFormatter::error($errors, 'Failed to update product', 500);
            return $this->response->setStatusCode(500)->setJSON($response);
        }
    }

    /**
     * API: Delete product
     *
     * @param int $id
     * @return ResponseInterface
     */
    public function delete(int $id): ResponseInterface
    {
        try {
            // Check if product exists
            if (!$this->productModel->productExists($id)) {
                $errors = ErrorHandler::handleNotFoundException('product', $id);
                $response = ResponseFormatter::error($errors, 'Product not found', 404);
                return $this->response->setStatusCode(404)->setJSON($response);
            }

            // Delete product
            $deleted = $this->productModel->deleteProduct($id);

            if (!$deleted) {
                $errors = [['field' => 'product', 'message' => 'Failed to delete product', 'type' => 'deletion_error']];
                $response = ResponseFormatter::error($errors, 'Product deletion failed', 500);
                return $this->response->setStatusCode(500)->setJSON($response);
            }

            $response = ResponseFormatter::deleted('Product deleted successfully');

            $this->appLogger->logOperation('delete_product', $id);

            return $this->response->setJSON($response);
        } catch (\Exception $e) {
            $errors = ErrorHandler::handleGenericError($e);
            $response = ResponseFormatter::error($errors, 'Failed to delete product', 500);
            return $this->response->setStatusCode(500)->setJSON($response);
        }
    }

    /**
     * API: Get single product
     *
     * @param int $id
     * @return ResponseInterface
     */
    public function apiShow(int $id): ResponseInterface
    {
        try {
            $product = $this->productModel->getProductById($id);
            
            if (!$product) {
                $errors = ErrorHandler::handleNotFoundException('product', $id);
                $response = ResponseFormatter::error($errors, 'Product not found', 404);
                return $this->response->setStatusCode(404)->setJSON($response);
            }

            $productResponse = ProductResponse::fromModel($product);
            $response = ResponseFormatter::success($productResponse->toArray(), 'Product retrieved successfully');

            $this->appLogger->logOperation('api_view_product', $id);
            
            return $this->response->setJSON($response);
        } catch (\Exception $e) {
            $errors = ErrorHandler::handleGenericError($e);
            $response = ResponseFormatter::error($errors, 'Failed to retrieve product', 500);
            return $this->response->setStatusCode(500)->setJSON($response);
        }
    }

    /**
     * API: Search products
     *
     * @return ResponseInterface
     */
    public function search(): ResponseInterface
    {
        try {
            $query = $this->request->getGet('q') ?? '';
            $query = $this->sanitizer->sanitizeString($query);

            $products = $this->productModel->searchProducts($query);
            $formattedProducts = ProductResponse::fromCollection($products);

            $response = ResponseFormatter::success($formattedProducts, 'Search completed');

            $this->appLogger->logOperation('search_products', null, ['query' => $query, 'results_count' => count($products)]);

            return $this->response->setJSON($response);
        } catch (\Exception $e) {
            $errors = ErrorHandler::handleGenericError($e);
            $response = ResponseFormatter::error($errors, 'Search failed', 500);
            return $this->response->setStatusCode(500)->setJSON($response);
        }
    }

    // ==================== HELPER METHODS ====================

    /**
     * Validate product input data using Product entity
     *
     * @param array $data
     * @return array
     */
    private function validateProductInput(array $data): array
    {
        try {
            $productRequest = ProductRequest::fromArray($data);
            return $productRequest->validate();
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Format product response for API
     *
     * @param Product $product
     * @return array
     */
    private function formatProductResponse(Product $product): array
    {
        return ProductResponse::fromModel($product)->toArray();
    }

    /**
     * Format error response for API
     *
     * @param array $errors
     * @return array
     */
    private function formatErrorResponse(array $errors): array
    {
        return ErrorHandler::handleValidationErrors($errors);
    }

    /**
     * Log operation with context
     *
     * @param string $action
     * @param int|null $productId
     * @return void
     */
    private function logOperation(string $action, ?int $productId = null): void
    {
        $this->appLogger->logOperation($action, $productId);
    }

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
            $filters['min_price'] = $this->sanitizer->sanitizeNumeric($minPrice);
        }

        if ($maxPrice = $this->request->getGet('max_price')) {
            $filters['max_price'] = $this->sanitizer->sanitizeNumeric($maxPrice);
        }

        // Search filter
        if ($search = $this->request->getGet('search')) {
            $filters['search'] = $this->sanitizer->sanitizeString($search);
        }

        // Date filters
        if ($dateFrom = $this->request->getGet('date_from')) {
            $filters['date_from'] = $this->sanitizer->sanitizeString($dateFrom);
        }

        if ($dateTo = $this->request->getGet('date_to')) {
            $filters['date_to'] = $this->sanitizer->sanitizeString($dateTo);
        }

        // Sorting
        $filters['sort_by'] = $this->sanitizer->sanitizeString($this->request->getGet('sort_by') ?? 'id');
        $filters['order'] = $this->sanitizer->sanitizeString($this->request->getGet('order') ?? 'desc');

        return $filters;
    }

}
