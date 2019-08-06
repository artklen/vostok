<?php

class Products__field extends ActiveRecord
{
	
	function filter_instance()
	{
		$class = substr(__CLASS__, 0, -7) . '_filter';
		$type = $this->type;
		if ("$type" !== '' && class_exists("{$class}_{$type}")) {
			$class .= '_' . $type;
		}
		return new $class($this->field_name);	
	}
	
	
	function is_filter_show()
	{
		return $this->is_filter
				? '<span style="color:#3c3;font-weight:bold;font-size:1.5em;line-height:1em;">☑</span>'
				: '<span style="color:#999;font-weight:bold;font-size:1.5em;line-height:1em;">☒</span>'; 
	}
	
	function variants_list()
	{
		$strs = explode("\n", trim($this->variants));
		foreach ($strs as $str) {
			$result[] = array('title' => trim($str));
		}
		return $result;
	}
	
	function value_of($object)
	{
		return $object->get($this->field_name);
	}

	public function get_text($object) {
        if (in_array(d()->lang, ['', 'ru'])) {
            return trim($this->value_of($object));
        }

	    $original_text = trim($this->value_of($object));

	    if (mb_strlen($original_text) <= 0) {
	        return null;
        }

        $column = d()->lang . '_variants';

        if (mb_strlen($this->{$column}) <= 0) {
	        return $original_text;
        }

	    $en_variants = explode("\n", trim($this->{$column}));
        foreach ($en_variants as $en_variant) {
            if (mb_stristr($en_variant, $original_text . '=') !== false) {
                $result = mb_substr($en_variant, mb_stripos($en_variant, '=') + 1, -1);

                if (mb_strlen(trim($result)) > 0) {
                    return $result;
                } else {
                    return $original_text;
                }
            }
        }

        return $original_text;
    }

	public function getText2($field_name) {
        $this->where('field_name = ?', $field_name);
    }
	
	function show_value_of($object)
	{
		$result = $this->value_of($object);
		/*switch ($this->type) {
		case 3:
			return $result ? 'да' : 'нет';
			break;
		case 4:
			break; // ???
		case 5:
			break; // ???
		case 6:
			break; // ???
		}*/
		return $result;
	}
	
	function formatted_value_of($object) {
		$t = $object;
		$value = $this->value_of($object);
		if (trim($this->value_template) !== '') {
			$value = str_replace('&quot;', '"', eval('return "' . str_replace('"', '&quot;', $this->value_template) . '";'));
		}
		return $value;
	}
	
	function title_of($value) {
		$result = $value;
		$variants = explode("\n", $this->variants);
		foreach ($variants as $variant) {
			$parts = explode('=', $variant, 2);
			if ($parts[0] !== '' && $parts[0]{0} === '!') {
				$parts[0] = ltrim($parts[0], '!');
			}
			if (mb_strtolower(trim($parts[0]), 'utf-8') === mb_strtolower(trim($value), 'utf-8')) {
				$result = trim($parts[1]);
				break;
			}
		}
		return $this->templated_value_of($result);
	}
	
	function is_special_title($value) {
		$variants = explode("\n", $this->variants);
		foreach ($variants as $variant) {
			$parts = explode('=', $variant, 2);
			if (mb_strtolower(trim($parts[0]), 'utf-8') === mb_strtolower('!' . trim($value), 'utf-8')) {
				return true;
			}
		}
		return false;
	}
	
	/*function title_of($value) {
		$result = $value;
		$variants = explode("\n", $this->variants);
		foreach ($variants as $variant) {
			$parts = explode('=', $variant, 2);
			if (mb_strtolower(trim($parts[0]), 'utf-8') === mb_strtolower(trim($value), 'utf-8')) {
				$result = trim($parts[1]);
				break;
			}
		}
		return $result;
	}*/
	
	function templated_value_of($value)
	{
		$result = $value;
		if ($this->value_template !== '' && strpos($this->value_template, '{$value}') !== false) {
			$result = str_replace('{$value}', $value, $this->value_template);
		}
		return $result;
	}
	
}
 