<?php

namespace App\Config;

class Database {
    public $raw_connection;
    public $dbase_name = 'repiev_ed_blog';

    private $dbase_host = 'pma.has.bik';
    private $dbase_login = 'student';
    private $dbase_password = 'student';
    
    /**
     * Функция-конструктор класса Database.
     *
     * @return void
     */
    public function __construct()
    {
        $this -> raw_connection = new \mysqli(
            $this -> dbase_host,
            $this -> dbase_login,
            $this -> dbase_password,
            $this -> dbase_name,
        );
        $this -> raw_connection -> set_charset('utf8mb4');
    }
    
    /**
     * Приватная функция, которая определяет тип переданного аргумента для подготовленного запроса.
     *
     * @param mixed $key аргумент, у которого необходимо определить тип.
     * @return string функция возвращает i, s или d.
     */
    private function get_type(mixed $key): string
    {
        return match (gettype($key)) {
            'boolean', 'integer' => 'i',
            'string' => 's',
            'double' => 'd',
        };
    }
    
    /**
     * Приватная функция, которая выполняет подготовленный запрос в базе данных.
     *
     * @param string $query подготовленный SQL-запрос.
     * @param string $types типы, которые присутствуют в подготовленном запросе.
     * @param array $values передаваемые значения в подготовленный запрос.
     * @return array возвращается массив в котором первый аргумент - это объект запроса, а второй - булевое значение из метода execute().
     */
    private function prepared_query(string $query, string $types = '', array $values = []): array
    {
        $stmt = $this
            -> raw_connection
            -> prepare($query);
        if ($types) {
            $values = array_map(fn($item) => htmlspecialchars($item), $values);
            $stmt -> bind_param($types, ... $values);
        }
        $status = $stmt -> execute();
        
        return [$stmt -> get_result(), $status];
    }
    
    /**
     * Функция, которая создает из двумерного массива выборок SQL-строку выборки.
     *
     * @param array $where массив массивов, где в каждом элементе находятся значения выборки.
     * @return array возвращаемый массив, где первый элемент - подготовленная строка, второй элемент - строка с типами для подготовленного запроса, третий элемент - массив значений.
     */
    private function where_condition(array $where): array
    {
        $where_merged = '';
        $types = '';
        $vals = [];
        $end = end($where);
        
        foreach ($where as $cond) {
            if ($cond[2] !== 'NULL') {
                $where_merged .= "{$cond[0]} {$cond[1]} ?" . ($end != $cond ? (isset($cond[3]) ? " {$cond[3]} " : ' AND ') : '');
                $types .= $this -> get_type($cond[2]);
                $vals[] = $cond[2];
            } else {
                $where_merged .= "{$cond[0]} {$cond[1]} NULL";
            }
            
        }

        return [
            " WHERE {$where_merged}",
            $types,
            $vals,
        ];
    }
    
    /**
     * Функция, которая реализует добавление данных в указанную таблицу.
     *
     * @param string $table строка, которая принимает в себя имя таблицы, в которую нужно добавить данные.
     * @param mixed $data ассоциативный массив, который принимает в себя элементы, у которых ключи - это имена колонок в таблице.
     * @return bool функция возвращает true если данные были добавлены в базу данных 
     */
    public function create(string $table, array $data): bool
    {
        $sql = "INSERT INTO {$table} (";
        $keys = array_keys($data);
        $vals = array_values($data);
        $end = end($keys);
        $types = '';
        foreach($keys as $key) {
            $sql .= $end != $key ? "{$key}, " : "{$key}) VALUES (" . substr(str_repeat('?, ', count($keys)), 0, -2) . ');';
            $types .= $this -> get_type($data[$key]);
        }

        unset($keys, $end);

        $result = $this -> prepared_query($sql, $types, $vals);
        return $result[1];
    }
    
    /**
     * Функция, которая считывает данные из указанной таблицы.
     *
     * @param string $table строка, которая принимает в себя имя таблицы, из которой нужно получить данные.
     * @param ?array $where массив указателей выборки данных.
     * @param ?array $cols массив получаемых колонок из выборки.
     * @param ?int $limit целое число - указатель на ограничение выборки элементов.
     * @param ?int $offset целое число - указатель на шаг выборки элементов.
     * @return array функция возвращает определенное количество строк из выборки.
     */
    public function read(string $table, ?array $cols = null, ?array $where = null, ?int $limit = null, ?int $offset = null): array
    {
        $sql = 'SELECT ' . ($cols === null ? '*' : implode(', ', $cols)) . " FROM {$table}";
        $types = '';
        $vals = [];

        if ($where) {
            $cond = $this -> where_condition($where);
            
            $sql .= $cond[0];
            $types .= $cond[1];
            $vals = $cond[2];

            unset($cond);
        }

        if ($limit) {
            $sql .= " LIMIT {$limit}";

            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }

        unset($table, $cols, $where, $limit, $offset);

        $query = $this -> prepared_query($sql, $types, $vals);
        if ($query[1]) {
            $query = $query[0];
            $returned = [];
            while ($row = $query -> fetch_assoc()) {
                $returned[] = $row;
            }

            return $returned;
            // Комментарий тут для снижения оценки... если вы его увидели, то не пишите его.
        } else return [];
    }
    
    /**
     * Функция, которая реализует изменение данных по условию (или без условия)
     *
     * @param string $table строка, которая принимает в себя имя таблицы, в которой нужно изменить данные. 
     * @param  array $data ассоциативный массив, который принимает в себя элементы, у которых ключи - это имена колонок в таблице.
     * @param  ?array $where массив указателей выборки данных.
     * @return bool функция возвращает true если изменение данных произошло (или нет, исходя из выборки) или возвращает false если данные были не изменены или произошла ошибка.
     */
    public function update(string $table, array $data, ?array $where = null): bool
    {
        $sql = "UPDATE {$table} SET ";
        $keys = array_keys($data);
        $vals = array_values($data);
        $end = end($keys);
        $types = '';
        foreach($keys as $key) {
            $sql .= "{$key} = ?" . ($end != $key ? ', ' : ' ');
            $types .= $this -> get_type($data[$key]);
        }

        unset($keys, $end);

        if ($where) {
            $where_result = $this -> where_condition($where);
            $sql .= $where_result[0];
            $types .= $where_result[1];
            $vals = array_merge($vals, $where_result[2]);

            unset($where_result);
        }

        $result = $this -> prepared_query($sql, $types, $vals);

        return $result[1];
    }
    
    /**
     * Функция, которая удаляет записи по заданной выборке.
     *
     * @param  string $table строка, которая принимает в себя имя таблицы, в которой нужно удалить данные.
     * @param  mixed $where ассоциативный массив, который принимает в себя элементы, у которых ключи - это имена колонок в таблице.
     * @return bool функция возвращает true если изменение данных произошло (или нет, исходя из выборки) или возвращает false если данные были не изменены или произошла ошибка.
     */
    public function delete(string $table, array $where = null): bool
    {
        $sql = "DELETE FROM {$table} ";
        $where_cond = [];

        if ($where) {
            $where_cond = $this -> where_condition($where);
            $sql .= $where_cond[0];
        }

        return $this -> prepared_query($sql, $where_cond[1] ?? '', $where_cond[2] ?? [])[1];
    }
}