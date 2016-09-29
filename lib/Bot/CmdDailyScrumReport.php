<?php

namespace Bot;

use SlackHookFramework\AbstractCommand;
use SlackHookFramework\SlackResult;
use SlackHookFramework\SlackResultAttachment;
use SlackHookFramework\SlackResultAttachmentField;

/**
 * Class to format a Daily Scrum Report.
 * Usage: /bot daily <done>;<doing>;<block>
 * /bot daily <done>;<doing>
 *
 * @author Luis Augusto Peña Pereira <luis at digitalicagroup dot com>
 *        
 */
class CmdDailyScrumReport extends AbstractCommand {
	
	/**
	 * Factory method to be implemented from \SlackHookFramework\AbstractCommand .
	 * Must return an instance of \SlackHookFramework\SlackResult .
	 *
	 * The method should update the protected property instance
	 * of SlackResult.
	 * Inside a single instance of SlackResult, several
	 * SlackResultAttachment instances can be stored.
	 * Inside a SlackResultAttachment instance, several
	 * SlackResultAttachmentField instances can be stored.
	 * The result is then formating according to the Slack
	 * formating guide.
	 *
	 * So you must process your command here, and then
	 * prepare your SlackResult instance and attachments with fields.
	 *
	 * @see \SlackHookFramework\AbstractCommand::executeImpl()
	 * @return \SlackHookFramework\SlackResult
	 */
	protected function executeImpl($params) {
		$log = $this->log;
		$log->debug ( "CmdDailyScrumReport: Parameters received: " . implode ( ",", $params ) );
		$attachments = array ();
		$resultText = "*" . ucfirst ( $this->post ["user_name"] ) . "* daily summary for " . date ( 'l jS \of F' );
		if (empty ( $params ) || count ( $params ) < 2) {
			$resultText = "*" . $this->post ["user_name"] . "*: Try this: /<command> daily <what have i done>;<what i will be doing>[;<my current blocks>]";
		} else {
			$done = $this->createSlackResultAttachment ();
			$done->setColor ( "good" );
			$done->setTitle ( "Done" );
			$done->setFieldsArray ( $this->createFieldsArrayFromText ( "/[.]+/", $params [0] ) );
			$attachments [] = $done;
			
			$doing = $this->createSlackResultAttachment ();
			$doing->setColor ( "warning" );
			$doing->setTitle ( "Doing" );
			$doing->setFieldsArray ( $this->createFieldsArrayFromText ( "/[.]+/", $params [1] ) );
			$attachments [] = $doing;
			
			$block = $this->createSlackResultAttachment ();
			$block->setColor ( "danger" );
			$block->setTitle ( "Blocks" );
			if (isset ( $params [2] )) {
				$block->setFieldsArray ( $this->createFieldsArrayFromText ( "/[.]+/", $params [2] ) );
			} else {
				$block->setFieldsArray ( array (
						SlackResultAttachmentField::withAttributes ( "", "None", FALSE ) 
				) );
			}
			$attachments [] = $block;
		}
		
		$this->setSlackResultAttachments ( $attachments );
		$this->setResultText ( $resultText );
	}
	protected function createFieldsArrayFromText($regexp, $text) {
		$fields = array ();
		$list = preg_split ( $regexp, $text );
		foreach ( $list as $item ) {
			$fields [] = SlackResultAttachmentField::withAttributes ( "", $item, FALSE );
		}
		return $fields;
	}
}
