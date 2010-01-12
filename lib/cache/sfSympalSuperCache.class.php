<?php

class sfSympalSuperCache
{
  private $_sympalConfiguration;

  public function __construct(sfSympalConfiguration $sympalConfiguration)
  {
    $this->_sympalConfiguration = $sympalConfiguration;
  }

  public function listenToResponseFilterContent(sfEvent $event, $content)
  {
    $symfonyContext = $this->_sympalConfiguration->getSympalContext()->getSymfonyContext();
    $response = $event->getSubject();
    $request = $symfonyContext->getRequest();

    if (count($_GET) || count($_POST) || $response->getStatusCode() != 200)
    {
      return $content;
    }

    // only if cache is set for the entire page
    $cacheManager = $symfonyContext->getViewCacheManager();
    $uri = $symfonyContext->getRouting()->getCurrentInternalUri();
    if ($cacheManager->isCacheable($uri) && $cacheManager->withLayout($uri))
    {
      // save super cache
      $request = $symfonyContext->getRequest();
      $pathInfo = $request->getPathInfo();
      $file =
        sfConfig::get('sf_web_dir').'/cache/'.$request->getHost().
        ('/' == $pathInfo[strlen($pathInfo) - 1] ? $pathInfo.'index.html' : $pathInfo).'.php'
      ;

      $current_umask = umask();
      umask(0000);
      $dir = dirname($file);
      if (is_file($dir))
      {
        unlink($dir);
      }

      if (!is_dir($dir))
      {
        mkdir($dir, 0777, true);
      }
      // check conflicts between directories and files with the same name
      if (!is_dir($file))
      {
        $expiryDate = time() + $cacheManager->getLifetime($uri);
        $header = sprintf("<?php if (time() > %d) { unlink(__FILE__); header('Pragma: no-cache'); header('Location: '.\$_SERVER['REQUEST_URI']);  exit; } ?>\n", $expiryDate);
        $header .= sprintf("<?php header('Content-Type: %s') ?>\n", $response->getContentType());
        foreach(array('Cache-Control', 'Pragma', 'Expires') as $key)
        {
          if ($value = $response->getHttpHeader($key))
          {
            $header .= sprintf("<?php header('%s: %s') ?>\n", $key, $value);
          }
        }
        file_put_contents($file, $header.$content);
        chmod($file, 0666);
      }
      umask($current_umask);
    }
    return $content;
  }
}