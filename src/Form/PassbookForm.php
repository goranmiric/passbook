<?php

namespace Drupal\passbook\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\passbook\Entity\PassbookInterface;
use Drupal\passbook\Entity\PassbookType;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for the passbook edit forms.
 */
class PassbookForm extends ContentEntityForm {

  /**
   * The tempstore factory.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\user\PrivateTempStoreFactory $temp_store_factory
   *   The factory for the temp store object.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(EntityManagerInterface $entity_manager, PrivateTempStoreFactory $temp_store_factory, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL) {
    parent::__construct($entity_manager, $entity_type_bundle_info, $time);
    $this->tempStoreFactory = $temp_store_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('user.private_tempstore'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    // Try to restore from temp store, this must be done before calling
    // parent::form().
    $store = $this->tempStoreFactory->get('passbook_preview');

    // Attempt to load from preview when the uuid is present unless we are
    // rebuilding the form.
    $request_uuid = \Drupal::request()->query->get('uuid');
    if (!$form_state->isRebuilding() && $request_uuid && $preview = $store->get($request_uuid)) {
      /* @var $preview \Drupal\Core\Form\FormStateInterface */

      $form_state->setStorage($preview->getStorage());
      $form_state->setUserInput($preview->getUserInput());

      // Rebuild the form.
      $form_state->setRebuild();

      // The combination of having user input and rebuilding the form means
      // that it will attempt to cache the form state which will fail if it is
      // a GET request.
      $form_state->setRequestMethod('POST');

      $this->entity = $preview->getFormObject()->getEntity();
      $this->entity->inPreview = NULL;

      $form_state->set('has_been_previewed', TRUE);
    }

    /** @var \Drupal\passbook\Entity\PassbookInterface $entity */
    $entity = $this->entity;

    if ($this->operation == 'edit') {
      $form['#title'] = $this->t('<em>Edit @type</em> @title', ['@type' => PassbookType::load($entity->bundle())->label(), '@title' => $entity->label()]);
    }

    // Changed must be sent to the client, for later overwrite error checking.
    $form['changed'] = [
      '#type' => 'hidden',
      '#default_value' => $entity->getChangedTime(),
    ];

    $form = parent::form($form, $form_state);

    $form['advanced']['#attributes']['class'][] = 'entity-meta';

    // Entity author information for administrators.
    $form['author'] = [
      '#type' => 'details',
      '#title' => t('Authoring information'),
      '#group' => 'advanced',
      '#attributes' => [
        'class' => ['form-author-data'],
      ],
      '#attached' => [
        'library' => ['passbook/passbook.edit.form'],
      ],
      '#weight' => 90,
      '#optional' => TRUE,
    ];

    if (isset($form['uid'])) {
      $form['uid']['#group'] = 'author';
    }

    if (isset($form['created'])) {
      $form['created']['#group'] = 'author';
    }

    $form['#entity_builders']['update_status'] = '::updateStatus';

    return $form;
  }

  /**
   * Entity builder updating the entity status with the submitted value.
   *
   * @param string $entity_type_id
   *   The entity type identifier.
   * @param \Drupal\passbook\Entity\PassbookInterface $entity
   *   The entity updated with the submitted values.
   * @param array $form
   *   The complete form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function updateStatus($entity_type_id, PassbookInterface $entity, array $form, FormStateInterface $form_state) {
    $element = $form_state->getTriggeringElement();
    if (isset($element['#published_status'])) {
      $entity->setPublished($element['#published_status']);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);
    $entity = $this->entity;
    $preview_mode = $entity->type->entity->getPreviewMode();

    $element['submit']['#access'] = $preview_mode != DRUPAL_REQUIRED || $form_state->get('has_been_previewed');

    // If saving is an option, privileged users get dedicated form submit
    // buttons to adjust the publishing status while saving in one go.
    if ($element['submit']['#access'] && \Drupal::currentUser()->hasPermission('administer passbooks')) {
      // Add a "Publish" button.
      $element['publish'] = $element['submit'];
      // If the "Publish" button is clicked,  update the status to "published".
      $element['publish']['#published_status'] = TRUE;
      $element['publish']['#dropbutton'] = 'save';
      if ($entity->isNew()) {
        $element['publish']['#value'] = t('Save and publish');
      }
      else {
        $element['publish']['#value'] = $entity->isPublished() ? t('Save and keep published') : t('Save and publish');
      }
      $element['publish']['#weight'] = 0;

      // Add a "Unpublish" button.
      $element['unpublish'] = $element['submit'];
      // If the "Unpublish" button is clicked,
      // update the status to "unpublished".
      $element['unpublish']['#published_status'] = FALSE;
      $element['unpublish']['#dropbutton'] = 'save';
      if ($entity->isNew()) {
        $element['unpublish']['#value'] = t('Save as unpublished');
      }
      else {
        $element['unpublish']['#value'] = !$entity->isPublished() ? t('Save and keep unpublished') : t('Save and unpublish');
      }
      $element['unpublish']['#weight'] = 10;

      // If already published, the 'publish' button is primary.
      if ($entity->isPublished()) {
        unset($element['unpublish']['#button_type']);
      }
      // Otherwise, the 'unpublish' button is primary and should come first.
      else {
        unset($element['publish']['#button_type']);
        $element['unpublish']['#weight'] = -10;
      }

      // Remove the "Save" button.
      $element['submit']['#access'] = FALSE;
    }

    $element['preview'] = [
      '#type' => 'submit',
      '#access' => $preview_mode != DRUPAL_DISABLED && ($entity->access('create') || $entity->access('update')),
      '#value' => t('Preview'),
      '#weight' => 20,
      '#submit' => ['::submitForm', '::preview'],
    ];

    $element['delete']['#access'] = $entity->access('delete');
    $element['delete']['#weight'] = 100;

    return $element;
  }

  /**
   * Form submission handler for the 'preview' action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function preview(array $form, FormStateInterface $form_state) {
    $store = $this->tempStoreFactory->get('_preview');
    $this->entity->inPreview = TRUE;
    $store->set($this->entity->uuid(), $form_state);

    $route_parameters = [
      'passbook_preview' => $this->entity->uuid(),
      'view_mode_id' => 'full',
    ];

    $options = [];
    $query = $this->getRequest()->query;
    if ($query->has('destination')) {
      $options['query']['destination'] = $query->get('destination');
      $query->remove('destination');
    }
    $form_state->setRedirect('entity.passbook.preview', $route_parameters, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $insert = $entity->isNew();
    $entity->save();
    $entity_link = $entity->toLink($this->t('View'))->toString();
    $context = [
      '@type' => $entity->getType(),
      '%title' => $entity->label(),
      'link' => $entity_link,
    ];
    $t_args = ['@type' => PassbookType::load($entity->bundle())->label(), '%title' => $entity->toLink($entity->label())->toString()];

    if ($insert) {
      $this->logger('passbook')->notice('@type: added %title.', $context);
      drupal_set_message(t('@type %title has been created.', $t_args));
    }
    else {
      $this->logger('passbook')->notice('@type: updated %title.', $context);
      drupal_set_message(t('@type %title has been updated.', $t_args));
    }

    if ($entity->id()) {
      $form_state->setValue('id', $entity->id());
      $form_state->set('id', $entity->id());
      if ($entity->access('view')) {
        $form_state->setRedirect(
          'entity.passbook.canonical',
          ['passbook' => $entity->id()]
        );
      }
      else {
        $form_state->setRedirect('<front>');
      }

      // Remove the preview entry from the temp store, if any.
      $store = $this->tempStoreFactory->get('passbook_preview');
      $store->delete($entity->uuid());
    }
    else {
      // In the unlikely case something went wrong on save, the entity will be
      // rebuilt and entity form redisplayed the same way as in preview.
      drupal_set_message(t('The post could not be saved.'), 'error');
      $form_state->setRebuild();
    }
  }

}
