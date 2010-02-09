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
class Bal_Model_User extends Base_User {

	/**
	 * Apply accessors and modifiers
	 * @return
	 */
	public function setUp ( ) {
		$this->hasAccessor('fullname', 'getFullname');
		$this->hasMutator('avatar', 'setAvatar');
		$this->hasMutator('password', 'setPassword');
		return parent::setUp();
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
		$Message->Receiver = $this;
		$Message->setCode('user-password-reset',false,compact('password'));
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
	 * Set the User's Avatar
	 * @return string
	 */
	protected function setMediaAttachment ( $what, $value ) {
		# Prepare
		$Media = false;
		
		# Create Media
		if ( is_array($value) ) {
			if ( array_key_exists('delete', $value) && $value['delete'] ) {
				$this->_set($what, null);
			} elseif ( array_key_exists('id', $value) ) {
				$Media = Doctrine::getTable('Media')->find($value);
			} elseif ( array_key_exists('tmpname', $value) ) {
				if ( empty($value['error']) ) {
					$Media = new Media();
					$Media->file = $value;
				}
			} elseif ( array_key_exists('file', $value) ) {
				if ( empty($value['file']['error']) ) {
					$Media = new Media();
					$Media->file = $value['file'];
				}
			}
		}
		
		# Apply Media
		if ( $Media ) {
			if ( isset($this->$what) ) {
				$this->$what->delete();
			}
			$this->_set($what, $Media);
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
	 * Get the User's fullname
	 * @return string
	 */
	public function getFullname ( ) {
		$fullname = array($this->title, $this->firstname, $this->lastname);
		return implode(' ', $fullname);
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
		$this->enabled = true;
		
		# Done
		return true;
	}
	
	/**
	 * Has the user been activated?
	 * @return string
	 */
	public function isActive ( ) {
		return $this->enabled === true;
	}
	
	/**
	 * Ensure Code
	 * @param Doctrine_Event $Event
	 * @return bool
	 */
	public function ensureCode ( $Event ) {
		# Prepare
		$code = md5($this->username.$this->email);
		$save = false;
		
		# Is it different?
		if ( $this->_get('code') != $code ) {
			$this->_set('code', $code, false);
			$save = true;
		}
		
		# Done
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
		if ( $this->_get('fullname') !== $this->getFullname() ) {
			$this->_set('fullname', $this->getFullname(), false); // false at end to prevent comparison
			$save = true;
		}
		
		# Done
		return $save;
	}
	
	/**
	 * Ensure Consistency
	 * @param Doctrine_Event $Event
	 * @return boolean	wheter or not to save
	 */
	public function ensure ( $Event ) {
		$ensure = array(
			$this->ensureCode($Event),
			$this->ensureFullname($Event)
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
		$Invoker = $Event->getInvoker();
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
		$Invoker = $Event->getInvoker();
		$result = true;
		
		# Ensure
		if ( self::ensure($Event) ) {
			$this->save();
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
		$Invoker = $Event->getInvoker();
		$result = true;
		
		# Create Welcome Message
		$Message = new Message();
		$Message->Receiver = $Invoker;
		$Message->template = 'user-insert';
		$Message->save();
		
		# Done
		return method_exists(get_parent_class($this),$parent_method = __FUNCTION__) ? parent::$parent_method($Event) : $result;
	}
	
}
