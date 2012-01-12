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
		$inviteForm = new Form_Invite();
		$request = $this->getRequest();

		if ($request->isPost()) {

			if ($request->getParam('job') == 'register') {
				
				if ($form->isValid($request->getParams())) {
					$this->_connector->addProfilesToAccount($this->_user->id, $session->googleUserId, $request->profiles);

					if (!$userToConnector) {
						$_uc->insert(array(
							'idUser'		=> $this->_user->id,
							'idConnector'	=> self::ID_CONNECTOR,
						));
					}

					$this->_helper->FlashMessenger->addMessage('success|google.analytics.register.success', 'https://secure.gooddata.com/#s=/gdc/projects/'.$this->_user->idGD.'|projectDashboardPage');
				} else {
					$this->_helper->getHelper('FlashMessenger')->addMessage('error|google.analytics.register.formInvalid');
				}

			} else if ($request->getParam('job') == 'invite') {

				if ($inviteForm->isValid($request->getParams())) {

					$_i = new Model_Invitations();
					$i = $_i->fetchRow(array('idUserConnector=?' => $userToConnector->id, 'email=?' => $this->_request->email));
					if (!$i && $this->_request->email != $this->_user->email) {
						$_i->insert(array(
							'idUserConnector'	=> $userToConnector->id,
							'email'				=> $this->_request->email,
							'role'				=> $this->_request->role,
							'text'				=> $this->_request->text,
							'code'				=> substr(md5(uniqid(rand(), true)), 0, 5)
						));
						$this->_helper->getHelper('FlashMessenger')->addMessage('success|facebook.register.invitationSent');
					} else {
						$this->_helper->getHelper('FlashMessenger')->addMessage('error|facebook.register.invitationExists');
					}
				}
			}
			
		}

		$this->_gapi = new App_GoogleAnalytics($session->oauthToken);

		try {
			$profiles = $this->_gapi->getAllProfiles();
		} catch (Zend_Exception $e) {

			$session->__unset('oauthToken');
			$session->__unset('refreshToken');
			$session->__unset('googleUserId');

			Zend_Debug::dump($e);

			//$this->_helper->redirector('register');
		}

		if (count($profiles) > 0) {
			$form->setProfiles($profiles);
			$form->getElement('profiles')
				->setAttrib('disable', array_keys($this->_connector->getProfiles($this->_user->id)));			

		} else {
			$this->_helper->getHelper('FlashMessenger')->addMessage('error|google.analytics.register.noProfilesFound');
		}

		$this->view->form = $form;
		$this->view->userToConnector = $userToConnector;
		$this->view->user = $this->_user;
		$this->view->inviteForm = $inviteForm;
	}

	public function logoutAction()
	{
		$session = new Zend_Session_Namespace('GoogleAnalyticsForm');
		
		$session->__unset('oauthToken');
		$session->__unset('refreshToken');

		//$returnUrl = $this->_baseUrl . '/google-analytics/account';

		//@FIXME: maybe logout via AJAX and then redirect to /google-analytics/account
		/*
		$client = new Zend_Http_Client('https://www.google.com/accounts/logout');		
		$client->setMethod('GET');		
		$response = $client->request();
		 *
		 */

		
		$this->_redirect('https://accounts.google.com/o/logout?continue=https://accounts.google.com/o/oauth2/auth?response_type%3Dcode%26scope%3Dhttps://www.googleapis.com/auth/analytics.readonly%2Bhttps://www.googleapis.com/auth/userinfo.profile%26access_type%3Doffline%26redirect_uri%3D'.$this->_baseUrl.'/google-analytics/register%26approval_prompt%3Dforce%26client_id%3D'.$config->google->oauth->id.'%26hl%3Dcs');
		//%26from_login%26as%3D-3124dfe953c17dd7
	}

	protected function _clearSession()
	{
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();

		session_destroy();
	}

}