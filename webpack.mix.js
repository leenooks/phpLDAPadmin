const mix = require('laravel-mix');

const webpack = require('webpack')

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

mix.js([
		'resources/js/app.js',
		'resources/js/bootstrap3-typeahead.js',
		'resources/themes/architect/src/init.js'
	],'public/js').extract()
	.setResourceRoot("..")
	.sass('resources/sass/app.scss','public/css');
