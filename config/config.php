<?php

use Spatie\Image\Enums\Fit;

return [
  /**
   * ...
   */
  'menu'    => [
    'model' => \Neon\Models\Menu::class,
  ],

  'link'  => [
    'model' => \Neon\Models\Link::class,
  ],

  'content' => [
    'layouts' => [
      //   \App\Nova\Flexible\Layouts\NewsLayout::class
    ]
  ],
  
  'conversations' => [
    'og_image' => [
      'fit'           => Fit::Max,
      'height'        => 300,
      'optimize'      => true,
    ],
  ]
];
