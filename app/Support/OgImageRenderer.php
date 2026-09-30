<?php

namespace App\Support;

use RuntimeException;

class OgImageRenderer
{
    private const FONT_PATH = '/usr/share/fonts/opentype/noto/NotoSansCJK-Regular.ttc';

    private const WIDTH = 1200;

    private const HEIGHT = 630;

    public function render(string $title, string $description, string $eyebrow): string
    {
        if (! extension_loaded('gd') || ! function_exists('imagettftext')) {
            throw new RuntimeException('GD with FreeType support is required to render Open Graph images.');
        }

        if (! is_file(self::FONT_PATH)) {
            throw new RuntimeException('The Noto Sans CJK font is required to render Open Graph images.');
        }

        $image = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        if ($image === false) {
            throw new RuntimeException('Unable to create the Open Graph image.');
        }

        try {
            $this->drawBackground($image);
            $this->drawRoundedRectangle($image, 64, 62, 1136, 568, 32, $this->color($image, 255, 255, 255, 23));
            $this->drawRoundedRectangle($image, 104, 112, 116, 518, 6, $this->color($image, 249, 115, 22));

            $this->drawText($image, 25, 154, 190, $this->truncate($eyebrow, 32), [194, 65, 12]);
            $title = $this->truncate($title, 22);
            $this->drawText($image, mb_strlen($title) > 14 ? 42 : 64, 154, 320, $title, [41, 37, 36]);
            $this->drawText($image, 30, 154, 390, $this->truncate($description, 44), [87, 83, 78]);

            $this->drawRoundedRectangle($image, 154, 448, 404, 506, 29, $this->color($image, 234, 88, 12));
            $this->drawText($image, 23, 198, 486, 'メニューガチャ', [255, 255, 255]);
            $this->drawText($image, 18, 920, 510, 'MENU GACHA', [168, 162, 158]);

            ob_start();
            imagepng($image);
            $png = ob_get_clean();
            if (! is_string($png)) {
                throw new RuntimeException('Unable to encode the Open Graph image.');
            }

            return $png;
        } finally {
            imagedestroy($image);
        }
    }

    private function drawBackground(\GdImage $image): void
    {
        for ($y = 0; $y < self::HEIGHT; $y++) {
            $progress = $y / self::HEIGHT;
            $color = $this->color(
                $image,
                255,
                (int) (248 - 22 * $progress),
                (int) (237 - 43 * $progress));
            imageline($image, 0, $y, self::WIDTH, $y, $color);
        }

        imagealphablending($image, true);
        imagefilledellipse($image, 1080, 100, 440, 440, $this->color($image, 251, 146, 60, 112));
        imagefilledellipse($image, 80, 610, 500, 500, $this->color($image, 253, 186, 116, 82));
    }

    private function drawRoundedRectangle(\GdImage $image, int $left, int $top, int $right, int $bottom, int $radius, int $color): void
    {
        imagefilledrectangle($image, $left + $radius, $top, $right - $radius, $bottom, $color);
        imagefilledrectangle($image, $left, $top + $radius, $right, $bottom - $radius, $color);
        imagefilledellipse($image, $left + $radius, $top + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($image, $right - $radius, $top + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($image, $left + $radius, $bottom - $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($image, $right - $radius, $bottom - $radius, $radius * 2, $radius * 2, $color);
    }

    private function drawText(\GdImage $image, int $size, int $x, int $y, string $text, array $rgb): void
    {
        imagettftext($image, $size, 0, $x, $y, $this->color($image, ...$rgb), self::FONT_PATH, $text);
    }

    private function color(\GdImage $image, int $red, int $green, int $blue, int $opacity = 0): int
    {
        return imagecolorallocatealpha($image, $red, $green, $blue, $opacity);
    }

    private function truncate(string $text, int $length): string
    {
        return mb_strlen($text) > $length ? mb_substr($text, 0, $length).'…' : $text;
    }
}
