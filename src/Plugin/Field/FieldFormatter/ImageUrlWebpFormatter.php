<?php

namespace Drupal\tandem_performance\Plugin\Field\FieldFormatter;

use Drupal\image_url_formatter\Plugin\Field\FieldFormatter\ImageUrlFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

/**
 * Extends the image_url formatter to allow for webp.
 *
 * @FieldFormatter(
 *   id = "image_url_webp",
 *   label = @Translation("Image URL (WebP)"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class ImageUrlWebpFormatter extends ImageUrlFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);

    if (empty($elements) || strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') === FALSE) {
      return $elements;
    }

    // I know I should be injecting this.
    $webp = \Drupal::service('webp.webp');

    foreach ($elements as &$element) {
      /** @var \Drupal\image\Plugin\Field\FieldType\ImageItem $image */
      $image = $element["#item"];
      $id = $image->get('target_id')->getValue();
      if ($id) {
        /** @var \Drupal\file\Entity\File $file */
        $file = File::load($id);
        $web = $webp->createWebpCopy($file->getFileUri(), '70');
        $element['#url'] = Url::fromUri(file_create_url($web));
      }
    }

    return $elements;
  }

}
