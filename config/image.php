<?php

return [
    'driver' => extension_loaded('imagick')
        ? \Intervention\Image\Drivers\Imagick\Driver::class
        : \Intervention\Image\Drivers\Gd\Driver::class,
    'options' => [
        'autoOrientation' => true,
        'decodeAnimation' => true,
        'blendingColor' => 'ffffff',
        'strip' => true,
    ],
];
