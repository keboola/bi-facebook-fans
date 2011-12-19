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
		$config = Zend_Registry::get('config');
		$utility = new Zend_Oauth_Http_Utility();
		$session = new Zend_Session_Namespace('GoogleAnalyticsForm');
		$this->_gapi = new App_GoogleAnalytics();

		$redirectUri = urlencode($this->_baseUrl.'/google-analytics/register');

		if (!isset($session->oauthToken)) {
			
			// Login With Google Account
			if ($this->_request->getParam('code')) {
				
				$res = $this->_gapi->getAccessToken($this->_request->code);

				//$session->oauthToken = $res['access_token'];
				//$session->refreshToken = $res['refresh_token'];

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
		$_u = new Model_User();
		$_p = new Model_Profile();

		$profiles = $this->_gapi->getAllProfiles();
		if (count($profiles) > 0) {
			$form->setProfiles($profiles);
		} else {
			$this->_helper->getHelper('FlashMessenger')->addMessage('error|google.analytics.register.noAccountsFound');
		}

		$request = $this->getRequest();

		if ($request->isPost() && $form->isValid($request->getParams())) {

			// Authenticate user and get profile list
			$email = $request->getParam('email');
			$row = $_u->fetchRow(array('gdEmail=?' => $email));

			$data = array(
				'gdEmail'			=> $email,
				'gaAccessToken'		=> $session->oauthToken
			);

			if (isset($session->refreshToken)) {
				$data['gaRefreshToken'] = $session->refreshToken;
			}

			if (!$row) {
				$idUser = $_u->insert($data);
			} else {
				$_u->update($data, array('id=?' => $row->id));
				$idUser = $row->id;
			}

			$this->_connector->addProfilesToUser($idUser, $request->profiles);			

			$this->_helper->getHelper('FlashMessenger')->addMessage('success|google.analytics.register.success');
		} else {
			$this->view->form = $form;
		}

	}

}