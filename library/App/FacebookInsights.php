<?php
/**
 * Class to get data from Insights
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-06-09
 */

class App_FacebookInsights
{
	/**
	 * @var \App_Facebook
	 */
	private $_api;

	/**
	 * @param int $pageId
	 * @param string $appToken
	 */
	public function __construct($pageId, $appToken)
	{
		$this->_api = new App_Facebook($pageId, $appToken);
	}

	public function getData($since, $until)
	{
		$data = array();

		$results = $this->_api->call('insights/page_active_users', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['activeUsers'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_active_users_country', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['activeUsersCountry'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_views', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['views'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_views_unique', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['viewsUnique'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_views_login', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['viewsLogin'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_views_logout', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['viewsLogout'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_views_internal_referrals', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['internalReferrals'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_views_external_referrals', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['externalReferrals'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_fans', 'lifetime', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['likesTotal'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_fan_adds', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['likes'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_comment_adds', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['comments'] = $v['value'];
			}
		}

		return $data;
	}
}