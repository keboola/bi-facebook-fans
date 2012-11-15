<?php
/**
 * index controller
 *
 * @author Miroslav Cillik <miro@keboola.com>
 */
class IndexController extends App_Controller_Action
{

	public function indexAction()
	{
		$c = Zend_Registry::get('config');
		$accounTable = new Model_Accounts();
		$pageTable = new Model_Pages();
		$userTable = new Model_Users();
		$pagesUsersTable = new Model_PagesUsers();

		$ns = new Zend_Session_Namespace('Facebook');
		$baseUrl = urlencode($this->_baseUrl.'/');

		$signedRequest = App_Facebook::loadSignedRequest($this->getRequest()->getParam('signed_request'));

		// Save pageId for redirect to app on this page after auth
		if (isset($signedRequest['page'])) {
			$ns->pageIdFB = $signedRequest['page']['id'];
			$ns->isAdmin = $signedRequest['page']['admin'];
		}		

		//ladybug_dump_die($signedRequest); die;

		if (empty($signedRequest['user_id'])) {

			if (empty($this->_request->code)) {
				$ns->state = md5('tajnyhash'); // CSRF protection
				$dialogUrl = App_Facebook::authorizationUrl($baseUrl, $ns->state);

				$this->_redirect($dialogUrl);
				//echo("<script> top.location.href='" . $dialogUrl . "'</script>");
			}

			if ($this->_request->state == $ns->state) {

				$accessToken = App_Facebook::accessToken($baseUrl, $this->_request->code);

				if (!empty($accessToken)) {

					$ns->accessToken = $accessToken;

					// Get App URL and redirect
					$fb = new App_Facebook($accessToken);
					$pageInfo = $fb->request($ns->pageIdFB);
					$pageLink = $pageInfo['link'];

					$this->_redirect($pageLink . '?sk=app_' . $c->facebook->appId);
				}
			}
		} else {
			// User has authorized our app
			// Client token
			$accessToken = $signedRequest['oauth_token'];
			$fb = new App_Facebook($accessToken);

			// Obtain longliving token
			$newToken = $fb->exchangeToken();

			$userInfo = $fb->request('me');

			$idUser = $userTable->save(array(
				'idFB'	=> $userInfo['id'],
				'name'	=> $userInfo['name'],
				'email'	=> $userInfo['email'],
				'accessToken'	=> $newToken
			));

			//Save Page details and relation
			$pageInfo = $fb->request($ns->pageIdFB);
			$pageData = array(
				'idFB'	=> $pageInfo['id'],
				'name'	=> $pageInfo['name'],
				'category'	=> $pageInfo['category']
			);

			$pageId = $pageTable->save($pageData);
			//$page = $pageTable->fetchRow(array('idFB=?' => $ns->pageIdFB));

			$pagesUsersTable->save(array(
				'idPage'	=> $pageId,
				'idUser'	=> $idUser,
				'isAdmin'	=> isset($ns->isAdmin)?1:0
			));
		}		

		$this->view->isAdmin = $ns->isAdmin;
	}

	/**
	 * This action can be used to add our app to Facebook Page
	 */
	public function addToPage()
	{
		// App was added to new page
		if ($this->_request->getParam('tabs_added')) {
			$pages = $this->_request->getParam('tabs_added');
			foreach($pages as $k=>$v) {

				$pageTable->save(array(
					'idFB'	=> $k
				));
			}
		}
	}

}
