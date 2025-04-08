public function login(string $email, string $password): bool
{
    $user = (new User()) -> getByEmail($email);
    if ($user && password_verify($password, $user -> getAttribute('password_hash'))) {
        $_SESSION['user_id'] = $user -> getAttribute('id');
        $this -> user = $user;
        return true;
    }
    return false;
}
