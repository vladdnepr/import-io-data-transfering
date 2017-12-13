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

    public function __construct($data)
    {
        $this->rows = explode("\n", $data);
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

                foreach ($row_parsed as $column_name => &$column_value) {

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
                    }
                }

                if ($row_parsed !== null) {
                    $pool[] = $row_parsed;

                    if (count($pool) >= $threshold) {
                        yield $pool;
                        $pool = [];
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

    public function getTotalRows()
    {
        return count($this->rows);
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
