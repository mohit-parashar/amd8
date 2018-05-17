<?php

/**
 * @file
 * Contains \Drupal\gdocs_publish\Logger class
 *
 * A slightly more sophisticated logger intended for debug or verbose messages.
 * Use the normal Drupal logger for messages that always need to be registered.
 *
 * This function uses two configuration settings:
 *
 * logging - Determines whether to log at all. This allows us to leave debugging
 *           messages in the code but have them do nothing most of the time.
 *
 * display_errors - This determines how the errors are displayed. If it's set to
 *                  anything falsey, the errors will not be displayed at all, just
 *                  logged to the DB. If it's set to 'direct' then they'll be printed
 *                  straight to the screen when they occur. Any other truthy value
 *                  will display them via drupal_set_message.
 *
 * Note that for safety's sake, errors are not displayed no matter what if error
 * display is turned off in the INI settings.
 */

namespace Drupal\gdocs_publish\Logger;

use Drupal\Core\Logger\RfcLoggerTrait;
use Psr\Log\LoggerInterface;

class GDPLogger implements LoggerInterface {
  use RfcLoggerTrait;

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = array()) {
    $config = \Drupal::config('gdocs_publish.config');

    $message_text = empty($context) ? $message : t($message, $context);
    if (ini_get('display_errors') && $config->get('gdocs_publish.logging')) {
      if ($config->get('gdocs_publish.display_errors') == 'direct') {
        print "<pre>$message_text\n</pre>";
      }
      else {
        drupal_set_message ($message_text, $level);
      }
      switch ($level) {
        case 'status':
        case 'info':
          \Drupal::logger('gdocs_publish')->info($message, $context);
          break;
        case 'notice':
        case 'warning':
          \Drupal::logger('gdocs_publish')->warning($message, $context);
          break;
        case 'error':
          \Drupal::logger('gdocs_publish')->error($message, $context);
          break;
      }
    }
  }
}
