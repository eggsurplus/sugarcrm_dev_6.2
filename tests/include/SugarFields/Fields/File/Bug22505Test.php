<?php
/*********************************************************************************
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2011 SugarCRM Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 ********************************************************************************/

/**
 * @ticket 22505
 *
 *		Original Bug: Sugar should indicate to customer the max file size that can be uploaded 
 *
 */
class Bug22505Test extends Sugar_PHPUnit_Framework_TestCase {

	private $_post_max_size;
	private $_upload_max_filesize;
	private $_upload_maxsize;
	private $_file_field;
	
	function setUp() 
	{
		$this->_post_max_size = ini_get('post_max_size');
		$this->_upload_max_filesize = ini_get('upload_max_filesize');
		$this->_upload_maxsize = $GLOBALS['sugar_config']['upload_maxsize'];
		
		require_once('include/SugarFields/Fields/File/SugarFieldFile.php');
		$this->_file_field = new SugarFieldFile('file');
	}

	function tearDown() {
		//ini_set('post_max_size',$this->_post_max_size);
		//ini_set('upload_max_filesize',$this->_upload_max_filesize);
		$GLOBALS['sugar_config']['upload_maxsize'] = $this->_upload_maxsize;
		
		unset($this->_post_max_size);
		unset($this->_upload_max_filesize);
		unset($this->_upload_maxsize);
		unset($this->_file_field);
	}

	function testMaxFileUploadSize() {	
		$small = '9999'; //9.76 kb
		$large = '99999999999999';
		
		//Test 1: upload_maxsize is smallest
		//ini_set('post_max_size',$small);
		//ini_set('upload_max_filesize',$large);
		$GLOBALS['sugar_config']['upload_maxsize'] = $small;
		$max_size = $this->_file_field->get_max_fileupload_size();

		$this->assertEquals($max_size, '9.76 kb','Max file upload size is not 9.76 kb as expected');
		
		//Test 2: upload_maxsize is greatest
		$GLOBALS['sugar_config']['upload_maxsize'] = $large;
		$max_size = $this->_file_field->get_max_fileupload_size();

		$this->assertNotEquals($max_size, '9.76 kb','Max file upload size is 9.76 kb which is not expected');
	}

}
?>