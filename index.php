<?php

/**
 * Plugin Name: [Propeople] Google News Sitemap
 * Plugin URI: http://wordpress.org/plugins/propeople-google-news-sitemap/
 * License: MIT
 * Version: 1.0.1
 * Text Domain: index
 * Author: Sergey Bondarenko
 * Author URI: http://wearepropeople.com/
 * Description: Basic XML sitemap generator for submission to Google News.
 *
 * @package GoogleNewsSitemap
 * @copyright 2013 (c) Propeople
 * @license MIT, <http://plugins.svn.wordpress.org/propeople-google-news-sitemap/trunk/LICENCE>
 * @version 1.0.1, November 12, 2013
 * @author BR0kEN, <broken@firstvector.org>
 *
 * @uses InvalidArgumentException
 * @uses RuntimeException
 * @uses Plugin
 * @uses wpdb
 */

namespace Propeople\GoogleNewsSitemap;

//==============================================================================

/**
 * @todo Verifies the existence of a constant ABSPATH, which defined in
 * wp-load.php. This action is necessary to prohibit the executing this
 * file in browser.
 */

if (!defined('ABSPATH')) {

  if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {

    die('Hacking attempt!');

  }

}

define('PLUGIN_ROOT', __DIR__);
define('PLUGIN_DIR', plugin_dir_url(__FILE__));

//==============================================================================

/**
 * @todo Set error handler for catching invalig argument error.
 */
set_error_handler(function($errno, $errstr, $errfile, $errline) {

  if (E_RECOVERABLE_ERROR === $errno) {

    throw new \InvalidArgumentException(
      preg_replace('/line ([0-9]+)/', "line $errline", $errstr)
    );

  }

});

//==============================================================================

/**
 * @todo Save default settings for plugin, return answer and stop work.
 */
add_action('wp_ajax_gns_default', function(){
  $status = update_option($_POST['action'], $_POST['options']);

  echo json_encode(array(
    'status' => $status ? 'done' : 'fail',
    'message' => $status ? 'Saved!' : "Trouble on server. Options don't saved."
  ));

  exit;
});

//==============================================================================

/**
 * @todo Checks for exceptions and writes it, if there is,
 * to error log of the server.
 */
try {

  require 'classes/sitemap.class.php';

  $dev = __NAMESPACE__ . '\Plugin';

  if (class_exists('Plugin')) {

    $gns = new \Plugin($GLOBALS['wpdb']);

    if (is_object($gns)) {

      /**
       * @todo Creating a sitemap and send notice to Google about it.
       */
      register_activation_hook(__FILE__, function() use ($gns) {
        return $gns->install()->getPosts()->write()->ping();
      });

      /**
       * @todo Remove sitemap file or throw exception.
       */
      register_deactivation_hook(__FILE__, function() use ($gns) {
        return $gns->removeSitemap();
      });

      /**
       * @todo Add the "Settings" link to plugin row.
       */
      add_filter('plugin_row_meta', function($links, $file = false) use ($gns) {

        if (strpos($links[1], 'Sergey') !== false) {

          echo $gns->pluginSettings();

          $links[] = sprintf('<a id="gns_open">%s</a>', __('Settings'));

        }

        return $links;
      });

      /**
       * @todo Next code is necessary for Google XML Sitemaps plugin. It
       * updates Google News Sitemap in general sitemap.
       *
       * @link http://wordpress.org/plugins/google-sitemap-generator/
       */
      add_action('sm_buildmap', function(){

        $index = \GoogleSitemapGenerator::GetInstance();

        if ($index !== null) {

          $index->AddUrl(content_url(\Plugin::FILE), time(), 'daily', 0.9);

        }

      });

    } else {

      throw new \RuntimeException("Failed to create $dev instance.");

    }

  } else {

    throw new \RuntimeException(
      "Can't create object from $dev, because it doesn't found!"
    );

  }

} catch (\Exception $e) {

  /**
   * @todo All errors of plugin will be placed to server error log.
   */
  error_log($e->getMessage());

}

?>