<?php

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

  public function compile(sfSympalContent $content)
  {
    $this->_routeName = $this->_buildRouteName($content);
    $this->_routePath = $this->_buildRoutePath($content);
    $this->_routeObject = $this->_buildRouteObject($content);
    $this->_routeValues = $this->_buildRouteValues($content);
  }

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

  public function getRouteName()
  {
    return $this->_routeName;
  }

  public function getRoutePath()
  {
    return $this->_routePath;
  }

  public function getRouteObject()
  {
    return $this->_routeObject;
  }

  public function getRouteValues()
  {
    return $this->_routeValues;
  }

  public function getCultureRouteValues($culture = null)
  {
    if ($culture === null)
    {
      $culture = $this->getCurrentCulture();
    }
    return $culture && isset($this->_routeValues[$culture]) ? $this->_routeValues[$culture] : current($this->_routeValues);
  }

  public function getCurrentCulture()
  {
    if ($user = sfContext::getInstance()->getUser())
    {
      return $user->getCulture();
    } else {
      return sfConfig::get('sf_default_culture');
    }
  }

  public function getEvaluatedRoutePath()
  {
    $values = $this->getCultureRouteValues();
    $values['sf_culture'] = $this->getCurrentCulture();
    return $this->getRouteObject()->generate($values);
  }

  protected function _buildRouteValues(sfSympalContent $content)
  {
    $variables = $this->getRouteObject()->getVariables();
    $isI18nEnabled = sfSympalConfig::isI18nEnabled();

    $languageCodes = $isI18nEnabled ? sfSympalConfig::getLanguageCodes() : array($this->getCurrentCulture());
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