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

	public function indexAction()
	{
		$this->_helper->layout->setLayout('simple');
		if($this->_request->iframe==1) {
			$this->view->iframe = TRUE;
		}
	}

	public function choosePlanAction()
	{
		if ($this->_request->order=='trial') {
			$_utc = new Model_UsersToConnectors();
			$_oh = new Model_OrdersHistory();

			$utc = $_utc->fetchRow(array('idUser=?' => $this->_user->id, 'idConnector=?' => self::ID_CONNECTOR));
			if(!$utc) {
				$_utc->insert(array(
					'idUser' 			=> $this->_user->id,
					'idConnector' 		=> self::ID_CONNECTOR,
					'idPlan'			=> 1,
					'idSubscription'	=> null,
					'paidUntil'			=> date('Y-m-d', strtotime('+7 days'))
				));

				$_oh->insert(array(
					'idUser'	=> $this->_user->id,
					'idPlan'	=> 1,
					'price'		=> 0
				));

				if(!$this->_user->export) {
					$this->_user->export = 1;
					$this->_user->save();
				}

				$this->_helper->getHelper('FlashMessenger')->addMessage('success|facebook.register.subscribed');
				$this->_helper->redirector('register');
			}
		}
	}

	public function disableAction()
	{
		if(!empty($this->_request->id)) {
			$_i = new Model_Invitations();
			$i = $_i->fetchRow(array('id=?' => $this->_request->id));
			if($i) {
				$_g = new App_GoodDataService($this->_config->gooddata->username, $this->_config->gooddata->password);
				$_g->disableUser($this->_user->idGD, $i->email);
				//@TODO
				die();
				$this->_helper->getHelper('FlashMessenger')->addMessage('error|facebook.disable.success');
				$this->_helper->redirector('register');
			}
		}

		$this->_helper->getHelper('FlashMessenger')->addMessage('error|facebook.disable.failed');
		$this->_helper->redirector('register');
	}


	public function registerAction()
	{
		$_pp = new Model_PricingPlans();
		$_uc = new Model_UsersToConnectors();
		$ns = new Zend_Session_Namespace('FacebookForm');

		$facebookSetupForm = new Form_FacebookSetup();
		$facebookSetupForm->getElement('pages')->removeDecorator('Label');

		$inviteForm = new Form_Invite();

		$pageUrl = urlencode($this->_baseUrl.'/facebook/register');
		$userToConnector = $_uc->fetchRow(array('idUser=?' => $this->_user->id, 'idConnector=?' => self::ID_CONNECTOR));

		if (!$userToConnector) {
			$this->_helper->redirector('choose-plan');
			return;
		}

		$subscribedPlan = $userToConnector->findParentRow('Model_PricingPlans');
		$userAccounts = $this->_connector->accounts($this->_user->id);

		$paidAccountsCount = $subscribedPlan->accountsCount;
		$usedAccountsCount = count($userAccounts);
		$remainingAccountsCount = $paidAccountsCount - $usedAccountsCount;

		$paidUsersCount = $subscribedPlan->usersCount;
		$usedInvitations = $userToConnector->findDependentRowset('Model_Invitations');

		// Complete pages registration
		if ($this->_request->isPost()) {

			// Add pages
			if ($this->_request->job=='register') {
				// Second check if jQuery validation fails - test no added pages or more added then subscribed
				if(!isset($this->_request->pages)) {
					$this->_helper->getHelper('FlashMessenger')->addMessage('error|facebook.register.noPagesAdded');
					$this->_helper->redirector('register');
					return;
				} else {
					if (count($this->_request->pages) > $remainingAccountsCount) {
						$this->_helper->getHelper('FlashMessenger')->addMessage('error|facebook.register.manyPagesAdded');
						$this->_helper->redirector('register');
						return;
					}
				}


				if(!empty($ns->pages)) {
					$facebookSetupForm->getElement('pages')->setMultiOptions($ns->pages);
				}
				if (!empty($ns->knownPages)) {
					$facebookSetupForm->getElement('pages')->setAttrib('disable', $ns->knownPages);
				}

				if ($facebookSetupForm->isValid($this->_request->getParams())) {
					$userAlreadyHasAccounts = $this->_user->idGD && count($this->_connector->accounts($this->_user->id));

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

					if($userAlreadyHasAccounts) {
						$this->_helper->getHelper('FlashMessenger')->addMessage('success|facebook.register.success', 'https://secure.gooddata.com/#s=/gdc/projects/'.$this->_user->idGD.'|projectDashboardPage');
					} else {
						$this->_helper->getHelper('FlashMessenger')->addMessage('success|facebook.register.successFirst');
					}
					$this->_helper->redirector('register');
					return;
				}

				$facebookSetupForm->populate($this->_request->getParams());
			}

			// send invitation
			if ($this->_request->job=='invite') {
				if ($inviteForm->isValid($this->_request->getParams())) {
					$usedInvitationsCount = count($usedInvitations)+1;
					$remainingInvitationsCount = $paidUsersCount-$usedInvitationsCount;

					if ($userToConnector && $remainingInvitationsCount > 0) {

						$_i = new Model_Invitations();
						$i = $_i->fetchRow(array('idUserConnector=?' => $userToConnector->id, 'email=?' => $this->_request->email));
						if (!$i && $this->_request->email != $this->_user->email) {
							$_i->insert(array(
								'idUserConnector'	=> $userToConnector->id,
								'email'				=> $this->_request->email,
								'role'				=> $this->_request->role,
								'text'				=> $this->_request->text
							));
							$this->_helper->getHelper('FlashMessenger')->addMessage('success|facebook.register.invitationSent');
						} else {
							$this->_helper->getHelper('FlashMessenger')->addMessage('error|facebook.register.invitationExists');
						}
					} else {
						$this->_helper->getHelper('FlashMessenger')->addMessage('error|facebook.register.noRemainingInvitations');
					}

					$this->_helper->redirector('register');
					return;
				}

				$inviteForm->populate($this->_request->getParams());
			}
		} else {

			// Facebook authorization
			if (empty($this->_request->code)) {
				$ns->state = md5($this->_user->salt); //CSRF protection
				$dialogUrl = App_Facebook::authorizationUrl($pageUrl, $ns->state);
				$this->_redirect($dialogUrl);
				return;
			}
	
			// show registration form
			if ($this->_request->state == $ns->state) {
				$accessToken = App_Facebook::accessToken($pageUrl, $this->_request->code);
	
				$logoutUrl = 'https://www.facebook.com/logout.php?next='.$pageUrl.'&access_token='.$accessToken;
	
				if (!empty($accessToken)) {
					$gd = new App_Facebook(null, $accessToken);
	
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
							$this->_helper->getHelper('FlashMessenger')->addMessage('error|'.$this->view->translate('facebook.register.noPages', $logoutUrl));
						} else {
							$ns->pages = $pages;
							$facebookSetupForm->getElement('pages')->setMultiOptions($pages);
							$ns->knownPages = $knownPages;
							if(count($knownPages)) {
								$facebookSetupForm->getElement('pages')->setAttrib('disable', $knownPages);
								$facebookSetupForm->getElement('pages')->setValue($knownPages);
							}
						}
	
						$facebookSetupForm->getElement('pages')->setDescription($this->view->translate('facebook.register.facebookLogin',
							$userInfo['email'], $logoutUrl));
	
					} else {
						$this->_helper->getHelper('FlashMessenger')->addMessage('error|facebook.register.apiError');
					}
				} else {
					$this->_helper->getHelper('FlashMessenger')->addMessage('error|facebook.register.apiError');
				}
			} else {
				$this->_helper->getHelper('FlashMessenger')->addMessage('error|facebook.register.csrfError');
			}
		}

		$this->view->inviteForm = $inviteForm;
		$this->view->facebookSetupForm = $facebookSetupForm;
		$this->view->pageUrl = $this->_baseUrl.'/facebook/register';

		$this->view->pricingPlans = $_pp->fetchAll(null, 'accountsCount ASC');
		$this->view->subscribedPlan = $subscribedPlan;

		$this->view->userToConnector = $userToConnector;
		$this->view->paidAccountsCount = $paidAccountsCount;
		$this->view->userAccounts = $userAccounts;
		$this->view->paidUsersCount = $paidUsersCount;
		$this->view->usedInvitations = $usedInvitations;
	}


	public function subscribeAction()
	{
		$_utc = new Model_UsersToConnectors();
		$_oh = new Model_OrdersHistory();

		if(!empty($this->_request->tx)) {

			$request = curl_init();
			curl_setopt_array($request, array(
				CURLOPT_URL => 'https://www.paypal.com/cgi-bin/webscr',
				CURLOPT_POST => TRUE,
				CURLOPT_POSTFIELDS => http_build_query(array(
					'cmd' => '_notify-synch',
					'tx' => $this->_request->tx,
					'at' => $this->_config->paypal->pdt,
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
				if($verifier[0] == $this->_user->id && $verifier[2] == sha1($this->_user->id.$verifier[1].$this->_user->salt)) {

					$utcData = array(
						'idUser' => $this->_user->id,
						'idConnector' => self::ID_CONNECTOR,
						'idPlan' => $verifier[1],
						'idSubscription' => $response['subscr_id'],
						'paidUntil' => date('Y-m-d', strtotime('+7 days'))
					);
					$utc = $_utc->fetchRow(array('idUser=?' => $this->_user->id, 'idConnector=?' => self::ID_CONNECTOR));
					if(!$utc) {
						$_utc->insert($utcData);
						$this->_helper->getHelper('FlashMessenger')->addMessage('success|facebook.register.subscribed');
					} else {
						$utcData['paidUntil'] = date('Y-m-d', strtotime('+1 month'));

						// Suspend previous subscription
						require_once 'PayPal/CallerService.php';
						$nvpStr="&PROFILEID={$utc->idSubscription}&ACTION=Suspend";
						$resArray = hash_call("ManageRecurringPaymentsProfileStatus", $nvpStr);

						$ack = strtoupper($resArray["ACK"]);
						if($ack == 'SUCCESS') {
							$utc->setFromArray($utcData);
							$utc->save();
						} else {
							App_Debug::send(array(
								'error' => 'PayPal suspend subscription '.$utc->idSubscription.' for user '.$this->_user->email,
								'data' => $resArray
							));
							$this->_helper->getHelper('FlashMessenger')->addMessage('error|'.$this->view->translate('facebook.register.subscribedTwice',
								$this->_config->paypal->url.'cmd=_subscr-find&alias='.$this->_config->paypal->merchantId));
							$this->_helper->redirector('register');
							return;
						}
						$this->_helper->getHelper('FlashMessenger')->addMessage('success|facebook.register.subscriptionChanged');
					}

					$_oh->insert(array(
						'idUser' => $this->_user->id,
						'idPlan' => $verifier[1],
						'price' => $response['payment_gross']
					));

					if(!$this->_user->export) {
						$this->_user->export = 1;
						$this->_user->save();
					}

					$this->_helper->redirector('register');
					return;

				}

			} else {
				App_Debug::send(curl_error($request));
			}
		}

		$this->_helper->getHelper('FlashMessenger')->addMessage('error|facebook.register.confirmationFailed');
		$this->_helper->redirector('register');
	}

	public function generateTokenAction()
	{

		if (!empty($this->_request->plan))
			$this->_helper->json(array(
				'status' => 'ok',
				'token' => $this->_user->id.'-'.$this->_request->plan.'-'.sha1($this->_user->id.$this->_request->plan.$this->_user->salt)
			));
		else
			$this->_helper->json(array(
				'status' => 'noPlan'
			));
	}

}