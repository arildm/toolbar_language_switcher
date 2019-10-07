<?php

namespace Drupal\toolbar_language_switcher;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Class RenderBuilder.
 */
class RenderBuilder {

  use StringTranslationTrait;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Path matcher service.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * Id of the current language.
   *
   * @var string
   */
  protected $currentLanguage;

  /**
   * List of the available languages.
   *
   * @var \Drupal\Core\Language\LanguageInterface[]
   */
  protected $languages;

  /**
   * Current route name.
   *
   * @var string
   */
  protected $route;

  /**
   * RenderBuilder constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   Path builder.
   */
  public function __construct(LanguageManagerInterface $language_manager, PathMatcherInterface $path_matcher) {
    $this->languageManager = $language_manager;
    $this->pathMatcher = $path_matcher;
    // Get languages, get current route.
    $this->currentLanguage = $this->languageManager->getCurrentLanguage()->getId();
    $this->languages = $this->languageManager->getLanguages();
    $this->route = $this->pathMatcher->isFrontPage() ? '<front>' : '<current>';
  }

  /**
   * Main build method.
   *
   * @return array
   *   Render array for the toolbar items.
   */
  public function build() {

    // Get links.
    $links = [];
    foreach ($this->languages as $language) {
      $url = new Url($this->route, [], ['language' => $language]);
      $links[] = [
        'title' => $language->getName(),
        'url' => $url,
      ];
    }

    // Set cache.
    $items['admin_toolbar_langswitch'] = [
      '#cache' => [
        'contexts' => [
          'languages:language_interface',
          'url',
        ],
      ],
    ];

    // Build toolbar item and tray.
    $items['admin_toolbar_langswitch'] += [
      '#type'   => 'toolbar_item',
      '#weight' => 999,
      'tab'     => [
        '#type'       => 'html_tag',
        '#tag'        => 'div',
        '#value'      => $this->t('Language: @lang', ['@lang' => strtoupper($this->currentLanguage)]),
        '#attributes' => [
          'class' => ['toolbar-item-admin-toolbar-langswitch'],
          'title' => $this->t('Admin Toolbar Language Switcher'),
        ],
      ],
      'tray'    => [
        '#heading' => $this->t('Admin Toolbar Language Switcher'),
        'content'  => [
          '#theme'      => 'links',
          '#links'      => $links,
          '#attributes' => [
            'class' => ['toolbar-menu'],
          ],
        ],
      ],
    ];

    return $items;
  }

}
