<?php

namespace VladDnepr\ImportIO;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

class ServiceResponse
{
    /**
     * @var array
     */
    protected $rows;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * ServiceResponse constructor.
     * @param string $data Import.io JSON data
     */
    public function __construct($data)
    {
        $data = explode("\n", $data);
        $data = array_slice($data, 0, 1); // Trial
        
        $this->rows = $data;
    }

    /**
     * @param int $threshold
     * @return \Generator|array
     */
    public function getRows($threshold = 100)
    {
        $this->errors = [];

        try {
            $pool = [];

            foreach ($this->rows as $row) {
                $row_parsed = \json_decode($row, true);

                if ($row_parsed !== null) {
                    foreach ($row_parsed['result']['extractorData']['data'] as $data_row) {
                        foreach ($data_row['group'] as $group_row) {
                            $pool[] = $this->getCleanedRow($group_row);

                            if (count($pool) >= $threshold) {
                                yield $pool;
                                $pool = [];
                            }
                        }
                    }
                } else {
                    $this->errors[] = 'Parse error: ' . \json_last_error_msg() . ". JSON raw: " . $row;
                }
            }

            // Остатки
            if ($pool) {
                yield $pool;
            }

        } catch (BadResponseException $e) {
            $response = \json_decode(strval($e->getResponse()->getBody()), true);

            $this->errors[] = isset($response['message']) ?
                $response['message'] . ' ' . $response['details'] :
                $e->getMessage();
        }
    }

    protected function getCleanedRow($row)
    {
        $row_cleaned = [];

        foreach ($row as $column_name => $column_value) {
            if (is_array($column_value)) {
                $value = '';

                foreach ($column_value as $item) {
                    $value .= $item['text'] . '';
                }

                $column_value = trim($value);
            }

            $column_name_cleaned = $column_name;
            $column_name_cleaned = preg_replace('/[^a-zA-Z0-9=\s—–-]+/u', '', $column_name_cleaned);
            $column_name_cleaned = preg_replace('/[=\s—–-]+/u', '_', $column_name_cleaned);
            $column_name_cleaned = trim($column_name_cleaned, '_');
            $column_name_cleaned = strtolower($column_name_cleaned);

            // Пытаемся угадать где целые и дробные числа
            if (is_string($column_value)) {
                $column_value_cleaned = str_replace([',', ' '], ['.', ''], $column_value);

                if (is_numeric($column_value_cleaned)) {
                    $floatVal = floatval($column_value_cleaned);
                    $intVal = intval($column_value_cleaned);

                    if ($floatVal && $intVal != $floatVal) {
                        $column_value = $floatVal;
                    } else {
                        $column_value = $intVal;
                    }
                }
            } elseif (is_array($column_value)) {
                $column_value = \json_encode($column_value);
            }

            $row_cleaned[$column_name_cleaned] = $column_value;
        }
        
        return $row_cleaned;
    }

    public function getTotalRows()
    {
        return count($this->rows);
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
