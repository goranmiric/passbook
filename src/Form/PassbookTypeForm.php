<?php

namespace Drupal\passbook\Form;

use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\language\Entity\ContentLanguageSettings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for passbook type forms.
 */
class PassbookTypeForm extends BundleEntityFormBase {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * Construct.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $type = $this->entity;
    if ($this->operation == 'add') {
      $form['#title'] = $this->t('Add passbook type');
      $fields = $this->entityManager->getBaseFieldDefinitions('passbook');

      $entity = $this->entityManager->getStorage('passbook')->create(['type' => $type->uuid()]);
    }
    else {
      $form['#title'] = $this->t('Edit %label passbook type', ['%label' => $type->label()]);
      $fields = $this->entityManager->getFieldDefinitions('passbook', $type->id());
      $entity = $this->entityManager->getStorage('passbook')->create(['type' => $type->id()]);
    }

    $form['label'] = [
      '#title' => t('Name'),
      '#type' => 'textfield',
      '#default_value' => $type->label(),
      '#description' => t('The human-readable name. This name must be unique.'),
      '#required' => TRUE,
      '#size' => 30,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $type->id(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#disabled' => $type->isLocked(),
      '#machine_name' => [
        'exists' => ['Drupal\passbook\Entity\PassbookType', 'load'],
        'source' => ['label'],
      ],
      '#description' => t('A unique machine-readable name for this passbook type. It must only contain lowercase letters, numbers, and underscores.'),
    ];

    $form['pass_type'] = [
      '#type' => 'select',
      '#title' => t('Pass type'),
      '#default_value' => $type->passType(),
      '#required' => TRUE,
      '#options' => [
        'boardingPass' => 'BoardingPass',
        'coupon' => 'Coupon',
        'eventTicket' => 'EventTicket',
        'generic' => 'Generic',
        'storeCard' => 'StoreCard',
      ],
      '#description' => t('The passbook type that will be used for this bundle.'),
    ];

    $form['description'] = [
      '#title' => t('Description'),
      '#type' => 'textarea',
      '#default_value' => $type->getDescription(),
      '#description' => t('This text will be displayed on the <em>Add new passbook</em> page.'),
    ];

    $form['additional_settings'] = [
      '#type' => 'vertical_tabs',
      '#attached' => [
        'library' => ['passbook/passbook.types'],
      ],
    ];

    $form['submission'] = [
      '#type' => 'details',
      '#title' => t('Submission form settings'),
      '#group' => 'additional_settings',
      '#open' => TRUE,
    ];

    $form['submission']['title_label'] = [
      '#title' => t('Title field label'),
      '#type' => 'textfield',
      '#default_value' => $fields['title']->getLabel(),
      '#required' => TRUE,
    ];

    $form['submission']['preview_mode'] = [
      '#type' => 'radios',
      '#title' => t('Preview before submitting'),
      '#default_value' => $type->getPreviewMode(),
      '#options' => [
        DRUPAL_DISABLED => t('Disabled'),
        DRUPAL_OPTIONAL => t('Optional'),
        DRUPAL_REQUIRED => t('Required'),
      ],
    ];

    $form['workflow'] = [
      '#type' => 'details',
      '#title' => t('Publishing options'),
      '#group' => 'additional_settings',
    ];
    $workflow_options = [
      'status' => $entity->status->value,
      'revision' => $type->isNewRevision(),
    ];

    // Prepare workflow options to be used for 'checkboxes' form element.
    $keys = array_keys(array_filter($workflow_options));
    $workflow_options = array_combine($keys, $keys);
    $form['workflow']['options'] = [
      '#type' => 'checkboxes',
      '#title' => t('Default options'),
      '#default_value' => $workflow_options,
      '#options' => [
        'status' => t('Published'),

        'revision' => t('Create new revision'),
      ],
      '#description' => t('Users with the <em>Administer passbook</em> permission will be able to override these options.'),
    ];

    if ($this->moduleHandler->moduleExists('language')) {
      $form['language'] = [
        '#type' => 'details',
        '#title' => t('Language settings'),
        '#group' => 'additional_settings',
      ];

      $language_configuration = ContentLanguageSettings::loadByEntityTypeBundle('passbook', $type->id());
      $form['language']['language_configuration'] = [
        '#type' => 'language_configuration',
        '#entity_information' => [
          'entity_type' => 'passbook',
          'bundle' => $type->id(),
        ],
        '#default_value' => $language_configuration,
      ];
    }

    $form['display'] = [
      '#type' => 'details',
      '#title' => t('Display settings'),
      '#group' => 'additional_settings',
    ];

    $form['display']['display_submitted'] = [
      '#type' => 'checkbox',
      '#title' => t('Display author and date information'),
      '#default_value' => $type->displaySubmitted(),
      '#description' => t('Author username and publish date will be displayed.'),
    ];

    return $this->protectBundleIdElement($form);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = t('Save passbook type');
    $actions['delete']['#value'] = t('Delete passbook type');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $id = trim($form_state->getValue('type'));
    if ($id == '0') {
      $form_state->setErrorByName('type', $this->t("Invalid machine-readable name. Enter a name other than %invalid.", ['%invalid' => $id]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $type = $this->entity;
    $type->setNewRevision($form_state->getValue(['options', 'revision']));
    $type->set('id', trim($type->id()));
    $type->set('label', trim($type->label()));
    $type->set('pass_type', $type->passType());

    $status = $type->save();

    $t_args = ['%name' => $type->label()];

    if ($status == SAVED_UPDATED) {
      drupal_set_message(t('The passbook type %name has been updated.', $t_args));
    }
    elseif ($status == SAVED_NEW) {
      // Attach default fields.
      $defaultFields = new PassbookTypeDefaultFields();
      $defaultFields->addDefaultFields($type);

      drupal_set_message(t('The passbook type %name has been added.', $t_args));
      $context = array_merge($t_args, ['link' => $type->link($this->t('View'), 'collection')]);
      $this->logger('passbook')->notice('Added passbook type %name.', $context);
    }

    $fields = $this->entityManager->getFieldDefinitions('passbook', $type->id());
    // Update title field definition.
    $title_field = $fields['title'];
    $title_label = $form_state->getValue('title_label');
    if ($title_field->getLabel() != $title_label) {
      $title_field->getConfig($type->id())->setLabel($title_label)->save();
    }

    // Update workflow options.
    $entity = $this->entityManager->getStorage('passbook')->create(['type' => $type->id()]);
    foreach (['status'] as $field_name) {
      $value = (bool) $form_state->getValue(['options', $field_name]);
      if ($entity->$field_name->value != $value) {
        $fields[$field_name]->getConfig($type->id())->setDefaultValue($value)->save();
      }
    }

    $this->entityManager->clearCachedFieldDefinitions();
    $form_state->setRedirectUrl($type->urlInfo('collection'));
  }

}
