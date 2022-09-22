let mix = require('laravel-mix');

mix.setPublicPath('./public');
mix.setResourceRoot('./')

mix
    .sass('resources/styles/growtype-post.scss', 'styles')
    .sass('resources/styles/growtype-post-render.scss', 'styles')
    .sass('resources/styles/forms/login/main.scss', 'styles/forms/login')
    .sass('resources/styles/forms/signup/main.scss', 'styles/forms/signup');

mix
    .js('resources/scripts/growtype-post.js', 'scripts')
    .js('resources/scripts/growtype-post-render.js', 'scripts');

mix
    .copyDirectory('resources/plugins', 'public/plugins')

mix
    .sourceMaps()
    .version();
