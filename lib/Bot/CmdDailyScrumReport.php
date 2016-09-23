<?php

namespace Bot;

use SlackHookFramework\AbstractCommand;
use SlackHookFramework\SlackResult;
use SlackHookFramework\SlackResultAttachment;

/**
 * Class to format a Daily Scrum Report.
 * Usage: /bot daily <done>;<doing>;<block>
 *        /bot daily <done>;<doing>
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
		$log->debug ( "CmdDailyScrumReport: Parameters received: " . implode ( ",", $this->cmd ) );
		
		/**
		 * Preparing the result text and validating parameters.
		 */
		$resultText = $this->post ["user_name"] . " daily summary for ".date('l jS \of F Y h:i:s A')."]";
		if (empty ( $this->cmd )) {
			$resultText .= " Usage: /<command> daily <what have i done>;<what i will be doing>[;<my current blocks>]";
		}
		
		/**
		 * Preparing attachments.
		 */
		$attachments = array ();
		
		/**
		 * Cycling through parameters, just for fun.
		 */
		foreach ( $this->cmd as $param ) {
			$log->debug ( "CmdDailyScrumReport: processing parameter $param" );
			
			/**
			 * Preparing one result attachment for processing this parameter.
			 */
			$attachment = new SlackResultAttachment ();
			$attachment->setTitle ( "Processing $param" );
			$attachment->setText ( "Hello $param !!" );
			$attachment->setFallback ( "fallback text." );
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
