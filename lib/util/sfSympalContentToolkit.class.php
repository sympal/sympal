<?php

class sfSympalContentToolkit
{
  /**
   * Get the redirect routes yaml for the routing.yml
   *
   * @return string $yaml
   */
  public static function getRedirectRoutesYaml()
  {
    $cachePath = sfConfig::get('sf_cache_dir').'/'.sfConfig::get('sf_app').'/'.sfConfig::get('sf_environment').'/redirect_routes.cache.yml';
    if (file_exists($cachePath) && sfConfig::get('sf_environment') !== 'test')
    {
      return file_get_contents($cachePath);
    }

    try {
      $routeTemplate =
'%s:
  url:   %s
  param:
    module: %s
    action: %s
    id: %s
';

      $routes = array();
      $siteSlug = sfConfig::get('sf_app');

      $redirects = Doctrine::getTable('sfSympalRedirect')
        ->createQuery('r')
        ->innerJoin('r.Site s')
        ->andWhere('s.slug = ?', $siteSlug)
        ->execute();

      foreach ($redirects as $redirect)
      {
        $routes[] = sprintf($routeTemplate,
          'sympal_redirect_'.$redirect->getId(),
          $redirect->getSource(),
          'sympal_redirecter',
          'index',
          $redirect->getId()
        );
      }

      $routes = implode("\n", $routes);
      file_put_contents($cachePath, $routes);
      return $routes;
    } catch (Exception $e) { }
  }

  /**
   * Get the content routes yaml
   *
   * @return string $yaml
   */
  public static function getContentRoutesYaml()
  {
    $cachePath = sfConfig::get('sf_cache_dir').'/'.sfConfig::get('sf_app').'/'.sfConfig::get('sf_environment').'/content_routes.cache.yml';
    if (file_exists($cachePath) && sfConfig::get('sf_environment') !== 'test')
    {
      return file_get_contents($cachePath);
    }

    try {
      $routeTemplate =
'%s:
  url:   %s
  param:
    module: %s
    action: %s
    sf_format: html
    sympal_content_type: %s
    sympal_content_type_id: %s
    sympal_content_id: %s
  class: sfDoctrineRoute
  options:
    model: sfSympalContent
    type: object
    method: getContent
    allow_empty: true
  requirements:
    sf_culture:  (%s)
    sf_format:   (%s)
    sf_method:   [post, get]
';

      $routes = array();
      $siteSlug = sfConfig::get('sf_app');
      
      if (!sfContext::hasInstance())
      {
        $configuration = ProjectConfiguration::getApplicationConfiguration(sfConfig::get('sf_app'), 'prod', false);
        sfContext::createInstance($configuration);
      }

      $contents = Doctrine::getTable('sfSympalContent')
        ->createQuery('c')
        ->leftJoin('c.Type t')
        ->innerJoin('c.Site s')
        ->where("c.custom_path IS NOT NULL AND c.custom_path != ''")
        ->andWhere('s.slug = ?', $siteSlug)
        ->execute();

      foreach ($contents as $content)
      {
        $routes['content_'.$content->getId()] = sprintf($routeTemplate,
          substr($content->getRouteName(), 1),
          $content->getRoutePath(),
          $content->getModuleToRenderWith(),
          $content->getActionToRenderWith(),
          $content->Type->name,
          $content->Type->id,
          $content->id,
          implode('|', sfSympalConfig::getLanguageCodes()),
          implode('|', sfSympalConfig::get('content_formats'))
        );
      }

      $contents = Doctrine::getTable('sfSympalContent')
        ->createQuery('c')
        ->leftJoin('c.Type t')
        ->innerJoin('c.Site s')
        ->where("c.module IS NOT NULL AND c.module != ''")
        ->orWhere("c.action IS NOT NULL AND c.action != ''")
        ->andWhere('s.slug = ?', $siteSlug)
        ->execute();

      foreach ($contents as $content)
      {
        $routes['content_'.$content->getId()] = sprintf($routeTemplate,
          substr($content->getRouteName(), 1),
          $content->getRoutePath(),
          $content->getModuleToRenderWith(),
          $content->getActionToRenderWith(),
          $content->Type->name,
          $content->Type->id,
          $content->id,
          implode('|', sfSympalConfig::getLanguageCodes()),
          implode('|', sfSympalConfig::get('content_formats'))
        );
      }

      $contentTypes = Doctrine::getTable('sfSympalContentType')
        ->createQuery('t')
        ->execute();

      foreach ($contentTypes as $contentType)
      {
        $routes['content_type_'.$contentType->getId()] = sprintf($routeTemplate,
          substr($contentType->getRouteName(), 1),
          $contentType->getRoutePath(),
          $contentType->getModuleToRenderWith(),
          $contentType->getActionToRenderWith(),
          $contentType->name,
          $contentType->id,
          null,
          implode('|', sfSympalConfig::getLanguageCodes()),
          implode('|', sfSympalConfig::get('content_formats'))
        );
      }

      $routes = implode("\n", $routes);
      file_put_contents($cachePath, $routes);

      return $routes;
    }
    catch (Exception $e)
    {
      // for now, I'd like to not obfuscate the errors - rather reportthem
      throw $e;
    }
  }
}