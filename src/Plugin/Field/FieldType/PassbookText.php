<?php

namespace Drupal\passbook\Plugin\Field\FieldType;

/**
 * Plugin implementation of the 'passbook_text' field.
 *
 * @FieldType(
 *   id = "passbook_text",
 *   label = @Translation("Passbook text"),
 *   description = @Translation("Passbook text field."),
 *   category = @Translation("Passbook"),
 *   default_widget = "passbook_text_default",
 *   default_formatter = "passbook_text_default"
 * )
 */
class PassbookText extends PassbookItemBase {

}
