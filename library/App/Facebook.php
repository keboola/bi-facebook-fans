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
	 * @var int Page id
	 */
	private $_pageId;

	/**
	 * @var string App token
	 */
	private $_appToken;

	/**
	 * @param int $pageId
	 * @param string $appToken
	 */
	public function __construct($pageId, $appToken)
	{
		$this->_pageId = $pageId;
		$this->_appToken = $appToken;
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
		$url .= 'access_token='.$this->_appToken;

		ob_start();
		$result = file_get_contents($url);
		$output = ob_get_contents();
		ob_end_clean();

		if ($result) {
			return Zend_Json::decode($result);
		} else {
			App_Debug::send($output);
			if ($tries > 0) {
				sleep(5);
				return $this->call($request, $tries-1);
			} else {
				throw new App_FacebookException($output);
			}
		}
	}

	/**
	 * Make a call to Graph API
	 * @param string $path url path without boundary slashes
	 * @param string $period period of the statistics, one of: day|week|month
	 * @param string $since date in yyyy-mm-dd format
	 * @param string $until date in yyyy-mm-dd format
	 * @return void
	 */
	public function call($path, $period=null, $since=null, $until=null)
	{
		$path = $this->_pageId.'/'.$path;
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