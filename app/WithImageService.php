<?php

namespace App;

use App\Services\ImageService;

trait WithImageService
{
    protected function imageService(): ImageService
    {
        return app(ImageService::class);
    }
}
