<?php

namespace tryg;

class Listing {

  public $errors = [];
  public $options = [];
  public $filters = [];
  public $searches = [];

  protected $model = false;
  private $class = false;

  public $total = false;
  public $count = false;

  public $sortable = [];
  public $filterable = [];

  public function __construct() {
    $this->class = '\mdl\\'.$this->model;
  }

  public function get($query=[], $sort=[], $limit=50, $skip=0) {

    $model = '\mdl\\'.$this->model;

    if ($limit == 1) {
      return (new $model($model::findOne($query)))->data(true);
    }

    $this->total = $model::count($query, ['sort' => $sort]);
    $cursor = $model::find($query, ['sort' => $sort, 'skip' => $skip, 'limit' => $limit]);

    $docs = [];
    foreach ($cursor as $doc) {
      $modeled = new $model($doc);
      $docs[$modeled->id(true)] = $modeled->data(true);
    }

    $this->count = count($docs);

    return $docs;

  }

  public function browse($params) {

    $sort = $query = [];
    $skip = 0;
    $order = -1;
    $page = 1;

    if (isset($params['order']) && $params['order'] == 'asc') {
      $order = 1;
    }

    if (isset($params['limit']) && is_numeric($params['limit']) && $params['limit'] <= $this->max) {
      $this->limit = (int) $params['limit'];
    }

    if (isset($params['sort'])) {

      if (!in_array($params['sort'], array_keys($this->sortable))) {
        $this->errors = ['type' => 'sort', 'error' => 'Invalid sorting parameter'];
        return false;
      }

      $sort = [$this->sortable[$params['sort']] => $order];

    }

    if (false === ($this->filters = $this->filterCompile($params))) {
      return false;
    }

    if (false === ($this->searches = $this->searchCompile($params))) {
      return false;
    }

    if (isset($params['page']) && is_numeric($params['page'])) {
      $page = $params['page'];
      $skip = $page <= 1 ? 0 : $this->limit * ($page-1);
    }

    $query = $this->search($query);
    $query = $this->filterRegex($query);
    $query = $this->filterIs($query);
    $query = $this->filterIn($query);
    $query = $this->filterAll($query);
    $query = $this->filterExists($query);

    $result = $this->get($query, $sort, $this->limit, $skip);

    $this->options = [
      'filterable' => $this->filterable,
      'sortable' => $this->sortable,
      'filters' => $this->filters,
      'searches' => $this->searches,
      'query' => $query,
      'sort' => $sort,
      'limit' => $this->limit,
      'total' => $this->total,
      'count' => $this->count,
      'skip' => $skip,
      'page' => $page,
      'paginate' => self::paginate($page, $this->total, $this->limit)
    ];

    return $result;

  }

  public function search($query) {

    foreach ($this->searches as $i=>$search) {
      foreach ($this->searchable as $field) {
        $regex = new \MongoDB\BSON\Regex(preg_quote($search),'i');
        $query['$and'][$i]['$or'][][$field] = ['$regex' => $regex];
      }
    }

    return $query;

  }

  public function filterRegex($query) {

    if (!isset($this->filterable['regex'])) {
      return $query;
    }

    foreach ($this->filterable['regex'] as $name=>$field) {

      if (isset($this->filters[$name])) {
        $filters = is_array($this->filters[$name]) ? $this->filters[$name] : [$this->filters[$name]];
        foreach ($filters as $filter) {
          $regex = new \MongoDB\BSON\Regex(preg_quote($filter),'i');
          $query['$and'][][$field] = ['$regex' => $regex];
        }
      }

    }

    return $query;

  }

  public function filterIs($query) {

    if (!isset($this->filterable['is'])) {
      return $query;
    }

    foreach ($this->filterable['is'] as $name=>$field) {

      if (isset($this->filters[$name])) {
        $filters = is_array($this->filters[$name]) ? $this->filters[$name] : [$this->filters[$name]];
        foreach ($filters as $filter) {
          $query['$and'][][$field] = $filter;
        }
      }
    }

    return $query;

  }

  public function filterIn($query) {

    if (!isset($this->filterable['in'])) {
      return $query;
    }

    foreach ($this->filterable['in'] as $name=>$field) {

      if (isset($this->filters[$name])) {
        $filters = is_array($this->filters[$name]) ? $this->filters[$name] : [$this->filters[$name]];
        $query['$and'][][$field] = ['$in' => $filters];
      }
    }

    return $query;

  }

  public function filterAll($query) {

    if (!isset($this->filterable['all'])) {
      return $query;
    }

    foreach ($this->filterable['all'] as $name=>$field) {

      if (isset($this->filters[$name])) {
        $filters = is_array($this->filters[$name]) ? $this->filters[$name] : [$this->filters[$name]];
        $query['$and'][][$field] = ['$all' => $filters];
      }
    }

    return $query;

  }

  public function filterExists($query) {

    if (!isset($this->filterable['exists'])) {
      return $query;
    }

    foreach ($this->filterable['exists'] as $name=>$field) {

      if (isset($this->filters[$name])) {
        $filters = is_array($this->filters[$name]) ? $this->filters[$name] : [$this->filters[$name]];
        foreach ($filters as $filter) {
          $query['$and'][][$field.'.'.$filter] = ['$exists' => true];
        }
      }
    }

    return $query;

  }

  public function filterCompile($data) {

    $filters = [];

    for ($i = 1; $i != 100; $i++) {

      if (isset($data['filter'.$i])) {

        if (!isset($data['to'.$i])) {

          $this->errors = ['type' => 'filter', 'error' => 'Mismatched filter for #'.$i];
          return false;

        } else {

          $filter = $data['filter'.$i];
          if (isset($filters[$filter])) {
            if (!is_array($filters[$filter])) {
              $filters[$filter] = [$filters[$filter], $data['to'.$i]];
            } else {
              $filters[$filter][] = $data['to'.$i];
            }

          } else {
            $filters[$filter] = $data['to'.$i];
          }

        }

      }

    }

    return $filters;

  }

  public function searchCompile($data) {

    $searches = [];

    for ($i = 1; $i != 100; $i++) {
      if (isset($data['search'.$i])) {
        $searches[] = $data['search'.$i];
      }
    }

    return $searches;

  }

  public static function paginate($page, $rows, $perpage, $view=13) {

    $total_pages = ceil($rows / $perpage);

    if ($page > $total_pages) {
      $page = 1;
    }

    $side = floor($view/2);
    $pages = range($page-$side, $page+$side);

    if ($page <= $side) {
      $max = ($total_pages < $view) ? $total_pages : $view;
      $pages = range(1, $max);
    }

    if ($page+$side > $total_pages) {
      $start = ($total_pages-$view > 0) ? $total_pages-$view : 1;
      $pages = range($start, $total_pages);
    }

    $offset = $page <= 1 ? 0 : $perpage * ($page-1);
    $start = $offset+1;
    $end = $offset+$perpage > $rows ? $rows : $offset+$perpage;

    return [
      'total' => $total_pages,
      'pages' => $pages,
      'offset' => $offset,
      'start' => $start,
      'end' => $end
    ];

  }

}
