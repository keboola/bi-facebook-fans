<?php
/**
 * index controller
 *
 * @author Jakub Matejka <jakub@keboola.com>
 */
class IndexController extends Zend_Controller_Action
{

	public function init()
	{
		$this->_redirect('/api');
	}

}
