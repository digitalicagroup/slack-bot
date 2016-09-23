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
	 *
	 * @see \SlackHookFramework\AbstractCommand::executeImpl()
	 * @return \SlackHookFramework\SlackResult
	 */
	protected function executeImpl() {
		$log = $this->log;
		$log->debug ( "CmdDailyScrumReport: Parameters received: " . implode ( ",", $this->cmd ) );
		
		$resultText = "*" . $this->post ["user_name"] . "* daily summary for " . date ( 'l jS \of F' );
		if (empty ( $this->cmd ) || count ( $this->cmd ) < 2) {
			$resultText = "*" . $this->post ["user_name"] . "*: Try this: /<command> daily <what have i done>;<what i will be doing>[;<my current blocks>]";
		} else {
			$attachment = $this->createSlackResultAttachment ();
			$fields = array ();
			$fields [] = SlackResultAttachmentField::withAttributes ( "*Done*", $this->cmd [0], FALSE );
			$fields [] = SlackResultAttachmentField::withAttributes ( "*Doing*", $this->cmd [1], FALSE );
			$fields [] = SlackResultAttachmentField::withAttributes ( "*Block*", isset ( $this->cmd [2] ) ? $this->cmd [2] : "None", FALSE );
			$attachment->setFieldsArray ( $fields );
			
			$this->addSlackResultAttachment ( $attachment );
		}
		
		$this->setResultText ( $resultText );
	}
}
