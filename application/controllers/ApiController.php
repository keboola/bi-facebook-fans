<?php
/**
 * api controller
 *
 * @author Jakub Matejka <jakub@keboola.com>
 */
class ApiController extends Zend_Controller_Action
{

	public function init()
	{
		$this->_helper->viewRenderer->setNoRender(true);
	}

	public function indexAction()
	{
		echo 'GD Service';
	}

	public function createProjectAction()
	{
		$_t = new Model_Templates();

		$result = array();

		if($this->_request->template) {
			$template = $_t->fetchRow(array('id=?' => $this->_request->template));
			if($template) {

				if($this->_request->project) {
					//deploy to existing project
				} else {
					//create new project

				}

			} else {
				$result['error'] = 'Bad template id';
			}
		} else {
			$result['error'] = 'Empty template id';
		}

		echo Zend_Json::encode($result);die();
	}

}
