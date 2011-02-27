<?php

class FormKeeperComponent extends Dispatcher {

	public function startup(&$controller) {
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
		debug($this->stitchFields($_POST));
	}

	public function stitchFields(&$data) {
		extract($this->settings);
		$fields = array();
		$cachedNames = Cache::read(Security::hash('fieldMaps'.$salt, null, false), $cacheKey);

		if (!empty($data) && !empty($cachedNames)) {
			$cachedNames = array_flip($cachedNames);
			foreach ($data as $hash => &$field) {
				if (in_array($hash, array_keys($cachedNames))) {
					$fields[$cachedNames[$hash]] = $field;
					$this->addFieldToData($cachedNames[$hash], $data, $hash);
				}
			}
		}

		return $fields;
	}

	public function wormholeArray($levels = array(), $value = '', &$data = array()) {
		if (!empty($levels)) {
			$level = array_shift($levels);
			if (empty($levels)) {
				$data[$level] = $value;
			} else {
				$data[$level] = array();
			}
			$this->wormholeArray($levels, $value, $data[$level]);
		}
		return $data;
	}

	public function addFieldToData($field = '', &$data, $hash) {
		$field = 'data[User][ME][mew]';
		preg_match_all('/\[(.*?)\]/', $field, $levels);
		$data = $this->wormholeArray($levels[1], $data[$hash]);


	}

}