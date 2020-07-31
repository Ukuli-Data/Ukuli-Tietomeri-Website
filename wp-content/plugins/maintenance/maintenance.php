<?php
/*
	Plugin Name: Maintenance
	Plugin URI: http://wordpress.org/plugins/maintenance/
	Description: Put your site in maintenance mode, away from the public view. Use maintenance plugin if your website is in development or you need to change a few things, run an upgrade. Make it only accessible to logged in users.
	Version: 3.90
	Author: WP Maintenance
	Author URI: https://wpmaintenancemode.com/
	License: GPL2

  Copyright 2013-2019  WebFactory Ltd  (email : support@webfactoryltd.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class MTNC
{
  public function __construct()
  {
    global $mtnc_variable;
    $mtnc_variable = new stdClass();

    add_action('plugins_loaded', array(&$this, 'mtnc_constants'), 1);
    add_action('plugins_loaded', array(&$this, 'mtnc_lang'), 2);
    add_action('plugins_loaded', array(&$this, 'mtnc_includes'), 3);
    add_action('plugins_loaded', array(&$this, 'mtnc_admin'), 4);

    register_activation_hook(__FILE__, array(&$this, 'mtnc_activation'));
    register_deactivation_hook(__FILE__, array(&$this, 'mtnc_deactivation'));

    add_action('template_include', array(&$this, 'mtnc_template_include'), 999999);
    add_action('do_feed_rdf', array(&$this, 'disable_feed'), 0, 1);
    add_action('do_feed_rss', array(&$this, 'disable_feed'), 0, 1);
    add_action('do_feed_rss2', array(&$this, 'disable_feed'), 0, 1);
    add_action('do_feed_atom', array(&$this, 'disable_feed'), 0, 1);
    add_action('wp_logout', array(&$this, 'mtnc_user_logout'));
    add_action('init', array(&$this, 'mtnc_admin_bar'));
    add_action('init', array(&$this, 'mtnc_set_global_options'), 1);

    add_action('admin_action_mtnc_install_weglot', array(&$this, 'install_weglot'));

    add_filter(
      'plugin_action_links_' . plugin_basename(__FILE__),
      array(&$this, 'plugin_action_links')
    );
    add_filter('install_plugins_table_api_args_featured', array(&$this, 'featured_plugins_tab'));
    add_filter('install_plugins_table_api_args_recommended', array(&$this, 'featured_plugins_tab'));
  }

  // add settings link to plugins page
  function plugin_action_links($links)
  {
    $settings_link = '<a href="' . admin_url('admin.php?page=maintenance') . '" title="' . __('Maintenance Settings', 'maintenance') . '">' . __('Settings', 'maintenance') . '</a>';

    array_unshift($links, $settings_link);

    return $links;
  } // plugin_action_links

  public function mtnc_constants()
  {
    define('MTNC_VERSION', '3.8');
    define('MTNC_DB_VERSION', 2);
    define('MTNC_WP_VERSION', get_bloginfo('version'));
    define('MTNC_DIR', trailingslashit(plugin_dir_path(__FILE__)));
    define('MTNC_URI', trailingslashit(plugin_dir_url(__FILE__)));
    define('MTNC_INCLUDES', MTNC_DIR . trailingslashit('includes'));
    define('MTNC_LOAD', MTNC_DIR . trailingslashit('load'));
  }

  public function mtnc_set_global_options()
  {
    global $mt_options;
    $mt_options = mtnc_get_plugin_options(true);
  }

  public function mtnc_lang()
  {
    load_plugin_textdomain('maintenance');
  }

  public function mtnc_includes()
  {
    require_once MTNC_INCLUDES . 'functions.php';
    require_once MTNC_INCLUDES . 'update.php';
    require_once MTNC_DIR . 'load/functions.php';
  }

  public function mtnc_admin()
  {
    if (is_admin()) {
      require_once MTNC_INCLUDES . 'admin.php';
    }
  }

  public function mtnc_activation()
  {
    self::mtnc_clear_cache();
  }

  public function mtnc_deactivation()
  {
    self::mtnc_clear_cache();
  }

  public static function mtnc_clear_cache()
  {
    wp_cache_flush();
    if (function_exists('w3tc_pgcache_flush')) {
      w3tc_pgcache_flush();
    }
    if (function_exists('wp_cache_clear_cache')) {
      wp_cache_clear_cache();
    }
    if (method_exists('LiteSpeed_Cache_API', 'purge_all')) {
      LiteSpeed_Cache_API::purge_all();
    }
    if (class_exists('Endurance_Page_Cache')) {
      $epc = new Endurance_Page_Cache;
      $epc->purge_all();
    }
    if (class_exists('SG_CachePress_Supercacher') && method_exists('SG_CachePress_Supercacher', 'purge_cache')) {
      SG_CachePress_Supercacher::purge_cache(true);
    }
    if (class_exists('SiteGround_Optimizer\Supercacher\Supercacher')) {
      SiteGround_Optimizer\Supercacher\Supercacher::purge_cache();
    }
    if (isset($GLOBALS['wp_fastest_cache']) && method_exists($GLOBALS['wp_fastest_cache'], 'deleteCache')) {
      $GLOBALS['wp_fastest_cache']->deleteCache(true);
    }
    if (is_callable('wpfc_clear_all_cache')) {
      wpfc_clear_all_cache(true);
    }
    if (is_callable(array('Swift_Performance_Cache', 'clear_all_cache'))) {
      Swift_Performance_Cache::clear_all_cache();
    }
    if (is_callable(array('Hummingbird\WP_Hummingbird', 'flush_cache'))) {
      Hummingbird\WP_Hummingbird::flush_cache(true, false);
    }
    if (function_exists('rocket_clean_domain')) {
      rocket_clean_domain();
    }
  }

  public function mtnc_user_logout()
  {
    wp_safe_redirect(get_bloginfo('url'));
    exit;
  }

  public function disable_feed()
  {
    global $mt_options;

    if (!is_user_logged_in() && !empty($mt_options['state'])) {
      nocache_headers();
      echo '<?xml version="1.0" encoding="UTF-8" ?><status>Service unavailable.</status>';
      exit;
    }
  }

  public function mtnc_template_include($original_template)
  {
    $original_template = mtnc_load_maintenance_page($original_template);
    return $original_template;
  }

  public function mtnc_admin_bar()
  {
    add_action('admin_bar_menu', 'mtnc_add_toolbar_items', 100);
  }

  // helper function for adding plugins to fav list
  function featured_plugins_tab($args)
  {
    add_filter('plugins_api_result', array(&$this, 'plugins_api_result'), 10, 3);

    return $args;
  } // featured_plugins_tab

  // add single plugin to list of favs
  static function add_plugin_favs($plugin_slug, $res)
  {
    if (!isset($res->plugins) || !is_array($res->plugins)) {
      return $res;
    }

    if (!empty($res->plugins) && is_array($res->plugins)) {
      foreach ($res->plugins as $plugin) {
        if (is_object($plugin) && !empty($plugin->slug) && $plugin->slug == $plugin_slug) {
          return $res;
        }
      } // foreach
    }

    $plugin_info = get_transient('wf-plugin-info-' . $plugin_slug);
    if ($plugin_info && is_object($plugin_info)) {
      array_unshift($res->plugins, $plugin_info);
    } else {
      $plugin_info = plugins_api('plugin_information', array(
        'slug'   => $plugin_slug,
        'is_ssl' => is_ssl(),
        'fields' => array(
          'banners'           => true,
          'reviews'           => true,
          'downloaded'        => true,
          'active_installs'   => true,
          'icons'             => true,
          'short_description' => true,
        )
      ));
      if (!is_wp_error($plugin_info)) {
        array_unshift($res->plugins, $plugin_info);
        set_transient('wf-plugin-info-' . $plugin_slug, $plugin_info, DAY_IN_SECONDS * 7);
      }
    }

    return $res;
  } // add_plugin_favs

  // add our plugins to recommended list
  function plugins_api_result($res, $action, $args)
  {
    remove_filter('plugins_api_result', array(__CLASS__, 'plugins_api_result'), 10, 3);

    $res = $this->add_plugin_favs('simple-author-box', $res);
    $res = $this->add_plugin_favs('eps-301-redirects', $res);
    $res = $this->add_plugin_favs('wp-force-ssl', $res);
    $res = $this->add_plugin_favs('security-ninja', $res);

    return $res;
  } // plugins_api_result


  // auto download / install / activate Weglot plugin
  function install_weglot()
  {
    if (false === current_user_can('administrator')) {
      wp_die('Sorry, you have to be an admin to run this action.');
    }

    $plugin_slug = 'weglot/weglot.php';
    $plugin_zip = 'https://downloads.wordpress.org/plugin/weglot.latest-stable.zip';

    @include_once ABSPATH . 'wp-admin/includes/plugin.php';
    @include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    @include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
    @include_once ABSPATH . 'wp-admin/includes/file.php';
    @include_once ABSPATH . 'wp-admin/includes/misc.php';
    echo '<style>
		body{
			font-family: sans-serif;
			font-size: 14px;
			line-height: 1.5;
			color: #444;
		}
		</style>';

    echo '<div style="margin: 20px; color:#444;">';
    echo 'If things are not done in a minute <a target="_parent" href="' . admin_url('plugin-install.php?s=weglot&tab=search&type=term') . '">install the plugin manually via Plugins page</a><br><br>';
    echo 'Starting ...<br><br>';

    wp_cache_flush();
    $upgrader = new Plugin_Upgrader();
    echo 'Check if Weglot is already installed ... <br />';
    if ($this->is_plugin_installed($plugin_slug)) {
      echo 'Weglot is already installed! <br /><br />Making sure it\'s the latest version.<br />';
      $upgrader->upgrade($plugin_slug);
      $installed = true;
    } else {
      echo 'Installing Weglot.<br />';
      $installed = $upgrader->install($plugin_zip);
    }
    wp_cache_flush();

    if (!is_wp_error($installed) && $installed) {
      echo 'Activating Weglot.<br />';
      $activate = activate_plugin($plugin_slug);

      if (is_null($activate)) {
        echo 'Weglot Activated.<br />';

        echo '<script>setTimeout(function() { top.location = "admin.php?page=maintenance"; }, 1000);</script>';
        echo '<br>If you are not redirected in a few seconds - <a href="admin.php?page=maintenance" target="_parent">click here</a>.';
      }
    } else {
      echo 'Could not install Weglot. You\'ll have to <a target="_parent" href="' . admin_url('plugin-install.php?s=weglot&tab=search&type=term') . '">download and install manually</a>.';
    }

    echo '</div>';
  } // install_weglot



  function is_plugin_installed($slug)
  {
    if (!function_exists('get_plugins')) {
      require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $all_plugins = get_plugins();

    if (!empty($all_plugins[$slug])) {
      return true;
    } else {
      return false;
    }
  } // is_plugin_installed

} // class MTNC

$mtnc = new MTNC();
