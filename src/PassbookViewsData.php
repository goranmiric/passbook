<?php

namespace Drupal\passbook;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data.
 */
class PassbookViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['passbook_field_data']['table']['join']['passbook']['left_field'] = 'id';
    $data['passbook_field_data']['table']['join']['passbook']['field'] = 'id';

    return $data;
  }

}
