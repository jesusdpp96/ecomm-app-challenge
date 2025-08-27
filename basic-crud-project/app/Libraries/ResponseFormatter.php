<?php

namespace App\Libraries;

/**
 * Standardized JSON response formatter
 */
class ResponseFormatter
{
    /**
     * Success response format
     *
     * @param array $data
     * @param string $message
     * @param int $code
     * @return array
     */
    public static function success(array $data, string $message = '', int $code = 200): array
    {
        $response = [
            'success' => true,
            'code' => $code,
            'data' => $data
        ];

        if (!empty($message)) {
            $response['message'] = $message;
        }

        return self::addMetadata($response);
    }

    /**
     * Error response format
     *
     * @param array $errors
     * @param string $message
     * @param int $code
     * @return array
     */
    public static function error(array $errors, string $message = '', int $code = 400): array
    {
        $response = [
            'success' => false,
            'code' => $code,
            'errors' => $errors
        ];

        if (!empty($message)) {
            $response['message'] = $message;
        }

        return self::addMetadata($response);
    }

    /**
     * Paginated response format
     *
     * @param array $data
     * @param int $total
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public static function paginated(array $data, int $total, int $page, int $perPage): array
    {
        $totalPages = ceil($total / $perPage);
        
        $response = [
            'success' => true,
            'code' => 200,
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_items' => $total,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ];

        return self::addMetadata($response);
    }

    /**
     * Created response format
     *
     * @param array $data
     * @param string $message
     * @return array
     */
    public static function created(array $data, string $message = 'Created successfully'): array
    {
        return self::success($data, $message, 201);
    }

    /**
     * Updated response format
     *
     * @param array $data
     * @param string $message
     * @return array
     */
    public static function updated(array $data, string $message = 'Updated successfully'): array
    {
        return self::success($data, $message, 200);
    }

    /**
     * Deleted response format
     *
     * @param string $message
     * @return array
     */
    public static function deleted(string $message = 'Deleted successfully'): array
    {
        return self::success([], $message, 200);
    }

    /**
     * Add timestamp and request ID to response
     *
     * @param array $response
     * @return array
     */
    private static function addMetadata(array $response): array
    {
        $response = self::addTimestamp($response);
        $response = self::addRequestId($response);
        
        return $response;
    }

    /**
     * Add timestamp to response
     *
     * @param array $response
     * @return array
     */
    private static function addTimestamp(array $response): array
    {
        $response['timestamp'] = date('c');
        return $response;
    }

    /**
     * Add request ID to response
     *
     * @param array $response
     * @return array
     */
    private static function addRequestId(array $response): array
    {
        $response['request_id'] = uniqid('req_', true);
        return $response;
    }
}
