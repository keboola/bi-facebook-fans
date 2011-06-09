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
	 * @param string $path url path without boundary slashes
	 * @param string $period period of the statistics, one of: day|week|month
	 * @param string $since date in yyyy-mm-dd format
	 * @param string $until date in yyyy-mm-dd format
	 * @return void
	 */
	public function call($path, $period=null, $since=null, $until=null)
	{
		$url = self::API_URL.$this->_pageId.'/'.$path;
		if ($period) {
			$url .= '/'.$period;
		}
		$url .= '?access_token='.$this->_appToken;
		if ($since) {
			$url .= '&since='.$since;
		}
		if ($until) {
			$url .= '&until='.$until;
		}
		$result = file_get_contents($url);
		if ($result) {
			$result = Zend_Json::decode($result);
			if (isset($result['data'][0]))
				return $result['data'][0];
			else
				return $result;
		} else
			return false;
	}

	
}