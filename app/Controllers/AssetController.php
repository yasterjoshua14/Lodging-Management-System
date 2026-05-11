<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;

class AssetController extends BaseController
{
    public function themeCss()
    {
        $path = APPPATH . 'Views/theme/style.css';

        if (! is_file($path)) {
            throw PageNotFoundException::forPageNotFound('Theme stylesheet not found.');
        }

        $css = file_get_contents($path);

        if (! is_string($css)) {
            throw PageNotFoundException::forPageNotFound('Theme stylesheet could not be loaded.');
        }

        return $this->response
            ->setHeader('Cache-Control', 'public, max-age=300')
            ->setContentType('text/css')
            ->setBody($css);
    }

    public function themeImage(string $filename)
    {
        $filename = basename($filename);
        $path     = APPPATH . 'Views/theme/img/' . $filename;

        if (! is_file($path)) {
            throw PageNotFoundException::forPageNotFound('Theme image not found.');
        }

        $image = file_get_contents($path);

        if (! is_string($image)) {
            throw PageNotFoundException::forPageNotFound('Theme image could not be loaded.');
        }

        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        $contentType = match ($extension) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'application/octet-stream',
        };

        return $this->response
            ->setHeader('Cache-Control', 'public, max-age=300')
            ->setContentType($contentType)
            ->setBody($image);
    }
}
