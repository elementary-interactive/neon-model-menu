<?php

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
    'model' => \Neon\Models\Content::class,
    'layouts' => [
    //   \App\Nova\Flexible\Layouts\NewsLayout::class
    ]
  ]
];