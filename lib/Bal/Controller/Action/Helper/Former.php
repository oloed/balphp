<?php
class Bal_Controller_Action_Helper_Former extends Zend_Controller_Action_Helper_Abstract {

	
	public function fetch ( $fields ) {
		$result = array();
		
		
		return $result;
	}
	
	public function fetchField ( $field, $from = null ) {
		# Prepare
		$keys = array();
		if ( $from === null ) {
			$from =& $_REQUEST;
		}
		
		# Determine Keys
		if ( is_string($field) ) {
			$keys = explode($field, '.');
		} elseif ( is_array($field) ) {
			$keys = $field;
		} else {
			$keys = array($field);
		}
		
		# Fetch
		$result = array_delve($from, $keys);
		
		# Convert
		$result = real_value($result);
		
		# Done
		return $result;
	}
	
	public function appendFields ( &$arr, $fields ) {
		$result = $this->appendField($arr, null, $fields);
		return $result;
	}
	
	public function appendField ( &$result, $keys, $value ) {
		# Prepare
		if ( !is_array($keys) ) {
			$keys = empty($keys) ? array() : array($keys);
		}
		
		# Handle
		if ( is_array($value) ) {
			# Cycle
			foreach ( $value as $key_ => $value_ ) {
				$this->appendField($arr, $keys, $value_);
			}
		} else {
			# Prepare
			$key = array_last($keys);
			$type = 'normal';
			if ( is_integer($key) ) {
				$key = $value;
			} else {
				if ( !empty($value) ) {
					if ( strpos($value, ',') !== false ) {
						$type = 'enum';
					} else {
						$type = $value;
					}
				}
			}
			
			# Handle
			switch ( $type ) {
				case 'FILE':
					$field_value = $_FILES[array_shift($keys)];
					$key_parts = $keys;
					$key_parts[] = $key;
					foreach ( $field_value as $field_part => &$field_part_value ) {
						foreach ( $key_parts as $key_part ) {
							$field_part_value = $field_part_value[$key_part];
						}
					}
					$field_value = real_value($field_value);
					array_apply($arr, $keys, $field_value);
					break;
					
				case 'enum':
				case 'normal':
				default:
					$field_value = array_delve($_REQUEST, $keys);
					$field_value = real_value($field_value);
					array_apply($arr, $keys, $field_value);
					break;
			}
		}
		
		# Done
		return $result;
	}
	
}