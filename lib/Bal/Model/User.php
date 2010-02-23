<?php

/**
 * User
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6508 2009-10-14 06:28:49Z jwage $
 */
class Bal_Model_User extends Base_BalUser {

	/**
	 * Apply accessors and modifiers
	 * @return
	 */
	public function setUp ( ) {
		$this->hasMutator('Avatar', 'setAvatar');
		$this->hasMutator('password', 'setPassword');
		return parent::setUp();
	}
	
	/**
	 * Set the User's Avatar
	 * @return string
	 */
	protected function setMediaAttachment ( $what, $media ) {
		# Prepare
		$Media = Media::fetch($media);
		
		# Apply Media
		if ( $Media === false || $Media ) {
			if ( isset($this->$what) ) {
				$this->$what->delete();
			}
			$this->_set($what, $Media ? $Media : null, false);
		}
		
		# Done
		return true;
	}
	
	/**
	 * Set the User's Avatar
	 * @return string
	 */
	public function setAvatar ( $value ) {
		return $this->setMediaAttachment('Avatar', $value);
	}
	
	/**
	 * Prepare a User's Password
	 * @return
	 */
	public function preparePassword ( $value ) {
		$password = md5($value);
		return $password;
	}
	
	/**
	 * Reset the User's Password
	 * @return
	 */
	public function resetPassword ( ) {
		# Reset Password
		$password = generate_password();
		$this->password = $password;
		
		# Create Welcome Message
		$Message = new Message();
		$Message->For = $this;
		$Message->useTemplate('user-password-reset',compact('password'));
		$Message->save();
		
		# Chain
		return $this;
	}
	
	/**
	 * Set the User's Password
	 * @return
	 */
	public function setPassword ( $value ) {
		$password = $this->preparePassword($value);
		return $this->_set('password',$password);
	}
	
	/**
	 * Compare the User's Credentials with passed
	 * @return boolean
	 */
	public function compareCredentials ( $username, $password ) {
		return $this->username === $username && ($this->password === $password || $this->password === $this->preparePassword($password));
	}
	
	/**
	 * Set the Role(s) for a User (clear others)
	 * @param mixed $role
	 */
	public function setRole ( $role ) {
		$this->unlink('Roles');
		$this->link('Roles', $role);
		return true;
	}

	/**
	 * Add a Role(s) to the User
	 * @param mixed $role
	 */
	public function addRole ( $role ) {
		$this->link('Roles', $role);
		return true;
	}

	/**
	 * Does user have Role?
	 * @param mixed $permission
	 */
	public function hasRole ( $role ) {
		// Prepare
		if ( is_object($role) ) {
			$role = $role->code;
		} elseif ( is_array($role) ) {
			$role = $role['code'];
		}
		// Search
		$List = $this->Roles;
		foreach ( $List as $Role ) {
			if ( $role === $Role->code ) {
				$result = true;
				break;
			}
		}
		// Done
		return $result;
	}
	
	/**
	 * Does user have Permission?
	 * @param mixed $permission
	 */
	public function hasPermission ( $permission ) {
		// Prepare
		if ( is_object($permission) ) {
			$permission = $permission->code;
		} elseif ( is_array($permission) ) {
			$permission = $permission['code'];
		}
		// Search
		$List = $this->Permissions;
		foreach ( $List as $Permission ) {
			if ( $permission === $Permission->code ) {
				$result = true;
				break;
			}
		}
		// Done
		return $result;
	}
	
	/**
	 * Activate this User
	 * @return string
	 */
	public function activate ( ) {
		# Proceed
		$this->status = 'published';
		
		# Done
		return true;
	}
	
	/**
	 * Has the user been activated?
	 * @return string
	 */
	public function isActive ( ) {
		return $this->status === 'published';
	}
	
	/**
	 * Ensure Uid
	 * @param Doctrine_Event $Event
	 * @return bool
	 */
	public function ensureUid ( $Event ) {
		# Prepare
		$uid = md5($this->username.$this->email);
		$save = false;
		
		# Is it different?
		if ( $this->_get('uid') != $uid ) {
			$this->_set('uid', $uid, false);
			$save = true;
		}
		
		# Return
		return $save;
	}
	
	/**
	 * Ensure Fullname
	 * @param Doctrine_Event $Event
	 * @return boolean	wheter or not to save
	 */
	public function ensureFullname ( $Event ) {
		# Prepare
		$save = false;
		
		# Fullname
		$fullname = implode(' ', array($this->title, $this->firstname, $this->lastname));
		if ( $this->_get('fullname') !== $fullname ) {
			$this->_set('fullname', $fullname, false); // false at end to prevent comparison
			$save = true;
		}
		
		# Return
		return $save;
	}
	
	/**
	 * Ensure Code
	 * @param Doctrine_Event $Event
	 * @return boolean	wheter or not to save
	 */
	public function ensureCode ( $Event ) {
		# Prepare
		$save = false;
		
		# Fullname
		if ( !$this->_get('code') ) {
			$this->_set('code', $this->username, false); // false at end to prevent comparison
			$save = true;
		}
		
		# Return
		return $save;
	}
	
	/**
	 * Ensure Displayname
	 * @param Doctrine_Event $Event
	 * @return boolean	wheter or not to save
	 */
	public function ensureDisplayname ( $Event ) {
		# Prepare
		$save = false;
		
		# Fullname
		if ( !$this->_get('displayname') ) {
			$this->_set('displayname', $this->username, false); // false at end to prevent comparison
			$save = true;
		}
		
		# Return
		return $save;
	}
	
	/**
	 * Ensure Username
	 * @param Doctrine_Event $Event
	 * @return boolean	wheter or not to save
	 */
	public function ensureUsername ( $Event ) {
		# Prepare
		$save = false;
		
		# Fullname
		if ( !$this->_get('username') ) {
			$this->_set('username', $this->email, false); // false at end to prevent comparison
			$save = true;
		}
		
		# Return
		return $save;
	}
	
	/**
	 * Ensure Subscriptions
	 * @param int $value [optional]
	 * @return bool
	 */
	public function ensureSubscriptions ( $Event ) {
		# Prepare
		$User = $Event->getInvoker();
		$modified = $User->getModified();
		$save = false;
		
		# Fetch
		$tags = implode(', ', $this->SubscriptionTagsNames);
		$subscriptions = $this->_get('subscriptions');
		if ( is_array($subscriptions) ) $subscriptions = implode(', ', $subscriptions);
		
		# Has Changed?
		if ( $subscriptions != $tags ) {
			if ( array_key_exists('subscriptions', $modified) ) {
				# Update Subscriptions with Subscriptions String
				$this->_set('subscriptions', $subscriptions, false); // false at end to prevent comparison
				# Update SubscriptionTags with Subscriptions
				if ( $this->id ) {
					$tags = prepare_csv_str($subscriptions);
					$this->SubscriptionTags = $tags;
				}
			}
			else {
				# Update Subscriptions with Tags
				$this->_set('subscriptions', $tags, false); // false at end to prevent comparison
			}
			$save = true;
		}
		
		# Return
		return $save;
	}
	
	/**
	 * Ensure Level
	 * @param Doctrine_Event $Event
	 * @return boolean	wheter or not to save
	 */
	public function ensureLevel ( $Event, $Event_type ) {
		# Prepare
		$User = $Event->getInvoker();
		$modified = $User->getModified();
		$save = false;
		
		# Level
		if ( $Event_type === 'preSave' && empty($modified['level']) ) {
			# Update the User's level with the latest highest role level
			$level_highest = 0;
			$User_Roles = $User->Roles;
			foreach ( $User_Roles as $User_Role ) {
				$level = $User_Role->level;
				if ( $level && $level > $level_highest ) {
					$level_highest = $level;
				}
			}
			if ( $User->level !== $level_highest ) {
				$User->_set('level', $level_highest, false); // false at end to prevent comparison
				$save = true;
			}
		}
		
		# Return
		return $save;
	}
	
	/**
	 * Ensure Consistency
	 * @param Doctrine_Event $Event
	 * @return boolean	wheter or not to save
	 */
	public function ensure ( $Event, $Event_type ) {
		# Prepare
		$User = $Event->getInvoker();
		
		# Handle
		$ensure = array(
			$User->ensureCode($Event,$Event_type),
			$User->ensureUid($Event,$Event_type),
			$User->ensureFullname($Event,$Event_type),
			$User->ensureUsername($Event,$Event_type),
			$User->ensureDisplayname($Event,$Event_type),
			$User->ensureSubscriptions($Event,$Event_type),
			$User->ensureLevel($Event,$Event_type)
		);
		
		# Return save
		return in_array(true,$ensure);
	}
	
	/**
	 * preSave Event
	 * @param Doctrine_Event $Event
	 * @return
	 */
	public function preSave ( $Event ) {
		# Prepare
		$User = $Event->getInvoker();
		$result = true;
		
		# Ensure
		if ( self::ensure($Event,'preSave') ) {
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
		$User = $Event->getInvoker();
		$result = true;
		
		# Ensure
		if ( self::ensure($Event,'postSave') ) {
			$User->save();
		}
		
		# Done
		return method_exists(get_parent_class($this),$parent_method = __FUNCTION__) ? parent::$parent_method($Event) : $result;
	}
	
	/**
	 * Post Insert Event
	 * @param Doctrine_Event $Event
	 * @return string
	 */
	public function postInsert ( $Event ) {
		# Prepare
		$User = $Event->getInvoker();
		$result = true;
		
		# Create Welcome Message
		$Message = new Message();
		$Message->For = $User;
		$Message->useTemplate('user-insert');
		$Message->save();
		
		# Done
		return method_exists(get_parent_class($this),$parent_method = __FUNCTION__) ? parent::$parent_method($Event) : $result;
	}
	
	
	
	/**
	 * Fetch a form for a User
	 * @param Bal_Model_User $User
	 * @return Zend_Form
	 */
	public static function fetchForm ( Bal_Model_User $User ) {
		# Prepare
		$Form = Bal_Form_Doctrine::createForm('User');
		
		# Group Elements
		$elements = array(
			'essential' => array(
				'username','password','email','displayname','type','status'
			),
			'names' => array(
				'title','firstname','lastname','description'
			),
			'contact' => array(
				'phone','address1','address2','suburb','state','country'
			),
			'other' => array(
				'subscriptions', 'Avatar', 'Permissions', 'Roles'
			)
		);
		
		# Add Id
		Bal_Form_Doctrine::addIdElement($Form,'User',$User);
		
		# Generate Elements
		$Elements = Bal_Form_Doctrine::addElements($Form, 'User', $elements, $User);
		
		# Return Form
		return $Form;
	}
	
}
