<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Form_Element_Xhtml */
require_once 'Zend/Form/Element/Xhtml.php';

/**
 * Textarea form element
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Textarea.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
class Bal_Form_Element_Doctrine extends Zend_Form_Element_Xhtml
{
    /**
     * Use formTextarea view helper by default
     * @var string
     */
    public $helper = 'formDoctrine';
	
	public function setTableAndField ( $table, $fieldName, $Record = null ) {
		# Prepare
		$Element = $this;
		$tableName = Bal_Form_Doctrine::getTableName($table);
		
		# Apply Options
		$options = array('table'=>$tableName,'field'=>$fieldName);
		$Element->setOptions($options);
		
		# Apply Doctrine Options
		Bal_Form_Doctrine::applyElementProperties($Element, $table, $fieldName, $Record );
		
		# Chain
		return $this;
	}
	
}