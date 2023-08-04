<?php

declare(strict_types = 1);

namespace Drupal\cmis;

use Dkd\PhpCmis\DataObjects\AbstractFileableCmisObject;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Dkd\PhpCmis\Enum\Action;
use Drupal\node\NodeInterface;

/**
 * Description of CmisElement.
 */
class CmisElement {

  use StringTranslationTrait;

  /**
   * Cmis element.
   *
   * @var object
   */
  private $element;

  /**
   * Config.
   *
   * @var string
   */
  private $config;

  /**
   * Popup flag.
   *
   * @var string
   */
  private $popup;

  /**
   * Element parent id.
   *
   * @var string
   */
  private $parent;

  /**
   * The query string from cmis query form.
   *
   * @var string
   */
  private $queryString;

  /**
   * Render array of element.
   *
   * @var array
   */
  private $data = [];

  /**
   * The type of caller.
   *
   * @var string
   */
  private $type;

  /**
   * Root folder id.
   *
   * @var object
   */
  private $rootId;

  /**
   * {@inheritdoc}
   *
   * @param string $config
   *   Entity label.
   * @param bool $popup
   *   Get true if is a popup.
   * @param \Dkd\PhpCmis\DataObjects\AbstractFileableCmisObject $parent
   *   Get AbstractFileableCmisObject class.
   * @param string $query
   *   CMIS Query.
   * @param string $root_id
   *   CMIS root id.
   */
  public function __construct($config, $popup = FALSE, AbstractFileableCmisObject $parent = NULL, $query = '', $root_id = '') {
    $this->config = $config;
    $this->popup = $popup;
    $this->parent = $parent;
    $this->queryString = $query;
    $this->rootId = $root_id;
  }

  /**
   * Set element.
   *
   * @param string $type
   *   Controller type.
   * @param \Dkd\PhpCmis\DataObjects\AbstractFileableCmisObject $element
   *   Get AbstractFileableCmisObject class.
   */
  public function setElement($type, AbstractFileableCmisObject $element) {
    $this->type = $type;
    $this->element = $element;

    $this->prepare();
  }

  /**
   * Prepare element data.
   */
  protected function prepare() {
    $type_id = $this->element->getBaseTypeId()->__toString();
    $name = $this->element->getName();
    $id = $this->element->getId();
    switch ($type_id) {
      case 'cmis:folder':
        $this->prepareElement('cmis_browser_folder_item', $name, $id);
        break;

      case 'cmis:document':
        $this->prepareElement('cmis_browser_document_item', $name, $id);
        break;

      default:
        $element = [
          '#theme' => 'cmis_browser_other_item',
          '#element' => $name,
            // '#cache' => $this->cacheable,.
        ];
        $this->data = [render($element)];
    }
  }

  /**
   * Get element data.
   *
   * @return array
   *   Return data object.
   */
  public function getData() {
    return $this->data;
  }

  /**
   * Set element to render array.
   *
   * @param string $theme
   *   Render theme.
   * @param string $data
   *   Data.
   * @param string $id
   *   CMIS Object id.
   */
  protected function prepareElement($theme, $data, $id = '') {
    $author = $this->element->getCreatedBy();
    $created = $this->element->getCreationDate()->format('Y-m-d H:i:s');
    $description = $this->element->getDescription();

    $title = '';
    if ($title_property = $this->element->getProperty('cm:title')) {
      $title = $title_property->getFirstValue();
    }

    $size = 0;
    if ($size_property = $this->element->getProperty('cmis:contentStreamLength')) {
      $size = $size_property->getFirstValue();
    }

    $mime_type = '';
    $operations = '';
    if ($theme == 'cmis_browser_folder_item') {
      $this->prepareFolderElement($data, $operations, $id);
    }
    if ($theme == 'cmis_browser_document_item') {
      $this->prepareDocumentElement($data, $operations, $id);
    }
    if (!$this->popup) {
      $this->preparePropertiesLink($operations);
    }

    $element = [
      '#theme' => $theme,
      '#element' => $data,
        // '#cache' => $this->cacheable,.
    ];

    $details = [
      '#theme' => 'cmis_browser_document_details',
      '#title' => $title,
      '#mime_type' => $mime_type,
      '#size' => number_format($size, 0, '', ' '),
        // '#cache' => $this->cacheable,.
    ];
    $this->data = [
      render($element),
      render($details),
      $author,
      $created,
      $description,
      $operations,
    ];
  }

  /**
   * Prepare document element.
   *
   * @param string $data
   *   Get data.
   * @param string $operations
   *   Get render.
   * @param string $id
   *   CMIS object id.
   */
  private function prepareDocumentElement(&$data, &$operations, $id) {
    if ($this->popup) {
      $url = Url::fromUserInput('/');
      $link_options = [
        'attributes' => [
          'class' => [
            'cmis-field-insert',
          ],
          'id' => $this->element->getProperty('cmis:objectId')->getFirstValue(),
          'name' => $data,
        ],
      ];
      $url->setOptions($link_options);
      $path = Link::fromTextAndUrl($this->t('Choose'), $url)->toRenderable();
      $operations = render($path);
    }
    $url = Url::fromUserInput('/cmis/document/' . $this->config . '/' . $id);
    $path = Link::fromTextAndUrl($data, $url)->toRenderable();
    $data = ['#markup' => render($path)];
  }

  /**
   * Prepare folder element.
   *
   * @param string $data
   *   Get data.
   * @param string $operations
   *   Get render.
   * @param string $id
   *   CMIS object id.
   */
  private function prepareFolderElement(&$data, &$operations, $id) {
    switch ($this->type) {
      case 'browser':
      case 'node':
        $url = Url::fromUserInput('/cmis/browser/nojs/' . $this->config . '/' . $id);
        $link_options = [
          'attributes' => [
            'class' => [
              'use-ajax',
            ],
          ],
        ];
        break;

      case 'query':
        $url = Url::fromUserInput('/cmis/browser/' . $this->config . '/' . $id);
        break;
    }

    if ($this->popup) {
      $url_operation = Url::fromUserInput('/');
      $link_options_operations = [
        'attributes' => [
          'class' => [
            'cmis-field-insert',
          ],
          'id' => $this->element->getProperty('cmis:objectId')->getFirstValue(),
          'name' => $data,
        ],
      ];
      $url_operation->setOptions($link_options_operations);
      $path = Link::fromTextAndUrl($this->t('Choose folder'), $url_operation)->toRenderable();
      $operations = render($path);
      $link_options['query'] = ['type' => 'popup'];
    }

    if (!empty($link_options)) {
      $url->setOptions($link_options);
    }
    $data = Link::fromTextAndUrl($data, $url)->toRenderable();
  }

  /**
   * Prepare properties link.
   *
   * @param string $operations
   *   Get render.
   */
  private function preparePropertiesLink(&$operations) {
    $url = Url::fromUserInput('/cmis/object-properties/' . $this->config . '/' . $this->element->getId());
    $link_options = [
      'attributes' => [
        'class' => ['use-ajax'],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => Json::encode([
          'width' => 700,
        ]),
      ],
    ];
    $url->setOptions($link_options);
    $path = Link::fromTextAndUrl($this->t('Properties'), $url)->toRenderable();
    $links[] = [
      '#markup' => render($path),
      '#wrapper_attributes' => [
        'class' => [
          'object-properties',
        ],
      ],
    ];
    $cmis_connection_api = new CmisConnectionApi($this->config);
    $cmis_connection_api->setDefaultParameters();
    $element_id = $this->element->getId();
    if ($this->rootId != $element_id && $cmis_connection_api->hasAllowableActionById($element_id, Action::CAN_DELETE_OBJECT)) {
      $url = Url::fromUserInput('/cmis/object-delete-verify/' . $this->config . '/' . $element_id);
      $link_options = [
        'attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode([
            'width' => 700,
          ]),
        ],
        'query' => [
          'type' => $this->type,
        ],
      ];
      switch ($this->type) {
        case 'browser':
          $link_options['query']['parent'] = $this->parent->getId();
          break;

        case 'node':
          $node = \Drupal::routeMatch()->getParameter('node');
          if ($node instanceof NodeInterface) {
            $nid = $node->id();
            $link_options['query']['node'] = $nid;
            $link_options['query']['parent'] = $this->parent->getId();
          }
          break;

        case 'query':
          $link_options['query']['query_string'] = $this->queryString;
          break;
      }
      $url->setOptions($link_options);
      $path = Link::fromTextAndUrl($this->t('Delete'), $url)->toRenderable();
      $links[] = [
        '#markup' => render($path),
        '#wrapper_attributes' => [
          'class' => [
            'object-delete',
          ],
        ],
      ];
    }

    $list = [
      '#theme' => 'item_list',
      '#items' => $links,
      '#type' => 'ul',
    ];

    $operations = render($list);
  }

}
