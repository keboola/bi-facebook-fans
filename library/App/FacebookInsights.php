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
				$data[date('Y-m-d', strtotime($v['end_time']))]['dau'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_active_users', 'month', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['mau'] = $v['value'];
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
				$data[date('Y-m-d', strtotime($v['end_time']))]['likesAdded'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_fan_removes', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['likesRemoved'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_like_adds', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['contentLikesAdded'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_like_removes', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['contentLikesRemoved'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_comment_adds', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['comments'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_stream_views', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['feedViews'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_stream_views_unique', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['feedViewsUnique'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_wall_posts', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['wallPosts'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_wall_posts_unique', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['wallPostsUnique'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_photos', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['photos'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_photo_views', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['photoViews'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_photo_views_unique', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['photoViewsUnique'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_videos', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['videos'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_video_plays', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['videoPlays'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_video_plays_unique', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['videoPlaysUnique'] = $v['value'];
			}
		}

		return $data;
	}
}