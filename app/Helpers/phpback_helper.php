<?php

/**
 * PHPBack view/session helpers.
 *
 * Consolidates the three inconsistent legacy role mechanisms
 * (auth_helper::is_admin, common::isAdmin/hasRole, RoleFilter) into one
 * session-based source of truth. Authorization for routes is enforced by
 * App\Filters\AdminFilter / LoginFilter; these helpers are for the views.
 */

if (! function_exists('is_logged_in')) {
    function is_logged_in(): bool
    {
        return (bool) session()->get('isLoggedIn');
    }
}

if (! function_exists('current_user_id')) {
    function current_user_id(): ?int
    {
        $id = session()->get('userid');

        return $id === null ? null : (int) $id;
    }
}

if (! function_exists('current_username')) {
    function current_username(): ?string
    {
        return session()->get('username');
    }
}

if (! function_exists('is_admin')) {
    /**
     * True when the current session has at least the given admin level.
     * Levels mirror the legacy 1.3.x `isadmin` privilege levels.
     */
    function is_admin(int $level = 1): bool
    {
        return is_logged_in() && (int) session()->get('isadmin') >= $level;
    }
}

if (! function_exists('phpback_login')) {
    /**
     * Establishes an authenticated session. Regenerates the session id first
     * to prevent session fixation.
     */
    function phpback_login(object $user): void
    {
        $session = session();
        $session->regenerate(true);
        $session->set([
            'isLoggedIn' => true,
            'userid'     => (int) $user->id,
            'username'   => $user->name,
            'email'      => $user->email,
            'isadmin'    => (int) $user->isadmin,
            'role_id'    => (int) ($user->role_id ?? 0),
            'role'       => ((int) $user->isadmin > 0 ? 'Admin' : 'User'),
        ]);
    }
}

if (! function_exists('phpback_logout')) {
    function phpback_logout(): void
    {
        session()->destroy();
    }
}
