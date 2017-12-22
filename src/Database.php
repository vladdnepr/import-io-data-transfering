<?php

namespace VladDnepr\ImportIO;

use Pixie\Connection;
use Pixie\QueryBuilder\QueryBuilderHandler;

class Database
{
    /**
     * @var \Pixie\Connection
     */
    protected $connection;

    /**
     * @var QueryBuilderHandler
     */
    protected $builder;

    public function __construct($host, $username, $password, $db)
    {
        $config = array(
            'driver'    => 'mysql', // Db driver
            'host'      => 'localhost',
            'database'  => $db,
            'username'  => $username,
            'password'  => $password,
            'charset'   => 'utf8', // Optional
            'collation' => 'utf8_unicode_ci', // Optional
        );

        $this->connection = new Connection('mysql', $config, 'QB');
        $this->builder = new QueryBuilderHandler($this->connection);
    }

    /**
     * @param $table
     * @param $rows
     * @return array|string
     */
    public function insert($table, $rows)
    {
        $this->ensureTableExist($table, $rows);
        return $this->builder->table($table)->insert($rows);
    }

    public function ensureTableExist($table, $rows)
    {
        if (!$this->isTableExists($table) && $rows) {
            $row_example = [];

            foreach ($rows as $row) {
                foreach ($row as $column_name => $column_value) {
                    if (!isset($row_example[$column_name])) {
                        $row_example[$column_name] = $column_value;
                    }
                }
            }

            $columns = ['ID INT( 11 ) AUTO_INCREMENT PRIMARY KEY'];

            foreach ($row_example as $column_name => $column_value) {
                $type = 'TEXT';

                if (is_float($column_value)) {
                    $type = 'FLOAT';
                } elseif (is_int($column_value)) {
                    $type = 'integer';
                }

                $columns[] = $column_name . ' ' . $type . ' NOT NULL';
            }

            $this->builder->pdo()->exec(
                "CREATE table {$table} (" . implode(', ', $columns) . ") DEFAULT CHARSET utf8;"
            );
        }
    }

    protected function isTableExists($table)
    {
        $pdo = $this->builder->pdo();

        $mrStmt = $pdo->prepare("SHOW TABLES LIKE :table_name");
        $mrStmt->bindParam(":table_name", $table, \PDO::PARAM_STR);

        $sqlResult = $mrStmt->execute();

        if ($sqlResult) {
            $row = $mrStmt->fetch(\PDO::FETCH_NUM);

            if ($row[0]) {
                //table was found
                return true;
            } else {
                //table was not found
                return false;
            }
        } else {
            //some PDO error occurred
            throw new \RuntimeException(
                "Could not check if table exists, Error: ".var_export($pdo->errorInfo(), true)
            );
        }
    }
}
