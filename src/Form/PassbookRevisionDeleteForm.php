<?php

namespace Drupal\passbook\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a revision.
 */
class PassbookRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The revision.
   *
   * @var \Drupal\passbook\Entity\PassbookInterface
   */
  protected $revision;

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityStorage;

  /**
   * The entity type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityTypeStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage.
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_type_storage
   *   The entity type storage.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(EntityStorageInterface $entity_storage, EntityStorageInterface $entity_type_storage, Connection $connection) {
    $this->entityStorage = $entity_storage;
    $this->entityTypeStorage = $entity_type_storage;
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $entity_manager = $container->get('entity.manager');
    return new static(
      $entity_manager->getStorage('passbook'),
      $entity_manager->getStorage('passbook_type'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'passbook_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete the revision from %revision-date?', ['%revision-date' => format_date($this->revision->getRevisionCreationTime())]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.passbook.version_history', ['passbook' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $passbook_revision = NULL) {
    $this->revision = $this->entityStorage->loadRevision($passbook_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entityStorage->deleteRevision($this->revision->getRevisionId());

    $type = $this->entityTypeStorage->load($this->revision->bundle())->label();
    drupal_set_message(t('Revision from %revision-date of @type %title has been deleted.', [
      '%revision-date' => format_date($this->revision->getRevisionCreationTime()),
      '@type' => $type,
      '%title' => $this->revision->label(),
    ]));

    $form_state->setRedirect(
      'entity.passbook.canonical',
      ['passbook' => $this->revision->id()]
    );

    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {passbook_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.passbook.version_history',
        ['passbook' => $this->revision->id()]
      );
    }
  }

}
