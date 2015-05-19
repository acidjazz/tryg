<?

namespace cune\clb;

class listing {

  public $errors = [];

  public function filters($data) {

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
