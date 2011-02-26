<?php

class FormKeeperHelper extends FormHelper {

	public $settings = array();

	public function beforeRender() {

		$default = array(
			'salt' => Configure::read('Security.salt'),
			'cacheKey' => 'default',
		);

		if (Configure::load('form_keeper')) {
			$settings = Configure::read('FormKeeper');
		} else {
			$settings = array();
		}

		$this->settings = array_merge($settings, $default);

	}

	public function _name($options = array(), $field = null, $key = 'name') {
		extract($this->settings);
		$view =& ClassRegistry::getObject('view');
		if ($options === null) {
			$options = array();
		} elseif (is_string($options)) {
			$field = $options;
			$options = 0;
		}

		if (!empty($field)) {
			$this->setEntity($field);
		}

		if (is_array($options) && array_key_exists($key, $options)) {
			return $options;
		}

		switch ($field) {
			case '_method':
				$name = $field;
			break;
			default:
				$name = 'data[' . implode('][', $view->entity()) . ']';
			break;
		}

		$cachedName = Cache::read(Security::hash('fieldMaps'.$salt, null, false), $cacheKey);
		if (!empty($cachedName)) {
			$cachedName = array_flip($cachedName);
		}
		if (empty($cachedName[$name])) {
			$hashKey = Security::hash($name.$salt, null, false);
			$cachedName[$hashKey] = $name;
			Cache::write(Security::hash('fieldMaps'.$salt, null, false), array_flip($cachedName), $cacheKey);
		} else {
			$hashKey = $cachedName[$name];
		}

		$name = $hashKey;
		$options['id'] = $name;

		if (is_array($options)) {
			$options[$key] = $name;
			return $options;
		} else {
			return $name;
		}
	}

}