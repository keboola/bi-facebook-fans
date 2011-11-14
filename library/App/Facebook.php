<?php
/**
 * Class to call Facebook Graph API
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-06-07
 */

class App_Facebook
{
	/**
	 * base url of Graph API
	 */
	const API_URL = 'https://graph.facebook.com/';

	/**
	 * @var Model_Account
	 */
	private $_account;

	/**
	 * @var array App tokens
	 */
	private $_tokens;

	/**
	 * @var int array position of used token in $this->_tokens
	 */
	private $_currentToken;

	/**
	 * @param $account
	 */
	public function __construct($account=null, $token=null)
	{
		$this->_account = $account;
		$this->_currentToken = 0;

		if ($account) {
			$_oa = new Model_OwnersAccounts();
			$this->_tokens = $_oa->fetchAll(array('idAccount=?' => $account->id));

			if (!count($this->_tokens)) {
				$this->invalidToken();
				return FALSE;
			}
		} else {
			$this->_token = $token;
		}
	}

	/**
	 * Solve invalid token situation
	 */
	public function invalidToken()
	{
		$c = Zend_Registry::get('config');

		if (isset($this->_tokens[$this->_currentToken])) {
			$_o = new Model_Owners();
			$token = $this->_tokens[$this->_currentToken];
			$owner = $_o->fetchRow(array('id=?' => $token->idOwner));

			if ($owner) {
				$validation = md5($owner->idGD);

				$html = new Zend_View();
				$html->setScriptPath(APPLICATION_PATH.'/vews/email/');
				$html->revalidationUrl = $c->app->url."/revalidate/index/id/".$token->idOwner.'/verify/'.$validation;
				$bodyHtml = $html->render("revalidation.phtml");

				$m = new Zend_Mail('utf8');
				$m->setFrom($c->app->email);
				$m->addTo($owner->email);
				$m->setSubject('Bad Facebook credentials for Keboola GoodData App');
				$m->setBodyHtml($bodyHtml);
				$m->send();
			}

			if ($this->_currentToken+1 < count($this->_tokens)) {
				// set new token
				$this->_currentToken++;
			} else {
				// invalidate account because we don't have valid token
				$this->_account->import = 0;
				$this->_account->save();
			}
		}
	}

	/**
	 * Make a call to Graph API
	 * @param string $request url path without boundary slashes
	 * @param int $tries nu,ber of tries when API call fails
	 * @return array
	 */
	public function request($request, $tries=3)
	{
		if ($this->_account && !isset($this->_tokens[$this->_currentToken])) {
			throw new App_FacebookException('No valid FB oAuth token available.');
			return FALSE;
		}

		$url = self::API_URL . $request;
		$url .= strpos($url, '?') ? '&' : '?';
		if ($this->_account)
			$url .= 'access_token='.$this->_tokens[$this->_currentToken]->oauthToken;
		else
			$url .= 'access_token='.$this->_token;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$resultCurl = curl_exec($ch);
		$curlError = curl_error($ch);
		curl_close($ch);
		if ($resultCurl) {
			$result = Zend_Json::decode($resultCurl);
			if(isset($result['error'])) {
				if (isset($result['error']['type']) && $result['error']['type']=='OAuthException' && $this->_account) {
					$this->invalidToken();
					return $this->request($request, $tries);
				} else {
					if ($tries > 0) {
						App_Debug::log(array('Facebook error', $url, $result));
						sleep(5);
						return $this->request($request, $tries-1);
					} else {
						App_Debug::send(array('Facebook error', $url, $result));
						throw new App_FacebookException('There was an API error while calling '.$url.': '.$result['error']['message']);
					}
				}
			} else {
				return $result;
			}
		} else {
			if ($tries > 0) {
				App_Debug::log(array('cUrl error', $url, $curlError));
				sleep(5);
				return $this->request($request, $tries-1);
			} else {
				App_Debug::send(array('cUrl error', $url, $curlError));
				throw new App_FacebookException('There was an error while calling '.$url.': '.$curlError);
			}
		}
	}

	/**
	 * Make a call to Graph API
	 * @param string $path url path without boundary slashes
	 * @param string $period period of the statistics, one of: day|week|month
	 * @param string $since date in yyyy-mm-dd format
	 * @param string $until date in yyyy-mm-dd format
	 * @return array
	 */
	public function call($path, $period=null, $since=null, $until=null)
	{
		$path = $this->_account->idFB.'/'.$path;
		if ($period) {
			$path .= '/'.$period;
		}

		if ($since) {
			$path .= strpos($path, '?') ? '&' : '?';
			$path .= 'since='.$since;
		}
		if ($until) {
			$path .= strpos($path, '?') ? '&' : '?';
			$path .= 'until='.$until;
		}

		$result = $this->request($path);
		if (isset($result['data'][0])) {
			return $result['data'][0];
		} else {
			return $result;
		}
	}

	
}