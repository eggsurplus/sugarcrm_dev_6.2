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
 * eggsurplus: if anyone finds a better way to test viewdef options please let me know
 */
require_once 'tests/SugarTestAccountUtilities.php';
class Bug28321Test extends Sugar_PHPUnit_Framework_OutputTestCase {

    var $has_custom_accounts_detailviewdefs_file;
    var $dev_mode = false;
    
	function setUp() 
	{
		global $sugar_config;
	    $this->bean = new Account();
	    $this->defs = $this->bean->field_defs; //temporarily store defs
    	$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    	$this->acc = SugarTestAccountUtilities::createAccount();
    	$dev_mode = $sugar_config['developerMode'];
    	//$sugar_config['developerMode'] = true;
    	
    	
		//save the custom version
        if(file_exists('custom/modules/accounts/metadata/detailviewdefs.php')) {
           $this->has_custom_accounts_detailviewdefs_file = true;
           copy('custom/modules/accounts/metadata/detailviewdefs.php', 'custom/modules/accounts/metadata/detailviewdefs.php.bak');
           unlink('custom/modules/accounts/metadata/detailviewdefs.php');
        }
           
        //use the default for testing
        copy('modules/accounts/metadata/detailviewdefs.php', 'modules/accounts/metadata/detailviewdefs.php.bak');
   

        require('modules/Accounts/metadata/detailviewdefs.php');
 
 		//set up our customCode for testing
        foreach($viewdefs['Accounts']['DetailView']['panels'] as $panel_name=>$panels) {
        	foreach($panels as $row_num=>$row) {
        		foreach($row as $col_num=>$col) {
        			if(!is_array($col)) continue;
        				
        			//Test 1: custom code && render field
					if($col['name'] == 'name') {
						$viewdefs['Accounts']['DetailView']['panels'][$panel_name][$row_num][$col_num]['customCode'] = '<a href="http://www.google.com?q={$fields.name.value}">Search</a>';
						$viewdefs['Accounts']['DetailView']['panels'][$panel_name][$row_num][$col_num]['customCodeRenderField'] = true;
					} 
  					//Test 2: custom code && no render field set
					if($col['name'] == 'website') {
						$viewdefs['Accounts']['DetailView']['panels'][$panel_name][$row_num][$col_num]['customCode'] = '12344321';
					}   
					//Test 3: custom code && render field false

					//Test 4: no custom code
        		}
        	}
        }
      
        write_array_to_file("viewdefs['Accounts']['DetailView']", $viewdefs['Accounts']['DetailView'], 'modules/Accounts/metadata/detailviewdefs.php');
        //merge with array above and save
        //clear cache
        if(file_exists('cache/modules/Accounts/DetailView.tpl')) {
        	unlink('cache/modules/Accounts/DetailView.tpl');
        }
      
	}

	function tearDown() {
	    $this->bean->field_defs = $this->defs; //restore defs
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    	$sugar_config['developerMode'] = $dev_mode;
    	
        if($this->has_custom_accounts_detailviewdefs_file) {
           copy('custom/modules/accounts/metadata/detailviewdefs.php.bak', 'custom/modules/accounts/metadata/detailviewdefs.php');
           unlink('custom/modules/accounts/metadata/detailviewdefs.php.bak');
        }
        //copy('modules/accounts/metadata/detailviewdefs.php', 'modules/accounts/metadata/test.detailviewdefs.php'); //for testing the test
        copy('modules/accounts/metadata/detailviewdefs.php.bak', 'modules/accounts/metadata/detailviewdefs.php');
        unlink('modules/accounts/metadata/detailviewdefs.php.bak');
        
	}


	function testCustomCodeRenderField() {	
    	
		//Test 1: custom code && render field

        //Test 2: custom code && no render field set
        $this->acc->website = 'www.sugarcrm.comTEST';
   
        //Test 3: custom code && render field false
        
        //Test 4: no custom code
		

    	global $app_strings;
    	$_REQUEST['module'] = 'Accounts';
    	$_REQUEST['action'] = 'DetailView';	 
    	$_REQUEST['record'] = $this->acc->id;	

    	require_once('include/MVC/View/SugarView.php');
    	require_once('include/MVC/View/views/view.detail.php');
    	require_once('include/utils/layout_utils.php');
    	$detail = new ViewDetail();
    	$detail->init();
    	$detail->module = 'Accounts';
    	$detail->bean = $this->acc;
    	//$detail->metadataFile = 'modules/accounts/metadata/test.detailviewdefs.php';
    	$detail->preDisplay();
		ob_start();
		$detail->display();
		$output_html = ob_get_contents();
		ob_end_clean();
		
		//$GLOBALS['log']->fatal("output: ".$output_html);    	
    	//Test 1  
    	//<span class="sugar_field" id="name">Pullman Cart Company</span>
		$this->assertContains('<span class="sugar_field" id="name">'.$this->acc->name.'</span>',$output_html,"Output did not contain original field");
    	$this->assertContains('http://www.google.com?q='.$this->acc->name,$output_html,"Output did not contain customCode");
    	
    	//Test 2
    	$this->assertNotContains('www.sugarcrm.comTEST',$output_html,"Output contains website");
    	$this->assertContains('12344321',$output_html,"Output did not contain website customCode");
    	
    	//Test 3
    	
    	//Test 4
    	
 
    	
	}


}
?>