<?php

function sympal_minify()
{
  if (sfSympalConfig::get('minifier', 'enabled', true))
  {
    $minifier = new sfSympalMinifier(
      sfContext::getInstance()->getResponse(),
      sfContext::getInstance()->getRequest()
    );
    $minifier->minify();
  }
}

function sympal_use_jquery($plugins = array())
{
  sfSympalToolkit::useJQuery($plugins);
}

function sympal_use_javascript($path, $position = 'last')
{
  return use_javascript(sfSympalConfig::getAssetPath($path), $position);
}

function sympal_use_stylesheet($path, $position = 'last')
{
  return use_stylesheet(sfSympalConfig::getAssetPath($path), $position);
}

function sympal_link_to_site($site, $name, $path = null)
{
  $request = sfContext::getInstance()->getRequest();
  $env = sfConfig::get('sf_environment');
  $file = $env == 'dev' ? $site.'_dev.php' : ($site.'.php');
  return '<a href="'.$request->getRelativeUrlRoot().'/'.$file.($path ? '/'.$path:null).'">'.$name.'</a>';
}

/**
 * Get the Sympal flash boxes
 *
 * @return string $html
 */
function get_sympal_flash()
{
  return get_partial('sympal_default/flash');
}

/**
 * Get a sfSympalMenuBreadcrumbs instances for the given MenuItem 
 *
 * @param MenuItem $menuItem  The MenuItem instance to generate the breadcrumbs for
 * @param string $subItem     A string to append to the end of the breadcrumbs  
 * @return string $html
 */
function get_sympal_breadcrumbs($menuItem, $subItem = null)
{
  if (!$menuItem)
  {
    return false;
  }

  // If we were passed an array then generate manual breacrumbs from it
  if (is_array($menuItem))
  {
    $breadcrumbs = sfSympalMenuBreadcrumbs::generate($menuItem);
  } else {
    $breadcrumbs = $menuItem->getBreadcrumbs($subItem);
  }

  if ($html = (string) $breadcrumbs)
  {
    return $html;
  } else {
    return false;
  }
}

/**
 * Get the floating sympal editor for the given MenuItem and Content instances
 *
 * @return string $html
 */
function get_sympal_editor()
{
  return get_partial('sympal_editor/editor');
}

/**
 * Get icons for changing languages
 *
 * @return string $html
 */
function get_change_language_icons()
{
  $icons = array();
  foreach (sfSympalConfig::getLanguageCodes() as $code)
  {
    if (sfContext::getInstance()->getUser()->getCulture() == $code)
    {
      $icons[] = image_tag('/sfSympalPlugin/images/flags/'.strtolower($code).'.png');
    } else {
      $icons[] = link_to(image_tag('/sfSympalPlugin/images/flags/'.strtolower($code).'.png'), '@sympal_change_language?language='.$code, 'title=Switch to '.format_language($code));
    }
  }
  return implode(' ', $icons);
}

/**
 * Returns the url to a gravatar image based on the given email address
 * 
 * @param string $emailAddress The email address to lookup in gravatar
 * @param string The size of the image to return
 */
function get_gravatar_url($emailAddress, $size = 40)
{
  $default = sfSympalConfig::get('gravatar_default_image');
  $default = image_path($default, true);

  $url = 'http://www.gravatar.com/avatar.php?gravatar_id='.md5(strtolower($emailAddress)).'&default='.urlencode($default).'&size='.$size;
  
  return $url;
}