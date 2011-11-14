<?
class App_View_Helper_LoggedInAs extends Zend_View_Helper_Abstract
{
	public function loggedInAs ()
	{
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
			$username = $auth->getIdentity()->email;
			$logoutUrl = $this->view->url(array('controller'=>'auth', 'action'=>'logout'), null, true);
			return '<span class="text">' . $this->view->translate('layout.loggedInAs') . '</span> '
				. '<a class="logout" href="' . $logoutUrl.'">'.$this->view->translate('layout.logout').'</a><br />'
				. '<strong class="email">' . $username . '</strong>';
		}

		$request = Zend_Controller_Front::getInstance()->getRequest();
		$controller = $request->getControllerName();
		$action = $request->getActionName();
		if($controller == 'auth' && $action == 'index') {
			return '';
		}
		$loginUrl = $this->view->url(array('controller'=>'auth', 'action'=>'index'));
		return '<a href="'.$loginUrl.'">'.$this->view->translate('layout.login').'</a>';
	}
}