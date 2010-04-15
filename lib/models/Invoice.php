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
class Bal_Invoice extends Base_Bal_Invoice
{
	
	/**
	 * Apply accessors and modifiers
	 * @return
	 */
	public function setUp ( ) {
		parent::setUp();
	}
	
	/**
	 * Create a Invoice from a template
	 * @param string $template
	 * @param array $data [optiona]
	 * @return Invoice
	 */
	public static function createFromTemplate ( $template, array $data = array() ) {
		# Prepare
		$Connection = Bal_App::getDataConnection();
		$Invoice = null;
		
		# Wrap
		try {
			# Start
			$Connection->beginTransaction();
			
			# Prepare Invoice
			$Invoice = new Invoice();
			
			# Template Invoice
			$Invoice->useTemplate($template, $data);
			
			# Save Invoice
			$Invoice->save();
			
			# Generate File
			$Invoice->generateFile();
			$Invoice->save();
			
			# Done
			$Connection->commit();
		}
		catch ( Exception $Exception ) {
			# Revert
			$Connection->rollback();
			
			# Log the Event and Continue
			$Exceptor = new Bal_Exceptor($Exception);
			$Exceptor->log();
		}
		
		# Done
		return $Invoice;
	}
	
	/**
	 * Shortcut Message Creation via Codes
	 * @return string
	 */
	public function useTemplate ( $template, array $data = array() ) {
		# Prepare
		$Locale = Bal_App::getLocale();
		$applicationConfig = Bal_App::getConfig();
		$Invoice = $this;
		
		# Merge Data
		if ( !empty($data) ) {
			foreach ( $data as $key => $value )
				$Invoice->set($key, $value);
		}
		
		# Apply Config
		$config = delve($applicationConfig, 'bal.invoice', array());
		$this->config = $config;
		
		# Apply Template
		$this->_set('template', $template, false);
		
		# Handle Template
		$function = '_template'.magic_function($template);
		if ( method_exists($this, $function) ) {
			$this->$function();
		}
		
		# Chain
		return $Invoice;
	}
	
	protected function _templateUserInvoice ( ) {
		# All properties are manually set
		
		# Chain
		return $this;
	}
	
	
	public function getPath ( ) {
		# Prepare
		$invoices_path = Bal_App::getConfig('invoices_path') . DIRECTORY_SEPARATOR;
		
		# Handle
		$name = $this->id.'-'.md5(serialize($this->toArray(false))); // $this->id.'-'.$this->title;
		$invoice_path = $invoices_path . $name . '.pdf';
		
		# Return
		return $invoice_path;
	}
	
	public function download ( ) {
		# Prepare
		$invoice_path = $this->getPath();
		
		# Download
		become_file_download($invoice_path);
		
		# Done
		die;
	}
	
	public static function getTemplatePath ( $template ) {
		# Prepare
		$templates_path = Bal_App::getConfig('templates_path') . DIRECTORY_SEPARATOR;
		# Handle
		$template_path = $templates_path . 'invoice-'.$template.'.pdf';
		# Return path
		return $template_path;
	}
	
	public function generateFile ( ) {
		# Prepare
		$Locale = Bal_App::getLocale();
		$Invoice = $this;
		$InvoiceArray = $this->toArray(true);
		$Invoice_path = $this->getPath();
		$template_path = self::getTemplatePath($this->template);
		
		# Setup PDF + Meta
		$Pdf = Zend_Pdf::load($template_path);
		$Pdf->properties['Author'] = $Locale->translate('invoice-author');
		$Pdf->properties['Title'] = $Locale->translate('invoice-title', $Invoice);
		$Page = $Pdf->pages[0];
		
		# Font
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER);
		$Page->setFont($font, 9);
		
		# Handle Matrix
		$template = $this->template;
		$function = '_matrix'.magic_function($template);
		if ( method_exists($this, $function) ) {
			$matrix = $this->$function();
		}
		
		# Translate Matrix
		foreach ( $matrix as $column => $details ) {
			# Fetch value
			$key = $column;
			$column = strtolower(str_replace('.','-',$key));
			$name = delve($details,'name',$column);
			$value = array_key_exists('value',$details) ? $details['value'] : delve($Invoice,$key,'');
			
			# Fetch type
			$type = delve($details,'type');
			if ( !$type ) {
				# Get field
				$field = delve($details,'field',$column);
				$Table = empty($details['table']) ? $this->getTable() : Doctrine::getTable($details['table']);
				$definition = $Table->getColumnDefinition($field);
				$type = delve($definition,'type');
				
				# Adjust
				if ( $type === 'enum' ) {
					$type = gettype($value);
				}
				elseif ( delve($definition,'extra.currency') ) {
					$type = 'currency';
				}
			}
			
			# Translate type
			if ( empty($type) ) $type = 'translate';
			$value = $Locale->translate_default('invoice-'.$name, array(
				'value' => $Locale->$type($value),
				'Invoice' => $InvoiceArray
			),$Locale->$type($value));
			
			# Check translation
			$length = delve($details,'length');
			if ( $length && strlen($value) > $length ) {
				# Trim
				$value = substr($value,0,$length-4).' ...';
			}
			
			# Draw onto the PDF
			$Page->drawText($value, $details['position'][0], $details['position'][1]);
		}
		
		# Save PDF
		$Pdf->save($Invoice_path);
		
		# Prepare File
		$File = new File();
		$File->file = $Invoice_path;
		$File->url = Bal_App::getConfig('invoices_url') . DIRECTORY_SEPARATOR . $File->name;
		
		# Reset File
		Doctrine_Query::create()
			->delete('File m')
			->where('m.name = ?', $File->name)
			->execute();
		
		# Save File
		$File->save();
		$this->File = $File;
		
		# Chain
		return $this;
	}
	
	
	protected function _matrixUserInvoice ( $Invoice ) {
		# Prepare
		$InvoiceItems = delve($Invoice,'InvoiceItems');
		
		# Set Matrix
		$matrix = array(
			# Invoice
			'id' => array(
				'position' => array(140,713),
				'length' => 65
			),
			'cost' => array(
				'position' => array(190,436),
				'length' => 70
			),
			
			# Times
			'created' => array(
				'position' => array(405,713),
				'length' => 65
			),
			'updated' => array(
				'position' => array(220,748),
				'length' => 65
			)
		);
		
		# Alter Matrix Depending on Code
		switch ( $code ) {
			case 'user_invoice':
				# Add Invoice Items
				$line_height = 15;
				foreach ( $InvoiceItems as $i => $InvoiceItem ) {
					# Title
					$matrix['invoiceitem-'.$InvoiceItem->id] = array(
						'position' => array(150,519+$line_height*$i),
						'length' => 55,
						'value' => $InvoiceItem->title,
						'type' => 'string'
					);
			
					# Cost
					$matrix['invoiceitem-'.$InvoiceItem->id] = array(
						'position' => array(300,519+$line_height*$i),
						'length' => 55,
						'value' => $InvoiceItem->cost,
						'type' => 'currency'
					);
				}
				
				# Done
				break;
			
			default:
				# Unkown
				throw new Zend_Exception('error-invoice-unknown_code');
		}
		
		
		# Return
		return $matrix;
	}
	
	
	
	/**
	 * Ensure Cache
	 * @param Doctrine_Event $Event
	 * @return boolean	wheter or not to save
	 */
	public function ensureCache($Event,$Event_type){
		# Check
		if ( !in_array($Event_type,array('preInsert','postInsert')) ) {
			# Not designed for these events
			return null;
		}
		
		# Prepare
		$save = false;
		
		# Fetch
		$Invoice = $Event->getInvoker();
		
		# Check Existance
		if ( $Invoice->id && empty($Invoice->cache) ) {
			# Fetch Cache
			$cache = $Invoice->toArray(true);
			unset($cache['cache']);
			$Invoice->cache = $cache;
			$save = true;
		}
		elseif ( empty($Invoice->cache) ) {
			$Invoice->cache = array();
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
	public function ensure ( $Event, $Event_type ){
		return Bal_Doctrine_Core::ensure($Event,$Event_type,array(
			'ensureCache',
			'ensureMessages'
		));
	}
	
	
	/**
	 * Ensure Messages
	 * @param Doctrine_Event $Event
	 * @return boolean	success
	 * @todo on postDelete remove messages that haven't been sent
	 */
	public function ensureMessages($Event, $Event_type){
		# Check
		if ( !in_array($Event_type,array('postInsert')) ) {
			# Not designed for these events
			return null;
		}
		
		# Prepare
		$save = false;
		
		# Fetch
		$Invoice = $Event->getInvoker();
		$Booking = $Invoice->Booking;
	
		# --------------------------
		# Messages
		
		# Create Invoice Insert Messages
		$Receivers = array(
			delve($Invoice,'UserFor'),
			delve($Invoice,'UserBy')
		);
		foreach ( $Receivers as $Receiver ) {
			$Message = new Message();
			$Message->UserFor = $Receiver;
			$Message->Booking = $Booking;
			$Message->useTemplate('invoice-insert');
			$Message->save();
		}
		
		# --------------------------
		
		# Return save
		return $save;
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
	 * postInsert Event
	 * @param Doctrine_Event $Event
	 * @return
	 */
	public function postInsert ( $Event ) {
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
	 * preInsert Event
	 * @param Doctrine_Event $Event
	 * @return
	 */
	public function preInsert ( $Event ) {
		# Prepare
		$result = true;
		
		# Ensure
		if ( self::ensure($Event, __FUNCTION__) ) {
			// no need
		}
		
		# Done
		return method_exists(get_parent_class($this),$parent_method = __FUNCTION__) ? parent::$parent_method($Event) : $result;
	}
	
	
	# ========================
	# CRUD HELPERS
	
	
	/**
	 * Fetch all the records for public access
	 * @version 1.0, April 12, 2010
	 * @return mixed
	 */
	public static function fetch ( array $params = array() ) {
		# Prepare
		Bal_Doctrine_Core::prepareFetchParams($params,array('Invoice','User','UserFor','UserFrom'));
		extract($params);
		
		# Query
		$Query = Doctrine_Query::create()
			->select('Invoice.*, InvoiceItem.*, Booking.*, Media.*')
			->from('Invoice, Invoice.InvoiceItems InvoiceItem')
			->orderBy('Invoice.created_at ASC');
		
		# Criteria
		if ( $User ) {
			$User = Bal_Doctrine_Core::resolveId($User);
			$Query->andWhere('UserFor.id = ? OR i.UserFrom.id = ?', array($User,$User));
		}
		if ( $UserFor ) {
			$UserFor = Bal_Doctrine_Core::resolveId($UserFor);
			$Query->andWhere('UserFor.id = ?', $UserFor);
		}
		if ( $UserFrom ) {
			$UserFrom = Bal_Doctrine_Core::resolveId($UserFrom);
			$Query->andWhere('UserFrom.id = ?', $UserFrom);
		}
		if ( $Invoice ) {
			$Invoice = Bal_Doctrine_Core::resolveId($Invoice);
			$Query->andWhere('Invoice.id = ?', $Invoice);
		}
		
		# Fetch
		$result = Bal_Doctrine_Core::prepareFetchResult($params,$Query,'Invoice');
		
		# Done
		return $result;
	}
	
}
