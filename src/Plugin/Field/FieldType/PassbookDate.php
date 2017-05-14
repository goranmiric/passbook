<?php

namespace Drupal\passbook\Plugin\Field\FieldType;

/**
 * Plugin implementation of the 'passbook_date' field.
 *
 * @FieldType(
 *   id = "passbook_date",
 *   label = @Translation("Passbook date"),
 *   description = @Translation("Passbook date field."),
 *   category = @Translation("Passbook"),
 *   default_widget = "passbook_date_default",
 *   default_formatter = "passbook_date_default"
 * )
 */
class PassbookDate extends PassbookItemBase {

}
