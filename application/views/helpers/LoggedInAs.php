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

		return '';
	}
}