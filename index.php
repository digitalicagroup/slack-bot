<?php
require_once 'vendor/autoload.php';

use Psr\Log\LogLevel;
use SlackHookConfiguration\Configuration;

/**
 * This is the entry point for your application and slack integration.
 * It handles the configuration parameters, invokes the command
 * factory parsing and execution of commands.
 * This file should be placed at the same level of your "vendor" folder.
 */

$config = new SlackHookFramework\Configuration ();

/**
 * token sent by slack (from your "Slash Commands" integration).
 * It is used by the validator to skip command processing if the request
 * is from an unauthorized slack domain.
 */
$config->token = "vuLKJlkjdsflkjLKJLKJlkjd";

/**
 * URL of the Incoming WebHook slack integration.
 * Command processing results will be pushed to this URL.
 */
$config->slack_webhook_url = "https://hooks.slack.com/services/LKJDFKLJFD/DFDFSFDDSFDS/sdlfkjdlkfjLKJLKJKLJO";

/**
 * Slack API authentication testing token for your team.
 * We have not implemented an "Add to Slack" button yet, so a testing token
 * must be used in the meantime.
 * See README.md for instructions on how to get a testing token from slack.
 */
$config->slack_api_token = "xoxp-98475983759834-38475984579843-34985793845";

/**
 * Log level threshold.
 * The default is DEBUG.
 * 
 * Available levels:
 * LogLevel::EMERGENCY;
 * LogLevel::ALERT;
 * LogLevel::CRITICAL;
 * LogLevel::ERROR;
 * LogLevel::WARNING;
 * LogLevel::NOTICE;
 * LogLevel::INFO;
 * LogLevel::DEBUG;
 */
$config->log_level = LogLevel::DEBUG;

/**
 * logs folder, make sure the invoker(*) have write permission.
 */
$config->log_dir = __DIR__."/logs";

/**
 * Database folder, used by some commands to store user related temporal information.
 * Make sure the invoker(*) have write permission.
 */
$config->db_dir = __DIR__."/db";

/**
 * Custom commands definition. Use this file if you wish to add new commands to be
 * recognized by the framework.
 */
$config->custom_cmds = __DIR__."/custom_cmds.json";

/**
 * This is to prevent the entry point to be called outside slack.
 * It will validate the slack token in the request.
 */
if (! SlackHookFramework\Validator::validate ( $_POST, $config )) {
	die ();
}

/**
 * Entry point execution.
 */
$command = SlackHookFramework\CommandFactory::create ( $_POST, $config );
$command->execute ();
$command->post ();
