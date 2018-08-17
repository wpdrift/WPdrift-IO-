let mix = require('laravel-mix');
mix.setPublicPath('public');
mix.js('public/src/wpdrift-worker-public.js', 'public/js');
