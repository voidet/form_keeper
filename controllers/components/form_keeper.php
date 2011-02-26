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
		debug($_POST);
		$_POST = $this->stitchFields($_POST);
		debug(parent::parseParams($this->getUrl()));
		debug($controller->data);
	}

	public function stitchFields(&$data) {
		extract($this->settings);

		$cachedNames = Cache::read(Security::hash('fieldMaps'.$salt, null, false), $cacheKey);
		if (!empty($data) && !empty($cachedNames)) {
			$cachedNames = array_flip($cachedNames);
			$fields = array();
			foreach ($data as $hash => &$field) {
				if (in_array($hash, array_keys($cachedNames))) {
					$fields[$cachedNames[$hash]] = $field;
				}
			}
		}

		return $fields;

	}

	public function parseParams(&$controller) {

		debug($controller->_POST);

		if (empty($controller->data)) {
			return true;
		}
		$data = $controller->data;

		if (!isset($data['_Token']) || !isset($data['_Token']['fields']) || !isset($data['_Token']['key'])) {
			return false;
		}
		$token = $data['_Token']['key'];

		if ($this->Session->check('_Token')) {
			$tokenData = unserialize($this->Session->read('_Token'));

			if ($tokenData['expires'] < time() || $tokenData['key'] !== $token) {
				return false;
			}
		}

		$locked = null;
		$check = $controller->data;
		$token = urldecode($check['_Token']['fields']);

		if (strpos($token, ':')) {
			list($token, $locked) = explode(':', $token, 2);
		}
		unset($check['_Token']);

		$locked = explode('|', $locked);

		$lockedFields = array();
		$fields = Set::flatten($check);
		$fieldList = array_keys($fields);
		$multi = array();

		foreach ($fieldList as $i => $key) {
			if (preg_match('/\.\d+$/', $key)) {
				$multi[$i] = preg_replace('/\.\d+$/', '', $key);
				unset($fieldList[$i]);
			}
		}
		if (!empty($multi)) {
			$fieldList += array_unique($multi);
		}

		foreach ($fieldList as $i => $key) {
			$isDisabled = false;
			$isLocked = (is_array($locked) && in_array($key, $locked));

			if (!empty($this->disabledFields)) {
				foreach ((array)$this->disabledFields as $disabled) {
					$disabled = explode('.', $disabled);
					$field = array_values(array_intersect(explode('.', $key), $disabled));
					$isDisabled = ($field === $disabled);
					if ($isDisabled) {
						break;
					}
				}
			}

			if ($isDisabled || $isLocked) {
				unset($fieldList[$i]);
				if ($isLocked) {
					$lockedFields[$key] = $fields[$key];
				}
			}
		}
		sort($fieldList, SORT_STRING);
		ksort($lockedFields, SORT_STRING);

		$fieldList += $lockedFields;
		$check = Security::hash(serialize($fieldList) . Configure::read('Security.salt'));
		return ($token === $check);
	}



}