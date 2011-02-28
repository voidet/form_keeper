<?php

class FormKeeperComponent extends Dispatcher {

	public function initialize(&$controller, $settings = array()) {
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
		if (!empty($_POST)) {
			if (empty($controller->data)) {
				$controller->data = array();
			}
			$controller->data = array_merge_recursive($controller->data, $this->stitchFields($_POST));
		}
	}

	public function stitchFields(&$data) {
		extract($this->settings);
		$cachedNames = Cache::read(Security::hash('fieldMaps'.$salt, null, false), $cacheKey);
		$reStitched = array();
		if (!empty($data) && !empty($cachedNames)) {
			$cachedNames = array_flip($cachedNames);
			foreach ($data as $hash => &$field) {
				if (in_array($hash, array_keys($cachedNames))) {
					$reStitched = array_merge_recursive($this->addFieldToData($cachedNames[$hash], $field), $reStitched);
					unset($data[$hash]);
				}
			}
		}
		return $reStitched;
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

	public function addFieldToData($field = '', &$data) {
		preg_match_all('/\[(.*?)\]/', $field, $levels);
		$field = $this->wormholeArray($levels[1], $data);
		return $field;
	}

}