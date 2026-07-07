<?php

/**
 * Helper to integrate Vite assets in CodeIgniter 4 views.
 */

if (!function_exists('vite_tags')) {
    /**
     * Outputs the <script> and <link> tags for Vite assets.
     */
    function vite_tags(): string
    {
        $isDev = env('VITE_DEV_SERVER', false);
        $manifestPath = FCPATH . 'dist/.vite/manifest.json';
        $entryPoint = 'resources/js/main.ts';

        if ($isDev) {
            $devServerUrl = 'http://localhost:5173';
            return '<script type="module" src="' . $devServerUrl . '/@vite/client"></script>' . "\n" .
                   '<script type="module" src="' . $devServerUrl . '/' . $entryPoint . '"></script>';
        }

        if (!is_file($manifestPath)) {
            return '<!-- Vite manifest not found. Please run `npm run build`. -->';
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        if (!$manifest || !isset($manifest[$entryPoint])) {
            return '<!-- Entry point not found in Vite manifest. -->';
        }

        $tags = '';
        $entry = $manifest[$entryPoint];

        // CSS
        if (isset($entry['css'])) {
            foreach ($entry['css'] as $cssFile) {
                $tags .= '<link rel="stylesheet" href="' . base_url('dist/' . $cssFile) . '">' . "\n";
            }
        }

        // JS
        $tags .= '<script type="module" src="' . base_url('dist/' . $entry['file']) . '"></script>' . "\n";

        return $tags;
    }
}
