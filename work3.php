public function posts(): array|null
{
    if ($this -> current_record['id'] !== null) {
        return $this -> database -> read('posts', where: [['user_id', '=', $this -> current_record['id']]]);
    } else return null;
}