<?php

namespace AthosFeed\Validation;

use AthosFeed\Schema\FeedSchema;

class FeedValidator
{
    public function validateFile($path, $minimumRecords = 10)
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new \RuntimeException('Feed file is not readable');
        }
        $handle = fopen($path, 'rb');
        if (!$handle) {
            throw new \RuntimeException('Feed file cannot be opened');
        }
        $ids = array();
        $lastParent = null;
        $closedParents = array();
        $errors = array();
        $count = 0;
        $lineNumber = 0;
        while (($line = fgets($handle)) !== false) {
            ++$lineNumber;
            $line = rtrim($line, "\r\n");
            if ($line === '') {
                $errors[] = "Line $lineNumber is empty";
                continue;
            }
            $record = json_decode($line, true);
            if (!is_array($record) || json_last_error() !== JSON_ERROR_NONE) {
                $errors[] = "Line $lineNumber contains invalid JSON";
                continue;
            }
            ++$count;
            foreach ($this->validateRecord($record, $lineNumber, $ids) as $error) {
                $errors[] = $error;
            }
            $parent = isset($record['__parent_id']) ? $record['__parent_id'] : null;
            if ($parent !== null) {
                if (isset($closedParents[$parent])) {
                    $errors[] = "Line $lineNumber breaks variant sequence for $parent";
                }
                if ($lastParent !== null && $lastParent !== $parent) {
                    $closedParents[$lastParent] = true;
                }
                $lastParent = $parent;
            }
        }
        fclose($handle);
        if ($count < (int) $minimumRecords) {
            $errors[] = "Feed has $count records; minimum is " . (int) $minimumRecords;
        }
        if ($errors) {
            throw new \RuntimeException(implode('; ', array_slice($errors, 0, 20)));
        }
        return array('records' => $count, 'valid' => true);
    }

    public function validateRecord(array $record, $line = 1, array &$ids = array())
    {
        $errors = array();
        foreach (FeedSchema::REQUIRED as $field) {
            if (!array_key_exists($field, $record) || $record[$field] === '' || $record[$field] === null) {
                $errors[] = "Line $line misses required field $field";
            }
        }
        if (isset($record['id'])) {
            if (isset($ids[$record['id']])) {
                $errors[] = "Line $line has duplicate id " . $record['id'];
            }
            $ids[$record['id']] = true;
        }
        foreach (array('url', 'thumbnail_url') as $field) {
            if (isset($record[$field]) && $record[$field] !== null && filter_var($record[$field], FILTER_VALIDATE_URL) === false) {
                $errors[] = "Line $line has invalid $field";
            }
        }
        if (isset($record['price']) && (!is_int($record['price']) && !is_float($record['price']) || $record['price'] < 0)) {
            $errors[] = "Line $line has invalid price";
        }
        foreach (array('__in_stock', 'active', 'available_for_order', 'on_sale') as $field) {
            if (isset($record[$field]) && !is_bool($record[$field])) {
                $errors[] = "Line $line has invalid boolean $field";
            }
        }
        foreach (array('product_id', 'combination_id', 'quantity', '__variant_position', '__in_stock_pct') as $field) {
            if (isset($record[$field]) && $record[$field] !== null && !is_int($record[$field])) {
                $errors[] = "Line $line has invalid integer $field";
            }
        }
        if (($record['record_type'] ?? null) === 'combination' && empty($record['__parent_id'])) {
            $errors[] = "Line $line variant has no __parent_id";
        }
        $encoded = json_encode($record);
        if (preg_match('#(?:/var/|/home/|access[_-]?token|basic[_-]?auth|password)#i', (string) $encoded)) {
            $errors[] = "Line $line may contain a secret or filesystem path";
        }
        return $errors;
    }
}
