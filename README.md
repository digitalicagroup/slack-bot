# Slack Bot

A simple bot for Slack. It an be use as a starting point to build your own Slack Bot. This project is also a testing facility for the [slack-hook-framework](https://github.com/digitalicagroup/slack-hook-framework).

Uses
* One Slack "Slash Commands" and one "Incoming WebHooks" integration (see Install).
* [slack-hook-framework](https://github.com/digitalicagroup/slack-hook-framework)
* [KLogger](https://github.com/katzgrau/KLogger)

How does it work?
* It installs as a PHP application on your web server (using composer).
* Through a "Slash Commands" Slack integration, it receives requests.
* Posts the results to an "Incoming WebHooks" Slack integration in the originator's channel or private group (yeah, private group!).

## Current Features
* Commands supported:
 * All slack-hook-framework commands.
 * trade: Get trade classifications for Marc Miller's Traveller T4 RPG

## Requirements

* PHP >= 5.4 with cURL extension,
* Slack integrations (see install).

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

* Go to [Slack API](https://api.slack.com/) and copy the authentication token for your team.

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
 */
$config->token =              "vuLKJlkjdsflkjLKJLKJlkjd";

/**
 * URL of the Incoming WebHook slack integration.
 */ 
$config->slack_webhook_url =  "https://hooks.slack.com/services/LKJDFKLJFD/DFDFSFDDSFDS/sdlfkjdlkfjLKJLKJKLJO";

/**
 * Slack API authentication token for your team.
 */
$config->slack_api_token =    "xoxp-98475983759834-38475984579843-34985793845";

/**
 * Log level threshold. The default is DEBUG.
 * If you are done testing or installing in production environment,
 * uncomment this line.
 */
//$config->log_level =           LogLevel::WARNING;

/**
 * logs folder, make sure the invoker have write permission.
 */
$config->log_dir =            "/srv/api/slack-bot/logs";
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
 * php composer.phar update  
* If you have any bug or error to report, feel free to contact me:  luis at digitalicagroup.com

## Adding more Commands.

If You wish to add more commands, you can do so with the following (basic) steps:
Inside slack-bot install dir, go to the lib dir:
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
	
	/**
	 * Factory method to be implemented from \SlackHookFramework\AbstractCommand .
	 * Must return an instance of \SlackHookFramework\SlackResult .
	 *
	 * Basically, the method returns an instance of SlackResult.
	 * Inside a single instance of SlackResult, several
	 * SlackResultAttachment instances can be stored.
	 * Inside a SlackResultAttachment instance, several
	 * SlackResultAttachmentField instances can be stored.
	 * The result is then formating according to the Slack
	 * formating guide.
	 *
	 * So you must process your command here, and then
	 * prepare your SlackResult instance.
	 */
	protected function executeImpl() {
		/**
		 * Get a reference to the log.
		 */
		$log = $this->log;
		
		/**
		 * Create a new instance to store results.
		 */
		$result = new SlackResult ();
		
		/**
		 * Output some debug info to log file.
		 */
		$log->debug ( "CmdPing: Parameters received: " . implode ( ",", $this->cmd ) );
		
		/**
		 * Preparing the result text and validating parameters.
		 */
		$resultText = "[requested by " . $this->post ["user_name"] . "]";
		if (empty ( $this->cmd )) {
			$resultText .= " You must specify at least one parameter!";
		} else {
			$resultText .= " CmdPing Result: ";
		}
		
		/**
		 * Preparing attachments.
		 */
		$attachments = array ();
		
		/**
		 * Cycling through parameters, just for fun.
		 */
		foreach ( $this->cmd as $param ) {
			$log->debug ( "CmdPing: processing parameter $param" );
			
			/**
			 * Preparing one result attachment for processing this parameter.
			 */
			$attachment = new SlackResultAttachment ();
			$attachment->setTitle ( "Processing $param" );
			$attachment->setText ( "Ping $param !!" );
			$attachment->setFallback ( "fallback text." );
			/**
			 * Optional pretext
			 */
			$attachment->setPretext ( "pretext here." );
			
			/**
			 * Adding some fields to the attachment.
			 */
			$fields = array ();
			$fields [] = SlackResultAttachmentField::withAttributes ( "Field 1", "Value" );
			$fields [] = SlackResultAttachmentField::withAttributes ( "Field 2", "Value" );
			$fields [] = SlackResultAttachmentField::withAttributes ( "This is a long field", "this is a long Value", FALSE );
			$attachment->setFieldsArray ( $fields );
			
			/**
			 * Adding the attachment to the attachments array.
			 */
			$attachments [] = $attachment;
		}
		
		$result->setText ( $resultText );
		$result->setAttachmentsArray ( $attachments );
		return $result;
	}
}
```

Now go to the folder vendor/digitalicagroup/slack-hook-framework/lib/SlackHookFramework and edit the commands_definition.json file and add a command definition for CmdPing:
```json
{
	"commands": [
		{
			"trigger": "ping",
			"class": "Bot\\CmdPing",
			"help_title": "ping <1 2 ...>",
			"help_text": "example command."
		},
		{
 			"trigger": "hello",
 			"class": "SlackHookFramework\\CmdHello",
			"help_title": "hello",
			"help_text": "Shows how to use the slack-hook-framework"
		},
		{
			"trigger": "help",
			"class": "SlackHookFramework\\CmdHelp",
			"help_title": "help",
			"help_text": "Shows this help."
		}
	]
}
```

Go to the slack-bot install dir and run:
```bash
$ php composer.phar update
```

## About Digitalica

We are a small firm focusing on mobile apps development (iOS, Android) and we are passionate about new technologies and ways that helps us work better.
* This project homepage: [slack-bot](https://github.com/digitalicagroup/slack-bot)
* Digitalica homepage: [digitalicagroup.com](http://digitalicagroup.com)
