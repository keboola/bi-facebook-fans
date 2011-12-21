<?php
/**
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-10-13
 */

class Model_UsersToConnectors extends App_Db_Table
{
	protected $_name = 'rUsersConnectors';
	protected $_referenceMap    = array(
		'User' => array(
			'columns'           => array('idUser'),
			'refTableClass'     => 'Model_Users',
			'refColumns'        => array('id')
		),
		'Connector' => array(
			'columns'           => array('idConnector'),
			'refTableClass'     => 'Model_Connectors',
			'refColumns'        => array('id')
		),
		'Plan' => array(
			'columns'           => array('idPlan'),
			'refTableClass'     => 'Model_PricingPlans',
			'refColumns'        => array('id')
		)
	);	
	//protected $_dependentTables = array('Model_Connectors', 'Model_Users');

}
