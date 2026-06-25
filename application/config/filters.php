<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public $aliases = [
        'csrf'     => \CodeIgniter\Filters\CSRF::class,
        'toolbar'  => \CodeIgniter\Filters\DebugToolbar::class,
        'honeypot' => \CodeIgniter\Filters\Honeypot::class,
        'role'     => \Application\Filters\RoleFilter::class,
    ];

    public $globals = [
        'before' => [
            // Add any global filters you want to apply to all routes here
        ],
        'after' => [
            // Add any global filters for after the route here
        ],
    ];

    public $methods = [];

    public $filters = [];
}
