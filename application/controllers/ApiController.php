<?php
/**
 * api controller
 *
 * @author Jakub Matejka <jakub@keboola.com>
 */
class ApiController extends Zend_Controller_Action
{

	/**
	 * @var Zend_Config
	 */
	protected $_config;

	/**
	 * @var App_GoodDataService
	 */
	protected $_gds;

	public function init()
	{
		$this->_helper->viewRenderer->setNoRender(true);

		$this->_config = Zend_Registry::get('config');
		$this->_gds = new App_GoodDataService($this->_config->gooddata->username, $this->_config->gooddata->password);
	}

	public function indexAction()
	{
		echo 'GD Service';
	}

	public function createProjectAction()
	{
		$result = array('status' => TRUE);

		if($this->_request->name) {
			if($this->_request->email) {

				$idProject = $this->_gds->createProject($this->_request->name, $this->_request->email);
				if ($idProject) {
					$result['project'] = $idProject;
				} else {
					$result['status'] = FALSE;
					$result['error'] = 'GD Error';
				}

			} else {
				$result['status'] = FALSE;
				$result['error'] = 'Email not set';
			}
		} else {
			$result['status'] = FALSE;
			$result['error'] = 'Project name not set';
		}

		echo Zend_Json::encode($result);
	}

	public function copyDashboardAction()
	{
		$result = array('status' => TRUE);

		if($this->_request->template) {
			if(!$this->_request->project) {
				$_t = new Model_Templates();
				$template = $_t->fetchRow(array('id=?' => $this->_request->template));
				if($template) {

					$this->_gds->copyMetaData($template->dashboard, $template->idGD, $this->_request->project);

				} else {
					$result['status'] = FALSE;
					$result['error'] = 'Bad template';
				}
			} else {
				$result['status'] = FALSE;
				$result['error'] = 'Project not set';
			}
		} else {
			$result['status'] = FALSE;
			$result['error'] = 'Template not set';
		}

		echo Zend_Json::encode($result);
	}

}
