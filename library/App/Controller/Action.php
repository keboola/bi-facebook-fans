<?
/**
 * Common controllers setup
 * @author Jakub Matejka <jakub@keboola.com>
 * @since 1.0
 */

class App_Controller_Action extends Zend_Controller_Action
{
	/**
	 * @var Zend_Translate
	 */
	protected $_translator;

	/**
	 * @var Zend_Config
	 */
	protected $_config;

	/**
	 * @var string
	 */
	protected $_baseUrl;

	/**
	 * @var Model_User
	 */
	protected $_user;


	/**
	 * Setup before page is dispatched
	 */
	public function preDispatch()
	{
		parent::preDispatch();

		$auth = Zend_Auth::getInstance();
		$controller = $this->_request->getControllerName();
		$action = $this->_request->getActionName();

		$publicAccess = $controller == 'index'
			|| $controller == 'auth'
			|| $controller == 'revalidate'
			|| ($controller == 'facebook' && $action == 'index');

        if (!$publicAccess && !$auth->hasIdentity()) {
			$this->_helper->redirector('login', 'auth');
		} else {
			$identity = $auth->getIdentity();
			if ($auth->hasIdentity() && $identity) {
				$_u = new Model_Users();
				$user = $_u->fetchRow(array('email=?' => $identity->email));
				if ($user) {
					$this->_user = $user;
					$this->view->user = $user;
					return;
				}
			}
			$auth->clearIdentity();
		}
	}

	/**
	 * Setup before page load
	 */
	public function init()
	{
		$this->_translator = Zend_Registry::get('Zend_Translate');
		$this->_config = Zend_Registry::get('config');
		$this->_baseUrl = $this->_config->app->url;

		$navigationConfig = new Zend_Config_Xml(APPLICATION_PATH.'/configs/navigation.xml');
		$navigation = new Zend_Navigation($navigationConfig);
		$this->view->navigation($navigation)->setTranslator(Zend_Registry::get('Zend_Translate'));

		$this->view->pageTitle = $this->_translator->_($this->_request->getControllerName().'.'.$this->_request->getActionName().'.title');
		$this->view->baseUrl = $this->_config->app->url;
		$this->view->config = $this->_config;

		parent::init();
	}

}