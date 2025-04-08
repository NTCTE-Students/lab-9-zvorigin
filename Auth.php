public function __construct()
{
    session_start();
    if (isset($_SESSION['user_id'])) {
        $this -> user = new User($_SESSION['user_id']);
    }
}
