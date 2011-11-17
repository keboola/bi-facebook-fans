<?php

class AuthController extends App_Controller_Action
{

    public function indexAction()
    {
		$this->_helper->layout->setLayout('simple');
        $form = new Form_Login();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                if ($this->_process($form->getValues())) {
                    // We're authenticated! Redirect to the home page
					$this->_helper->redirector('index', 'index');
                } else {
					$this->_helper->redirector('index', 'auth');
				}
            } else {
				$this->_helper->redirector('index');
			}

        }
        $this->view->form = $form;
    }

    protected function _process($values)
    {
        // Get our authentication adapter and check credentials
        $adapter = $this->_getAuthAdapter();
        $adapter->setIdentity($values['email']);
        $adapter->setCredential($values['password']);

        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($adapter);
        if ($result->isValid()) {
            $user = $adapter->getResultRowObject();
			if ($user->isActivated) {
            	$auth->getStorage()->write($user);
            	return TRUE;
			} else {
				$auth->clearIdentity();
				$this->_helper->getHelper('FlashMessenger')->addMessage('auth.login.notActivated');
				return FALSE;
			}
        } else {
			$auth->clearIdentity();
			$this->_helper->getHelper('FlashMessenger')->addMessage('auth.login.failed');
			return FALSE;
		}
    }

    protected function _getAuthAdapter()
    {

        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);

        $authAdapter->setTableName($this->_config->db->prefix.'users')
            ->setIdentityColumn('email')
            ->setCredentialColumn('password')
            ->setCredentialTreatment('SHA1(CONCAT(?,salt))');


        return $authAdapter;
    }

    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_helper->redirector('index');
    }

	public function registerAction()
	{
		$form = new Form_Register();
		$form->getElement('submit')->setDecorators(array(
			array('viewScript', array('viewScript' => 'helpers/registerButtons.phtml'))
		));
		$request = $this->getRequest();
		if ($request->isPost()) {
			if ($form->isValid($request->getPost())) {
				$salt = md5(date('YmdHis'));

				$_u = new Model_Users();
				$idUser = $_u->insert(array(
					'email' => $request->email,
					'password' => sha1($request->password.$salt),
					'salt' => $salt
				));

				$validation = md5($request->email.$salt);
				$url = $this->_config->app->url."/auth/activate/id/".$idUser.'/verify/'.$validation;

				App_Email::send($request->email, 'auth.activate.subject', 'activation', array('url' => $url));

				$this->_helper->getHelper('FlashMessenger')->addMessage('auth.register.registrationComplete');
				$this->_helper->redirector('index', 'auth');
			}
		}
		$this->view->form = $form;
	}

	/**
	 * Activate account
	 * @return void
	 */
	public function activateAction()
	{
		if(!empty($this->_request->id) && !empty($this->_request->verify)) {
			$_u = new Model_Users();
			$u = $_u->fetchRow(array('id = ?' => $this->_request->id));
			if ($u) {
				if ($u->isActivated) {
					$this->_helper->getHelper('FlashMessenger')->addMessage('auth.activate.alreadyActivated');
					$this->_helper->redirector('index', 'auth');
					return;
				} elseif($this->_request->verify == md5($u->email.$u->salt)) {
					$u->isActivated = 1;
					$u->save();
					$this->_helper->getHelper('FlashMessenger')->addMessage('auth.activate.success');
					$this->_helper->redirector('index', 'auth');
					return;
				}
			}
		}
		$this->_helper->getHelper('FlashMessenger')->addMessage('auth.activate.failed');
		$this->_helper->redirector('index', 'auth');
	}

	/**
	 * Reload captcha
	 * @return void
	 */
	public function refreshCaptchaAction()
	{
		$form = new Form_Register();
		$captcha = $form->getElement('captcha')->getCaptcha();

		$this->_helper->json(array(
			'id' => $captcha->generate(),
			'src' => $captcha->getImgUrl() . $captcha->getId() . $captcha->getSuffix()
		));
	}

	public function lostPasswordAction()
	{
		$form = new Form_LostPassword();
		$form->getElement('submit')->setDecorators(array(
			array('viewScript', array('viewScript' => 'helpers/registerButtons.phtml'))
		));

		$request = $this->getRequest();
		if ($request->isPost()) {
			if ($form->isValid($request->getPost())) {

				$_u = new Model_Users();

				$u = $_u->fetchRow(array('email=?' => $request->email));
				$u->changePasswordUntil = date('Y-m-d H:i:s', strtotime('+1 hour'));
				$u->save();

				$validation = md5($request->email.$u->salt);
				$url = $this->_config->app->url."/auth/new-password/id/".$u->id.'/verify/'.$validation;

				App_Email::send($request->email, 'auth.lost-password.subject', 'lostPassword', array('url' => $url));

				$this->_helper->getHelper('FlashMessenger')->addMessage('auth.lost-password.emailSent');
				$this->_helper->redirector('index', 'auth');
				return;
			}
		}
		$this->view->form = $form;
	}

	public function newPasswordAction()
	{
		$form = new Form_NewPassword();
		$request = $this->getRequest();
		if(!empty($request->id) && !empty($request->verify)) {
			$_u = new Model_Users();

			$u = $_u->fetchRow(array('id=?' => $request->id));
			if ($u && $u->changePasswordUntil && date('Y-m-d H:i:s') <= $u->changePasswordUntil) {
				if($request->verify == md5($u->email.$u->salt)) {

					if ($request->isPost()) {
						if ($form->isValid($request->getPost())) {
							$salt = md5(date('YmdHis'));

							$u->password = sha1($request->password.$salt);
							$u->salt = $salt;
							$u->changePasswordUntil = null;
							$u->save();

							$this->_helper->getHelper('FlashMessenger')->addMessage('auth.new-password.complete');
							$this->_helper->redirector('index', 'auth');
							return;
						}
					}
					$this->view->form = $form;
					return;
				}
			}
		}

		$this->_helper->getHelper('FlashMessenger')->addMessage('auth.new-password.failed');
		$this->_helper->redirector('index', 'auth');
	}


}





