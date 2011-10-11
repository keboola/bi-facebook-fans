<?php
/**
 *
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-10-11
 */

class Model_Accounts extends Zend_Db_Table
{
	protected $_name = 'fbi_accounts';
	protected $_dependentTables = array('Model_Pages');
}