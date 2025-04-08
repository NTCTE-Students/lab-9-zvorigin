<?php

namespace App\Models;

use App\Config\Database;

abstract class Model {
    protected Database $database;
    protected string $table;
    protected string $key = 'id';
    protected array $fillable = [];
    protected array $hidden = [];
    protected ?array $current_record = null;
    
        
    /**
     * Метод-конструктор абстрактного класса модели.
     *
     * @return void
     */
    public function __construct()
    {
        $this -> database = new Database();
    }
    
    /**
     * Получение массива атрибутов модели, которые являются открытыми для просмотра.
     *
     * @return array возвращается массив с атрибутами модели.
     */
    protected function getVisibleColumns(): array
    {
        return array_filter(array_diff($this -> fillable, $this -> hidden));
    }
    
    /**
     * Метод, который устанавливает в модель запись по ее идентификатору
     *
     * @param  string|int $id идентификатор искомой записи.
     * @return Model метод возвращает модель.
     */
    public function getById(string|int $id): Model
    {
        $record = $this -> database
            -> read($this -> table, where: [[$this -> key, '=', $id]]);

        if (!empty($record)) {
            $this -> current_record = $record[0];
        }

        return $this;
    }
    
    /**
     * Метод, который устанавливает в модель запись по условию.
     *
     * @param array $where массив условий для поиска записи.
     * @return Model метод возвращает модель.
     */
    public function getByWhere(array $where): Model
    {
        $database = new Database();
        $record = $this -> database
            -> read($this -> table, where: $where);

        if (!empty($record)) {
            $this -> current_record = $record[0];
        }

        return $this;
    }
    
    /**
     * Метод, который обновляет атрибуты модели, сохраняет их в базу данных, а также сохраняет значения в модель.
     *
     * @param  array $data массив атрибутов, которые нужно заполнить.
     * @return Model метод возвращает модель.
     */
    public function fillAndSave(array $data): Model
    {
        if (!empty($this -> current_record)) {
            $id = $this -> current_record[$this -> key];
            $data = array_intersect_key($data, array_flip($this -> fillable));
            if ($this -> database -> update($this -> table, $data, [[$this -> key, '=', $id]])) {
                $this -> current_record = array_merge($this -> current_record, $data);
                // Комментарий тут для снижения оценки... если вы его увидели, то не пишите его.
            }
        }

        return $this;
    }
    
    /**
     * Метод, который удаляет запись из базы данных, а также запись из модели. Удаление происходит при условии, что модель имеет ссылку на запись.
     *
     * @return Model метод возвращает модель.
     */
    public function delete(): Model
    {
        if (!empty($this -> current_record)) {
            $id = $this -> current_record[$this -> key];
            if ($this -> database -> delete($this -> table, [[$this -> key, '=', $id]])) {
                $this -> current_record = null;
            }
        }

        return $this;
    }
    
    /**
     * Метод, который создает новую запись в базе данных, а также сохраняет сведения в модель.
     *
     * @param  array $data данные, которые необходимо внести.
     * @return Model метод возвращает модель.
     */
    public function createAndSet(array $data): Model
    {
        $data = array_intersect_key($data, array_flip($this -> fillable));
        if ($this -> database -> create($this -> table, $data)) {
            $this -> current_record = array_merge([
                $this -> key => $this -> database -> raw_connection -> insert_id,
            ], $data);
        }

        return $this;
    }
    
    /**
     * Метод, реализующий получение данных атрибутов прибинденной модели.
     *
     * @param string $attribute ключ атрибута.
     * @return mixed возвращается любое значение, которое присутствует в атрибуте. Если происходит обращение к несуществующему атрибуту, то возвращается null.
     */
    public function getAttribute(string $attribute): mixed
    {
        return $this -> current_record[$attribute] ?? null;
    }
}