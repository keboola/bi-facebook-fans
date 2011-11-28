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
				. '<a href="/account"class="right">' . $this->view->translate('layout.account') . '</a><br />'
				. '<a href="/account"><strong class="email">' . $username . '</strong></a>'
				. '<a class="right ico" href="' . $logoutUrl.'">'.$this->view->translate('layout.logout').'</a>';
		} else {
			return '<a class="right ico" href="/auth/login">'.$this->view->translate('layout.login').'</a>';
		}
	}
}