<?
require_once 'Zend/Validate/Abstract.php';
class App_Validate_FacebookPage extends Zend_Validate_Abstract
{
	const NOT_MATCH = 'notMatch';

    protected $_messageTemplates = array(
        self::NOT_MATCH => 'Page with given ID was not found on Facebook.'
    );

    public function isValid($value, $context = null)
    {
        $value = (string) $value;
        $this->_setValue($value);

		ob_start();
		$result = file_get_contents('https://graph.facebook.com/'.$value);
		$output = ob_get_contents();
		ob_end_clean();

		if ($result) {
			$resultJson = Zend_Json::decode($result);
			if (isset($resultJson['name'])) {
				return true;
			} else if (isset($resultJson['error'])) {
				$this->_error(self::NOT_MATCH);
        		return false;
			} else {
				App_Debug::send('Confirmation of page id '.$value.' failed:'."\n".$result);
				return true;
			}
		} else {
			App_Debug::send('Confirmation of page id '.$value.' failed:'."\n".$output);
			return true;
		}
    }
}