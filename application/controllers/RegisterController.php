<?php
/**
 * index controller
 *
 * @author Jakub Matejka <jakub@keboola.com>
 */
class RegisterController extends Zend_Controller_Action
{
	private $_facebook;
	private $_config;
	private $_pageUrl;

	public function init()
	{
		$this->view->addScriptPath(APPLICATION_PATH . '/layouts');
		Zend_Layout::startMvc();
		Zend_Session::start();

		$this->_config = Zend_Registry::get('config');

		$this->_pageUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/register';
	}

	public function logoutAction()
	{
		if (!empty($this->_request->processed)) {
			Zend_Session::destroy();
			echo 'Logout';
			die();
		} else {
			$this->_redirect('https://www.facebook.com/logout.php?next=' . $this->_pageUrl . '/logout?processed=1&access_token='
							 . $this->_config->facebook->appId . '|' . $this->_config->facebook->appSecret);
		}
	}

	/**
	 * @return void
	 */
	public function indexAction()
	{
		if (empty($this->_request->code)) {
			$_SESSION['state'] = md5(uniqid(rand(), TRUE)); //CSRF protection
			$dialogUrl = "http://www.facebook.com/dialog/oauth?client_id=" . $this->_config->facebook->appId
						 . "&scope=offline_access,read_insights&redirect_uri=" . urlencode($this->_pageUrl)
						 . "&state=" . $_SESSION['state'];
			$this->_redirect($dialogUrl);
			return;
		}

		if ($_REQUEST['state'] == $_SESSION['state']) {
			$tokenUrl = "https://graph.facebook.com/oauth/access_token?client_id=" . $this->_config->facebook->appId
						. "&redirect_uri=" . urlencode($this->_pageUrl) . "&client_secret=" . $this->_config->facebook->appSecret
						. "&code=" . $this->_request->code;
			$response = file_get_contents($tokenUrl);
			$params = null;
			parse_str($response, $params);

			$form = new Form_AddPage();
			$form->getElement('fbToken')->setValue($params['access_token']);

			$this->view->form = $form;
		}
		else {
			echo("The state does not match. You may be a victim of CSRF.");
		}
	}
}
