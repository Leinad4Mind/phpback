<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Requires an admin session with at least the given privilege level.
 *
 * Usage in Routes: ['filter' => 'admin:1'] (view), 'admin:2' (users),
 * 'admin:3' (system). Levels mirror the legacy 1.3.x `isadmin` column so
 * that an upgraded database keeps working without re-assigning privileges.
 */
class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $required = 1;
        if (is_array($arguments) && isset($arguments[0]) && ctype_digit((string) $arguments[0])) {
            $required = (int) $arguments[0];
        }

        $session = session();

        if (! $session->get('isLoggedIn') || (int) $session->get('isadmin') < $required) {
            return redirect()->to('admin')->with('error', 'noadmin');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
