<?php

return [
    // 默认输出格式，可选：png、svg、webp、eps、binary
    // png/webp 需要 PHP 的 GD 扩展；eps/binary 不依赖 GD
    'writer' => 'png',

    // 二维码内容编码，可选：UTF-8、ISO-8859-1 等
    // 内容包含中文时请使用 UTF-8；纯英文内容使用 ISO-8859-1 兼容性更好
    'encoding' => 'UTF-8',

    // 纠错级别，可选：low(L)、medium(M)、quartile(Q)、high(H)
    // 级别越高，二维码越密集，被遮挡后越容易识别
    'level' => 'low',

    // 二维码图片尺寸，单位像素
    'size' => 300,

    // 二维码四周留白，单位像素
    'margin' => 10,

    // 圆块尺寸模式，可选：margin、enlarge、shrink、none
    // margin：尺寸不足时补充留白，最终图片尺寸保持不变
    // enlarge：尺寸不足时放大二维码和图片
    // shrink：尺寸不足时缩小二维码和图片
    // none：不做取整，适合 SVG 等矢量格式
    'round_block_size' => 'margin',

    // 前景色（二维码图案颜色），格式：[红, 绿, 蓝, 透明度] 或 #RRGGBB / #RRGGBBAA
    // 红、绿、蓝取值范围 0-255，透明度取值范围 0-127，0 为不透明
    'foreground' => [0, 0, 0, 0],

    // 背景色（二维码底色），格式：[红, 绿, 蓝, 透明度] 或 #RRGGBB / #RRGGBBAA
    'background' => [255, 255, 255, 0],

    // 中心 Logo 图片路径，支持项目根目录相对路径、绝对路径
    // 留空表示不添加 Logo
    'logo' => '',

    // Logo 缩放后的宽度，单位像素，留空或 0 表示按原图尺寸
    'logo_size' => 50,

    // 是否在 Logo 周围挖空背景，避免 Logo 与二维码图案重叠影响识别
    'logo_punchout' => true,

    // 二维码下方的文字标签，留空表示不添加
    'label' => '',

    // 标签字体文件路径（TTF/OTF），留空使用 endroid/qr-code 自带字体
    'label_font' => '',

    // 标签文字大小
    'label_size' => 16,

    // 标签文字颜色，格式：[红, 绿, 蓝, 透明度] 或 #RRGGBB / #RRGGBBAA
    'label_color' => [0, 0, 0, 0],

    // 标签文字对齐方式，可选：left、center、right
    'label_alignment' => 'center',

    // 输出 writer 的专属参数，例如：
    // png：['compression_level' => 9, 'number_of_colors' => 16]
    // svg：['exclude_xml_declaration' => true]
    // webp：['quality' => 90]
    'writer_options' => [],
];