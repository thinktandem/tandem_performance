<?php

namespace Drupal\tandem_performance;

/**
 * Use to easily alter HTML Output.
 */
class MarkupAlter {

  /**
   * The content we are altering.
   *
   * @var string
   */
  protected $content;

  /**
   * The DOM Document.
   *
   * @var \DOMDocument
   */
  protected $dom;

  /**
   * MarkupAlter constructor.
   *
   * @param mixed $content
   *   The content we are altering.
   */
  public function __construct($content) {
    $this->content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');
  }

  /**
   * Initialize our DOM.
   */
  protected function initDom() {
    libxml_use_internal_errors(TRUE);
    // Load our DOM.
    $this->dom = new \DOMDocument();
    // Load the current string without wrappers.
    $this->dom->loadHTML($this->content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
  }

  /**
   * Preload our CSS.
   *
   * @return mixed
   *   The altered content.
   */
  public function preloadCss() {
    $this->initDom();
    $hrefs = [];

    // Go through and add the class we are adding.
    foreach ($this->dom->getElementsByTagName('link') as $node) {
      if ($node->hasAttribute('rel')) {
        if ($node->getAttribute('rel') === 'stylesheet') {
          $hrefs[] = $node->getAttribute('href');
        }
      }
    }

    foreach ($this->dom->getElementsByTagName('head') as $node) {
      foreach ($hrefs as $href) {
        $new_elm = $this->dom->createElement('link');
        $new_elm->setAttribute('rel', 'preload');
        $new_elm->setAttribute('href', $href);
        $new_elm->setAttribute('as', 'style');
        $node->insertBefore($new_elm, $node->firstChild);
      }
    }

    return $this->dom->saveHTML();
  }

  /**
   * Defer Google Script.
   *
   * @return mixed
   *   The altered content.
   */
  public function deferGoogleScripts() {
    $this->initDom();

    foreach ($this->dom->getElementsByTagName('script') as $node) {
      if ($node->hasAttribute('src')) {
        $src = $node->getAttribute('src');
        if (strpos($src, 'google') !== FALSE) {
          $node->setAttribute('defer', 'defer');
        }
      }
    }

    return $this->dom->saveHTML();
  }

}
