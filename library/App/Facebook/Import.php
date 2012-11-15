<?php

/**
 * Description of Import
 *
 * @author Miroslav Čillík <miro@keboola.com>
 */
class App_Facebook_Import {
	
	protected $_user;
	protected $_fb;
	protected $_f;
	protected $_s;
	protected $_o;
	protected $_so;
	protected $_pu;
	protected $_u;
	
	public function __construct($user) {
		$this->_user = $user;
		$this->_fb = new App_Facebook($user->accessToken);
		$this->_s = new Model_StatusMessages();
		$this->_o = new Model_Objects();
		$this->_so = new Model_StatusMessagesObjects();
		$this->_pu = new Model_PagesUsers();
		$this->_f = new Model_Friends();
		$this->_u = new Model_Users();
	}
	
	private function fb()
	{
		return $this->_fb;
	}
	
	private function _init()
	{
		$newToken = $this->_fb->exchangeToken();

		$userInfo = $this->_fb->request('me');
		
		// Update user
		$this->_user->idFB = $userInfo['id'];
		$this->_user->name = $userInfo['name'];
		$this->_user->email = $userInfo['email'];
		$this->_user->accessToken = $newToken;
		$this->_user->save();
		
	}
	
	public function importFriends()
	{
		$friends = $this->fb()->request('me/friends');
		
		foreach($friends['data'] as $f) {
			$this->_f->save(array(
				'idFB'	=> $f['id'],
				'name'	=> $f['name'],
				'idUser'	=> $this->_user->id
			));
		}				
	}
	
	/**
	 * How many of user's friends are using our app 
	 */
	public function influence()
	{
		$friends = $this->_f->fetchAll(array('idUser=?' => $this->_user->id));
		$fbIds = array();		
		
		foreach($friends as $f) {			
			$fbIds[] = $f->idFB;
		}						
		
		$rs = null;
		if (count($fbIds)) {
			$rs = $this->_u->getAdapter()->fetchOne("SELECT COUNT(id) FROM bi_users WHERE idFB IN (?)", array($fbIds));
		}

		$influence = 0;
		if ($rs != null) {
			$influence = count($rs);
		}

		$this->_user->influence = $influence;
		$this->_user->save();
	}
	
	/**
	 * Get User's Feed 
	 */
	public function importFeed($since, $until)
	{
		$userFeed = $this->fb()->request('me/feed?since=' . $since);

		// User's feed
		foreach($userFeed['data'] as $object) {

			$action = null;
			$subject = null;
			$story = $object['story'];
			if ($story != null) {

				// Like
				if (strstr($story, 'likes')) {
					$storyArr = explode(' likes ', $story);
					$action = 'like';
					$subject = $storyArr[1];
				} else if (strstr($story, 'friends')) {
					$action = 'friends';
				} else if (strstr($story, 'started using')) {
					$action = 'app';
				} else if (strstr($story, 'created')) {
					$action = 'create';
				}
			}

			$sId = $this->_s->save(array(
				'idFB'	=> $object['id'],
				'story'	=> $object['story'],
				'message'	=> App_GoodData::escapeString($object['message']),
				'type'		=> $object['type'],
				'datetime'	=> date('Y-m-d H:i:s', strtotime($object['created_time'])),
				'comments'	=> $object['comments']['count'],
				'idUser'	=> $this->_user->id,
				'action'	=> $action			
			));

			unset($object['story_tags'][0]);

			foreach($object['story_tags'] as $tag) {
				foreach($tag as $t) {
					$type = 'not set';

					foreach($this->_user->findManyToManyRowset('Model_Pages', 'Model_PagesUsers') as $page) {
						if ($page->name == $t['name']) {
							$type = 'parent page';
						}
					}

					//@TODO: query facebook for object type
					//$object = $fb->request($t['id']);
					//ladybug_dump_die($object);

					$oid = $this->_o->save(array(
						'idFB'	=> $t['id'],
						'name'		=> $t['name'],
						'type'		=> $type
					));

					$this->_so->save(array(
						'idStatusMessage' => $sId,
						'idObject'		=> $oid
					));
				}

			}


		}
	}
	
	/**
	 * Check  if user has unliked the parent page
	 */
	public function checkUnlike()
	{		
		// Check if user still likes pages with our app
		$userLikes = $this->fb()->request('me/likes');
		$likes = array();

		foreach($userLikes['data'] as $item) {
			$likes[] = $item['id'];
		}

		foreach($this->_user->findManyToManyRowset('Model_Pages', 'Model_PagesUsers') as $page) {

			// If there was a record of liking the page add "unlike" record
			$object = $this->_o->getAdapter()->fetchAll(
				"SELECT o.*, s.idFB as statusFbId, s.datetime as statusDatetime FROM bi_objects o
					JOIN bi_rStatusMessagesObjects so ON (so.idObject = o.id)
					JOIN bi_statusMessages s ON (so.idStatusMessage = s.id)
					WHERE s.idUser = ?
					AND s.action = 'like'
					AND o.idFB = ?
					AND o.type = 'parent page' ORDER BY statusDatetime ASC"
			, array($user->id, $page->idFB));

			if (count($object) > 0) {
				$object = $object[0];
				$isLike = 1;

				if (!array_search($page->idFB, $likes)) {		// page was unliked			

					$sid = $this->_s->save(array(
						'idFB'	=> $user->idFB . '_unlike_' . $page->idFB,
						'story'	=> $user->name . ' unlikes ' . $page->name . '.',
						'type'		=> 'status',
						'datetime'	=> date('Y-m-d H:i:s'),
						'comments'	=> 0,
						'idUser'	=> $user->id,
						'action'	=> 'unlike'
					));

					// Save object for new status
					$oid = $this->_o->save(array(
						'idFB'	=> $object['idFB'],
						'name'		=> $object['name'],
						'type'		=> 'parent page'
					));

					$this->_so->save(array(
						'idStatusMessage' => $sid,
						'idObject'		=> $oid
					));

					$isLike = 0;
				}

				// Save lifetime
				$lifetime = round((strtotime('today') - strtotime($object['statusDatetime'])) / 86400, 0);

				$this->_pu->save(array(
					'idPage'	=> $page->id,
					'idUser'	=> $user->id,
					'lifetime'	=> $lifetime,
					'isLike'	=> $isLike
				));

			}
		}
	}
	
	public function run()
	{
		
	}		
	
	
}

?>
