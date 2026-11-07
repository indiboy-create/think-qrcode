<?php

declare(strict_types=1);

use youvision\think\qrcode\QrCode;

if (!function_exists('qrcode')) {
    function qrcode(string $data = '', array $config = []): QrCode
    {
        return QrCode::make($data, $config);
    }
}