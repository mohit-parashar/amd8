<?php

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

/**
 * Include the Pantheon-specific settings file.
 *
 * n.b. The settings.pantheon.php file makes some changes
 *      that affect all envrionments that this site
 *      exists in.  Always include this file, even in
 *      a local development environment, to ensure that
 *      the site settings remain consistent.
 */
include __DIR__ . "/settings.pantheon.php";

/**
 * Place the config directory outside of the Drupal root.
 */
$config_directories = array(
  CONFIG_SYNC_DIRECTORY => dirname(DRUPAL_ROOT) . '/config',
);

/**
 * If there is a local settings file, then include it
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include $local_settings;
}

/**
 * Always install the 'standard' profile to stop the installer from
 * modifying settings.php.
 */
$settings['install_profile'] = 'standard';
//$settings['update_free_access'] = TRUE;
//$config['system.logging']['error_level'] = 'verbose';

// Configure Redis
if (defined('PANTHEON_ENVIRONMENT')) {
  // Include the Redis services.yml file. Adjust the path if you installed to a contrib or other subdirectory.
  $settings['container_yamls'][] = 'modules/contrib/redis/example.services.yml';

  //phpredis is built into the Pantheon application container.
  $settings['redis.connection']['interface'] = 'PhpRedis';
  // These are dynamic variables handled by Pantheon.
  $settings['redis.connection']['host']      = $_ENV['CACHE_HOST'];
  $settings['redis.connection']['port']      = $_ENV['CACHE_PORT'];
  $settings['redis.connection']['password']  = $_ENV['CACHE_PASSWORD'];

  $settings['cache']['default'] = 'cache.backend.redis'; // Use Redis as the default cache.
  $settings['cache_prefix']['default'] = 'pantheon-redis'; 

  // Always set the fast backend for bootstrap, discover and config, otherwise this gets lost when redis is enabled.
  $settings['cache']['bins']['bootstrap'] = 'cache.backend.chainedfast';
  $settings['cache']['bins']['discovery'] = 'cache.backend.chainedfast';
  $settings['cache']['bins']['config']    = 'cache.backend.chainedfast';
}

if (isset($_ENV['PANTHEON_ENVIRONMENT']) && php_sapi_name() != 'cli') {
  // Redirect to https://$primary_domain in the Live environment
  if ($_ENV['PANTHEON_ENVIRONMENT'] === 'live') {
    /** Replace www.example.com with your registered domain name */
    $primary_domain = 'www.americamagazine.org';
  }
  else {
    // Redirect to HTTPS on every Pantheon environment.
    $primary_domain = $_SERVER['HTTP_HOST'];
  }

  if ($_SERVER['HTTP_HOST'] != $primary_domain
      || !isset($_SERVER['HTTP_USER_AGENT_HTTPS'])
      || $_SERVER['HTTP_USER_AGENT_HTTPS'] != 'ON' ) {

    # Name transaction "redirect" in New Relic for improved reporting (optional)
    if (extension_loaded('newrelic')) {
      newrelic_name_transaction("redirect");
    }

    header('HTTP/1.0 301 Moved Permanently');
    header('Location: https://'. $primary_domain . $_SERVER['REQUEST_URI']);
    exit();
  }
  // Drupal 8 Trusted Host Settings
  if (is_array($settings)) {
    $settings['trusted_host_patterns'] = array('^'. preg_quote($primary_domain) .'$');  
  }
}

if (!defined('PANTHEON_ENVIRONMENT')) {
   $config['environment_indicator.indicator']['name'] = 'Local';
   $config['environment_indicator.indicator']['bg_color'] = '#808080';
   $config['environment_indicator.indicator']['fg_color'] = '#ffffff';
}
// Pantheon Env Specific Config
if (isset($_ENV['PANTHEON_ENVIRONMENT'])) {
   switch ($_ENV['PANTHEON_ENVIRONMENT']) {
     case 'dev':
       $config['environment_indicator.indicator']['name'] = 'Dev';
       $config['environment_indicator.indicator']['bg_color'] = '#FFA500';
       $config['environment_indicator.indicator']['fg_color'] = '#ffffff';
       break;
     case 'test':
       $config['environment_indicator.indicator']['name'] = 'Test';
       $config['environment_indicator.indicator']['bg_color'] = '#FFFF00';
       $config['environment_indicator.indicator']['fg_color'] = '#ffffff';
       break;
     case 'live':
       $config['environment_indicator.indicator']['name'] = 'Live!';
       $config['environment_indicator.indicator']['bg_color'] = '#008000';
       $config['environment_indicator.indicator']['fg_color'] = '#ffffff';
       break;
     default:
       $config['environment_indicator.indicator']['name'] = 'Multidev';
       $config['environment_indicator.indicator']['bg_color'] = '#FF0000';
       $config['environment_indicator.indicator']['fg_color'] = '#ffffff';
       break;
   }
}
