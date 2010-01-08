<?php

/**
 * Subscriber
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
class Bal_Model_Subscriber extends Base_Subscriber
{

	/**
	 * Apply modifiers
	 * @return
	 */
	public function setUp ( ) {
		$this->hasMutator('tagstr', 'setTagstr');
		$this->hasAccessor('tagarray', 'getTagArray');
		parent::setUp();
	}
	
	/**
	 * Get's a tag array
	 * @return array
	 */
	public function getTagArray ( ) {
		$tags = array();
		if ( isset($this->Tags) ) {
			$tags = array();
			foreach ( $this->Tags as $Tag ) {
				$tags[] = $Tag->name;
			}
			sort($tags);
		}
		return $tags;
	}

	/**
	 * Sets the tagstr field
	 * @param int $value [optional]
	 * @return bool
	 */
	public function setTagstr ( $value = null ) {
		/// Default
		if ( is_null($value) ) {
			$tags = $this->getTagArray();
			$value = implode($tags, ', ');
		}
		// Is Change?
		if ( $this->tagstr != $value ) {
			$this->_set('tagstr', $value);
			return true;
		}
		// No Change
		return false;
	}

	/**
	 * Ensure Consistency
	 * @return bool
	 */
	public function ensureConsistency(){
		# Prepare
		$save = false;
		
		# Tags
		if ( $this->setTagstr() ) {
			$save = true;
		}
		
		# Done
		return $save;
	}
	
	/**
	 * Backup old values
	 * @param Doctrine_Event $Event
	 */
	public function preSave ( $Event ) {
		# Prepare
		$Invoker = $Event->getInvoker();
		$save = false;
		
		# Ensure
		if ( $Invoker->ensureConsistency() ) {
			$save = true;
		}
		
		# Done
		return true;
	}
	
	/**
	 * Handle tagstr, authorstr, and code changes
	 * @param Doctrine_Event $Event
	 * @return string
	 */
	public function postSave ( $Event ) {
		# Prepare
		$Invoker = $Event->getInvoker();
		$save = false;
	
		# Ensure
		if ( $Invoker->ensureConsistency() ) {
			$save = true;
		}
		
		# Apply
		if ( $save ) {
			$Invoker->save();
		}
		
		# Done
		return true;
	}
	
}