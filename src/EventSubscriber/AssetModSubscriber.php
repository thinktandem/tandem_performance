<?php

namespace Drupal\tandem_performance\EventSubscriber;

use Drupal\Core\Render\HtmlResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\tandem_performance\MarkupAlter;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\CurrentRouteMatch;

/**
 * Modifies various assets for performance.
 */
class AssetModSubscriber implements EventSubscriberInterface {

  /**
   * The route admin context to determine whether a route is an admin one.
   *
   * @var \Drupal\Core\Routing\AdminContext
   */
  protected $adminContext;

  /**
   * Default object for current_route_match service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * Creates a new AssetModSubscriber instance.
   *
   * @param \Drupal\Core\Routing\AdminContext $admin_context
   *   The route admin context to determine whether the route is an admin one.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $current_route_match
   *   The current route match service.
   */
  public function __construct(AdminContext $admin_context, CurrentRouteMatch $current_route_match) {
    $this->adminContext = $admin_context;
    $this->currentRouteMatch = $current_route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::RESPONSE => [
        ['preloadCss', 0],
        ['adjustGoogleScript', 0],
        ['minifyHtml', 0],
      ],
    ];
  }

  /**
   * If we are going to skip the current route.
   *
   * @return bool
   *   If we are skipping the route or not.
   */
  private function skipRoute() {
    $route = $this->currentRouteMatch->getRouteName();
    if ($this->adminContext->isAdminRoute()) {
      return TRUE;
    }
    if (strpos($route, 'layout_builder') !== FALSE) {
      return TRUE;
    }
    if (strpos($route, 'user') !== FALSE) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Preload CSS defer actions.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $response
   *   The response event object.
   */
  public function preloadCss(FilterResponseEvent $response) {
    if ($this->skipRoute()) {
      return;
    }

    $response = $response->getResponse();

    // Only process Html Responses.
    if (!$response instanceof HtmlResponse) {
      return;
    }
    $content = $response->getContent();

    // Replace the contents of the head.
    $head = explode('</head>', $content);
    if (isset($head[0])) {
      $head[0] = (new MarkupAlter($head[0]))->preloadCss();
      $content = implode('</head>', $head);
    }

    $response->setContent($content);
  }

  /**
   * Adds a defer tag to the header Google script.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $response
   *   The response event object.
   */
  public function adjustGoogleScript(FilterResponseEvent $response) {
    if ($this->skipRoute()) {
      return;
    }

    $response = $response->getResponse();

    // Only process Html Responses.
    if (!$response instanceof HtmlResponse) {
      return;
    }
    $content = $response->getContent();

    // Replace the contents of the head.
    $head = explode('</head>', $content);
    if (isset($head[0])) {
      $head[0] = (new MarkupAlter($head[0]))->deferGoogleScripts();
      $content = implode('</head>', $head);
    }

    $response->setContent($content);
  }

  /**
   * Minify HTML.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $response
   *   The response event object.
   */
  public function minifyHtml(FilterResponseEvent $response) {
    if ($this->skipRoute()) {
      return;
    }

    $response = $response->getResponse();

    // Only process Html Responses.
    if (!$response instanceof HtmlResponse) {
      return;
    }
    $content = $response->getContent();

    $search = [
    // Strip whitespaces after tags, except space.
      '/\>\s+/s',
    // Strip whitespaces before tags, except space.
      '/\s+</s',
    ];

    $replace = [
      '> ',
      ' <',
    ];

    $content = preg_replace($search, $replace, $content);
    $response->setContent($content);
  }

}
