<?php
require_once 'vendor/autoload.php';

use Psr\Log\LogLevel;
use SlackHookConfiguration\Configuration;

/**
 * This is the entry point for your redmine-command slack integration.
 * It handles the configuration parameters and invokes the command
 * factory parsing and execution of commands.
 * This file should be placed at the same level of your "vendor" folder.
 */

$config = new SlackHookFramework\Configuration ();

/**
 * token sent by slack (from your "Slash Commands" integration).
 */
$config->token = "vuLKJlkjdsflkjLKJLKJlkjd";

/**
 * URL of the Incoming WebHook slack integration.
 */
$config->slack_webhook_url = "https://hooks.slack.com/services/LKJDFKLJFD/DFDFSFDDSFDS/sdlfkjdlkfjLKJLKJKLJO";

/**
 * Slack API authentication token for your team.
 */
$config->slack_api_token = "xoxp-98475983759834-38475984579843-34985793845";

/**
 * Log level threshold.
 * The default is DEBUG.
 * If you are done testing or installing in production environment,
 * uncomment this line.
 */
// $config->log_level = LogLevel::WARNING;

/**
 * logs folder, make sure the invoker have write permission.
 */
$config->log_dir = "/srv/api/slack-bot/logs";

/**
 * Database folder, used by some commands to store user related temporal information.
 * Make sure the invoker have write permission.
 */
$config->db_dir = "/srv/api/slack-bot/db";

/**
 * Database folder, used by some commands to store user related temporal information.
 * Make sure the invoker(*) have write permission.
 */
$config->db_dir = "/srv/api/slack-bot/db";

/**
 * Custom commands definition. Use this file if you wish to add new commands to be
 * recognized by the framework.
 */
$config->custom_cmds = "/srv/api/slack-bot/custom_cmds.json";

/**
 * This is to prevent redmine-command entry point to be called outside slack.
 * If you want it to be called from anywhere, comment the following 3 lines:
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
