<?php
/**
 * elFinder Integration
 *
 * Copyright (c) 2010-2020, Alexey Sukhotin. All rights reserved.
 */

namespace Drupal\elfinder\Routing;

use Symfony\Component\Routing\RouteCollection;
use Drupal\Core\Routing\RouteSubscriberBase;

class elFinderConnectorRouteSubscriber extends RouteSubscriberBase {

  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('elfinder.page')) {
      $config = \Drupal::config('elfinder.settings');
      if ($config->get('admin_theme')) {
        $route->setOption('_admin_route', TRUE);
      }
    }
  }

}
