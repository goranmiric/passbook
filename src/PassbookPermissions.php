<?php

namespace Drupal\passbook;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\passbook\Entity\PassbookType;

/**
 * Provides dynamic permissions.
 */
class PassbookPermissions {

  use StringTranslationTrait;

  /**
   * Returns an array of permissions.
   *
   * @return array
   *   The  permissions.
   */
  public function passbookTypePermissions() {
    $perms = [];
    // Generate permissions for all types.
    foreach (PassbookType::loadMultiple() as $type) {
      $perms += $this->buildPermissions($type);
    }

    return $perms;
  }

  /**
   * Returns a list of permissions for a given type.
   *
   * @param \Drupal\passbook\Entity\PassbookType $type
   *   The type.
   *
   * @return array
   *   An associative array of permission names and descriptions.
   */
  protected function buildPermissions(PassbookType $type) {
    $type_id = $type->id();
    $type_params = ['%type_name' => $type->label()];

    return [
      "create $type_id passbook" => [
        'title' => $this->t('%type_name: Create new passbook', $type_params),
      ],
      "edit own $type_id passbook" => [
        'title' => $this->t('%type_name: Edit own passbook', $type_params),
      ],
      "edit any $type_id passbook" => [
        'title' => $this->t('%type_name: Edit any passbook', $type_params),
      ],
      "delete own $type_id passbook" => [
        'title' => $this->t('%type_name: Delete own passbook', $type_params),
      ],
      "delete any $type_id passbook" => [
        'title' => $this->t('%type_name: Delete any passbook', $type_params),
      ],
      "view $type_id revisions" => [
        'title' => $this->t('%type_name: View revisions', $type_params),
      ],
      "revert $type_id revisions" => [
        'title' => $this->t('%type_name: Revert revisions', $type_params),
      ],
      "delete $type_id revisions" => [
        'title' => $this->t('%type_name: Delete revisions', $type_params),
      ],
    ];
  }

}
