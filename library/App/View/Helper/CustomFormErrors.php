<?php
	require_once 'Zend/View/Helper/FormErrors.php';

class App_View_Helper_CustomFormErrors extends Zend_View_Helper_FormErrors
{
	protected $_htmlElementEnd       = '</span></strong>';
	protected $_htmlElementStart     = '<strong%s><span>';
	protected $_htmlElementSeparator = '<br />';

	public function customFormErrors($errors, array $options = null)
	{
		if (empty($options['class'])) {
			$options['class'] = 'error zendError';
		}

		return parent::formErrors($errors, $options);
	}
}