public function __construct(?int $id = null)
{
    parent::__construct();
    if ($id) {
        $this -> getById($id);
    }
}