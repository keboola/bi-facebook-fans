<?
/**
 * @author Jakub Matejka <jakub@keboola.com>
 */
class FacebookController extends App_Controller_Action
{
	/**
	 * Id of connector in connectors table
	 */
	const ID_CONNECTOR = 1;


	/**
	 * @var App_Connector_Facebook
	 */
	private $_connector;

	public function init()
	{
		parent::init();

		$this->_connector = new App_Connector_Facebook();
	}

	/**
	 * @return void
	 */
	public function registerAction()
	{
		$_pp = new Model_PricePlans();
		$_uc = new Model_UsersToConnectors();
		$ns = new Zend_Session_Namespace('FacebookForm');

		$form = new Form_FacebookSetup();
		$form->getElement('pages')->removeDecorator('Label');

		$pageUrl = urlencode($this->_baseUrl.'/facebook/register');
		$userToConnector = $_uc->fetchRow(array('idUser=?' => $this->_user->id, 'idConnector=?' => self::ID_CONNECTOR));

		// Complete pages registration
		if ($this->_request->isPost()) {
			if(!empty($ns->pages)) {
				$form->getElement('pages')->setMultiOptions($ns->pages);
			}
			if (!empty($ns->knownPages)) {
				$form->getElement('pages')->setAttrib('disable', $ns->knownPages);
			}

			if ($form->isValid($this->_request->getParams())) {
				$accounts = array();
				foreach($this->_request->pages as $p) {
					$accounts[$p] = array('name' => $ns->pages[$p], 'token' => $ns->pageTokens[$p]);
				}

				// add accounts and their references to users to database of Facebook connector
				$this->_connector->addAccountsToUser($this->_user->id, $ns->idFB, $accounts);

				if(!$this->_user->export) {
					$this->_user->export = 1;
					$this->_user->save();
				}

				$this->_helper->getHelper('FlashMessenger')->addMessage('facebook.register.success');
				$this->_helper->redirector('index', 'index');
				return;
			} else {

			}

			$form->populate($this->_request->getParams());
			$this->view->form = $form;
			return;
		}

		// Facebook authorization
		if (empty($this->_request->code)) {
			$ns->state = md5(uniqid(rand(), TRUE)); //CSRF protection
			$dialogUrl = "http://www.facebook.com/dialog/oauth?client_id=" . $this->_config->facebook->appId
				. "&scope=offline_access,read_insights,email,manage_pages&redirect_uri=" . $pageUrl
				. "&state=" . $ns->state;
			$this->_redirect($dialogUrl);
			return;
		}

		// show registration form
		if ($this->_request->state == $ns->state) {
			$tokenUrl = "https://graph.facebook.com/oauth/access_token?client_id=" . $this->_config->facebook->appId
				. "&redirect_uri=" . $pageUrl . "&client_secret=" . $this->_config->facebook->appSecret
				. "&code=" . $this->_request->code;
			$response = file_get_contents($tokenUrl);
			$params = null;
			parse_str($response, $params);

			if (!empty($params['access_token'])) {
				$gd = new App_Facebook(null, $params['access_token']);

				$userInfo = $gd->request('me');
				if ($userInfo) {

					$ns->idFB = $userInfo['id'];
					$ns->pageTokens = array();
					$pages = array();
					$knownPages = array();
					$pagesList = $gd->request('/me/accounts');
					if($pagesList && isset($pagesList['data'])) {
						foreach($pagesList['data'] as $p) {
							if ($p['category'] != 'Application') {
								$pages[$p['id']] = $p['name'];

								if ($this->_connector->isKnownUserToAccount($this->_user->id, $p['id']))
									$knownPages[] = $p['id'];
								else
									$ns->pageTokens[$p['id']] = $p['access_token'];
							}
						}
					}

					if(!count($pages)) {
						$this->view->message = 'noPages';
						$form = null;
					} else {
						$ns->pages = $pages;
						$form->getElement('pages')->setMultiOptions($pages);
						$ns->knownPages = $knownPages;
						if(count($knownPages)) {
							$form->getElement('pages')->setAttrib('disable', $knownPages);
							$form->getElement('pages')->setValue($knownPages);
						}
					}
				} else {
					$this->view->message = 'apiError';
				}
			} else {
				$this->view->message = 'apiError';
			}

			$logoutLink = '<a href="https://www.facebook.com/logout.php?next='.$pageUrl
				.'&access_token='.$params['access_token'].'">'
				.$this->view->translate('facebook.register.facebookLogin').'</a>';
			$form->getElement('pages')->setDescription($logoutLink);
			$form->getElement('submit')->setDecorators(array(
				array('viewScript', array('viewScript' => 'helpers/facebookButtons.phtml'))
			));

			$this->view->form = $form;
			$this->view->pageUrl = $this->_baseUrl.'/facebook/register';
			$this->view->pricePlans = $_pp->fetchAll(null, 'accountsCount ASC');

			$this->view->userToConnector = $userToConnector;
			$this->view->userAccounts = $this->_connector->accounts($this->_user->id);
		} else {
			$this->view->message = 'csrfError';
		}
	}

	public function subscribeAction()
	{
		$_utc = new Model_UsersToConnectors();
		$_oh = new Model_OrdersHistory();

		if(!empty($this->_request->tx)) {

			$request = curl_init();
			curl_setopt_array($request, array(
				CURLOPT_URL => 'https://www.sandbox.paypal.com/cgi-bin/webscr',
				CURLOPT_POST => TRUE,
				CURLOPT_POSTFIELDS => http_build_query(array(
					'cmd' => '_notify-synch',
					'tx' => $this->_request->tx,
					'at' => 'qQNu_Sw5bfX2ry5bcujvEYQ5fuD7OwOXxItL_7alwEXZVs-vrEoUxb3rC3u',
				)),
				CURLOPT_RETURNTRANSFER => TRUE,
				CURLOPT_HEADER => FALSE,
			));
			$response = curl_exec($request);
			$status   = curl_getinfo($request, CURLINFO_HTTP_CODE);

			if($status == 200 AND strpos($response, 'SUCCESS') === 0) {
				$response = substr($response, 7);
				$response = urldecode($response);
				preg_match_all('/^([^=\s]++)=(.*+)/m', $response, $m, PREG_PATTERN_ORDER);
				$response = array_combine($m[1], $m[2]);
				if(isset($response['charset']) AND strtoupper($response['charset']) !== 'UTF-8') {
					foreach($response as $key => &$value) {
						$value = mb_convert_encoding($value, 'UTF-8', $response['charset']);
					}
					$response['charset_original'] = $response['charset'];
					$response['charset'] = 'UTF-8';
				}
				ksort($response);

				$verifier = explode('-', $response['custom']);
				if($verifier[2] == sha1($this->_user->id.$verifier[1].$this->_user->salt)) {

					$utcData = array(
						'idUser' => $this->_user->id,
						'idConnector' => self::ID_CONNECTOR,
						'idPlan' => $verifier[1],
						'paidUntil' => date('Y-m-d', strtotime('+7 days'))
					);
					$utc = $_utc->fetchRow(array('idUser=?' => $this->_user->id, 'idConnector=?' => self::ID_CONNECTOR));
					if(!$utc) {
						$_utc->insert($utcData);
					} else {
						$utc->setFromArray($utcData);
						$utc->save();
					}

					$_oh->insert(array(
						'idUser' => $this->_user->id,
						'idPlan' => $verifier[1],
						'price' => $response['payment_gross']
					));

					$this->_helper->getHelper('FlashMessenger')->addMessage('facebook.register.subscribed');
					$this->_helper->redirector('register');
					return;

				}

			} else {
				// Log the error, ignore it, whatever
				App_Debug::send(curl_error($request));
			}
		}

		$this->_helper->getHelper('FlashMessenger')->addMessage('facebook.register.confirmationFailed');
		$this->_helper->redirector('register');
	}

}
