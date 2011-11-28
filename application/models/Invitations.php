<?php
/**
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-11-23
 */

class Model_Invitations extends App_Db_Table
{
	protected $_name = 'invitations';
	protected $_referenceMap    = array(
		'UserToConnector' => array(
			'columns'           => array('idUserConnector'),
			'refTableClass'     => 'Model_UsersToConnectors',
			'refColumns'        => array('id')
		)
	);

}

