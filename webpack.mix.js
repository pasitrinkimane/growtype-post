let mix = require('laravel-mix');

mix.setResourceRoot('./')

/**
 * Block
 */
mix.setPublicPath('./public');

/**
 * General
 */
mix.sass('resources/styles/growtype-post.scss', 'styles')
mix.sass('resources/styles/growtype-post-admin.scss', 'styles')

mix.js('resources/scripts/growtype-post.js', 'scripts')

// mix.js('resources/scripts/growtype-post-admin.js', 'scripts')

mix.scripts([
    'resources/scripts/growtype-post-admin.js',
], 'public/scripts/growtype-post-admin.js');

mix
    .copyDirectory('resources/icons', 'public/icons')

mix
    .sourceMaps()
    .version();
