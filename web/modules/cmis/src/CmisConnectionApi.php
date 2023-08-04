<?php

declare(strict_types = 1);

namespace Drupal\cmis;

use Dkd\PhpCmis\Enum\BindingType;
use Dkd\PhpCmis\SessionFactory;
use Dkd\PhpCmis\SessionParameter;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Message;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Description of CmisConnectionApi.
 */
class CmisConnectionApi {

  use StringTranslationTrait;

  /**
   * The configuration entity.
   *
   * @var object
   */
  private $config;

  /**
   * Http invoker.
   *
   * @var object
   */
  private $httpInvoker;

  /**
   * The parameters for connection type.
   *
   * @var array
   */
  private $parameters;

  /**
   * The session factory for connection.
   *
   * @var object
   */
  private $sessionFactory;

  /**
   * The session of connection.
   *
   * @var mixed
   */
  private $session;

  /**
   * The root folder of CMIS repository.
   *
   * @var mixed
   */
  private $rootFolder;

  /**
   * {@inheritdoc}
   */
  public function __construct($config = '') {
    $this->setConfig($config);
  }

  /**
   * Set the configuration from configuration id.
   *
   * @param string $config_id
   *   Entity label.
   */
  private function setConfig($config_id) {
    $storage = \Drupal::entityTypeManager()->getStorage('cmis_connection_entity');
    if ($this->config = $storage->load($config_id)) {
      $this->setHttpInvoker();
    }
  }

  /**
   * Get configuration of this connection.
   *
   * @return mixed
   *   Return entity config.
   */
  public function getConfig() {
    return $this->config;
  }

  /**
   * Get the configuration from configuration id.
   *
   * @param string $config_id
   *   Entity label.
   */
  public function getConfigFromId($config_id) {
    $storage = \Drupal::entityTypeManager()->getStorage('cmis_connection_entity');
    return $storage->load($config_id);
  }

  /**
   * Set Http invoker.
   */
  private function setHttpInvoker() {
    if (\Drupal::currentUser()->isAuthenticated()) {
      if (!empty($this->config->getCmisUser()) && !empty($this->config->getCmisPassword())) {
        $auth = [
          'auth' => [
            $this->config->getCmisUser(),
            $this->config->getCmisPassword(),
          ],
        ];
      }
      else {
        $tempstore = \Drupal::service('tempstore.private')
          ->get('cmis_alfresco_auth_user');
        $auth = [
          'headers' => [
            'Authorization' => 'Basic ' . $tempstore->get('ticket'),
          ],
        ];
      }
      $this->httpInvoker = new Client($auth);
    }
  }

  /**
   * Get Http invoker.
   *
   * @return object
   *   Return httpInvoker.
   */
  public function getHttpInvoker() {
    return $this->httpInvoker;
  }

  /**
   * Set default parameters.
   */
  public function setDefaultParameters() {
    $parameters = [
      SessionParameter::BINDING_TYPE => BindingType::BROWSER,
      SessionParameter::BROWSER_URL => $this->getConfig()->getCmisUrl(),
      SessionParameter::BROWSER_SUCCINCT => FALSE,
      SessionParameter::HTTP_INVOKER_OBJECT => $this->getHttpInvoker(),
    ];

    $this->setParameters($parameters);
  }

  /**
   * Set parameters.
   *
   * @param array $parameters
   *   CMIS url parameters.
   */
  public function setParameters(array $parameters) {
    $this->parameters = $parameters;
    $this->setSessionFactory();
  }

  /**
   * Get parameters.
   *
   * @return array
   *   Return cmis url parameter object.
   */
  public function getParameters() {
    return $this->parameters;
  }

  /**
   * Set session factory.
   */
  private function setSessionFactory() {
    $this->sessionFactory = new SessionFactory();
    $this->setRepository();
  }

  /**
   * Get session factory.
   *
   * @return mixed
   *   Return SessionFactory class.
   */
  public function getSessionFactory() {
    return $this->sessionFactory;
  }

  /**
   * Set repository.
   */
  private function setRepository() {
    $repository_id = $this->config->getCmisRepository();
    // If no repository id is defined use the first repository.
    if ($repository_id === NULL || $repository_id == '') {
      $repositories = $this->sessionFactory->getRepositories($this->parameters);
      $this->parameters[SessionParameter::REPOSITORY_ID] = $repositories[0]->getId();
    }
    else {
      $this->parameters[SessionParameter::REPOSITORY_ID] = $repository_id;
    }

    $this->session = $this->sessionFactory->createSession($this->parameters);
    $this->setRootFolder();
  }

  /**
   * Get session.
   *
   * @return object
   *   Return createSession from SessionFactory class.
   */
  public function getSession() {
    return $this->session;
  }

  /**
   * Set the root folder of the repository.
   */
  private function setRootFolder() {
    $this->rootFolder = $this->session->getRootFolder();
  }

  /**
   * Get root folder of repository.
   *
   * @return object
   *   Return rootFolder.
   */
  public function getRootFolder() {
    return $this->rootFolder;
  }

  /**
   * Get object by object id.
   *
   * @param string $id
   *   Object id.
   *
   * @return object
   *   Return the current object or null.
   */
  public function getObjectById($id = '') {
    if (empty($id)) {
      return NULL;
    }

    if (!empty($this->validObjectId($id) || !empty($this->validObjectId($id, 'cmis:document')))) {
      $cid = $this->session->createObjectId($id);
      $object = $this->session->getObject($cid);

      return $object;
    }

    return NULL;
  }

  /**
   * Check the id is valid object.
   *
   * @param string $id
   *   CMIS object id.
   * @param string $type
   *   CMIS type.
   * @param string $parentId
   *   CMIS parent id.
   *
   * @return object
   *   the result object or empty array
   */
  public function validObjectId($id, $type = 'cmis:folder', $parentId = '') {
    $where = "cmis:objectId='$id'";
    if (!empty($parentId)) {
      $where .= " AND IN_FOLDER('$parentId')";
    }

    $result = $this->session->queryObjects($type, $where);

    return $result;
  }

  /**
   * Check the name is valid object.
   *
   * @param string $name
   *   CMIS object name.
   * @param string $type
   *   CMIS type.
   * @param string $parentId
   *   CMIS parent id.
   *
   * @return object
   *   the result object or empty array
   */
  public function validObjectName($name, $type = 'cmis:folder', $parentId = '') {
    $query = "SELECT * FROM $type WHERE cmis:name='$name'";
    if (!empty($parentId)) {
      $query .= " and IN_FOLDER('$parentId')";
    }
    $result = $this->session->query($query);

    return $result;
  }

  /**
   * Check CMIS Connection is Alive.
   *
   * @param string $config_id
   *   Config ID.
   * @param bool $is_ajax
   *   Is call is AJAX.
   */
  public function checkConnectionIsAlive($config_id, $is_ajax = FALSE) {
    $tempstore = \Drupal::service('tempstore.private')->get('cmis_alfresco_auth_user');
    if ($tempstore->get('ticket')) {
      $this->setConfig($config_id);
      $this->setHttpInvoker();
      try {
        $this->httpInvoker->request('GET', $this->getConfig()->getCmisUrl());
        return TRUE;
      }
      catch (RequestException $e) {
        // @todo It would be better to fire an event. See
        // https://www.drupal.org/project/cmis/issues/3162184.
        $guzzle_request = Message::toString($e->getRequest());
        \Drupal::logger('cmis')->notice($guzzle_request);

        if ($e->getCode() === 401) {
          $guzzle_response = Message::toString($e->getResponse());
          \Drupal::logger('cmis')->notice($guzzle_response);

          if (\Drupal::currentUser()->isAuthenticated()) {
            user_logout();
            if ($is_ajax) {
              $command = new RedirectCommand(Url::fromRoute('user.login')->toString());
              $response = new AjaxResponse();
              $response->addCommand($command);
            }
            else {
              $response = new RedirectResponse(Url::fromRoute('user.login')->toString());
              $response->send();
            }
          }
        }
      }
      catch (\Exception $e) {
        \Drupal::logger('cmis')->error($e->getMessage());
      }
      return FALSE;
    }
  }

  /**
   * Check if object id has the allowable action.
   *
   * @param string $id
   *   CMIS object ID.
   * @param string $action
   *   Get action.
   *
   * @return bool
   *   Return TRUE if object id has the allowable action.
   */
  public function hasAllowableActionById($id, $action) {
    if (empty($id)) {
      return FALSE;
    }

    $cid = $this->session->createObjectId($id);
    $object = $this->session->getObject($cid);
    $get_actions = $object->getAllowableActions()->getAllowableActions();

    foreach ($get_actions as $get_action) {
      if ($get_action->equals($action)) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
