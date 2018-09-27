let mix = require('laravel-mix');
mix.setPublicPath('./');
mix.js('public/src/wpdrift-worker-public.js', 'public/js');
