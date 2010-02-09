<?php

/**
 * Message
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
class Bal_Model_Message extends Base_Message
{
	
	/**
	 * Apply accessors and modifiers
	 * @return
	 */
	public function setUp ( ) {
		$this->hasMutator('template', 'setTemplate');
		parent::setUp();
	}
	
	
	/**
	 * Alias for Template
	 */
	public function setTemplate ( $code, $load = true ) {
		return $this->useTemplate($code);
	}
	
	/**
	 * Shortcut Message Creation via Codes
	 * @return string
	 */
	public function useTemplate ( $code, array $data = array() ) {
		# Prepare
		$Locale = Bal_App::getLocale();
		$View = Bal_App::getView(false);
		$Message = $this;
		
		# Prepare Urls
		$rootUrl = $View->app()->getRootUrl();
		$baseUrl = $View->app()->getBaseUrl(true);
		
		# Prepare Params
		$params = is_array($data) ? $data : array();
		$params['Message'] = $Message->toArray();
		$params['sender'] = delve($Message,'Sender.fullname','System');
		
		# Apply URLs
		$messageUrl = $rootUrl.$View->app()->getMessageUrl($Message);
		$params['rootUrl'] = $rootUrl;
		$params['baseUrl'] = $baseUrl;
		$params['messageUrl'] = $messageUrl;
		
		# Handle
		$function = '_template'.magic_function($code);
		if ( method_exists($this, $function) ) {
			$this->$function($params,$data);
		}
		
		# Render
		$title = $Locale->translate('message-'.$code.'-title', $params);
		$description = $Locale->translate('message-'.$code.'-description', $params);
		
		# Apply
		$this->title = $title;
		$this->description = $description;
		
		# Chain
		return $this;
	}
	
	/**
	 * Set Message Code: user-insert
	 * @return string
	 */
	protected function _templateUserInsert ( &$params, &$data ) {
		# Prepare
		$Locale = Bal_App::getLocale();
		$View = Bal_App::getView(false);
		$Message = $this;
		
		# Prepare Urls
		$rootUrl = $View->app()->getRootUrl();
		$baseUrl = $View->app()->getBaseUrl(true);
		
		# --------------------------
		
		# Prepare
		$Receiver = $this->Receiver;
		
		# Prepare URL
		$activateUrl = $rootUrl.$View->app()->getActivateUrl($Receiver);
		$params['activateUrl'] = $activateUrl;

		# --------------------------
		
		return true;
	}
	
	/**
	 * Send the Message
	 * @return string
	 */
	public function send ( ) {
		# Prepare
		$Receiver = $this->Receiver;
		$View = Bal_App::getView(true);
		$mail = Bal_App::getConfig('mail');
		
		# Apply
		$View->Message = $this;
		
		# Prepare Mail
		$mail['subject'] = $this->title;
		$mail['html'] = $View->render('email/message.phtml');
		$mail['text'] = strip_tags($mail['html']);
		
		# Create Mail
		$Mail = new Zend_Mail();
		$Mail->setFrom($mail['from']['address'], $mail['from']['name']);
		$Mail->setSubject($mail['subject']);
		$Mail->setBodyText($mail['text']);
		$Mail->setBodyHtml($mail['html']);
		
		# Add Receipient
		$email = $Receiver->email;
		$fullname = $Receiver->fullname;
		$Mail->addTo($email, $fullname);
		
		# Send Mail
		$Mail->send();
		
		# Done
		$this->sent_on = doctrine_timestamp();
		
		# Chain
		return $this;
	}
	
	
	/**
	 * Ensure Message
	 * @param Doctrine_Event $Event
	 * @return boolean	wheter or not to save
	 */
	public function ensureMessage($Event){
		# Prepare
		$Message = $Event->getInvoker();
		$save = false;
		
		# Send On
		if ( !$Message->send_on ) {
			$Message->send_on = doctrine_timestamp();
			$save = true;
		}
		
		# Hash
		$hash = md5($Message->send_on.$Message->title.$Message->description.$Message->Receiver->id);
		if ( $Message->hash != $hash ) {
			$Message->hash = $hash;
		}
		
		# Send
		if ( $Message->id && empty($Message->sent_on) && strtotime($Message->send_on) <= time() ) {
			# We want to send now or earlier
			$Message->send();
			$save = true;
		} else {
			# We want to send later
			// do nothing
		}
		
		# Done
		return $save;
	}
	
	/**
	 * Ensure Consistency
	 * @param Doctrine_Event $Event
	 * @return boolean	wheter or not to save
	 */
	public function ensure($Event){
		$ensure = array(
			$this->ensureMessage($Event)
		);
		return in_array(true,$ensure);
	}
	
	/**
	 * preSave Event
	 * @param Doctrine_Event $Event
	 * @return
	 */
	public function preSave ( $Event ) {
		# Prepare
		$result = true;
		
		# Ensure
		if ( self::ensure($Event) ) {
			// will save naturally
		}
		
		# Done
		return method_exists(get_parent_class($this),$parent_method = __FUNCTION__) ? parent::$parent_method($Event) : $result;
	}
	
	/**
	 * postSave Event
	 * @param Doctrine_Event $Event
	 * @return
	 */
	public function postSave ( $Event ) {
		# Prepare
		$result = true;
		
		# Ensure
		if ( self::ensure($Event) ) {
			$this->save();
		}
		
		# Done
		return method_exists(get_parent_class($this),$parent_method = __FUNCTION__) ? parent::$parent_method($Event) : $result;
	}
	
	/**
	 * Pre Insert Event
	 * @param Doctrine_Event $Event
	 */
	public function preInsert ( $Event ) {
		# Prepare
		$Message = $Event->getInvoker();
		$result = true;
		
		# Ensure Only One
		Doctrine_Query::create()
			->delete('Message m')
			->where('m.hash = ?', $Message->hash)
			;
		
		# Done
		return method_exists(get_parent_class($this),$parent_method = __FUNCTION__) ? parent::$parent_method($Event) : $result;
	}
	
}