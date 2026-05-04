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
}
