<?php
/**
 * GoogleAnalytics API wrapper
 *
 * handles OAuth 2.0 authorization and requests to the Google Analytics API
 *
 * Based on GAPI 1.3 by Stig Manning 2009
 * http://code.google.com/p/gapi-google-analytics-php-interface/
 *
 * @author Miroslav Čillík <miroslav.cillik@keboola.com>
 */
class App_GoogleAnalytics {

	// authentication variables
	//protected $_accessToken;
	//protected $_accessTokenSecret;
	protected $_utility;
	//protected $_oauthParams = array();
	//protected $_consumerSecret;
	protected $_protocol = 'OAuth';
	protected $_token;

	// request results variables
	protected $_dataParameters;
	protected $_dataAggregateMetrics;
	protected $_accountParameters;
	protected $_results;

	protected $_accounts = array();
	protected $_webProperties = array();
	protected $_profiles = array();

	//const http_interface = 'auto'; //'auto': autodetect, 'curl' or 'fopen'
	const ACCOUNTS_URL = 'https://www.googleapis.com/analytics/v3/management/accounts';
	const USER_INFO_URL = 'https://www.googleapis.com/oauth2/v1/userinfo';
	const DATA_URL = 'https://www.googleapis.com/analytics/v3/data/ga';
	const OAUTH_URL = 'https://accounts.google.com/o/oauth2/auth';
	const OAUTH_TOKEN_URL = 'https://accounts.google.com/o/oauth2/token';

	const DEV_MODE = true;

	/**
	 *
	 * @param string $consumerKey
	 * @param string $requestToken - authorized request token (from OpenID)
	 */
	public function __construct($token = null)
	{
		$this->_utility = new Zend_Oauth_Http_Utility();
		$this->_token = $token;
	}			

	/**
	 * Generate authentication header for all requests
	 *
	 * @return Array
	 */
	protected function _getAuthHeader()
	{
		switch ($this->_protocol) {
			case 'AuthSub':
				return $this->_authSubHeader();
				break;
			default: // OAuth 2.0
				return array(					
					'Authorization: Bearer ' . $this->_token
				);
				break;
		}
	}

	/**
	 * Headers for AuthSub requests
	 * @deprecated
	 * @return <type>
	 */
	protected function _authSubHeader()
	{
		return array('Authorization: AuthSub token="'.$this->_token.'"');
	}

	/**
	 * Request account data from Google Analytics,
	 * add web property and profiles for accounts
	 *
	 * @param Int $startIndex OPTIONAL: Start index of results
	 * @param Int $maxResults OPTIONAL: Max results returned
	 */
	public function getAccounts($startIndex=1, $maxResults=1000)
	{
		$get = array(
			'start-index' => $startIndex,
			'max-results'	=> $maxResults
		);

		$client = new Zend_Http_Client(App_GoogleAnalytics::ACCOUNTS_URL);
		$client->setHeaders(array(
			'Accept' => 'application/json',
			'Authorization'	=> 'Bearer ' . $this->_token
		));
		$client->setMethod('GET');
		$client->setParameterGet($get);
		$response = $client->request();

		$body = json_decode($response->getBody(), true);		

		if (isset($body['error'])) {
			throw new Zend_Exception("Error " . print_r($body), $body['code']);
		}
		if (isset($body['items'])) {
			return $body['items'];
		}
		return false;
	}

	/**
	 * Obtain basic user information
	 */
	public function getUserInfo()
	{
		$client = new Zend_Http_Client(App_GoogleAnalytics::USER_INFO_URL);
		$client->setHeaders(array(
			'Accept' => 'application/json',
			'Authorization'	=> 'Bearer ' . $this->_token
		));
		$client->setMethod('GET');
		$client->setParameterGet($get);
		$response = $client->request();

		$body = json_decode($response->getBody(), true);		

		if (isset($body['error'])) {
			throw new Exception("Error " . print_r($body), $body['code']);
		}
		
		return $body;
	}

	public function getWebProperties($accountId, $startIndex=1, $maxResults=1000)
	{
		$get = array(
			'start-index' => $startIndex,
			'max-results'	=> $maxResults
		);

		$client = new Zend_Http_Client(App_GoogleAnalytics::ACCOUNTS_URL . '/' . $accountId . '/webproperties');
		$client->setHeaders(array(
			'Accept' => 'application/json',
			'Authorization'	=> 'Bearer ' . $this->_token
		));
		$client->setMethod('GET');
		$client->setParameterGet($get);
		$response = $client->request();

		$body = json_decode($response->getBody(), true);

		if (isset($body['error'])) {
			throw new Exception("Error " . print_r($body), $body['code']);
		}
		if (isset($body['items'])) {
			return $body['items'];
		}
		return false;
	}

	public function getProfiles($accountId, $webpropertyId, $startIndex=1, $maxResults=1000)
	{		
		$get = array(
			'start-index' => $startIndex,
			'max-results'	=> $maxResults
		);

		$client = new Zend_Http_Client(App_GoogleAnalytics::ACCOUNTS_URL . '/'
				. $accountId . '/webproperties/' . $webpropertyId . '/profiles');
		$client->setHeaders(array(
			'Accept' => 'application/json',
			'Authorization'	=> 'Bearer ' . $this->_token
		));
		$client->setMethod('GET');
		$client->setParameterGet($get);
		$response = $client->request();

		$body = json_decode($response->getBody(), true);

		if (isset($body['error'])) {
			throw new Exception("Error " . print_r($body), $body['code']);
		}
		if (isset($body['items'])) {
			return $body['items'];
		}
		return false;
	}

	public function getGoals($accountId, $webpropertyId, $profileId, $startIndex=1, $maxResults=20)
	{
		$get = array(
			'start-index' => $startIndex,
			'max-results'	=> $maxResults
		);

		$client = new Zend_Http_Client(App_GoogleAnalytics::ACCOUNTS_URL . '/'
				. $accountId . '/webproperties/' . $webpropertyId . '/profiles/' . $profileId . '/goals');
		$client->setHeaders(array(
			'Accept' => 'application/json',
			'Authorization'	=> 'Bearer ' . $this->_token
		));
		$client->setMethod('GET');
		$client->setParameterGet($get);
		$response = $client->request();

		$body = json_decode($response->getBody(), true);

		if (isset($body['error'])) {
			throw new Exception("Error " . print_r($body), $body['code']);
		} else {
			return $body;
		}
	}

	/**
	 * Fetch all user's profiles through all his accounts
	 *
	 * @return array account -> profiles[]
	 */
	public function getAllProfiles()
	{
		$profiles = array();
		$webProperties = array();
		if ($this->getAccounts()) {
			foreach($this->getAccounts() as $account) {
				if ($this->getWebProperties($account['id'])) {
					$webProperties[$account['name']] = $this->getWebProperties($account['id']);
				}
			}
		}

		foreach($webProperties as $accountName => $wps) {
			foreach($wps as $wp) {
				if ($this->getProfiles($wp['accountId'], $wp['id'])) {
					$profiles[$accountName][$wp['id']] = $this->getProfiles($wp['accountId'], $wp['id']);
				}
			}
		}

		return $profiles;
	}

	/**
	 * Request report data from Google Analytics
	 *
	 * $profileId is the Google report ID for the selected account
	 *
	 * $parameters should be in key => value format
	 *
	 * @param String $profileId
	 * @param Array $dimensions Google Analytics dimensions e.g. array('browser')
	 * @param Array $metrics Google Analytics metrics e.g. array('pageviews')
	 * @param Array $sortMetric OPTIONAL: Dimension or dimensions to sort by e.g.('-visits')
	 * @param String $filter OPTIONAL: Filter logic for filtering results
	 * @param String $startDate OPTIONAL: Start of reporting period
	 * @param String $endDate OPTIONAL: End of reporting period
	 * @param Int $startIndex OPTIONAL: Start index of results
	 * @param Int $maxResults OPTIONAL: Max results returned
	 */
	public function getData($profileId, $dimensions, $metrics, $sortMetric=null,
		  $filter=null, $startDate=null, $endDate=null, $startIndex=1, $maxResults=30)
	{
		$parameters = array('ids'=>'ga:' . $profileId);

		if(is_array($dimensions)) {
			$dimensionsString = '';

			foreach($dimensions as $dimesion) {
				$dimensionsString .= ',ga:' . $dimesion;
			}
			$parameters['dimensions'] = substr($dimensionsString,1);
		} else {
			$parameters['dimensions'] = 'ga:'.$dimensions;
		}

		if(is_array($metrics)) {
			$metricsString = '';

			foreach($metrics as $metric) {
				$metricsString .= ',ga:' . $metric;
			}
			$parameters['metrics'] = substr($metricsString,1);
		} else {
			$parameters['metrics'] = 'ga:'.$metrics;
		}

		if($sortMetric == null && isset($parameters['metrics'])) {
			$parameters['sort'] = $parameters['metrics'];
		} elseif (is_array($sortMetric)) {
			$sortMetricString = '';

			foreach($sortMetric as $sortMetricValue) {
				//Reverse sort - Thanks Nick Sullivan
				if (substr($sortMetricValue, 0, 1) == "-") {
					$sortMetricString .= ',-ga:' . substr($sortMetricValue, 1); // Descending
				} else {
					$sortMetricString .= ',ga:' . $sortMetricValue; // Ascending
				}
			}

			$parameters['sort'] = substr($sortMetricString, 1);
		} else {
			if (substr($sortMetric, 0, 1) == "-") {
				$parameters['sort'] = '-ga:' . substr($sortMetric, 1);
			} else {
				$parameters['sort'] = 'ga:' . $sortMetric;
			}
		}

		if ($filter != null) {
			$filter = $this->processFilter($filter);

			if($filter !== false) {
				$parameters['filters'] = $filter;
			}
		}

		if ($startDate == null) {
			$startDate=date('Y-m-d',strtotime('1 month ago'));
		}

		$parameters['start-date'] = $startDate;

		if($endDate==null) {
			$endDate=date('Y-m-d');
		}

		$parameters['end-date'] = $endDate;
		$parameters['start-index'] = $startIndex;
		$parameters['max-results'] = $maxResults;
		$parameters['prettyprint'] = App_GoogleAnalytics::DEV_MODE;

		$response = $this->httpRequest(App_GoogleAnalytics::DATA_URL,
				$parameters, null, array_merge(
						$this->_getAuthHeader(),
						array('Accept: application/json')
					)
				);

		//HTTP 2xx
		if(substr($response['code'],0,1) == '2') {

			return $this->_mapDataResult(json_decode($response['body'], true));
		} else {
			throw new Exception('GAPI: Failed to request report data. Error: "' . strip_tags($response['body']) . '"');
		}
	}

	/**
	 *
	 * @param array $result json decoded response
	 */
	protected function _mapDataResult($result)
	{
		$paramKeys = array(
			'startIndex', 'itemsPerPage', 'totalResults'
		);
		foreach($paramKeys as $k) {
			$this->_dataParameters[$k] = $result[$k];
		}

		if (!isset($result['columnHeaders'])) {
			return false;
		}		

		$metrics = array();
		$dimensions = array();

		$metricNames;
		$dimensionNames;

		$dataSet = array();

		foreach($result['columnHeaders'] as $k=>$h) {
			$name = str_replace('ga:', '', $h['name']);
			if ($h['columnType'] == 'DIMENSION') {
				$dimensionNames[$k] = $name;
			} else {
				$metricNames[$k] = $name;
			}
		}

		if (isset($result['rows'])) {
			foreach($result['rows'] as $row) {
				foreach($row as $k => $v) {
					if (isset($dimensionNames[$k])) {
						$dimensions[$dimensionNames[$k]] = $v;
					} else {
						$metrics[$metricNames[$k]] = $v;
					}
				}

				$dataSet[] = new App_GoogleAnalytics_Data($metrics, $dimensions);
			}
		}

		return $dataSet;
	}

	public function refreshToken($userId)
	{
		$table = new Model_User();
		$user = $table->fetchRow(array('id=?' => $userId));

		$config = Zend_Registry::get('config');
		
		$post = array(
			'refresh_token'	=> $user->gaRefreshToken,
			'client_id'		=> $config->oauth->id,
			'client_secret' => $config->oauth->secret,
			'grant_type'	=> 'refresh_token'
				
		);

		$client = new Zend_Http_Client(App_GoogleAnalytics::OAUTH_TOKEN_URL);
		$client->setMethod('POST');
		$client->setParameterPost($post);

		$response = $client->request();

		$response = json_decode($response->getBody(), true);

		if (isset($response['access_token'])) {
			$this->_token = $response['access_token'];

			$user->gaAccessToken = $response['access_token'];
			$user->save();

			return true;
		}

		return false;
	}

	/**
	 * Get OAuth 2.0 Access Token
	 *
	 * @param String $code - authorization code
	 * @return array ['access_token', 'refresh_token']
	 */
	public function getAccessToken($code, $clientId, $clientSecret, $redirectUri)
	{
		//$config = Zend_Registry::get('config');
		
		$client = new Zend_Http_Client();
		$client->setUri(App_GoogleAnalytics::OAUTH_TOKEN_URL);
		$client->setMethod('POST');
		$client->setHeaders(array(
			'Content-Type'	=> 'application/x-www-form-urlencoded'
		));
		$client->setParameterPost(array(
			'code'	=> $code,
			'client_id'	=> $clientId,
			'client_secret'	=> $clientSecret,
			'redirect_uri'	=> $redirectUri,
			'grant_type'	=> 'authorization_code'
		));
		$response = $client->request();

		$body = $response->getBody();
		$body = str_replace(array("{", "}", '"', '"'), '', $body);
		$tmp = explode(',', $body);

		$res = array();
		foreach($tmp as $t) {
			$kv = explode(':', $t);
			$res[trim($kv[0])] = trim($kv[1]);
		}

		$this->_token = $res['access_token'];

		//var_dump($res); die;

		return $res;
	}

	/**
	 * Process filter string, clean parameters and convert to Google Analytics
	 * compatible format
 	 *
 	 * @param String $filter
	 * @return String Compatible filter string
	 */
	protected function processFilter($filter)
	{
		$validOperators = '(!~|=~|==|!=|>|<|>=|<=|=@|!@)';

		$filter = preg_replace('/\s\s+/',' ',trim($filter)); //Clean duplicate whitespace
		$filter = str_replace(array(',',';'),array('\,','\;'),$filter); //Escape Google Analytics reserved characters
		$filter = preg_replace('/(&&\s*|\|\|\s*|^)([a-z]+)(\s*' . $validOperators . ')/i','$1ga:$2$3',$filter); //Prefix ga: to metrics and dimensions
		$filter = preg_replace('/[\'\"]/i','',$filter); //Clear invalid quote characters
		$filter = preg_replace(array('/\s*&&\s*/','/\s*\|\|\s*/','/\s*' . $validOperators . '\s*/'),array(';',',','$1'),$filter); //Clean up operators

		if (strlen($filter)>0) {
			return urlencode($filter);
		} else {
			return false;
		}
	}

	public function getDataParameters()
	{
		return $this->_dataParameters;
	}


	/**
	 * Get Results
	 *
	 * @return Array
	 */
	public function getResults()
	{
		if(is_array($this->_results)) {
			return $this->_results;
		} else {
			return;
		}
	}

	/**
	 * Perform http request
	 *
	 *
	 * @param Array $get
	 * @param Array $post
	 * @param Array $headers
	 */
	public function httpRequest($url, $get=null, $post=null, $headers=null)
	{
		//only curl interface available now
		if(function_exists('curl_exec')) {
			return $this->curlRequest($url, $get, $post, $headers);
		} else {
			throw new Exception('Invalid http interface defined. No curl interface');
		}
	}

	/**
	 * HTTP request using PHP CURL functions
	 * Requires curl library installed and configured for PHP
	 *
	 * @param Array $get
	 * @param Array $post
	 * @param Array $headers
	 */
	private function curlRequest($url, $get=null, $post=null, $headers=null)
	{
		$ch = curl_init();

		if(is_array($get)) {
			$get = '?' . str_replace('&amp;','&',urldecode(http_build_query($get)));
		} else {
			$get = null;
		}

		curl_setopt($ch, CURLOPT_URL, $url . $get);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //CURL doesn't like google's cert

		if(is_array($post)) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}

		if(is_array($headers)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		
		$response = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		return array('body'=>$response,'code'=>$code);
	}

	/**
	 * Case insensitive array_key_exists function, also returns
	 * matching key.
	 *
	 * @param String $key
	 * @param Array $search
	 * @return String Matching array key
	 */
	public static function arrayKeyExistsNc($key, $search) {
		if (array_key_exists($key, $search)) {
			return $key;
		}

		if (!(is_string($key) && is_array($search))) {
			return false;
		}
		$key = strtolower($key);

		foreach ($search as $k => $v) {
			if (strtolower($k) == $key) {
				return $k;
			}
		}
		return false;
	}

	/**
	 * Utility function
	 * @deprecated
	 *
	 * @param string $date
	 * @param string $hour
	 * @return string
	 */
	public static function parseDate($date, $hour='00')
	{
		if ($date == '00000000') {
			$date = '19000101';
		}

		$result = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2) . ' ' . $hour . ':00:00';
		//echo $result . "\n";
		return $result;
	}
}
?>
