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
		$this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();

		$ns = new Zend_Session_Namespace('Messages');
		if (isset($ns->messages)) {
			$this->view->messages += $ns->messages;
			unset($ns->messages);
		}

		$auth = Zend_Auth::getInstance();

		$controller = $this->_request->getControllerName();
        if ($controller != 'auth' && $controller != 'revalidate' && !$auth->hasIdentity()) {
			$this->_helper->redirector('index', 'auth');
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

	/**
	 * @param $message
	 */
	public function addMessage($message)
	{
		if(!is_array($this->view->messages)) {
			$this->view->messages = array();
		}
		$this->view->messages[] = $message;
	}

	/**
	 * @param $messages
	 */
	public function addMessages($messages)
	{
		$ns = new Zend_Session_Namespace('Messages');
		if(!is_array($ns->messages)) {
			$ns->messages = $messages;
		}
		$ns->messages += $messages;
	}
}