<?php
/**
 * index controller
 *
 * @author Jakub Matejka <jakub@keboola.com>
 */
class IndexController extends App_Controller_Action
{

	public function indexAction()
	{
		$_c = new Model_Connectors();

		$this->view->subscribedConnectors = $this->_user->subscribedConnectors();
		$this->view->connectors = $_c->fetchAll();
	}

}
