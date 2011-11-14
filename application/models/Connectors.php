<?php
/**
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-10-13
 */

class Model_Connectors extends App_Db_Table
{
	protected $_name = 'connectors';
	protected $_dependentTables = array('Model_UsersToConnectors');

}