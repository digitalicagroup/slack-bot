# Slack Bot

A simple bot for Slack. It can be used as a starting point to build your own Slack Bot. This project is also a testing facility for the [slack-hook-framework](https://github.com/digitalicagroup/slack-hook-framework).

Uses
* One Slack "Slash Commands" and one "Incoming WebHooks" integration (see Install).
* [slack-hook-framework](https://github.com/digitalicagroup/slack-hook-framework)
* [KLogger](https://github.com/katzgrau/KLogger)

How does it work?
* It installs as a PHP application on your web server.
* Through a "Slash Commands" Slack integration, it receives requests.
* It parses the text received, detects which command to use, and forward the parameters.
* Posts the results to an "Incoming WebHooks" Slack integration in the originator's channel or private group (The framework makes use of slack api to look up channel info).

## Current Features
* Commands supported:
 * All [slack-hook-framework](https://github.com/digitalicagroup/slack-hook-framework) commands.
 * daily: Pretty formatter of daily Scrum reports (Done/Doing/Blocks).
 * trade: Get trade classifications for Marc Miller's Traveller T4 RPG
* Custom commands can be added easily (see Adding more Commands).
* Custom reg-exp configuration for parameter parsing for each command.

## Requirements

* PHP >= 5.4 with cURL extension,
* Slack integrations (see install).
* [Composer](http://getcomposer.org/download/).

## Install

### On Slack

* Create a new "Slash Commands" integration with the following data:
 * Command: /bot (or whatever you like)
 * URL: the URL pointing to the index.php of your slack-bot install
 * Method: POST
 * Token: copy this token, we'll need it later.

* Create a new "Incoming WebHooks" slack integration:
 * Post to Channel: Pick one, but this will be ignored by slack-bot.
 * Webhook URL: copy this URL, we'll need it later.
 * Descriptive Label, Customize Name, Customize Icon: whatever you like.

* Go to [Slack API](https://api.slack.com/) > "Authentication" > "Tokens for Testing" and generate a test token for your team. The framework needs this because:
 * When a command is received from Slack (in a private group), the payload does not have the private group name.
 * It needs to make a request to the Slack API in order to search for the group name.
 * If the authentication token have the rights to access that group, the framework will be able to post to it.

### On your web server

Go to you public folder and clone this repository:
```bash
$ git clone https://github.com/digitalicagroup/slack-bot.git
$ cd slack-bot
```

Install [composer](http://getcomposer.org/download/) inside slack-bot dir and then run:
```bash
$ php composer.phar update
```

Edit index.php and add the following configuration parameters:
```php
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
```

Give permissions to your logs/ and db/ folder to your web server process. If you are using apache under linux, it is usually www-data:
```bash
$ sudo chown -R :www-data logs/
$ sudo chown -R :www-data db/
$ sudo chmod g+w logs/
$ sudo chmod g+w db/
```

## Troubleshooting

This is a list of common errors:
* "I see some errors about permissions in the apache error log".
 * The process running slack-bot (usually the web server) needs write permissions to the folder configured in you $config->log_dir parameter.
 * For example, if you are running apache, that folder group must be assigned to www-data and its write permission for groups must be turned on.
  * change to your slack-bot dir
  * chown -R :www-data logs/
  * chmod -R g+w logs/
* "I followed the steps and nothing happens, nothing in web server error log and nothing in the app log".
 * If you see nothing in the logs (and have the debug level setted), may be the app is dying in the process of validating the slack token. slack-bot validates that the request matches with the configured token or the app dies at the very beginning.
* "There is no error in the web server error log, I see some output in the app log (with the debug log level), but i get nothing in my channel/group".
 * Check in the app log for the strings "[DEBUG] Util: group found!" or "[DEBUG] Util: channel found!" . If you can't see those strings, check if your slack authentication token for your team is from an user that have access to the private group you are writing from. 
* I just developed a new command but I am getting a class not found error on CommandFactory.
 * Every time you add a new command (hence a new class), you must update the composer autoloader. just type:
 * `php composer.phar update` 
* If you have any bug or error to report, feel free to contact me:  luis at digitalicagroup dot com

## Adding more Commands.

If You wish to add more commands, you can do so with the following (basic) steps:
Inside slack-bot install dir, go to the Bot folder:
```bash
$ cd lib/Bot/
```

Create a new php file for your new command, for example CmdPing.php:
```php
<?php

namespace Bot;

use SlackHookFramework\AbstractCommand;
use SlackHookFramework\SlackResult;
use SlackHookFramework\SlackResultAttachment;
use SlackHookFramework\SlackResultAttachmentField;

class CmdPing extends AbstractCommand {
	protected function executeImpl($params) {
		$this->setResultText ( "PONG!" );
	}
}
```

Now go to slack-bot's install folder, edit the custom_cmds.json file and add a command definition for CmdPing:
```json
{
	"commands": [
		{
			"trigger": "ping",
			"class": "Bot\\CmdPing",
			"help_title": "ping",
			"help_text": "example command."
		},
     . . .
	]
}
```

Go to the slack-bot install dir and run:
```bash
$ php composer.phar update
```

Check the contents of `vendor/digitalicagroup/slack-hook-framework/lib/SlackHookFramework/CmdHello.php` from [slack-hook-framework](https://github.com/digitalicagroup/slack-hook-framework) for detailed return options.

## Feedback

Feel free to reach us at: contact at digitalicagroup dot com .

## About Digitalica

We are a small firm focusing on mobile apps development (iOS, Android) and we are passionate about new technologies and ways that helps us work better. This project is an extension of our work to test and play with new things.
* This project homepage: [slack-bot](https://github.com/digitalicagroup/slack-hook)
* Digitalica homepage: [digitalicagroup.com](http://digitalicagroup.com)
* Our Engineering Team Blog: [blog.digitalicagroup.com](http://blog.digitalicagroup.com)