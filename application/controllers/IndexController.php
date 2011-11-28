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
		$this->_helper->layout->setLayout('simple');
	}

}
