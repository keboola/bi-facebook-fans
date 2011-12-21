<?
/**
 * @author
 */
class GoogleAnalyticsController extends App_Controller_Action
{
	/**
	 * Id of connector in connectors table
	 */
	const ID_CONNECTOR = 2;

	/**
	 * @var App_Connector_GoogleAnalytics
	 */
	private $_connector;

	public function init()
	{
		parent::init();

		$this->_connector = new App_Connector_GoogleAnalytics();
	}

	public function indexAction()
	{
		//@TODO Info o konektoru
		$this->_helper->layout->setLayout('simple');
		if($this->_request->iframe==1) {
			$this->view->iframe = TRUE;
		}
	}

	public function registerAction()
	{		
		$_uc = new Model_UsersToConnectors();

		$config = Zend_Registry::get('config');
		$utility = new Zend_Oauth_Http_Utility();
		$session = new Zend_Session_Namespace('GoogleAnalyticsForm');
		$this->_gapi = new App_GoogleAnalytics();

		$redirectUri = $this->_baseUrl.'/google-analytics/register';
		$userToConnector = $_uc->fetchRow(array('idUser=?' => $this->_user->id, 'idConnector=?' => self::ID_CONNECTOR));

		if (!isset($session->oauthToken)) {
			
			// Login With Google Account
			if ($this->_request->getParam('code')) {
				
				$res = $this->_gapi->getAccessToken(
					$this->_request->getParam('code'),
					$config->google->oauth->id,
					$config->google->oauth->secret,
					$redirectUri
				);

				// if something's wrong - google login again
				if (!isset($res['access_token'])) {
					$this->_helper->redirector('register');
				}

				$session->oauthToken = $res['access_token'];
				$session->refreshToken = $res['refresh_token'];
				
				$userInfo = $this->_gapi->getUserInfo();
				$session->googleUserId = $userInfo['id'];

			} else {
				$url = App_GoogleAnalytics::OAUTH_URL;

				$get = array(
					'redirect_uri'	=> $redirectUri,
					'scope'		=> 'https://www.googleapis.com/auth/analytics.readonly https://www.googleapis.com/auth/userinfo.profile',
					'client_id'		=> $config->google->oauth->id,
					'response_type' => 'code',
					'access_type'	=> 'offline',
					'approval_prompt' => 'force'
				);

				$get = '?' . str_replace('&amp;','&',urldecode(http_build_query($get)));

				$this->_redirect($url . $get);
			}
		}

		// Access Token obtained - Show/Process registration form
		$form = new Form_GoogleAnalyticsSetup();		

		$request = $this->getRequest();

		if ($request->isPost() && $form->isValid($request->getParams())) {

			$this->_connector->addProfilesToAccount($this->_user->id, $request->profiles);			

			if (!$userToConnector) {
				$_uc->insert(array(
					'idUser'		=> $this->_user->id,
					'idConnector'	=> self::ID_CONNECTOR,
				));
			}

			$this->_helper->getHelper('FlashMessenger')->addMessage('success|google.analytics.register.success');
			
		} else {

			try {
				$profiles = $this->_gapi->getAllProfiles();
			} catch (Zend_Exception $e) {

				$session->__unset('oauthToken');
				$session->__unset('refreshToken');
				$session->__unset('googleUserId');

				$this->_helper->redirector('register');
			}

			if (count($profiles) > 0) {
				$form->setProfiles($profiles);				
				$form->getElement('profiles')
					->setAttrib('disable', array_keys($this->_connector->getProfiles($session->googleUserId)));

			} else {
				$this->_helper->getHelper('FlashMessenger')->addMessage('error|google.analytics.register.noAccountsFound');
			}			

			$this->view->form = $form;
		}

	}

	public function logoutAction()
	{
		$session = new Zend_Session_Namespace('GoogleAnalyticsForm');
		
		$session->__unset('oauthToken');
		$session->__unset('refreshToken');

		//$returnUrl = $this->_baseUrl . '/google-analytics/account';

		//@FIXME: maybe logout via AJAX and then redirect to /google-analytics/account
		$this->_redirect('https://www.google.com/accounts/logout');
	}

	protected function _clearSession()
	{
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();

		session_destroy();
	}

}