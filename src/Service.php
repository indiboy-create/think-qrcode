<?php

declare(strict_types=1);

namespace youvision\think\qrcode;

use think\Service as BaseService;

class Service extends BaseService
{
    public function register(): void
    {
        $default = require __DIR__ . '/../config/qrcode.php';
        $config = $this->app->config->get('qrcode', []);
        $config = is_array($config) ? $config : [];

        $this->app->config->set(array_merge($default, $config), 'qrcode');

        $factory = static function () {
            return QrCode::make();
        };

        $this->app->bind(QrCode::class, $factory);
        $this->app->bind('qrcode', QrCode::class);
    }
}