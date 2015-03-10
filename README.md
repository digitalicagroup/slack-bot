# Slack Bot

A simple testing bot for slack. We are currently testing a parsing facility / framework that could be extended to make a general utility bot for slack

Uses
* One Slack "Slash Commands" and one "Incoming WebHooks" integration (see Install).
* [KLogger](https://github.com/katzgrau/KLogger)

How does it work?
* It installs as a PHP application on your web server (using composer).
* Through a "Slash Commands" Slack integration, it receives requests.
* Posts the results to an "Incoming WebHooks" Slack integration in the originator's channel or private group (yeah, private group!).

## Current Features

## TODO

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

Install [composer](http://getcomposer.org/download/) in a folder of your preference (should be accessible from your web server) then run:
```bash
$ php composer.phar require digitalicagroup/slack-bot:~0.1
$ cp vendor/digitalicagroup/slack-bot/index.php .
```
The last line copies index.php from the package with the configuration you need to modify.

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

Make sure you give write permissions to the log_dir folder.

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

## Contribute

## About Digitalica

We are a small firm focusing on mobile apps development (iOS, Android) and we are passionate about new technologies and ways that helps us work better.
* This project homepage: [slack-bot](https://github.com/digitalicagroup/slack-bot)
* Digitalica homepage: [digitalicagroup.com](http://digitalicagroup.com)
