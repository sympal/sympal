<?php

/**
 * Class responsible for generating the route information for a sfSympalContent
 * instance. Abstracted to this class so it can be used standalone and cached 
 * alone from the sfSympalContent instance
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalContentRouteObject
{
  protected
    $_content,
    $_routeName,
    $_routePath,
    $_routeObject,
    $_routeValues;

  public function __construct(sfSympalContent $content)
  {
    $this->compile($content);
  }

  /**
   * Compile all the information for the given sfSympalContent instance
   *
   * @param sfSympalContent $content
   * @return void
   */
  public function compile(sfSympalContent $content)
  {
    $this->_routeName = $this->_buildRouteName($content);
    $this->_routePath = $this->_buildRoutePath($content);
    $this->_routeObject = $this->_buildRouteObject($content);
    $this->_routeValues = $this->_buildRouteValues($content);
  }

  /**
   * Get the complete route for content
   *
   * @return string $route
   */
  public function getRoute()
  {
    $values = $this->getCultureRouteValues();

    if (!empty($values))
    {
      return $this->_routeName.'?'.http_build_query($values);
    } else {
      return $this->_routeName;
    }
  }

  /**
   * Get the name of the route.
   *
   * @return string $routeName
   */
  public function getRouteName()
  {
    return $this->_routeName;
  }

  /**
   * Get the route path. i.e. /route/path/:slug
   *
   * @return void
   * @author Jonathan Wage
   */
  public function getRoutePath()
  {
    return $this->_routePath;
  }

  /**
   * Get the sfRoute object that represents this route path
   *
   * @return sfRoute $routeObject
   */
  public function getRouteObject()
  {
    return $this->_routeObject;
  }

  /**
   * Get the array of values used to generates routes for this content
   *
   * @return array $routeValues
   */
  public function getRouteValues()
  {
    return $this->_routeValues;
  }

  /**
   * Get the array of values for the current culture used to generate routes for this content
   *
   * @param string $culture Optional culture to return, otherwise it uses the current culture
   * @return array $routeValues
   */
  public function getCultureRouteValues($culture = null)
  {
    if ($culture === null)
    {
      $culture = $this->getCurrentCulture();
    }
    return $culture && isset($this->_routeValues[$culture]) ? $this->_routeValues[$culture] : current($this->_routeValues);
  }

  /**
   * Get the current culture
   *
   * @return string $culture
   */
  public function getCurrentCulture()
  {
    if ($user = sfContext::getInstance()->getUser())
    {
      return $user->getCulture();
    } else {
      return sfConfig::get('sf_default_culture');
    }
  }

  /**
   * Get the evaluated route path. i.e. if you have /route/path/:slug
   * and your slug value was `my_slug` the evaluated route path would be /route/path/my_slug
   *
   * @return string $evaluatedRoutePath
   */
  public function getEvaluatedRoutePath()
  {
    $values = $this->getCultureRouteValues();
    $values['sf_culture'] = $this->getCurrentCulture();
    return $this->getRouteObject()->generate($values);
  }

  /**
   * Build the array of all culture values for the given content record
   *
   * @param sfSympalContent $content 
   * @return array $routeValues
   */
  protected function _buildRouteValues(sfSympalContent $content)
  {
    $variables = $this->getRouteObject()->getVariables();
    $isI18nEnabled = sfSympalConfig::isI18nEnabled();

    $languageCodes = $isI18nEnabled ? sfSympalConfig::getLanguageCodes() : array($this->getCurrentCulture());
    if (!is_array($languageCodes))
    {
      throw new sfException(sprintf('Language codes is not an array: "%s" given', $languageCodes));
    }
    
    $values = array();
    foreach ($languageCodes as $code)
    {
      foreach (array_keys($variables) as $name)
      {
        if ($isI18nEnabled && $name == 'slug' && $i18nSlug = $content->Translation[$code]->i18n_slug)
        {
          $values[$code][$name] = $i18nSlug;
        } else if ($content->hasField($name)) {
          if ($isI18nEnabled && isset($content->Translation[$code]->$name))
          {
            $values[$code][$name] = $content->Translation[$code]->$name;
          } else {
            $values[$code][$name] = $content->$name;
          }
        } else if (method_exists($content, $method = 'get'.sfInflector::camelize($name))) {
          $values[$code][$name] = $content->$method();
        }
      }
    }
    return $values;
  }

  /**
   * Build the route name for the given content record
   *
   * @param sfSympalContent $content 
   * @return string $routeName
   */
  protected function _buildRouteName(sfSympalContent $content)
  {
    if ($content->get('custom_path', false) || $content->get('module', false) || $content->get('action', false))
    {
      return '@sympal_content_' . $content->getUnderscoredSlug();
    }
    else if ($content->getType()->getDefaultPath())
    {
      return $content->getType()->getRouteName();
    }
    else if ($content->getSlug())
    {
      return '@sympal_content_view';
    }
  }

  /**
   * Build the sfRoute object for the given content record
   *
   * @param sfSympalContent $content 
   * @return sfRoute $routeObject
   */
  protected function _buildRouteObject(sfSympalContent $content)
  {
    // Generate a route object for this content only if it has a custom path
    if ($content->hasCustomPath())
    {
      return new sfRoute($this->getRoutePath(), array(
        'sf_format' => 'html',
        'sf_culture' => sfConfig::get('default_culture')
      ));
    // Otherwise get it from the content type
    } else {
      return $content->getType()->getRouteObject();
    }
  }

  /**
   * Build the route path for the given content record
   *
   * @param sfSympalContent $content 
   * @return string $routePath
   */
  protected function _buildRoutePath(sfSympalContent $content)
  {
    // If content has a custom path then lets use it
    if ($content->hasCustomPath())
    {
      $path = $content->custom_path;
      if ($path != '/')
      {
        $path .= '.:sf_format';
      }
      return $path;
    }
    // If content has a custom module or action then we need a route for it
    // so generate a path for this content to use in the route
    else if ($content->get('module', false) || $content->get('action', false))
    {
      $values = $this->getCultureRouteValues();
      $values['sf_culture'] = ':sf_culture';
      $values['sf_format'] = ':sf_format';
      return $this->getRouteObject()->generate($values);
    }
    // Otherwise fallback and get route path from the content type
    else if ($path = $content->getType()->getRoutePath())
    {
      return $path;
    }
    // Default if nothing else can be found
    else if ($content->getSlug())
    {
      return '/content/:slug';
    }
  }
}