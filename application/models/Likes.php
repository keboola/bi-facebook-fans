<?php
/**
 * Likes
 * @author Jakub Matejka <jakub@keboola.com>
 * @date 2011-06-28
 */

class Model_Likes extends Zend_Db_Table
{
	protected $_name = 'fbi_likes';
	protected $_rowClass = 'Model_Row_Like';

}