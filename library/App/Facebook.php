<?php
/**
 * Class to call Facebook Graph API
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-06-07
 */
class App_FacebookException extends Exception
{

}

class App_Facebook
{
	/**
	 * base url of Graph API
	 */
	const API_URL = 'https://graph.facebook.com/';

	/**
	 * @var int array position of used token in $this->_tokens
	 */
	private $_token;

	/**
	 * @param $token
	 */
	public function __construct($token)
	{
		$this->_token = $token;
	}

		/**
	 * Make a call to Graph API
	 * @param string $request url path without boundary slashes
	 * @param int $tries nu,ber of tries when API call fails
	 * @return array
	 */
	public function request($request, $tries=3)
	{
		$url = self::API_URL . $request;
		$url .= strpos($url, '?') ? '&' : '?';
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
					sleep(10);
					return $this->request($request, $tries-1);
				} else {
					if ($tries > 0) {
						App_Debug::log(array('Facebook error', $url, $result));
						sleep(5);
						return $this->request($request, $tries-1);
					} else {
						App_Debug::send(array('BI-Service Facebook error', $url, $result));
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
				App_Debug::send(array('BI-Service cUrl error', $url, $curlError));
				throw new App_FacebookException('There was an error while calling '.$url.': '.$curlError);
			}
		}
	}


	/**
	 * @static
	 * @param $callBack
	 * @param $csrf
	 * @return string
	 */
	public static function authorizationUrl($callBack, $csrf)
	{
		$c = Zend_Registry::get('config');
		return "http://www.facebook.com/dialog/oauth?client_id=" . $c->facebook->appId
			. "&scope=offline_access,read_insights,email,manage_pages&redirect_uri=" . $callBack
			. "&state=" . $csrf;
	}

	/**
	 * @static
	 * @param $callBack
	 * @param $csrf
	 */
	public static function accessToken($callBack, $csrf, $tries=3)
	{
		$c = Zend_Registry::get('config');
		$url = "https://graph.facebook.com/oauth/access_token?client_id=" . $c->facebook->appId
			. "&redirect_uri=" . $callBack . "&client_secret=" . $c->facebook->appSecret
			. "&code=" . $csrf;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$resultCurl = curl_exec($ch);
		$curlError = curl_error($ch);

		if ($resultCurl) {
			if(substr($resultCurl, 0, 13) == 'access_token=') {
				return substr($resultCurl, 13);
			} else {
				if ($tries > 0) {
					App_Debug::log(array('Facebook error', $url, $resultCurl));
					sleep(5);
					return self::accessToken($callBack, $csrf, $tries-1);
				} else {
					App_Debug::send(array('Facebook error', $url, $resultCurl));
					throw new App_FacebookException('There was an API error while calling '.$url.': '.$resultCurl);
				}
			}
		} else {
			if ($tries > 0) {
				App_Debug::log(array('cUrl error', $url, $curlError));
				sleep(5);
				return self::accessToken($callBack, $csrf, $tries-1);
			} else {
				App_Debug::send(array('cUrl error', $url, $curlError));
				throw new App_FacebookException('There was an error while calling '.$url.': '.$curlError);
			}
		}


	}
	
}