<?php

/**
 * Class Plugin declared as @final and can't to be inherited.
 *
 * @uses (const:string) WP_CONTENT_DIR
 * @uses (const:string) PLUGIN_ROOT
 * @uses (const:string) PLUGIN_DIR
 */

final class Plugin {
//==============================================================================
// Variables
//==============================================================================

  private

  /**
   * @access private
   * @var (array) $posts - selected, from DB, posts.
   */
  $posts = array(),

  /**
   * @access private
   * @var (object) $db - the instance of $wpdb;
   */
  $db;

//==============================================================================
// Constants
//==============================================================================

  /**
   * @access public
   * @const (string) FILE - the link to sitemap.
   */
  const FILE = '/uploads/google_news_sitemap.xml';

//==============================================================================
// Magic methods
//==============================================================================

  /**
   * @access public
   * @method __construct
   *
   * @uses (self) append
   * @uses (self) build
   *
   * @todo Save instance of $wpdb to local variable, append the form with
   * settings to the screen for adding or editing post, set the action on
   * save, delete or publish the post.
   *
   * @param (object) $wpdb - instance of WPDB class.
   *
   * @return (void)
   */
  public function __construct(\wpdb $wpdb) {
    $this->db = $wpdb;

    add_action('add_meta_boxes', array($this, 'append'));

    foreach (array('save', 'delete', 'publish') as $action) {

      add_action($action . '_post', array($this, 'build'));

    }
  }

//==============================================================================
// Public methods
//==============================================================================

  /**
   * @access public
   * @method append
   *
   * @uses (self) postSettings
   *
   * @todo Define the administrative form, which been adding to post page.
   *
   * @return (void)
   */
  public function append() {
    add_meta_box(
      'PGNS',                       // HTML id attr
      __('Google News Sitemap'),    // Section title
      array($this, 'postSettings'), // Callback
      'post',                       // Post type
      'side'                        // Context
    );
  }

  /**
   * @access public
   * @method postSettings
   *
   * @todo Show settings of plugin for administrators or message, when the
   * article can't be added to sitemap.
   *
   * @return (void)
   */
  public function postSettings() {
    if (self::dateDiff()->days <= 2) {

      echo $this->buildTemplate(
        get_post_meta($GLOBALS['post']->ID, 'gns_option', true) ? : get_site_option('gns_default'),
        $this->assets('form.tpl')
      ),

      $this->assets(array(
        'styles.css',
        'modal.css',
        'modal.js',
        'info.tpl'
      ));

    } else {

      echo __("This publication has been added more than two days ago and can't
        be added to Google News Sitemap according to the rules of service.");

    }
  }

  /**
   * @access public
   * @method pluginSettings
   *
   * @todo Load files, scripts and template with default settings.
   *
   * @return (string) - HTML of template for change default settings.
   */
  public function pluginSettings() {
    return $this->buildTemplate(get_site_option('gns_default'), str_replace(
      '{form}',
      $this->assets('form.tpl'),
      $this->assets(array(
        'styles.css',
        'modal.css',
        'modal.js',
        'settings.js',
        'settings.tpl'
      )
    )));
  }

  /**
   * @access public
   * @method install
   *
   * @todo Add default options to DB if it isn't exist. Called only when
   * you activate the plugin.
   *
   * @return (self)
   */
  public function install() {
    add_site_option('gns_default', array(
      'access' => 'None',
      'genres' => 'None',
      'stock' => '',
      'lang' => array('en' => 'English'),
      'loc' => '',
      'add' => 'yes'
    ));

    return $this;
  }

  /**
   * @access public
   * @method build
   *
   * @uses (self:static) dateDiff
   * @uses (self) getPosts
   * @uses (self) write
   * @uses (self) ping
   *
   * @todo Sitemap will be created only when publication saved. If the
   * post is editing, it will be included to sitemap if it has been
   * posted no more of two days ago.
   *
   * @param (integer) $post_id - ID of publication.
   *
   * @return (void)
   */
  public function build($post_id) {
    if (isset($_POST['gns_option'])) {

      if (self::dateDiff()->days <= 2) {

        /**
         * @todo Remove special chars from option value.
         */
        array_walk_recursive($_POST['gns_option'], function($key, $option){

          $option = preg_replace('/[^,\s;a-zA-Z0-9_-]|[,\s]$/s', '', $option);

        });

        /**
         * @todo Save options for current post to DB.
         */
        update_post_meta($post_id, 'gns_option', $_POST['gns_option']);

        /**
         * @todo Get the publications, which can be added to sitemap,
         * build it and send ping to Google.
         */
        $this->getPosts()->write()->ping();

      }

    }
  }

  /**
   * @access public
   * @method write
   *
   * @todo Build XML file and write it to file.
   *
   * @return (self)
   */
  public function write() {
    if ($this->posts && is_array($this->posts)) {

      $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n\n";
      $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">';

      foreach ($this->posts as $post) {

        $post['options'] = unserialize($post['options']);

        if ($post['options']['add'] !== 'no') {

          $xml .= "\n\t<url>";
          $xml .= "\n\t\t<loc>". get_permalink($post['ID']) .'</loc>';
          $xml .= "\n\t\t<news:news>";
          $xml .= "\n\t\t\t<news:publication>";
          $xml .= "\n\t\t\t\t<news:name>{$post['post_title']}</news:name>";
          $xml .= "\n\t\t\t\t<news:language>{$post['options']['lang']}</news:language>";
          $xml .= "\n\t\t\t</news:publication>";

          if ($post['options']['lang'] === 'en') {

            if ($post['options']['access'] && $post['options']['access'] !== 'None') {

              $xml .= "\n\t\t\t<news:access>{$post['options']['access']}</news:access>";

            }

            if (is_array($post['options']['genres'])) {

              if (!in_array('None', $post['options']['genres'])) {

                $xml .= "\n\t\t\t<news:genres>". implode(', ', $post['options']['genres']) .'</news:genres>';

              }

            }

          }

          $xml .= "\n\t\t\t<news:publication_date>". date('c', strtotime($post['post_modified_gmt'])) .'</news:publication_date>';
          $xml .= "\n\t\t\t<news:title>{$post['post_title']}</news:title>";

          if ($post['options']['loc']) {

            $xml .= "\n\t\t\t<news:geo_locations>{$post['options']['loc']}</news:geo_locations>";

          }

          if ($post['tags']) {

            $xml .= "\n\t\t\t<news:keywords>{$post['tags']}</news:keywords>";

          }

          if ($post['options']['stock']) {

            $xml .= "\n\t\t\t<news:stock_tickers>{$post['options']['stock']}</news:stock_tickers>";

          }

          $xml .= "\n\t\t</news:news>";
          $xml .= "\n\t</url>\n";

        }
      }

      $xml .= '</urlset>';

      file_put_contents(WP_CONTENT_DIR . self::FILE, $xml);
    }

    return $this;
  }

  /**
   * @access public
   * @method ping
   *
   * @todo Send notice to Google, that has been created a new sitemap.
   *
   * @return (self)
   */
  public function ping() {
    file_get_contents('http://www.google.ru/webmasters/tools/ping?sitemap=' . content_url(self::FILE));

    return $this;
  }

  /**
   * @access public
   * @method getPosts
   *
   * @todo Select all publications, which been added in the last two days.
   *
   * @return (self)
   */
  public function getPosts() {
    $this->posts = $this->db->get_results(
      "SELECT p.*, m.meta_value as options,

      (SELECT GROUP_CONCAT(t.name ORDER BY t.name ASC SEPARATOR ', ')
      FROM {$this->db->term_relationships} tr,
           {$this->db->term_taxonomy} tt,
           {$this->db->terms} t
      WHERE tr.term_taxonomy_id = tt.term_taxonomy_id
      AND tt.term_id = t.term_id
      AND tr.object_id = p.ID
      AND tt.taxonomy = 'post_tag') as tags

      FROM {$this->db->posts} p

      LEFT JOIN {$this->db->postmeta} m
      ON m.meta_key = 'gns_option'
      AND m.post_id = p.ID

      WHERE p.post_status = 'publish'
      AND p.post_type = 'post'
      AND p.post_date >= (NOW() - INTERVAL 2 DAY)

      ORDER BY p.ID DESC

      LIMIT 0,1000",

      ARRAY_A
    );

    return $this;
  }

//==============================================================================
// Static methods
//==============================================================================

  /**
   * @static
   * @access public
   * @method removeSitemap
   *
   * @todo Remove sitemap file if it is exist.
   *
   * @throws Exception if file doesn't exist.
   *
   * @return (void)
   */
  public static function removeSitemap() {
    $sitemap = WP_CONTENT_DIR . self::FILE;

    if (file_exists($sitemap)) {

      try {

        unlink(WP_CONTENT_DIR . self::FILE);

      } catch (\Exception $e) {

        throw new \Exception($e->getMesage());

      }

    }
  }

//==============================================================================
// Private methods
//==============================================================================

  /**
   * @access private
   * @method assets
   *
   * @todo Include styles, scripts and templates.
   *
   * @throws RuntimeException when file doesn't exist.
   *
   * @return (mixed)
   */
  private function assets($files) {
    if (!is_array($files)) {

      $files = array($files);

    }

    foreach ($files as $file) {

      $type = substr($file, -3);

      if ($type == '.js') {

        $type = 'js';

      }

      $path = "assets/$type/$file";
      $root = PLUGIN_ROOT . "/$path";
      $url = PLUGIN_DIR . $path;

      if (file_exists($root)) {

        switch ($type) {
          case 'css':

            wp_enqueue_style($file, $url);

          break;

          case 'js':

            wp_enqueue_script($file, $url, array('jquery'), '1.0', true);

          break;

          case 'tpl':

            return file_get_contents($root);

          break;
        }

      } else {

        throw new \RuntimeException("File \"$root\" not found!");

      }

    }

    return $this;
  }

  /**
   * @access private
   * @method buildTemplate
   *
   * @todo
   *
   * @return (string)
   */
  private function buildTemplate(array $options, $template) {
    return str_replace(
      array(
        '{access}',
        '{genres}',
        '{lang}',
        '{add}',
        '{stock}',
        '{loc}'
      ),
      array(
        self::buildSelect($options['access'], array(
          'None',
          'Subscription',
          'Registration'
        )),
        self::buildSelect($options['genres'], array(
          'None',
          'PressRelease',
          'Satire',
          'Blog',
          'OpEd',
          'Opinion',
          'UserGenerated'
        )),
        self::buildSelect($options['lang'], array(
          'en' => 'English',
          'ja' => 'Japanese',
          'cs' => 'Czech',
          'zh' => 'Chinese',
          'da' => 'Danish',
          'de' => 'German',
          'fi' => 'Finnish',
          'fr' => 'French',
          'it' => 'Italian',
          'ko' => 'Korean',
          'lv' => 'Latvian',
          'ro' => 'Romanian',
          'ru' => 'Russian',
          'sk' => 'Slovak',
          'sr' => 'Serbian',
          'sv' => 'Swedish',
          'tr' => 'Turkish',
          'uk' => 'Ukrainian'
        )),
        $options['add'] !== 'no' ? ' checked' : '',
        $options['stock'] ? : '',
        $options['loc'] ? : ''
      ),
      $template
    );
  }

  /**
   * @static
   * @access private
   * @method buildSelect
   *
   * @todo
   *
   * @param (mixed) $option - current value of option. May be an (array) or
   * (string). That's need for work with multiple selects. If value - is a
   * string, it will be transformed to array with one element - this string.
   *
   * @param (array) $data - May be contain a simple or associative array.
   * In first case, the value of array - it's a value for <option> and his
   * context. In second - the key of array - value of <option>,
   * and value - value of context.
   *
   * @return (string)
   */
  private static function buildSelect($option = 'None', array $data) {
    $html = '';

    if (!is_array($option)) {

      $option = array($option);

    }

    foreach ($data as $key => $value) {

      $key = is_int($key) ? $value : $key;

      $picked = in_array($key, $option) ? ' selected' : '';

      $html .= "<option value=\"$key\"$picked>$value</option>";

    }

    return $html;
  }

  /**
   * @static
   * @access private
   * @method dateDiff
   *
   * @todo
   *
   * @return (object)
   */
  private static function dateDiff() {
    $currentDate = new \DateTime(date('Y-m-d H:i:s', time()));
    $publishDate = new \DateTime($GLOBALS['post']->post_date);

    return $publishDate->diff($currentDate);
  }
}

?>