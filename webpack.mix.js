const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */
const tailwindcss             = require('tailwindcss');
const postCssImport           = require("postcss-import");
const postCssNested           = require("postcss-nested");
const postcssCustomProperties = require("postcss-custom-properties");
const autoprefixer            = require("autoprefixer");

mix.webpackConfig({
    stats: {
      hash: true,
      version: true,
      timings: true,
      children: true,
      errors: true,
      errorDetails: true,
      warnings: true,
      chunks: true,
      modules: false,
      reasons: true,
      source: true,
      publicPath: true,
    }
  }).copy('resources/js/theme', 'public/js')
    .ts('resources/js/app.tsx', 'public/js').react()
    .postCss('resources/css/app.css', 'public/css', [
      postCssImport(),
      require('tailwindcss/nesting')(require('postcss-nesting')),
      tailwindcss(),
      postCssNested(),
      postcssCustomProperties(),
      autoprefixer(),
    ])
    .version()
    .sourceMaps();
