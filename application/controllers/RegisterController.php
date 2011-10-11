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

	/**
	 * @return void
	 */
	public function indexAction()
	{
		$form = new Form_AddPage();
		$ns = new Zend_Session_Namespace('RegisterForm');
		$_a = new Model_Accounts();
		$_p = new Model_Pages();

		if ($this->_request->isPost()) {
			if(!empty($ns->pages)) {
				$form->getElement('pages')->setMultiOptions($ns->pages);
			}
			if (!empty($ns->knownPages)) {
				$form->getElement('pages')->setAttrib('disable', $ns->knownPages);
			}

			if ($form->isValid($this->_request->getParams())) {
				foreach($this->_request->pages as $p) {
					$a = $_a->fetchRow(array('email=?' => $this->_request->email));
					if (!$a) {
						$idAccount = $_a->insert(array(
								'email' => $this->_request->email,
								'idFB' => $this->_request->idUser,
								'idGD' => $this->_request->idProject,
							));
						$a = $_a->fetchRow(array('id=?' => $idAccount));
					}

					$_p->insert(array(
						'idAccount' => $a->id,
						'name' => $this->_request->name,
						'idFB' => $p,
						'token' => $ns->pageTokens[$p]
					));
					$this->view->message = 'success';
					$this->view->form = null;
					return;
				}
			} else {
				
			}
			
			$form->populate($this->_request->getParams());
			$this->view->form = $form;
			return;
		}

		if (empty($this->_request->code)) {
			$_SESSION['state'] = md5(uniqid(rand(), TRUE)); //CSRF protection
			$dialogUrl = "http://www.facebook.com/dialog/oauth?client_id=" . $this->_config->facebook->appId
						 . "&scope=offline_access,read_insights,email,manage_pages&redirect_uri=" . urlencode($this->_pageUrl)
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

			if (!empty($params['access_token'])) {
				$gd = new App_Facebook(null, $params['access_token']);

				$userInfo = $gd->request('me');
				if ($userInfo) {
					$form->getElement('idUser')->setValue($userInfo['id']);
					$form->getElement('email')->setValue($userInfo['email']);
					$form->getElement('fbToken')->setValue($params['access_token']);

					$ns->pageTokens = array();
					$pages = array();
					$knownPages = array();
					$pagesList = $gd->request('/me/accounts');
					if($pagesList && isset($pagesList['data'])) {
						foreach($pagesList['data'] as $p) {
							if ($p['category'] != 'Application') {
								$pages[$p['id']] = $p['name'];

								$kp = $_p->fetchRow(array('idFB=?' => $p['id']));
								if($kp) {
									$knownPages[] = $p['id'];
								} else {
									$ns->pageTokens[$p['id']] = $p['access_token'];
								}
							}
						}
					}
					
					if(!count($pages)) {
						$this->view->message = 'noPages';
						$this->view->logoutUrl = 'https://www.facebook.com/logout.php?next='.urlencode($this->_pageUrl).'&access_token='.$params['access_token'];
						$form = null;
					} else {
						$ns->pages = $pages;
						$form->getElement('pages')->setMultiOptions($pages);
						if(count($knownPages)) {
							$form->getElement('pages')->setAttrib('disable', $knownPages);
							$ns->knownPages = $knownPages;
						}
					}
				} else {
					$this->view->message = 'apiError';
				}
			} else {
				$this->view->message = 'apiError';
			}

			$this->view->form = $form;
		} else {
			$this->view->message = 'csrfError';
		}
	}
}
