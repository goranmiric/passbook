<?php

namespace Drupal\passbook\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\passbook\PassbookStorageInterface;
use Drupal\passbook\Entity\PassbookTypeInterface;
use Drupal\passbook\Entity\PassbookInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for routes.
 */
class PassbookController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(DateFormatterInterface $date_formatter, RendererInterface $renderer) {
    $this->dateFormatter = $date_formatter;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('renderer')
    );
  }

  /**
   * Displays add content links for available types.
   *
   * Redirects to passbook/add/[type] if only one type is available.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   A render array for a list of the types that can be added; however,
   *   if there is only one type defined for the site, the function
   *   will return a RedirectResponse to the passbook add page for that type.
   */
  public function addPage() {
    $build = [
      '#theme' => 'passbook_add_list',
      '#cache' => [
        'tags' => $this->entityTypeManager()->getDefinition('passbook_type')->getListCacheTags(),
      ],
    ];

    $content = [];

    // Only use types the user has access to.
    foreach ($this->entityTypeManager()->getStorage('passbook_type')->loadMultiple() as $type) {
      $access = $this->entityTypeManager()->getAccessControlHandler('passbook')->createAccess($type->id(), NULL, [], TRUE);
      if ($access->isAllowed()) {
        $content[$type->id()] = $type;
      }
      $this->renderer->addCacheableDependency($build, $access);
    }

    // Bypass the passbook/add listing if only one content type is available.
    if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('passbook.add', ['passbook_type' => $type->id()]);
    }

    $build['#content'] = $content;

    return $build;
  }

  /**
   * Provides the passbook submission form.
   *
   * @param \Drupal\passbook\Entity\PassbookTypeInterface $passbook_type
   *   The passbook type entity for the passbook.
   *
   * @return array
   *   A passbook submission form.
   */
  public function add(PassbookTypeInterface $passbook_type) {
    $passbook = $this->entityTypeManager()->getStorage('passbook')->create([
      'type' => $passbook_type->id(),
      'pass_type' => $passbook_type->passType(),
    ]);

    $form = $this->entityFormBuilder()->getForm($passbook);

    return $form;
  }

  /**
   * Displays a revision.
   *
   * @param int $passbook_revision
   *   The revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($passbook_revision) {
    $entity = $this->entityManager()->getStorage('passbook')->loadRevision($passbook_revision);
    $entity = $this->entityManager()->getTranslationFromContext($entity);
    $view_controller = new PassbookViewController($this->entityManager, $this->renderer, $this->currentUser());
    $page = $view_controller->view($entity);
    unset($page['passbooks'][$entity->id()]['#cache']);

    return $page;
  }

  /**
   * Page title callback for a revision.
   *
   * @param int $passbook_revision
   *   The revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($passbook_revision) {
    $entity = $this->entityTypeManager()->getStorage('passbook')->loadRevision($passbook_revision);
    return $this->t('Revision of %title from %date', ['%title' => $entity->label(), '%date' => format_date($entity->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions.
   *
   * @param \Drupal\passbook\Entity\PassbookInterface $passbook
   *   A entity object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(PassbookInterface $passbook) {
    $account = $this->currentUser();
    $langcode = $passbook->language()->getId();
    $langname = $passbook->language()->getName();
    $languages = $passbook->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $storage = $this->entityTypeManager()->getStorage('passbook');
    $type = $passbook->getType();

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $passbook->label()]) : $this->t('Revisions for %title', ['%title' => $passbook->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert $type revisions") || $account->hasPermission('revert all revisions') || $account->hasPermission('administer passbook')) && $passbook->access('update'));
    $delete_permission = (($account->hasPermission("delete $type revisions") || $account->hasPermission('delete all revisions') || $account->hasPermission('administer passbook')) && $passbook->access('delete'));

    $rows = [];
    $default_revision = $passbook->getRevisionId();

    foreach ($this->getRevisionIds($passbook, $storage) as $vid) {
      /** @var \Drupal\passbook\Entity\PassbookInterface $revision */
      $revision = $storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->revision_timestamp->value, 'short');
        if ($vid != $passbook->getRevisionId()) {
          $link = $this->l($date, new Url('entity.passbook.revision', ['passbook' => $passbook->id(), 'passbook_revision' => $vid]));
        }
        else {
          $link = $passbook->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => ['#markup' => $revision->revision_log->value, '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];

        $this->renderer->addCacheableDependency($column['data'], $username);
        $row[] = $column;

        if ($vid == $default_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];

          $rows[] = [
            'data' => $row,
            'class' => ['revision-current'],
          ];
        }
        else {
          $links = [];
          if ($revert_permission) {
            $params = [
              'passbook' => $passbook->id(),
              'passbook_revision' => $vid,
            ];
            $has_translations ? $params['langcode'] = $langcode : NULL;

            $links['revert'] = [
              'title' => $vid < $passbook->getRevisionId() ? $this->t('Revert') : $this->t('Set as current revision'),
              'url' => $has_translations ? Url::fromRoute('passbook.revision_revert_translation_confirm', $params) : Url::fromRoute('passbook.revision_revert_confirm', $params),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('passbook.revision_delete_confirm', ['passbook' => $passbook->id(), 'passbook_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];

          $rows[] = $row;
        }
      }
    }

    $build['passbook_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    $build['pager'] = ['#type' => 'pager'];

    return $build;
  }

  /**
   * The _title_callback for the passbook.add route.
   *
   * @param \Drupal\passbook\Entity\PassbookTypeInterface $passbook_type
   *   The current entity.
   *
   * @return string
   *   The page title.
   */
  public function addPageTitle(PassbookTypeInterface $passbook_type) {
    return $this->t('Create @name', ['@name' => $passbook_type->label()]);
  }

  /**
   * Gets a list of revision IDs.
   *
   * @param \Drupal\passbook\Entity\PassbookInterface $passbook
   *   The entity.
   * @param \Drupal\passbook\PassbookStorageInterface $storage
   *   The entity storage handler.
   *
   * @return int[]
   *   Entity revision IDs (in descending order).
   */
  protected function getRevisionIds(PassbookInterface $passbook, PassbookStorageInterface $storage) {
    $result = $storage->getQuery()
      ->allRevisions()
      ->condition($passbook->getEntityType()->getKey('id'), $passbook->id())
      ->sort($passbook->getEntityType()->getKey('revision'), 'DESC')
      ->pager(50)
      ->execute();
    return array_keys($result);
  }

}
