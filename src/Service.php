<?php

declare(strict_types=1);

namespace YouVision\ThinkUuid;

use think\Service as ThinkService;

class Service extends ThinkService
{
    public function register(): void
    {
        $defaults = require dirname(__DIR__) . '/config/uuid.php';
        $current = (array) $this->app->config->get('uuid', []);

        $this->app->config->set(array_diff_key($defaults, $current), 'uuid');

        $app = $this->app;
        $this->app->bind('uuid', function () use ($app) {
            return new Uuid((array) $app->config->get('uuid', []));
        });
    }
}