<?php
namespace app\common\util;

class Jwt
{
    private static $secret = 'blog_api_jwt_secret_key_2024';
    private static $expire = 2592000; // 30天

    public static function setSecret($secret)
    {
        self::$secret = $secret;
    }

    public static function setExpire($expire)
    {
        self::$expire = $expire;
    }

    /**
     * 生成 JWT Token
     */
    public static function encode(array $payload): string
    {
        $header = self::base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload['iat'] = time();
        $payload['exp'] = time() + self::$expire;
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        $signature = self::base64UrlEncode(
            hash_hmac('sha256', "$header.$payloadEncoded", self::$secret, true)
        );
        return "$header.$payloadEncoded.$signature";
    }

    /**
     * 解析 JWT Token
     */
    public static function decode(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;

        // 验证签名
        $expectedSig = self::base64UrlEncode(
            hash_hmac('sha256', "$header.$payload", self::$secret, true)
        );
        if (!hash_equals($expectedSig, $signature)) {
            return null;
        }

        $data = json_decode(self::base64UrlDecode($payload), true);
        if (!$data || !isset($data['exp'])) {
            return null;
        }

        // 验证过期
        if ($data['exp'] < time()) {
            return null;
        }

        return $data;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
