<?php
/**
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-10-13
 */

class Model_Users extends App_Db_Table
{
	protected $_name = 'users';
	protected $_rowClass = 'Model_Row_User';
	protected $_dependentTables = array('Model_UsersToConnectors');


}

