<?php

declare(strict_types=1);

use YouVision\ThinkUuid\Uuid;

if (!function_exists('uuid')) {

    function uuid(?string $version = null, mixed ...$arguments): string
    {

        $instance = function_exists('app')
            ? app('uuid')
            : \think\Container::getInstance()->make('uuid');

        return $instance->make($version, ...$arguments)->toString();
    }
}