<?php

class Get extends UniversalHelper implements ArrayAccess {

	protected $array = null;

	protected $url_path = '';
	protected $order = array();
	protected $cleaned = array();
	protected $params = array();

	public function clean_key($key) {
		return str_replace('.', '_', $key);
	}

	public function __construct($url = null) {
		$this->init(is_array($url) ? $url[0] : $url);
	}

	public function init($url = null) {
		$this->is_initialised = true;
		$this->array = null;
		$this->order = array();
		$this->cleaned = array();
		$this->params = array();
		if ($url === null) {
			$url = $_SERVER['REQUEST_URI'];
		}
		list($this->url_path, $search) = explode('?', $url, 2);
		$parts = explode('&', $search);
		foreach ($parts as $part) {
			list($key, $value) = explode('=', $part, 2);
			if (strlen($key)) {
				$this->add(urldecode($key), urldecode($value));
			}
		}
		return $this;
	}

	public function add($key, $value = null) {
		$this->try_to_init();
		$this->array = null;
		$cleaned_key = $this->clean_key($key);
		if (isset($value)) {
			$value = "{$value}";
		}
		if (strpos($cleaned_key, '[]') === false) {
			$this->params[$cleaned_key] = $value;
			$pos = array_search($cleaned_key, $this->cleaned, true);
			if ($pos !== false) {
				unset($this->order[$pos]);
				unset($this->cleaned[$pos]);
			}
		} else {
			$this->params[$cleaned_key][] = $value;
		}
		$this->order[] = $key;
		$this->cleaned[] = $cleaned_key;
		return $this;
	}

	public function delete($key, $value = null) {
		$this->try_to_init();
		$this->array = null;
		$cleaned_key = $this->clean_key($key);
		if (isset($value)) {
			$value = "{$value}";
		}
		if (strpos($cleaned_key, '[]') === false) {
			if (!isset($value) || ($this->params[$cleaned_key] === $value) || (($value === '') && ($this->params[$cleaned_key] === null))) {
				unset($this->params[$cleaned_key]);
				$pos = array_search($cleaned_key, $this->cleaned, true);
				if ($pos !== false) {
					unset($this->order[$pos]);
					unset($this->cleaned[$pos]);
				}
			}

			if (!isset($value)) {
				$broken_indexes = array();
				$s = "{$cleaned_key}[";
				$l = strlen($s);
				foreach ($this->cleaned as $j => $current_cleaned_key) {
					if (substr($current_cleaned_key, 0, $l) === $s) {
						if (is_array($this->params[$current_cleaned_key])) {
							if (!isset($broken_indexes[$current_cleaned_key])) {
								$broken_indexes[$current_cleaned_key] = 0;
							}
							unset($this->params[$current_cleaned_key][$broken_indexes[$current_cleaned_key]++]);
						} else {
							unset($this->params[$current_cleaned_key]);
						}
						unset($this->order[$j]);
						unset($this->cleaned[$j]);
					}
				}
				foreach ($broken_indexes as $broken_index => $foo) {
					unset($this->params[$broken_index]);
				}
			}

		} else {
			$values = $this->params[$cleaned_key];
			$i = 0;
			foreach ($this->cleaned as $j => $current_cleaned_key) {
				if ($current_cleaned_key === $cleaned_key) {
					if (!isset($value) || ($values[$i] === $value) || (($value === '') && ($values[$i] === null))) {
						unset($values[$i]);
						unset($this->order[$j]);
						unset($this->cleaned[$j]);
					}
					$i++;
				}
			}
			if (!count($values)) {
				unset($this->params[$cleaned_key]);
			} else {
				$this->params[$cleaned_key] = array_values($values);
			}
		}
		return $this;
	}

	public function __toString() {
		$this->try_to_init();
		$result = $this->url_path;
		$indexes = array();
		if (!empty($this->params)) {
			$result .= '?';
			$i = 0;
			foreach ($this->order as $j => $key) {
				if ($i++) {
					$result .= '&';
				}
				$result .= str_replace('%20', '+', urlencode($key));
				$cleaned_key = $this->cleaned[$j];
				if (is_array($this->params[$cleaned_key])) {
					if (!isset($indexes[$cleaned_key])) {
						$indexes[$cleaned_key] = 0;
					}
					if (isset($this->params[$cleaned_key][$indexes[$cleaned_key]])) {
						$result .= str_replace(' ', '+', "={$this->params[$cleaned_key][$indexes[$cleaned_key]]}");
					}
					$indexes[$cleaned_key]++;
				} else {
					if (isset($this->params[$cleaned_key])) {
						$result .= str_replace(' ', '+', "={$this->params[$cleaned_key]}");
					}
				}
			}
		}
		return $result;
	}

	public function to_array() {
		$this->try_to_init();
		if (!isset($this->array)) {
			$result = array();
			$indexes = array();
			foreach ($this->order as $j => $key) {
				$cleaned_key = $this->cleaned[$j];

				if (is_array($this->params[$cleaned_key])) {
					if (!isset($indexes[$cleaned_key])) {
						$indexes[$cleaned_key] = 0;
					}
					$value = isset($this->params[$cleaned_key][$indexes[$cleaned_key]]) ? "{$this->params[$cleaned_key][$indexes[$cleaned_key]]}" : null;
					$indexes[$cleaned_key]++;
				} else {
					$value = isset($this->params[$cleaned_key]) ? "{$this->params[$cleaned_key]}" : null;
				}

				$strs = array();
				$pos0 = 0;
				while (($pos1 = strpos($cleaned_key, '[', $pos0)) !== false) {
					$str = substr($cleaned_key, $pos0, $pos1 - $pos0);
					if (substr($str, -1) == ']') {
						$str = substr($str, 0, -1);
					}
					$strs[] = $str;
					$pos0 = $pos1 + 1;
				}
				$str = substr($cleaned_key, $pos0);
				if (substr($str, -1) == ']') {
					$str = substr($str, 0, -1);
				}
				$strs[] = $str;

				$ptr = &$result;
				foreach ($strs as $str) {
					if (!is_array($ptr)) {
						$ptr = array();
					}
					if (strlen($str)) {
						$ptr = &$ptr[$str];
					} else {
						$ptr = &$ptr[count($ptr)];
					}
				}
				$ptr = isset($value) ? "{$value}" : null;
			}
			$this->array = $result;
		}
		return $this->array;
	}

	public function copy() {
		return clone $this;
	}

	function get($name)
	{
		if (empty($this->array)) {
			$this->array = $this->to_array();
		}
		if (isset($this->array[$name])) {
			return $this->array[$name];
		}
		return '';
	}

	function __isset($name) {
		if (empty($this->array)) {
			$this->array = $this->to_array();
		}
		return isset($this->array[$name]);
	}

	function offsetExists($name) {
		if (empty($this->array)) {
			$this->array = $this->to_array();
		}
		return isset($this->array[$name]);
	}

	function offsetGet($name) {
		return $this->get($name);
	}

	function offsetSet($name, $value) {
		$this->add($name, $value);
	}

	function offsetUnset($name) {
		$this->delete($name);
	}

}
