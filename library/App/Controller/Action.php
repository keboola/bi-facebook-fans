<?
/**
 * Common controllers setup
 * @author Jakub Matejka <jakub@keboola.com>
 * @since 1.0
 */

class App_Controller_Action extends Zend_Controller_Action
{
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
	}

	/**
	 * Setup before page load
	 */
	public function init()
	{
		$this->_config = Zend_Registry::get('config');
		$this->_baseUrl = $this->_config->app->url;
		
		$this->view->baseUrl = $this->_config->app->url;
		$this->view->config = $this->_config;		

		parent::init();
	}

}