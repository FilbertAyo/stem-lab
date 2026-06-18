<?php

namespace App\Support;

class Model3dEmbed
{
    /**
     * Normalize admin input (URL or iframe snippet) into a clean embed source URL.
     */
    public static function normalizeInput(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        $input = trim(html_entity_decode($input));

        if ($input === '') {
            return null;
        }

        if (preg_match('/src=["\']([^"\']+)["\']/i', $input, $matches)) {
            $input = html_entity_decode($matches[1]);
        }

        if (! filter_var($input, FILTER_VALIDATE_URL)) {
            return null;
        }

        if ($sketchfabId = self::extractSketchfabModelId($input)) {
            return 'https://sketchfab.com/models/'.$sketchfabId.'/embed';
        }

        return $input;
    }

    /**
     * Build a viewer-friendly embed URL with minimal third-party chrome.
     */
    public static function viewerUrl(?string $embedUrl, string $modelName): ?string
    {
        $normalized = self::normalizeInput($embedUrl);

        if ($normalized === null) {
            return null;
        }

        if (self::extractSketchfabModelId($normalized) !== null) {
            return self::customizeSketchfabUrl($normalized, $modelName);
        }

        return $normalized;
    }

    public static function isSketchfabUrl(?string $url): bool
    {
        return $url !== null && self::extractSketchfabModelId($url) !== null;
    }

    private static function customizeSketchfabUrl(string $embedUrl, string $modelName): string
    {
        $modelId = self::extractSketchfabModelId($embedUrl);

        if ($modelId === null) {
            return $embedUrl;
        }

        $params = http_build_query([
            'autostart' => '1',
            'preload' => '1',
            'autospin' => '0.15',
            'ui_theme' => 'dark',
            'ui_controls' => '1',
            'ui_fullscreen' => '1',
            'ui_infos' => '0',
            'ui_inspector' => '0',
            'ui_watermark' => '0',
            'ui_author' => '0',
            'ui_title' => '0',
            'ui_hint' => '0',
            'ui_help' => '0',
            'ui_settings' => '0',
            'ui_related' => '0',
            'ui_share' => '0',
            'ui_ar' => '0',
            'ui_stop' => '0',
            'ui_annotations' => '0',
            'annotation' => '0',
            'transparent' => '0',
            'title' => $modelName,
        ]);

        return 'https://sketchfab.com/models/'.$modelId.'/embed?'.$params;
    }

    private static function extractSketchfabModelId(string $url): ?string
    {
        $patterns = [
            '/sketchfab\.com\/models\/([a-zA-Z0-9]+)(?:\/embed|\/)?/i',
            '/sketchfab\.com\/3d-models\/[^\/]+-([a-zA-Z0-9]+)(?:\/|\?|$)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }
}
