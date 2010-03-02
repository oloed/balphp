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
class Bal_Message extends Base_Bal_Message
{
	
	/**
	 * Shortcut Message Creation via Codes
	 * @return string
	 */
	public function useTemplate ( $template, array $data = array() ) {
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
		$params['by'] = delve($Message,'By.fullname','System');
		$params['for'] = delve($Message,'For.fullname','System');
		
		# Apply URLs
		$messageUrl = $rootUrl.$View->url()->message($Message)->toString();
		$params['rootUrl'] = $rootUrl;
		$params['baseUrl'] = $baseUrl;
		$params['Message_url'] = $messageUrl;
		
		# Handle
		$function = '_template'.magic_function($template);
		if ( method_exists($this, $function) ) {
			$this->$function($params,$data);
		}
		
		# Render
		$title = empty($this->title) ? $Locale->translate('message-'.$template.'-title', $params) : $Locale->translate_default('message-'.$template.'-title', $params, $this->title);
		$description = empty($this->description) ? $Locale->translate('message-'.$template.'-description', $params) : $Locale->translate_default('message-'.$template.'-description', $params, $this->description);
		
		# Apply
		$this->title = $title;
		$this->description = $description;
		$this->_set('template', $template, false);
		
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
		$For = delve($Message,'For');
		
		# Prepare Urls
		$rootUrl = $View->app()->getRootUrl();
		$baseUrl = $View->app()->getBaseUrl(true);
		
		# --------------------------
		
		# Prepare URL
		$activateUrl = $rootUrl.$View->url()->userActivate($For)->toString();
		$params['User_url_activate'] = $activateUrl;

		# --------------------------
		
		return true;
	}
	
	/**
	 * Send the Message
	 * @return string
	 */
	public function send ( ) {
		# Prepare
		$Message = $this;
		$For = delve($Message,'For');
		$View = Bal_App::getView(true);
		$mail = Bal_App::getConfig('mail');
		
		# Apply
		$View->Message = $Message;
		
		# Prepare Mail
		$mail['subject'] = $Message->title;
		$mail['html'] = $View->render('email/message.phtml');
		$mail['text'] = strip_tags($mail['html']);
		
		# Create Mail
		$Mail = new Zend_Mail();
		$Mail->setFrom($mail['from']['address'], $mail['from']['name']);
		$Mail->setSubject($mail['subject']);
		$Mail->setBodyText($mail['text']);
		$Mail->setBodyHtml($mail['html']);
		
		# Add Receipient
		if ( delve($For,'id') ) {
			$email = $For->email;
			$fullname = $For->fullname;
		} else {
			$email = $mail['from']['address'];
			$fullname = $mail['from']['name'];
		}
		$Mail->addTo($email, $fullname);
		
		# Send Mail
		$Mail->send();
		
		# Done
		$Message->sent_on = doctrine_timestamp();
		$Message->status = 'published';
		
		# Chain
		return $this;
	}
	
	
	/**
	 * Ensure Message
	 * @param Doctrine_Event $Event
	 * @return boolean	wheter or not to save
	 */
	public function ensureMessage (  $Event, $Event_type ) {
		# Check
		if ( !in_array($Event_type,array('postSave','preInsert')) ) {
			# Not designed for these events
			return null;
		}
		
		# Prepare
		$save = false;
		
		# Fetch
		$Message = $Event->getInvoker();
		
		# preInsert
		if ( $Event_type === 'preInsert' ) {
			# Ensure Only One
			Doctrine_Query::create()
				->delete('Message m')
				->where('m.hash = ?', $Message->hash)
				->execute();
				;
			
			# Send On
			if ( !$Message->send_on ) {
				$Message->set('send_on', doctrine_timestamp(), false);
				$save = true;
			}
			
			# Prepare
			$For = delve($Message,'For');
			$For_id = delve($For,'id');
			
			# Hash
			$hash = md5($Message->send_on.$Message->title.$Message->description.$For_id);
			if ( $Message->hash != $hash ) {
				$Message->set('hash', $hash, false);
				$save = true;
			}
		}
		elseif ( $Event_type === 'postSave' ) {
			# Send
			if ( $Message->id && empty($Message->sent_on) && strtotime($Message->send_on) <= time() ) {
				# We want to send now or earlier
				$Message->send();
				$save = true;
			} else {
				# We want to send later
				// do nothing
			}
		}
		
		# Done
		return $save;
	}
	
	/**
	 * Ensure Consistency
	 * @param Doctrine_Event $Event
	 * @return boolean	wheter or not to save
	 */
	public function ensure ( $Event, $Event_type ){
		return Bal_Doctrine_Core::ensure($Event,$Event_type,array(
			'ensureMessage'
		));
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
		if ( self::ensure($Event, __FUNCTION__) ) {
			// no need
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
		$Invoker = $Event->getInvoker();
		$result = true;
		
		# Ensure
		if ( self::ensure($Event, __FUNCTION__) ) {
			$Invoker->save();
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
		
		# Ensure
		if ( self::ensure($Event, __FUNCTION__) ) {
			// no need
		}
		
		# Done
		return method_exists(get_parent_class($this),$parent_method = __FUNCTION__) ? parent::$parent_method($Event) : $result;
	}
	
}
