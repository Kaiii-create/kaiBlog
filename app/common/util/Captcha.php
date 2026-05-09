<?php
namespace app\common\util;

class Captcha
{
    private $width = 120;
    private $height = 40;
    private $length = 4;
    private $fontSize = 20;
    private $bgColor = [245, 245, 245];
    private $fonts = []; // 字体文件路径

    // 干扰线数量
    private $lineCount = 4;
    // 干扰点数量
    private $noisePointCount = 50;

    // 字符集（去掉容易混淆的字符）
    private $charset = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    public function __construct(array $options = [])
    {
        foreach ($options as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * 生成验证码
     * @return array ['code' => string, 'image' => string(base64)]
     */
    public function generate(): array
    {
        $code = $this->randomCode();
        $image = $this->renderImage($code);

        return [
            'code' => $code,
            'image' => 'data:image/png;base64,' . base64_encode($image),
        ];
    }

    /**
     * 生成随机验证码
     */
    private function randomCode(): string
    {
        $code = '';
        $len = strlen($this->charset) - 1;
        for ($i = 0; $i < $this->length; $i++) {
            $code .= $this->charset[random_int(0, $len)];
        }
        return $code;
    }

    /**
     * 渲染验证码图片
     */
    private function renderImage(string $code): string
    {
        $img = imagecreatetruecolor($this->width, $this->height);

        // 背景
        $bg = imagecolorallocate($img, $this->bgColor[0], $this->bgColor[1], $this->bgColor[2]);
        imagefill($img, 0, 0, $bg);

        // 画干扰线
        for ($i = 0; $i < $this->lineCount; $i++) {
            $color = imagecolorallocate($img, random_int(100, 200), random_int(100, 200), random_int(100, 200));
            imageline($img, random_int(0, $this->width), random_int(0, $this->height),
                random_int(0, $this->width), random_int(0, $this->height), $color);
        }

        // 画干扰点
        for ($i = 0; $i < $this->noisePointCount; $i++) {
            $color = imagecolorallocate($img, random_int(100, 200), random_int(100, 200), random_int(100, 200));
            imagesetpixel($img, random_int(0, $this->width), random_int(0, $this->height), $color);
        }

        // 画验证码字符
        $x = 10;
        for ($i = 0; $i < strlen($code); $i++) {
            $color = imagecolorallocate($img, random_int(0, 100), random_int(0, 100), random_int(0, 100));
            $angle = random_int(-15, 15);
            $y = random_int(25, 35);
            imagestring($img, 5, $x, $y - 15, $code[$i], $color);
            $x += 25;
        }

        ob_start();
        imagepng($img);
        $image = ob_get_clean();
        imagedestroy($img);

        return $image;
    }
}
