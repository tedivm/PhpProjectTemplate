<?php

namespace Spark\Plugins\CLI;

use Spark\Plugins\Core\Plugin as Base;

class Plugin extends Base
{
    protected $permissions = array(
        'app/console' => 0755,
    );
}
