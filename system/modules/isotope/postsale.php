<?php

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


// Preserve $_POST data
$arrPOST = $_POST;
unset($_POST);

/**
 * Initialize the system
 */
define('TL_MODE', 'FE');
require('../../initialize.php');

$_POST = $arrPOST;


class PostSale extends Frontend
{
	
	/**
	 * Must be defined cause parent is protected.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		// Contao Hooks are not save to be run on the postsale script (e.g. parseFrontendTemplate)
		unset($GLOBALS['TL_HOOKS']);

		parent::__construct();
	}
	

	/**
	 * Run the controller
	 */
	public function run()
	{
		$strMod = strlen($this->Input->post('mod')) ? $this->Input->post('mod') : $this->Input->get('mod');
		$strId = strlen($this->Input->post('id')) ? $this->Input->post('id') : $this->Input->get('id');
		
		if (!strlen($strMod) || !strlen($strId))
		{
			$this->log('Invalid post-sale request: '.$this->Environment->request . "\n" . print_r($_POST, true), 'PostSale run()', TL_ERROR);
			return;
		}
		
		$this->log('New post-sale request: '.$this->Environment->request . "\n" . print_r($_POST, true), 'PostSale run()', TL_ACCESS);
		
		switch( $strMod )
		{
			case 'pay':
				$objModule = $this->Database->prepare("SELECT * FROM tl_iso_payment_modules WHERE id=?")->limit(1)->execute($strId);
				break;
				
			case 'ship':
				$objModule = $this->Database->prepare("SELECT * FROM tl_iso_shipping_modules WHERE id=?")->limit(1)->execute($strId);
				break;
		}

		if (!$objModule->numRows)
			return;
			
		$strClass = $GLOBALS['ISO_'.strtoupper($strMod)][$objModule->type];
		if (!strlen($strClass) || !$this->classFileExists($strClass))
			return;
		
		try 
		{
			$objModule = new $strClass($objModule->row());
			return $objModule->processPostSale();
		}
		catch (Exception $e) {}
		
		return;
	}
}


/**
 * Instantiate controller
 */
$objPostSale = new PostSale();
$objPostSale->run();

