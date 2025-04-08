<?php

namespace App\Models;

class User extends Model {
    protected string $table = 'users';
    
    protected array $fillable = [
        'firstname',
        'lastname',
        'patronymic',
        'email',
        'password_hash',
    ];

    protected array $hidden = [
        'password_hash',
        'created_at',
        'updated_at',
    ];

    
    /**
     * Переопределение конструктора. Если передан id, то вызывается метод getById.
     *
     * @param ?int $id id пользователя.
     * @return void
     */
    public function __construct(?int $id = null)
    {
        parent::__construct();
        if ($id) {
            $this -> getById($id);
        }
    }
    
    /**
     * Метод для получения пользователя по email.
     *
     * @param  string $email email пользователя.
     * @return User пользователь.
     */
    public function getByEmail(string $email): User
    {
        return $this -> getByWhere([['email', '=', $email]]);
    }
    
    /**
     * Приватный метод для хэширования пароля.
     *
     * @param array $data данные пользователя. 
     * @return void
     */
    private function setPassword(array &$data): void
    {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);
    }
    
    /**
     * Переопределение метода fillAndSave. Перед сохранением пароль хэшируется.
     *
     * @param array $data данные пользователя.
     * @return Model сохраненный пользователь.
     */
    public function fillAndSave(array $data): Model
    {
        $this -> setPassword($data);
        return parent::fillAndSave($data);
    }
    
    /**
     * Переопределение метода createAndSet. Перед сохранением пароль хэшируется.
     *
     * @param array $data данные пользователя.
     * @return Model сохраненный пользователь.
     */
    public function createAndSet(array $data): Model
    {
        $this -> setPassword($data);
        return parent::createAndSet($data);
    }
    
    /**
     * Метод, который возвращает посты пользователя. Если к модели не прибинден пользователь, то вернет null.
     *
     * @return array|null
     */
    public function posts(): array|null
    {
        if ($this -> current_record['id'] !== null) {
            return $this -> database -> read('posts', where: [['user_id', '=', $this -> current_record['id']]]);
        } else return null;
    }
}