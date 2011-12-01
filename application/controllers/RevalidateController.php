<?
	/**
	 * @author Jakub Matejka <jakub@keboola.com>
	 */
class RevalidateController extends App_Controller_Action
{

	public function init()
	{
		parent::init();
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->setLayout('simple');
	}

	public function facebookAction()
	{
		$ns = new Zend_Session_Namespace('FacebookRevalidate');
		$pageUrl = urlencode($this->_baseUrl.'/revalidate/facebook/id/'.$this->_request->id.'/verify/'.$this->_request->verify);

		if (!empty($this->_request->id) && !empty($this->_request->verify)) {
			$_u = new Model_Users();
			$user = $_u->fetchRow(array('id=?' => $this->_request->id));

			if ($user) {
				$_cf = new App_Connector_Facebook();

				$accounts = $_cf->accounts($user->id);

				if ($this->_request->verify == md5($user->idGD)) {

					if (empty($this->_request->code)) {
						$ns->state = md5(uniqid(rand(), TRUE)); //CSRF protection
						$dialogUrl = "http://www.facebook.com/dialog/oauth?client_id=" . $this->_config->facebook->appId
							. "&scope=offline_access,read_insights,email,manage_pages&redirect_uri=" . $pageUrl
							. "&state=" . $ns->state;
						$this->_redirect($dialogUrl);
						return;
					}

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
								$fbPages = array();
								$this->view->revalidatedPages = array();
								$pagesList = $gd->request('/me/accounts');
								if($pagesList && isset($pagesList['data'])) {
									foreach($pagesList['data'] as $p) {
										if ($p['category'] != 'Application') {
											$fbPages[$p['id']] = array('name' => $p['name'], 'token' => $p['access_token']);
										}
									}

									foreach($accounts as $id => $name) {
										$account = $_cf->account($id);
										if (isset($fbPages[$account['idFB']])) {
											$_cf->saveNewToken($user->id, $id, $fbPages[$account['idFB']]['token']);

											$this->view->revalidatedPages[] = $fbPages[$account['idFB']]['name'];
										}
									}

									$this->_helper->getHelper('FlashMessenger')->addMessage('success|facebook.register.revalidateSuccess');
								} else {
									$logoutUrl = 'https://www.facebook.com/logout.php?next='.urlencode($pageUrl).'&access_token='.$params['access_token'];
									$this->_helper->getHelper('FlashMessenger')->addMessage('error|facebook.register.badFBLogin', $logoutUrl);
								}

							} else {
								$this->_helper->getHelper('FlashMessenger')->addMessage('error|facebook.register.apiError');
							}
						} else {
							$this->_helper->getHelper('FlashMessenger')->addMessage('error|facebook.register.apiError');
						}
					} else {
						$this->_helper->getHelper('FlashMessenger')->addMessage('error|facebook.register.csrfError');
					}
					$this->_helper->redirector('login', 'auth');
					return;
				}
			}
		}

		$this->_helper->getHelper('FlashMessenger')->addMessage('error|facebook.register.badUrl');
		$this->_helper->redirector('login', 'auth');
	}
}