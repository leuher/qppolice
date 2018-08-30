<?php

return array (
  'autoload' => false,
  'hooks' => 
  array (
    'leesignhook' => 
    array (
      0 => 'leesign',
    ),
    'login_init' => 
    array (
      0 => 'loginbg',
    ),
  ),
  'route' => 
  array (
    '/cms$' => 'cms/index/index',
    '/cms/c/[:diyname]' => 'cms/channel/index',
    '/cms/t/[:name]' => 'cms/tags/index',
    '/cms/a/[:diyname]' => 'cms/archives/index',
    '/cms/p/[:diyname]' => 'cms/page/index',
    '/cms/s' => 'cms/search/index',
    '/example$' => 'example/index/index',
    '/example/d/[:name]' => 'example/demo/index',
    '/example/d1/[:name]' => 'example/demo/demo1',
    '/example/d2/[:name]' => 'example/demo/demo2',
    '/leesign$' => 'leesign/index/index',
    '/qrcode$' => 'qrcode/index/index',
    '/qrcode/build$' => 'qrcode/index/build',
    '/third$' => 'third/index/index',
    '/third/connect/[:platform]' => 'third/index/connect',
    '/third/callback/[:platform]' => 'third/index/callback',
  ),
);