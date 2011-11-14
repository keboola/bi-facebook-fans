<?
/**
 * Abstract class for wrapping concrete connectors
 * @author Jakub Matejka <jakub@keboola.com>
 */
abstract class App_Connector
{
	/**
	 * @var Zend_Db
	 */
	protected $_db;

	/**
	 * @var string
	 */
	protected $_dbPrefix;

	/**
	 * @param string $connector
	 */
	public function __construct($connector)
	{
		$config = Zend_Registry::get('config');
		$this->_db = Zend_Db::factory('pdo_mysql', array(
			'host'		=> $config->db->$connector->host,
			'username'	=> $config->db->$connector->login,
			'password'	=> $config->db->$connector->password,
			'dbname'	=> $config->db->$connector->db
		));
		$this->_db->getConnection();
		$this->_db->query('SET NAMES utf8');
		$this->_dbPrefix = $config->db->$connector->prefix;
	}

}