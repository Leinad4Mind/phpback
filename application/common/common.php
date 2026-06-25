function isAdmin()
{
    $session = session();
    return $session->get('role') === 'admin';
}

function hasRole($role)
{
    $session = session();
    return $session->get('role') === $role;
}
