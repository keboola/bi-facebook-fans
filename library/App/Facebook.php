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

					// First try exchangig access token
					$this->exchangeToken();

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
			. "&scope=offline_access,email,user_status&redirect_uri=" . $callBack
			. "&state=" . $csrf;
	}

	/**
	 * Exchanges expired token for new one
	 * @return string New Access Token
	 */
	public function exchangeToken($tries = 3)
	{
		$c = Zend_Registry::get('config');
		$url = "https://graph.facebook.com/oauth/access_token?client_id=" . $c->facebook->appId
			. "&client_secret=" . $c->facebook->appSecret
			. "&grant_type=fb_exchange_token"
			. "&fb_exchange_token=" . $this->_token;

		$client = new Zend_Http_Client($url);		
		$client->setMethod('GET');
		$response = $client->request();

		if (substr($response->getBody(), 0, 13) == 'access_token=') {
			$bodyArr = explode('&', $response->getBody());
			$this->_token = substr($bodyArr[0], 13);
			return $this->_token;
		} else {
			if ($tries > 0) {
				App_Debug::log(array('Facebook error', $url, $response));
				sleep(5);
				return $this->exchangeToken($tries-1);
			} else {
				App_Debug::send(array('Facebook error', $url, $response));
				throw new App_FacebookException('There was an API error while calling '.$url.': '.$response);
			}
		}
	}

	/**
	 * @static
	 * @param $callBack
	 * @param $code
	 */
	public static function accessToken($callBack, $code, $tries=3)
	{
		$c = Zend_Registry::get('config');
		$url = "https://graph.facebook.com/oauth/access_token?client_id=" . $c->facebook->appId
			. "&redirect_uri=" . $callBack . "&client_secret=" . $c->facebook->appSecret
			. "&code=" . $code;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$resultCurl = curl_exec($ch);
		$curlError = curl_error($ch);

		if ($resultCurl) {
			if(substr($resultCurl, 0, 13) == 'access_token=') {
				if (strstr($resultCurl, '&')) {
					$tmpArr = explode('&', $resultCurl);
					$resultCurl = $tmpArr[0];
				}
				return substr($resultCurl, 13);
			} else {
				if ($tries > 0) {
					App_Debug::log(array('Facebook error', $url, $resultCurl));
					sleep(5);
					return self::accessToken($callBack, $code, $tries-1);
				} else {
					App_Debug::send(array('Facebook error', $url, $resultCurl));
					throw new App_FacebookException('There was an API error while calling '.$url.': '.$resultCurl);
				}
			}
		} else {
			if ($tries > 0) {
				App_Debug::log(array('cUrl error', $url, $curlError));
				sleep(5);
				return self::accessToken($callBack, $code, $tries-1);
			} else {
				App_Debug::send(array('cUrl error', $url, $curlError));
				throw new App_FacebookException('There was an error while calling '.$url.': '.$curlError);
			}
		}


	}

	public static function base64UrlDecode($input) {
		return base64_decode(strtr($input, '-_', '+/'));
	}

	/**
	 * Decodes signed request obtained when user authenticate and authorize FB app
	 *
	 * @param <type> $signedRequest - encoded JSON object
	 * @return <type>
	 */
	public static function loadSignedRequest($signedRequest) {
		$c = Zend_Registry::get('config');

		list($signature, $payload) = explode('.', $signedRequest, 2);
		$data = json_decode(self::base64UrlDecode($payload), true);

		if (isset($data['issued_at']) && $data['issued_at'] > time() - 86400 &&
			self::base64UrlDecode($signature) == hash_hmac('sha256', $payload, $c->facebook->appSecret, $raw=true)) {

				return $data;
		}
	}
	
}