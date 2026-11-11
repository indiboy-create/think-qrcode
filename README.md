## ThinkPHP-PanGu - 盘古ThinkPHP开源索引!
面向 ThinkPHP 的开源工程索引：组件、框架、服务与发布工具，按能力组织，持续整理。
仓库地址：**<https://github.com/indiboy-create/ThinkPHP-PanGu/>**
在线站点：**<https://indiboy-create.github.io/ThinkPHP-PanGu/>**

# think-qrcode

基于 [endroid/qr-code](https://github.com/endroid/qr-code) 的 ThinkPHP 8 二维码生成组件。

- 支持 PNG、SVG、WebP、EPS、Binary 输出
- 支持 Logo、文字标签、前景色/背景色、纠错级别、圆块尺寸模式
- 配置自动合并，可通过项目 `config/qrcode.php` 覆盖
- 直接返回 ThinkPHP Response，支持浏览器展示、下载与文件保存
- 支持静态入口、依赖注入与 `qrcode()` 辅助函数

## 安装

```bash
composer require youvision/think-qrcode
```

生成 PNG、WebP 需要开启 PHP 的 GD 扩展：

```ini
extension=gd
```

组件通过 ThinkPHP 的服务发现自动注册，无需手动配置。

如果项目未生成 `vendor/services.php`，可在 `app/service.php` 中手动注册：

```php
return [
    \youvision\think\qrcode\Service::class,
];
```

## 配置

将配置文件复制到项目 `config/qrcode.php` 后按需修改：

```bash
cp vendor/youvision/think-qrcode/config/qrcode.php config/qrcode.php
```

未复制配置时组件会使用内置默认值，项目配置的优先级高于默认值。完整配置项说明见 [config/qrcode.php](config/qrcode.php)。

## 使用

### 直接输出图片

```php
use youvision\think\qrcode\QrCode;

class Index
{
    public function qrcode()
    {
        return QrCode::make('https://www.thinkphp.cn')
            ->size(400)
            ->margin(20)
            ->png();
    }
}
```

SVG 输出：

```php
return QrCode::make('https://www.thinkphp.cn')->svg();
```

### 保存为文件

```php
$path = QrCode::make('https://www.thinkphp.cn')
    ->save(runtime_path() . 'qrcode/thinkphp.png');

$path = QrCode::make('https://www.thinkphp.cn')
    ->save_to_dir(runtime_path() . 'qrcode');
```

`save_to_dir()` 会自动创建目录并生成 `qrcode.png`（或与输出格式匹配的扩展名）。

### 下载图片

```php
return QrCode::make('https://www.thinkphp.cn')
    ->download('thinkphp.png');
```

### Data URI 与字符串

```php
$dataUri = QrCode::make('https://www.thinkphp.cn')->data_uri();

$content = QrCode::make('https://www.thinkphp.cn')->to_string();
```

### Logo 与文字标签

```php
return QrCode::make('https://www.thinkphp.cn')
    ->size(400)
    ->logo(public_path() . 'static/logo.png', 64, true)
    ->label('ThinkPHP', 18, '#333333')
    ->label_alignment('center')
    ->png();
```

Logo 与字体路径支持项目根目录相对路径和绝对路径。

### 自定义颜色与纠错级别

```php
use youvision\think\qrcode\QrCode;

return QrCode::make('https://www.thinkphp.cn')
    ->foreground_color([0, 102, 204])
    ->background_color('#FFFFFF')
    ->level('high')
    ->round_block_size('margin')
    ->png();
```

颜色支持 `[红, 绿, 蓝]`、`[红, 绿, 蓝, 透明度]`、`#RGB`、`#RRGGBB`、`#RRGGBBAA` 等写法。透明度取值范围 `0-127`，`0` 为不透明。

### 输出格式与 writer 参数

```php
return QrCode::make('https://www.thinkphp.cn')
    ->writer('svg', ['exclude_xml_declaration' => true])
    ->svg();

return QrCode::make('https://www.thinkphp.cn')
    ->writer('png', ['compression_level' => 9])
    ->png();
```

支持的 writer：`png`、`svg`、`webp`、`eps`、`binary`。

### 辅助函数

```php
return qrcode('https://www.thinkphp.cn')->size(300)->png();

$qrCode = qrcode('https://www.thinkphp.cn')->size(300);
$path = $qrCode->save(public_path() . 'qrcode.png');
```

### 依赖注入

```php
use youvision\think\qrcode\QrCode;

class Index
{
    public function index(QrCode $qrCode)
    {
        return $qrCode->data('https://www.thinkphp.cn')->png();
    }
}
```

也可以使用容器别名 `app('qrcode')`。

## 方法说明

| 方法 | 说明 |
| --- | --- |
| `make(string $data, array $config = [])` | 创建实例，默认读取 `qrcode` 配置 |
| `data()` | 设置二维码内容 |
| `size()` | 设置图片尺寸，单位像素 |
| `margin()` | 设置四周留白，单位像素 |
| `encoding()` | 设置内容编码 |
| `level()` | 设置纠错级别：`low`、`medium`、`quartile`、`high` |
| `foreground_color()` | 设置前景色 |
| `background_color()` | 设置背景色 |
| `round_block_size()` | 设置圆块尺寸模式 |
| `logo()` | 设置 Logo 路径、缩放宽度、是否挖空背景 |
| `label()` | 设置标签文字、字号、颜色 |
| `label_font()` | 设置标签字体路径与字号 |
| `label_alignment()` | 设置标签对齐方式：`left`、`center`、`right` |
| `writer()` | 设置输出格式及 writer 参数 |
| `writer_options()` | 追加 writer 参数 |
| `result()` | 获取 endroid 的 `ResultInterface` 实例 |
| `to_string()` | 获取图片二进制内容 |
| `data_uri()` | 获取 Base64 Data URI |
| `mime_type()` | 获取 MIME 类型 |
| `save()` | 保存到指定路径或已存在的目录 |
| `save_to_dir()` | 自动创建目录并保存 |
| `response()` | 返回 ThinkPHP Response，可指定文件名和是否下载 |
| `download()` | 返回下载响应 |
| `png()` / `svg()` / `webp()` / `eps()` | 以指定格式返回响应 |

## 目录结构

```
config/qrcode.php    配置文件
src/QrCode.php       二维码生成类
src/Service.php      服务提供者
src/helpers.php      辅助函数
```

## 许可证

[MIT](LICENSE)