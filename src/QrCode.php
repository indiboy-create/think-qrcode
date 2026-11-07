<?php

declare(strict_types=1);

namespace youvision\think\qrcode;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Font\Font;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode as EndroidQrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\BinaryWriter;
use Endroid\QrCode\Writer\EpsWriter;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\WebPWriter;
use Endroid\QrCode\Writer\WriterInterface;
use InvalidArgumentException;
use think\Response;
use Throwable;

class QrCode
{
    protected const DEFAULTS = [
        'writer' => 'png',
        'encoding' => 'UTF-8',
        'level' => 'low',
        'size' => 300,
        'margin' => 10,
        'round_block_size' => 'margin',
        'foreground' => [0, 0, 0, 0],
        'background' => [255, 255, 255, 0],
        'logo' => '',
        'logo_size' => 50,
        'logo_punchout' => true,
        'label' => '',
        'label_font' => '',
        'label_size' => 16,
        'label_color' => [0, 0, 0, 0],
        'label_alignment' => 'center',
        'writer_options' => [],
    ];

    protected array $config;

    protected string $data;

    public function __construct(string $data = '', array $config = [])
    {
        $this->config = array_merge(static::DEFAULTS, $config);
        $this->data = $data;
    }

    public static function make(string $data = '', array $config = []): static
    {
        return new static($data, array_merge(static::config(), $config));
    }

    public static function config(): array
    {
        if (!function_exists('app')) {
            return [];
        }

        try {
            $config = app('config')->get('qrcode', []);
        } catch (Throwable) {
            return [];
        }

        return is_array($config) ? $config : [];
    }

    public function data(string $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function size(int $size): static
    {
        if ($size < 1) {
            throw new InvalidArgumentException('二维码尺寸必须大于 0');
        }

        $this->config['size'] = $size;

        return $this;
    }

    public function margin(int $margin): static
    {
        if ($margin < 0) {
            throw new InvalidArgumentException('二维码边距不能小于 0');
        }

        $this->config['margin'] = $margin;

        return $this;
    }

    public function encoding(string $encoding): static
    {
        $this->config['encoding'] = $encoding;

        return $this;
    }

    public function level(string|ErrorCorrectionLevel $level): static
    {
        $this->config['level'] = $level;

        return $this;
    }

    public function foreground_color(int|string|array $color): static
    {
        $this->config['foreground'] = $color;

        return $this;
    }

    public function background_color(int|string|array $color): static
    {
        $this->config['background'] = $color;

        return $this;
    }

    public function round_block_size(string|RoundBlockSizeMode $mode): static
    {
        $this->config['round_block_size'] = $mode;

        return $this;
    }

    public function logo(string $path, ?int $width = null, ?bool $punchout = null): static
    {
        $this->config['logo'] = $path;

        if ($width !== null) {
            $this->config['logo_size'] = $width;
        }

        if ($punchout !== null) {
            $this->config['logo_punchout'] = $punchout;
        }

        return $this;
    }

    public function label(string $text, ?int $size = null, int|string|array|null $color = null): static
    {
        $this->config['label'] = $text;

        if ($size !== null) {
            $this->config['label_size'] = $size;
        }

        if ($color !== null) {
            $this->config['label_color'] = $color;
        }

        return $this;
    }

    public function label_font(string $path, ?int $size = null): static
    {
        $this->config['label_font'] = $path;

        if ($size !== null) {
            $this->config['label_size'] = $size;
        }

        return $this;
    }

    public function label_alignment(string|LabelAlignment $alignment): static
    {
        $this->config['label_alignment'] = $alignment;

        return $this;
    }

    public function writer(string $writer, array $options = []): static
    {
        $this->config['writer'] = $writer;

        if ($options !== []) {
            $this->config['writer_options'] = array_merge((array) $this->config['writer_options'], $options);
        }

        return $this;
    }

    public function writer_options(array $options): static
    {
        $this->config['writer_options'] = array_merge((array) $this->config['writer_options'], $options);

        return $this;
    }

    public function result(): ResultInterface
    {
        if ($this->data === '') {
            throw new InvalidArgumentException('二维码内容不能为空');
        }

        return $this->build_writer()->write(
            $this->build_qr_code(),
            $this->build_logo(),
            $this->build_label(),
            (array) $this->config['writer_options'],
        );
    }

    public function to_string(): string
    {
        return $this->result()->getString();
    }

    public function data_uri(): string
    {
        return $this->result()->getDataUri();
    }

    public function mime_type(): string
    {
        return $this->result()->getMimeType();
    }

    public function save(string $path): string
    {
        if ($path === '') {
            throw new InvalidArgumentException('保存路径不能为空');
        }

        if (str_ends_with($path, '/') || str_ends_with($path, '\\') || is_dir($path)) {
            $path = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . $this->filename();
        }

        $directory = dirname($path);

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new InvalidArgumentException('无法创建目录：' . $directory);
        }

        $this->result()->saveToFile($path);

        return $path;
    }

    public function filename(string $name = 'qrcode'): string
    {
        if ($name === '') {
            $name = 'qrcode';
        }

        return str_contains($name, '.') ? $name : $name . '.' . $this->extension();
    }

    public function response(?string $filename = null, bool $download = false): Response
    {
        $result = $this->result();
        $content = $result->getString();

        $headers = [
            'Content-Type' => $result->getMimeType(),
            'Content-Length' => (string) strlen($content),
        ];

        if ($download) {
            $headers['Content-Disposition'] = $this->disposition($this->filename($filename ?: 'qrcode'), 'attachment');
        }

        return Response::create($content, 'html', 200)->header($headers);
    }

    public function save_to_dir(string $directory, string $name = 'qrcode'): string
    {
        if ($directory === '') {
            throw new InvalidArgumentException('保存目录不能为空');
        }

        return $this->save(rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $this->filename($name));
    }

    public function download(string $filename = 'qrcode'): Response
    {
        return $this->response($filename, true);
    }

    public function png(): Response
    {
        return $this->writer('png')->response($this->filename('qrcode'));
    }

    public function svg(): Response
    {
        return $this->writer('svg')->response($this->filename('qrcode'));
    }

    public function webp(): Response
    {
        return $this->writer('webp')->response($this->filename('qrcode'));
    }

    public function eps(): Response
    {
        return $this->writer('eps')->response($this->filename('qrcode'));
    }

    public function extension(): string
    {
        $writer = $this->config['writer'];

        if ($writer instanceof WriterInterface) {
            return match (true) {
                $writer instanceof SvgWriter => 'svg',
                $writer instanceof WebPWriter => 'webp',
                $writer instanceof EpsWriter => 'eps',
                $writer instanceof BinaryWriter => 'bin',
                default => 'png',
            };
        }

        return match (strtolower((string) $writer)) {
            'svg' => 'svg',
            'webp' => 'webp',
            'eps' => 'eps',
            'binary', 'bin' => 'bin',
            default => 'png',
        };
    }

    public function __toString(): string
    {
        return $this->to_string();
    }

    protected function build_writer(): WriterInterface
    {
        $writer = $this->config['writer'];

        if ($writer instanceof WriterInterface) {
            return $writer;
        }

        return match (strtolower((string) $writer)) {
            'png' => new PngWriter(),
            'svg' => new SvgWriter(),
            'webp' => new WebPWriter(),
            'eps' => new EpsWriter(),
            'binary', 'bin' => new BinaryWriter(),
            default => throw new InvalidArgumentException('不支持的输出格式：' . $writer),
        };
    }

    protected function build_qr_code(): EndroidQrCode
    {
        return new EndroidQrCode(
            data: $this->data,
            encoding: new Encoding((string) $this->config['encoding']),
            errorCorrectionLevel: $this->error_correction_level(),
            size: (int) $this->config['size'],
            margin: (int) $this->config['margin'],
            roundBlockSizeMode: $this->round_block_size_mode(),
            foregroundColor: $this->color($this->config['foreground']),
            backgroundColor: $this->color($this->config['background']),
        );
    }

    protected function build_logo(): ?Logo
    {
        $path = trim((string) $this->config['logo']);

        if ($path === '') {
            return null;
        }

        $path = $this->resolve_path($path);

        if (!is_file($path)) {
            throw new InvalidArgumentException('Logo 文件不存在：' . $path);
        }

        $width = (int) $this->config['logo_size'];

        return new Logo(
            path: $path,
            resizeToWidth: $width > 0 ? $width : null,
            punchoutBackground: (bool) $this->config['logo_punchout'],
        );
    }

    protected function build_label(): ?Label
    {
        $text = (string) $this->config['label'];

        if ($text === '') {
            return null;
        }

        $arguments = [
            'text' => $text,
            'alignment' => $this->resolve_label_alignment(),
            'textColor' => $this->color($this->config['label_color']),
        ];

        $font = trim((string) $this->config['label_font']);

        if ($font !== '') {
            $font = $this->resolve_path($font);

            if (!is_file($font)) {
                throw new InvalidArgumentException('标签字体文件不存在：' . $font);
            }

            $arguments['font'] = new Font($font, (int) $this->config['label_size']);
        }

        return new Label(...$arguments);
    }

    protected function error_correction_level(): ErrorCorrectionLevel
    {
        $level = $this->config['level'];

        if ($level instanceof ErrorCorrectionLevel) {
            return $level;
        }

        return match (strtolower(trim((string) $level))) {
            'l', 'low' => ErrorCorrectionLevel::Low,
            'm', 'medium' => ErrorCorrectionLevel::Medium,
            'q', 'quartile' => ErrorCorrectionLevel::Quartile,
            'h', 'high' => ErrorCorrectionLevel::High,
            default => throw new InvalidArgumentException('不支持的纠错级别：' . $level),
        };
    }

    protected function round_block_size_mode(): RoundBlockSizeMode
    {
        $mode = $this->config['round_block_size'];

        if ($mode instanceof RoundBlockSizeMode) {
            return $mode;
        }

        return match (strtolower(trim((string) $mode))) {
            'margin' => RoundBlockSizeMode::Margin,
            'enlarge' => RoundBlockSizeMode::Enlarge,
            'shrink' => RoundBlockSizeMode::Shrink,
            'none' => RoundBlockSizeMode::None,
            default => throw new InvalidArgumentException('不支持的圆块尺寸模式：' . $mode),
        };
    }

    protected function resolve_label_alignment(): LabelAlignment
    {
        $alignment = $this->config['label_alignment'];

        if ($alignment instanceof LabelAlignment) {
            return $alignment;
        }

        return match (strtolower(trim((string) $alignment))) {
            'left' => LabelAlignment::Left,
            'right' => LabelAlignment::Right,
            default => LabelAlignment::Center,
        };
    }

    protected function color(int|string|array $color): Color
    {
        if (is_int($color)) {
            $color = sprintf('%06x', $color & 0xFFFFFF);
        }

        if (is_string($color)) {
            $hex = ltrim(trim($color), '#');

            if (preg_match('/^[0-9a-f]{3}$/i', $hex) === 1) {
                $hex = preg_replace('/(.)/', '$1$1', $hex);
            }

            if (preg_match('/^[0-9a-f]{6}$/i', $hex) === 1) {
                $hex .= '00';
            }

            if (preg_match('/^[0-9a-f]{8}$/i', $hex) !== 1) {
                throw new InvalidArgumentException('无法识别的颜色值：' . $color);
            }

            $channels = array_map('hexdec', str_split($hex, 2));

            return new Color((int) $channels[0], (int) $channels[1], (int) $channels[2], (int) $channels[3]);
        }

        $channels = array_values($color);

        if (count($channels) < 3) {
            throw new InvalidArgumentException('颜色数组至少需要包含红、绿、蓝三个分量');
        }

        return new Color(
            (int) $channels[0],
            (int) $channels[1],
            (int) $channels[2],
            isset($channels[3]) ? (int) $channels[3] : 0,
        );
    }

    protected function resolve_path(string $path): string
    {
        if (preg_match('/^[a-zA-Z]:[\\\\\\/]/', $path) === 1 || str_starts_with($path, '/') || str_starts_with($path, '\\')) {
            return $path;
        }

        $root = function_exists('root_path') ? root_path() : getcwd() . DIRECTORY_SEPARATOR;

        return $root . ltrim($path, '/\\');
    }

    protected function disposition(string $filename, string $type): string
    {
        $fallback = preg_replace('/[^\x20-\x7E]/', '_', $filename) ?: 'qrcode';
        $encoded = rawurlencode($filename);

        return sprintf(
            '%s; filename="%s"; filename*=UTF-8\'\'%s',
            $type,
            addcslashes($fallback, '"\\'),
            $encoded,
        );
    }
}