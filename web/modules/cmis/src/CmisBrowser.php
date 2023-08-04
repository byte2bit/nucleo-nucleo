<?php

declare(strict_types = 1);

namespace Drupal\cmis;

use Dkd\PhpCmis\Data\FolderInterface;
use Dkd\PhpCmis\Enum\Action;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\sm_cmis\CMISException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of CmisBrowser.
 */
class CmisBrowser {

  use StringTranslationTrait;

  /**
   * Configuration id.
   *
   * @var string
   */
  protected $config;

  /**
   * Connection object.
   *
   * @var object
   */
  protected $connection;

  /**
   * The renderable content data.
   *
   * @var array
   */
  protected $data;

  /**
   * Parent folders list data. A list of links.
   *
   * @var array
   */
  protected $breadcrumbs = [];

  /**
   * Folder id to browse.
   *
   * @var string
   */
  protected $folderId;

  /**
   * Current object.
   *
   * @var \Dkd\PhpCmis\Data\FolderInterface
   */
  protected $current;

  /**
   * The browser popup flag.
   *
   * @var bool
   */
  protected $popup;

  /**
   * The browser cacheable flag.
   *
   * @var bool
   */
  protected $cacheable;

  /**
   * Show breadcrumb in field browser formatter.
   *
   * @var array
   */
  protected $additionalSettings;

  /**
   * Constructing the object.
   *
   * @param string $config
   *   Entity label.
   * @param string $folder_id
   *   CMIS folder id.
   * @param array $additional_settings
   *   Show additional_settings in field browser formatter.
   */
  public function __construct($config = '', $folder_id = '', array $additional_settings = []) {
    if (!empty($config)) {
      $this->init($config, $folder_id);
    }
    $this->additionalSettings = $additional_settings;
  }

  /**
   * Call from ajaxify url.
   *
   * @param string $config
   *   Entity label.
   * @param string $folder_id
   *   CMIS folder id.
   */
  public function ajaxCall($config = '', $folder_id = '') {
    \Drupal::service('cmis.connection_api')->checkConnectionIsAlive($config, TRUE);
    $this->init($config, $folder_id);
    if ($this->connection && !empty($this->current) && $browse = $this->browse()) {
      $response = new AjaxResponse();
      $content = render($browse);
      $response->addCommand(new HtmlCommand('#cmis-browser-wrapper', $content));

      return $response;
    }
  }

  /**
   * Get document by id.
   *
   * @param string $config
   *   Entity label.
   * @param string $document_id
   *   CMIS document id.
   */
  public function getDocument($config = '', $document_id = '') {
    \Drupal::service('cmis.connection_api')->checkConnectionIsAlive($config);
    $this->init($config, $document_id, 'cmis:document');
    if ($this->connection && !empty($this->current) &&
        $this->current->getBaseTypeId()->__toString() == 'cmis:document') {
      $id = $this->current->getId();
      $content = '';
      try {
        $content = $this->current->getContentStream($id);
      }
      catch (CMISException $e) {
        // TODO: testing this.
        $headers = ['' => 'HTTP/1.1 503 Service unavailable'];
        $response = new Response($content, 503, $headers);
        $response->send();
        exit();
      }

      $mime = $this->current->getContentStreamMimeType();
      $headers = [
        'Cache-Control' => 'no-cache, must-revalidate',
        'Content-type' => $mime,
        'Content-Disposition' => 'attachment; filename="' . $this->current->getName() . '"',
      ];
      $response = new Response($content, 200, $headers);
      $response->send();

      // TODO: Why a print and an exit?
      print($content);
      exit();
    }
  }

  /**
   * Get document properties.
   *
   * @return array
   *   the renderable array
   */
  public function getDocumentProperties() {
    if ($this->connection && !empty($this->current)) {
      $type_id = $this->current->getBaseTypeId()->__toString();
      $path = [];
      if ($type_id == 'cmis:document') {
        $url = Url::fromUserInput('/cmis/document/' . $this->config . '/' . $this->current->getId());
        $path = Link::fromTextAndUrl($this->t('Download'), $url)->toRenderable();
      }

      return [
        '#theme' => 'cmis_content_properties',
        '#object' => $this->current,
        '#download' => render($path),
      ];
    }
  }

  /**
   * Init variables.
   *
   * @param string $config
   *   Entity label.
   * @param string $folder_id
   *   CMIS folder id.
   */
  private function init($config, $folder_id) {
    $this->config = $config;
    $this->folderId = $folder_id;
    $this->connection = new CmisConnectionApi($this->config);
    // $cacheable = $this->connection->getConfig()->getCmisCacheable();
    // @TODO: find out the best cache options.
    // $cache_parameters = [
    // 'contexts' => ['user'],
    // 'max-age' => $cacheable ? 300 : 0,
    // ];
    // $this->cacheable = $cache_parameters;.
    if (!empty($this->connection->getHttpInvoker())) {
      $popup = \Drupal::request()->query->get('type');
      $this->popup = ($popup == 'popup');
      $this->connection->setDefaultParameters();

      if (empty($this->folderId)) {
        $root_folder = $this->connection->getRootFolder();
        $this->folderId = $root_folder->getId();
        $this->current = $root_folder;
      }
      else {
        $this->current = $this->connection->getObjectById($this->folderId);
      }
    }
  }

  /**
   * Get current object.
   *
   * @return object
   *   Return root folder or object id.
   */
  public function getCurrent() {
    return $this->current;
  }

  /**
   * Get connection.
   *
   * @return object
   *   Return CmisConnectionApi.
   */
  public function getConnection() {
    return $this->connection;
  }

  /**
   * Browse.
   *
   * @return array
   *   Return cmis browser render array.
   */
  public function browse($reset = FALSE) {
    if ($this->connection && !empty($this->current)) {

      $this->setBreadcrumbs($this->current);
      $this->printFolderContent($this->current);

      $table_header = [
        $this->t('Name'),
        $this->t('Details'),
        $this->t('Author'),
        $this->t('Created'),
        $this->t('Description'),
        $this->t('Operation'),
      ];

      $browse = [
        '#theme' => 'cmis_browser',
        '#header' => $table_header,
        '#elements' => $this->data,
        '#breadcrumbs' => $this->breadcrumbs,
        '#operations' => $this->prepareOperations(),
        '#attached' => [
          'library' => [
            'cmis/cmis-browser',
          ],
        ],
      ];

      return $browse;
    }

    return [];
  }

  /**
   * Prepare operation links.
   *
   * @return array|string
   *   Return rendered element.
   */
  private function prepareOperations() {
    $create = [];
    $current_object_id = $this->current->getId();
    if ($this->connection->hasAllowableActionById($current_object_id, Action::CAN_CREATE_FOLDER)) {
      $create = [
        '/cmis/browser-create-folder/' => $this->t('Create folder'),
      ];
    }

    $upload = [];
    if ($this->connection->hasAllowableActionById($current_object_id, Action::CAN_CREATE_DOCUMENT)) {
      $upload = [
        '/cmis/browser-upload-document/' => $this->t('Add document'),
      ];
    }

    $links = [];
    $routes = array_merge($create, $upload);
    if (isset($routes)) {
      foreach ($routes as $route => $title) {
        $url = Url::fromUserInput($route . $this->config . '/' . $current_object_id);
        $route_name = \Drupal::service('current_route_match')->getRouteName();

        // Redirect Form to current page.
        if (strpos($route_name, 'browser') !== FALSE) {
          $url_destination = Url::fromRoute('cmis.cmis_repository_controller_browser', ['config' => $this->config, 'folder_id' => $current_object_id]);
        }
        else {
          $url_destination = Url::fromRoute('<current>');
        }

        $link_options = [
          'query' => [
            'destination' => $url_destination->toString(),
          ],
          'attributes' => [
            'class' => ['use-ajax'],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => Json::encode([
              'width' => 700,
            ]),
          ],
        ];
        $url->setOptions($link_options);
        $path = Link::fromTextAndUrl($title, $url)->toRenderable();
        $links[] = [
          '#markup' => render($path),
          '#wrapper_attributes' => [
            'class' => ['object-properties'],
          ],
        ];
      }

      $list = [
        '#theme' => 'item_list',
        '#items' => $links,
        '#type' => 'ul',
      ];

      return render($list);
    }
  }

  /**
   * Add folder objects to render array.
   *
   * @param \Dkd\PhpCmis\Data\FolderInterface $folder
   *   A CMIS folder object.
   */
  protected function printFolderContent(FolderInterface $folder) {
    $root = $this->connection->getRootFolder();
    $element = new CmisElement($this->config, $this->popup, $this->current, '', $root->getId(), $this->additionalSettings);
    $node = \Drupal::routeMatch()->getParameter('node');
    $type = 'browser';
    if ($node instanceof NodeInterface) {
      $type = 'node';
    }
    foreach ($folder->getChildren() as $children) {
      $element->setElement($type, $children);
      $this->data[] = $element->getData();
    }
  }

  /**
   * Create breadcrumbs from parent folders.
   *
   * @param \Dkd\PhpCmis\Data\FolderInterface $folder
   *   Folder name.
   */
  protected function setBreadcrumbs(FolderInterface $folder) {
    $route_name = \Drupal::service('current_route_match')->getRouteName();

    // If the route is not a controller
    // or show_breadcrumb variable is enable, we display it.
    if (strpos($route_name, 'browser') !== FALSE || ($this->additionalSettings && $this->additionalSettings['show_breadcrumb'])) {
      $name = $folder->getName();
      $id = $folder->getId();

      $entity_config = \Drupal::service('cmis.connection_api')->getConfigFromId($this->config);
      if ($id !== $entity_config->getCmisRootFolder()) {
        $this->setBreadcrumb($name, $id);
        if ($parent = $folder->getFolderParent()) {
          $this->setBreadcrumbs($parent);
        }
      }
      // Root folder.
      else {
        $this->setBreadcrumb($this->t('Root'), $id);
      }
    }
  }

  /**
   * Prepare a breadcrumb url.
   *
   * @param mixed $label
   *   Breadcrumb label.
   * @param string $id
   *   Folder id.
   */
  protected function setBreadcrumb($label, $id) {
    $path = '/cmis/browser/nojs/' . $this->config;
    if (!empty($id)) {
      $path .= '/' . $id;
    }
    $url = Url::fromUserInput($path);
    $link_options = [
      'attributes' => [
        'class' => [
          'use-ajax',
        ],
      ],
    ];
    if ($this->popup) {
      $link_options['query'] = ['type' => 'popup'];
    }
    $url->setOptions($link_options);

    $item = Link::fromTextAndUrl($label, $url);

    array_unshift($this->breadcrumbs, $item);
  }

}
