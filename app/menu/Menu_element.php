<?php

/**
 * @property-read Category category
 */
class Menu_element extends ActiveRecord
{
    /**
     * @var array поле menu_elements => поле products
     */
    private const MAPPING = [
        'collection_id' => 'collection_id',
        'series_name' => 'series',
        'category_id' => 'category_id',
        'carcas_name' => 'carcas',
        'genders' => 'genders',
        'is_auto' => 'auto',
    ];

    private $cachedFilters;

    private $cachedLink;

    private $cachedNumber;

    public function link(): string
    {
        if ($this->is_empty()) {
            return '';
        }
        if (isset($this->cachedLink[$this->id])) {
            return $this->cachedLink[$this->id];
        }

        $result = $this->url;
        if ($result !== '') {
            return $this->cachedLink[$this->id] = $result;
        }

        $result = '/catalog/' . $this->category->url;

        foreach ($this->filters() as $field => $value) {
            if ($field !== 'category_id') {
                $params[$field . '[]'] = $value;
            }
        }
        if (isset($params)) {
            $result .= '?' . http_build_query($params);
        }

        return $this->cachedLink[$this->id] = $result;
    }

    public function number(): int
    {
        if ($this->is_empty()) {
            return 0;
        }
        if (isset($this->cachedNumber[$this->id])) {
            return $this->cachedNumber[$this->id];
        }

        if ($this->url !== '') {
            return $this->cachedNumber[$this->id] = 0;
        }

        $countQuery = d()->Product
            ->select('count(id) `count`')
            ->order('');

        $groupBy = [];
        foreach ($this->filters() as $field => $value) {
            $quoted = '`' . et($field) . '`';

            $countQuery->and_select($quoted);
            $countQuery->where("$quoted=?", $value);
            $groupBy[] = $quoted;
        }
        $countQuery->group_by(implode(',', $groupBy));

        return $this->cachedNumber[$this->id] = (int) $countQuery->get('count');
    }

    private function filters(): array
    {
        if ($this->is_empty()) {
            return [];
        }
        if (isset($this->cachedFilters[$this->id])) {
            return $this->cachedFilters[$this->id];
        }

        $result = [];
        foreach (self::MAPPING as $menuField => $productField) {
            $value = $this->get($menuField);

            if ($value !== '' && $value !== '0') {
                $result[$productField] = $value;
            }
        }

        return $this->cachedFilters[$this->id] = $result;
    }
}