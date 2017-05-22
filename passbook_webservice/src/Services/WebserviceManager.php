<?php

namespace Drupal\passbook_webservice\Services;

use Drupal\Core\Database\Connection;
use Drupal\Component\Datetime\Time;
/**
 * Webservice manager service.
 */
class WebserviceManager {

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Date time service.
   * @var \Drupal\Component\Datetime\Time
   */
  protected $time;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database service.
   */
  public function __construct(Connection $connection, Time $time) {
    $this->connection = $connection;
    $this->time = $time;
  }

  /**
   * Push notification.
   *
   * @param string $deviceLibraryIdentifier
   *   Pass deviceLibraryIdentifier.
   * @param object $data
   *   The ApplePass object.
   *
   * @return int
   *   Status code.
   */
  public function pushNotifications($deviceLibraryIdentifier, $data)  {
    $count = $this->countPassbookRegistrations($deviceLibraryIdentifier);
    //If a passbook registration exist : It's updated with a new push token
    if ($count > 0) {
//      $this->updatePassbookRegistrations($deviceLibraryIdentifier);
//      $query = 'INSERT INTO passbook_log VALUES(null, "Num série déjà enregistré", '.time().')';
//      $pdo->exec($query);

      return $this->updatePassbookRegistrations($deviceLibraryIdentifier, $data);
    }
    else {
//      $query = 'INSERT INTO passbook_log VALUES(null, "Enregistrement du num de série", '.time().')';
//      $pdo->exec($query);

      return $this->addPassbookRegistrations($deviceLibraryIdentifier, $data);
    }
  }

  /**
   * Count number of registration.
   *
   * @param string $deviceLibraryIdentifier
   *   Passbook deviceLibraryIdentifier.
   *
   * @return int
   *   Number of results.
   */
  public function countPassbookRegistrations($deviceLibraryIdentifier) {
    $query = $this->connection->select('passbook_registrations', 'pr');
    $query->fields('pr' ['id']);
    $query->condition('pr.device_library_identifier', $deviceLibraryIdentifier);

    return $query->execute()->rowCount();
  }

  /**
   * Update registration.
   *
   * @param string $deviceLibraryIdentifier
   *   Passbook deviceLibraryIdentifier.
   * @param object $data
   *   The ApplePass object.
   *
   * @return int
   *   Number of results.
   */
  public function updatePassbookRegistrations($deviceLibraryIdentifier, $data) {

    $this->connection->update('passbook_registrations')
      ->fields([
        'push_token' => $data->pushToken,
        'updated' => $this->time->getRequestTime(),
      ])
      ->condition('device_library_identifier', $deviceLibraryIdentifier)
      ->execute();

    return 200;
  }

  /**
   * Add registration.
   *
   * @param string $deviceLibraryIdentifier
   *   Passbook deviceLibraryIdentifier.
   * @param object $data
   *   The ApplePass object.
   *
   * @return int
   *   Number of results.
   */
  public function addPassbookRegistrations($deviceLibraryIdentifier, $data) {

    $this->connection->insert('passbook_registrations')
      ->fields([
        'pass_id' => 1,
        'device_library_identifier' => $deviceLibraryIdentifier,
        'push_token' => $data->pushToken,
        'created' => $this->time->getRequestTime(),

      ])
      ->execute();

    return 201;
  }

}
