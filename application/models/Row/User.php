<?
/**
 * @author Miroslav Cillik <miro@keboola.com>
 */

class Model_Row_User extends Zend_Db_Table_Row_Abstract
{

	public function getPages()
	{
		return $this->findManyToManyRowset('Pages', 'PagesUsers');
	}

}