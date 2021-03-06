<?php
/**
 * @file
 * Theme system overrides.
 */

/*****************************************************************************
 * Alter Functions: Alter data before it's displayed.
 *****************************************************************************/

/**
 * Implements hook_css_alter().
 */
function mtc_css_alter(&$css) {
  $radix_path = drupal_get_path('theme', 'radix');

  // Radix now includes compiled stylesheets for demo purposes.
  // We remove these from our subtheme since they are already included
  // in compass_radix.
  unset($css[$radix_path . '/assets/stylesheets/radix-style.css']);
  unset($css[$radix_path . '/assets/stylesheets/radix-print.css']);
}

/*****************************************************************************
 * Preprocess Functions: prepare variables for templates.
 *****************************************************************************/

/**
 * Implements template_preprocess_html().
 */
function mtc_preprocess_html(&$variables) {
  // Add body classes based on breadcrumb.
  $sections = drupal_get_breadcrumb();
  // Remove the first home link.
  array_shift($sections);
  foreach ($sections as $section) {
    // Clean up the menu/breadcrumb title to use as a css class.
    $class = drupal_clean_css_identifier(strtolower(strip_tags($section)));
    // Prevent double dashes.
    $class = str_replace('--', '-', $class);
    // Replace what's happening with news.
    $class = str_replace('what039s-happening', 'news', $class);

    $variables['classes_array'][] = 'section-' . $class;
  }
}

/**
 * Implements template_preprocess_page().
 */
function mtc_preprocess_page(&$variables) {
  // Add body classes based on menu section.
}

/**
 * Implements template_preprocess_page().
 */
function mtc_preprocess_panels_pane(&$variables) {
  if ($variables['pane']->type == 'block') {
    if ($variables['pane']->subtype == 'foo') {
      // for manipulating panels panes.
    }
  }
}

/**
 * Implements template_preprocess_page().
 */
function mtc_preprocess_block(&$variables) {
  // Find block template names.
  // dpm($variables['block_html_id']);
}

/*****************************************************************************
 * Process Functions: prepare variables for templates.
 *****************************************************************************/

/**
 * Implements template_preprocess_page().
 */
function mtc_process_html(&$variables) {
  // remove unneeded body classes.
  if (($key = array_search('html', $variables['classes_array'])) !== false) {
    unset($variables['classes_array'][$key]);
  }
  if (($key = array_search('not-front', $variables['classes_array'])) !== false) {
    unset($variables['classes_array'][$key]);
  }
  if (($key = array_search('no-sidebars', $variables['classes_array'])) !== false) {
    unset($variables['classes_array'][$key]);
  }
  if (($key = array_search('page-node', $variables['classes_array'])) !== false) {
    unset($variables['classes_array'][$key]);
  }
  if (($key = array_search('page-node-', $variables['classes_array'])) !== false) {
    unset($variables['classes_array'][$key]);
  }
  $variables['classes'] = implode(' ', $variables['classes_array']);
}

/*****************************************************************************
 * Theme Function Overrides: Change markup generated by Drupal.
 *****************************************************************************/


/**
 * Overrides theme_breadcrumb().
 */
function mtc_breadcrumb($variables) {
  $breadcrumb = $variables['breadcrumb'];
  $breadcrumb[] = '  <span class="active">'. drupal_get_title() .'</span>';

  if (!empty($breadcrumb)) {
    // Provide a navigational heading to give context for breadcrumb links to
    // screen-reader users. Make the heading invisible with .element-invisible.
    $output  = '<h2 class="element-invisible">' . t('You are here') . '</h2>';
    $output .= '<div class="breadcrumb">';
    $output .= '  ' . implode(' / ', $breadcrumb);
    $output .= '</div>';

    return $output;
  }
}

/**
 * Overrides theme_on_the_web_item().
 */
function mtc_on_the_web_item($variables) {
  $service = $variables['service'];
  $link = $variables['link'];
  $title = $variables['title'];

  // Determine attributes for the link
  $attributes  = array(
    'title' => $title,
    'rel' => 'nofollow',
  );
  if (variable_get('on_the_web_target', TRUE) == TRUE) {
    $attributes['target'] = '_blank';
  }

  // Add font-awesome classes.
  $attributes['class'][] = 'fa';
  $attributes['class'][] = 'fa-2x';
  switch ($service) {
    case 'twitter':
      $attributes['class'][] = 'fa-twitter';
    break;
    case 'facebook':
      $attributes['class'][] = 'fa-facebook-square';
    break;
    case 'instagram':
      $attributes['class'][] = 'fa-instagram';
    break;
    case 'youtube':
      $attributes['class'][] = 'fa-youtube-square';
    break;
  }

  // Link the image and wrap it in a span.
  $linked_image = l($service, $link, array('attributes' => $attributes));

  return $linked_image;
}

/**
 * Implements theme_menu_link().
 */
function mtc_menu_link__main_menu($variables) {
  $element = $variables['element'];;

  // No drop-down menus for the main menu.

  // Remove expanded or collapsed classes for main menu.
  if (($key = array_search('expanded', $element['#attributes']['class'])) !== false) {
    unset($element['#attributes']['class'][$key]);
  }
  if (($key = array_search('collapsed', $element['#attributes']['class'])) !== false) {
    unset($element['#attributes']['class'][$key]);
  }

  // Fix for active class.
  if (($element['#href'] == current_path() || ($element['#href'] == '<front>' && drupal_is_front_page())) && (empty($element['#localized_options']['language']) || $element['#localized_options']['language']->language == $language_url->language)) {
    $element['#attributes']['class'][] = 'active';
  }

  // Add active class to li if active trail.
  if (in_array('active-trail', $element['#attributes']['class'])) {
    $element['#attributes']['class'][] = 'active';
  }

  // Add a unique class using the title.
  $title = strip_tags($element['#title']);
  $element['#attributes']['class'][] = 'menu-link-' . drupal_html_class($title);

  $output = l($element['#title'], $element['#href'], $element['#localized_options']);
  return '<li' . drupal_attributes($element['#attributes']) . '>' . $output . "</li>\n";
}

/**
 * Implements theme_menu_link().
 */
function mtc_menu_link($variables) {
  $element = $variables['element'];
  $sub_menu = '';

  if (!empty($element['#below'])) {
    // Wrap in dropdown-menu.
    unset($element['#below']['#theme_wrappers']);
    $sub_menu = '<ul class="dropdown-menu">' . drupal_render($element['#below']) . '</ul>';
    $element['#localized_options']['attributes']['class'][] = 'dropdown-toggle';
    $element['#localized_options']['attributes']['data-toggle'] = 'dropdown';

    // Check if element is nested.
    if ((!empty($element['#original_link']['depth'])) && ($element['#original_link']['depth'] > 1)) {
      $element['#attributes']['class'][] = 'dropdown-submenu';
    }
    else {
      $element['#attributes']['class'][] = 'dropdown';
      $element['#localized_options']['html'] = TRUE;
      $element['#title'] .= '<span class="caret"></span>';
    }

    $element['#localized_options']['attributes']['data-target'] = '#';
  }

  // Fix for active class.
  if (($element['#href'] == current_path() || ($element['#href'] == '<front>' && drupal_is_front_page())) && (empty($element['#localized_options']['language']) || $element['#localized_options']['language']->language == $language_url->language)) {
    $element['#attributes']['class'][] = 'active';
  }

  // Add active class to li if active trail.
  if (in_array('active-trail', $element['#attributes']['class'])) {
    $element['#attributes']['class'][] = 'active';
  }

  // Add a unique class using the title.
  $title = strip_tags($element['#title']);
  $element['#attributes']['class'][] = 'menu-link-' . drupal_html_class($title);

  $output = l($element['#title'], $element['#href'], $element['#localized_options']);
  return '<li' . drupal_attributes($element['#attributes']) . '>' . $output . $sub_menu . "</li>\n";
}
