<?php
/**
 * Input Validation Utility
 * Sanitizes and validates user inputs
 */

namespace App\Utils;

class Validator
{
    // Maximum lengths
    const MAX_TITLE_LENGTH = 255;
    const MAX_DESCRIPTION_LENGTH = 1000;
    const MAX_ACTION_LENGTH = 50;

    /**
     * Sanitize a string input
     */
    public static function sanitizeString(mixed $input, int $maxLength = null): ?string
    {
        if ($input === null || $input === '') {
            return null;
        }

        // Convert to string if not already
        $input = (string) $input;

        // Remove null bytes and trim
        $input = str_replace("\0", '', trim($input));

        // Remove HTML tags but preserve content
        $input = strip_tags($input);

        // Convert special characters to entities
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Apply max length if specified
        if ($maxLength !== null && mb_strlen($input, 'UTF-8') > $maxLength) {
            $input = mb_substr($input, 0, $maxLength, 'UTF-8');
        }

        return $input;
    }

    /**
     * Validate task title
     */
    public static function validateTitle(mixed $title): array
    {
        $errors = [];

        if (empty($title) || !is_string($title)) {
            $errors[] = 'Title is required';
            return $errors;
        }

        $title = trim($title);

        if (mb_strlen($title, 'UTF-8') < 1) {
            $errors[] = 'Title cannot be empty';
        }

        if (mb_strlen($title, 'UTF-8') > self::MAX_TITLE_LENGTH) {
            $errors[] = 'Title cannot exceed ' . self::MAX_TITLE_LENGTH . ' characters';
        }

        // Check for potentially dangerous content
        if (preg_match('/<[^>]*>/', $title)) {
            $errors[] = 'Title contains invalid characters';
        }

        return $errors;
    }

    /**
     * Validate task description
     */
    public static function validateDescription(mixed $description): array
    {
        $errors = [];

        if ($description === null || $description === '') {
            // Empty description is allowed
            return $errors;
        }

        if (!is_string($description)) {
            $errors[] = 'Description must be a string';
            return $errors;
        }

        $description = trim($description);

        if (mb_strlen($description, 'UTF-8') > self::MAX_DESCRIPTION_LENGTH) {
            $errors[] = 'Description cannot exceed ' . self::MAX_DESCRIPTION_LENGTH . ' characters';
        }

        return $errors;
    }

    /**
     * Validate task ID
     */
    public static function validateId(mixed $id): ?int
    {
        if (empty($id)) {
            return null;
        }

        $id = filter_var($id, FILTER_VALIDATE_INT);

        if ($id === false || $id <= 0) {
            return null;
        }

        return $id;
    }

    /**
     * Validate action parameter
     */
    public static function validateAction(mixed $action): ?string
    {
        if (empty($action) || !is_string($action)) {
            return null;
        }

        // Whitelist of allowed actions
        $allowedActions = ['list', 'create', 'update', 'delete', 'toggle', 'reorder', 'move'];

        if (!in_array(strtolower($action), $allowedActions, true)) {
            return null;
        }

        return strtolower($action);
    }

    /**
     * Validate and sanitize task input
     */
    public static function validateTaskInput(array $data): array
    {
        $sanitized = [
            'title' => null,
            'description' => null,
            'status' => null,
            'errors' => []
        ];

        // Validate title
        $titleErrors = self::validateTitle($data['title'] ?? '');
        if (!empty($titleErrors)) {
            $sanitized['errors']['title'] = $titleErrors;
        } else {
            $sanitized['title'] = self::sanitizeString($data['title'], self::MAX_TITLE_LENGTH);
        }

        // Validate description
        $descErrors = self::validateDescription($data['description'] ?? '');
        if (!empty($descErrors)) {
            $sanitized['errors']['description'] = $descErrors;
        } else {
            $sanitized['description'] = self::sanitizeString($data['description'], self::MAX_DESCRIPTION_LENGTH);
        }

        return $sanitized;
    }

    /**
     * Check if input contains SQL injection patterns
     */
    public static function containsSqlInjectionPattern(mixed $input): bool
    {
        if (empty($input) || !is_string($input)) {
            return false;
        }

        $patterns = [
            '/(\'|%27|\\|"|%22|--|;|#|\/\*)/i',
            '/\b(UNION|SELECT|INSERT|UPDATE|DELETE|DROP|ALTER|EXEC|Xp_)\b/i',
            '/\b(OR|AND)\s+\d+\s*=\s*\d+/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate status value
     */
    public static function validateStatus(mixed $status): ?string
    {
        if (empty($status) || !is_string($status)) {
            return null;
        }

        $allowedStatuses = ['pending', 'completed'];

        $status = strtolower(trim($status));

        if (!in_array($status, $allowedStatuses, true)) {
            return null;
        }

        return $status;
    }
}

