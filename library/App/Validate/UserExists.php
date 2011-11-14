<?
require_once 'Zend/Validate/Abstract.php';
class App_Validate_UserExists extends Zend_Validate_Abstract
{
	const USER_EXISTS = 'userExists';

	protected $_messageTemplates = array(
		self::USER_EXISTS => 'User with such email already exists in our database.'
	);

	public function isValid($value, $context = null)
	{
		$value = (string) $value;
		$this->_setValue($value);

		$_u = new Model_Users();
		$u = $_u->fetchRow(array('email=?' => $value));
		if (!$u) {
			return TRUE;
		} else {
			$this->_error(self::USER_EXISTS);
			return FALSE;
		}
	}
}