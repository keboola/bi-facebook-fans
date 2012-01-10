<?php
/**
 * @author Jakub Matejka <jakub@keboola.com>
 */
class App_Email
{
	/**
	 * @param $email
	 * @param $subject
	 * @param $viewScript
	 * @param array|null $viewParams
	 */
	public static function send($email, $subject, $viewScript, array $viewParams=null)
	{
		$c = Zend_Registry::get('config');
		$t = Zend_Registry::get('Zend_Translate');

		$html = new Zend_View();
		$html->setScriptPath(APPLICATION_PATH.'/views/emails/');
		if(count($viewParams)) foreach($viewParams as $k => $v) {
			$html->$k = $v;
		}
		$bodyHtml = $html->render($viewScript.'.phtml');		

		$m = new Zend_Mail();
		$m->addTo($email);
		$m->setFrom($c->app->email);
		$m->setSubject($c->app->name.' - '.$t->translate($subject));
		$m->setBodyHtml($bodyHtml);
		$m->send();
	}
}