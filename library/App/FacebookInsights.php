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
	 * @param Zend_Table_Row $page
	 */
	public function __construct($page)
	{
		$this->_api = new App_Facebook($page->idPage, $page->token);
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

		$results = $this->_api->call('insights/page_active_users_city', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['activeUsersCity'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_active_users_country', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['activeUsersCountry'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_active_users_country', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['activeUsersCountry'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_active_users_gender_age', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $value) {
				$males = 0;
				$females = 0;
				$unknown = 0;
				$age = array();
				foreach($value['value'] as $k => $n) {
					$r = explode('.', $k); // is for example: "M.35-44" : 1
					if (isset($r[0])) {
						if ($r[0] == 'M') {
							$males += $n;
						} elseif ($r[0] == 'F') {
							$females += $n;
						} else {
							$unknown += $n;
						}
					}
					if (isset($r[1])) {
						if (!isset($age[$r[1]])) {
							$age[$r[1]] = $n;
						} else {
							$age[$r[1]] += $n;
						}
					}
				}
				$data[date('Y-m-d', strtotime($v['end_time']))]['age'] = $age;
				$data[date('Y-m-d', strtotime($v['end_time']))]['viewsMale'] = $males;
				$data[date('Y-m-d', strtotime($v['end_time']))]['viewsFemale'] = $females;
				$data[date('Y-m-d', strtotime($v['end_time']))]['viewsUnknownSex'] = $unknown;
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

		$results = $this->_api->call('insights/page_audio_plays', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['audioPlays'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_audio_plays_unique', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['audioPlaysUnique'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_discussions', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['discussions'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_discussions_unique', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['discussionsUnique'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_review_adds', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['reviewsAdded'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_review_adds_unique', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['reviewsAddedUnique'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_review_modifications', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['reviewsModified'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_review_modifications_unique', 'day', $since, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data[date('Y-m-d', strtotime($v['end_time']))]['reviewsModifiedUnique'] = $v['value'];
			}
		}


		// Lifetime aggregated likes data
		//fetch for last day of interval
		$untilPrev = date('Y-m-d', strtotime($until)-86400);
		$data['lifetime']['date'] = $untilPrev;

		$results = $this->_api->call('insights/page_fans_city', 'lifetime', $untilPrev, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data['lifetime']['cities'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_fans_country', 'lifetime', $untilPrev, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $v) {
				$data['lifetime']['countries'] = $v['value'];
			}
		}

		$results = $this->_api->call('insights/page_fans_gender_age', 'lifetime', $untilPrev, $until);
		if (isset($results['values'])) {
			foreach($results['values'] as $value) {
				$males = 0;
				$females = 0;
				$unknown = 0;
				$age = array();
				foreach($value['value'] as $k => $n) {
					$r = explode('.', $k); // is for example: "M.35-44" : 1
					if (isset($r[0])) {
						if ($r[0] == 'M') {
							$males += $n;
						} elseif ($r[0] == 'F') {
							$females += $n;
						} else {
							$unknown += $n;
						}
					}
					if (isset($r[1])) {
						if (!isset($age[$r[1]])) {
							$age[$r[1]] = $n;
						} else {
							$age[$r[1]] += $n;
						}
					}
				}
				$data['lifetime']['age'] = $age;
				$data['lifetime']['likesMale'] = $males;
				$data['lifetime']['likesFemale'] = $females;
				$data['lifetime']['likesUnknownSex'] = $unknown;
			}
		}
		

		return $data;
	}
}