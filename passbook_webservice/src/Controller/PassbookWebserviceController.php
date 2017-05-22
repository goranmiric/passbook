<?php

namespace Drupal\passbook_webservice\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Drupal\passbook_webservice\Services\WebserviceManager;

/**
 * Returns responses for routes.
 */
class PassbookWebserviceController extends ControllerBase {

  /**
   * Passbook webservice manager.
   *
   * @var \Drupal\passbook_webservice\Services\WebserviceManager
   */
  protected $webserviceManager;

  /**
   * Constructor.
   *
   * @param \Drupal\passbook_webservice\Services\WebserviceManager
   *   Passbook webservice manager.
   */
  public function __construct(WebserviceManager $webserviceManager) {
    $this->webserviceManager = $webserviceManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('passbook_webservice.manager')
    );
  }

  /**
   * Registering a Device to Receive Push Notifications for a Pass.
   */
  public function pushNotifications($deviceLibraryIdentifier, $passTypeIdentifier, $serialNumber, Request $request) {
    // If the request is not authorized, returns HTTP status 401.
    if(strstr($request->headers->get('Authorization'), 'ApplePass')) {
      return new Response('', 401);
    }

    $data = json_decode(file_get_contents("php://input"));
    $code = $this->webserviceManager->pushNotifications($deviceLibraryIdentifier, $data);

    $query = 'SELECT count(*) as nb FROM passbook_registrations WHERE device_library_identifier = "' . $deviceLibraryIdentifier . '"';
    $nb = $pdo->query($query)->fetchColumn();
    $data = json_decode(file_get_contents("php://input"));
    $pushtoken=$data->pushToken;
    //If a passbook registration exist : It's updated with a new push token
    if($nb > 0) {
      $query = 'INSERT INTO passbook_log VALUES(null, "Num série déjà enregistré", '.time().')';
      $pdo->exec($query);
      $queryUpdatePushToken = 'UPDATE passbook_registrations SET push_token = "'.$pushtoken.'", updated_at = NOW() WHERE device_library_identifier = "'.$deviceLibraryIdentifier.'"';
      $pdo->exec($queryUpdatePushToken);
      return new Response('', 200);
    }
    else {
      $query = 'INSERT INTO passbook_log VALUES(null, "Enregistrement du num de série", '.time().')';
      $pdo->exec($query);
      $queryRegistration = 'INSERT INTO passbook_registrations VALUES(null, 1, "'.$deviceLibraryIdentifier.'", "'.$pushtoken.'", NOW(), NOW())';
      $pdo->exec($queryRegistration);
      return new Response('', 201);
    }


    return new Response('', 201);
  }

  /**
   * Getting the Serial Numbers for Passes Associated with a Device.
   */
  public function get($deviceLibraryIdentifier, $passTypeIdentifier) {
    return new Response('', 204);
  }

  /**
   * Getting the Latest Version of a Pass.
   */
  public function latest($passTypeIdentifier, $serialNumber) {
    return new Response('', 301);
  }

}
