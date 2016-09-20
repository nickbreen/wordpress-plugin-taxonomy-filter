<?php
/**
 * Plugin Name: foo//bar Taxonomy Filter
 * Version: 1.0.2
 * Description: Provides a taxonomy filter widget to refine searches and archive page listings.
 * Author: nickbreennz
 * Author URI: https://www.foobar.net.nz
 * Plugin URI: https://github.com/nickbreen/wordpress-plugin-taxonomy-filter
 * Text Domain: wordpress-plugin-taxonomy-filter
 * Domain Path: /languages
 * @package Taxonomy Filter
 */

require_once 'nz/net/foobar/wp/class-taxonomy-walker.php';
require_once 'nz/net/foobar/wp/class-taxonomy-walker-filter.php';
require_once 'nz/net/foobar/wp/class-taxonomy-widget.php';

add_action('widgets_init', function () {
    register_widget(\nz\net\foobar\wp\WidgetTaxonomy::class);
});
