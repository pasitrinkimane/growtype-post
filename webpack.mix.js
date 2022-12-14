let mix = require('laravel-mix');

// mix.options({
//     legacyNodePolyfills: true
// });

mix.setResourceRoot('./')

/**
 * Block
 */
// mix.setPublicPath('./');
// mix.copy('resources/blocks/main/block.json', 'public/blocks/main');
//
mix.setPublicPath('./public');
//
// mix.js('resources/blocks/main/scripts/index.js', 'blocks/main/build')
//     .react();

// mix.sass('resources/blocks/main/styles/style.scss', 'blocks/main/styles')
//     .sass('resources/blocks/main/styles/editor.scss', 'blocks/main/styles')

/**
 * General
 */
mix.sass('resources/styles/growtype-post.scss', 'styles')
mix.sass('resources/styles/growtype-post-admin.scss', 'styles')

mix.js('resources/scripts/growtype-post.js', 'scripts')
mix.js('resources/scripts/growtype-post-admin.js', 'scripts')

/**
 * Plugins
 */

mix
    .sourceMaps()
    .version();
