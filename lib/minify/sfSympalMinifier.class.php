<?php

class sfSympalMinifier
{
  private
    $_response,
    $_request;

  public function __construct(sfWebResponse $response, sfWebRequest $request)
  {
    $this->_response = $response;
    $this->_request = $request;
  }

  public function minify()
  {
    $this->_minifyFiles($this->_response->getJavascripts(), 'js');
    $this->_minifyFiles($this->_response->getStylesheets(), 'css');
  }

  private function _minifyFiles(array $files, $type)
  {
    if ($files)
    {
      $typeName = $type == 'js' ? 'Javascript' : 'Stylesheet';
      $filename = md5(serialize($files)).'.'.$type;
      $webPath = '/cache/'.$type.'/'.$filename;
      $cachedPath = sfConfig::get('sf_web_dir').$webPath;
      if (!file_exists($cachedPath))
      {
        $minified = '';
        foreach ($files as $file => $options)
        {
          $path = sfConfig::get('sf_web_dir').'/'.$file;
          $minified .= "\n\n".$this->{'_minify'.$typeName}(file_get_contents($path), $this->_request->getUriPrefix().$this->_request->getRelativeUrlRoot().$file);
        }

        if (!is_dir($dir = dirname($cachedPath)))
        {
          mkdir($dir, 0777, true);
        }
        file_put_contents($cachedPath, $minified);
      }
    
      foreach ($this->_response->{'get'.$typeName.'s'}() as $file => $options)
      {
        $this->_response->{'remove'.$typeName}($file);
      }
      $this->_response->{'add'.$typeName}($webPath);
    }
  }

  private function _minifyJavascript($javascript, $path)
  {
    return $javascript;
  }

  private function _minifyStylesheet($stylesheet, $path)
  {
    $stylesheet = $this->_fixCssPaths($stylesheet, $path);
    return str_replace("\n", null,
      preg_replace(array("/\\;\s/", "/\s+\{\\s+/", "/\\:\s+\\#/", "/,\s+/i", "/\\:\s+\\\'/i", "/\\:\s+([0-9]+|[A-F]+)/i"), array(';', '{', ':#', ',', ":\'", ":$1"),
        preg_replace(array("/\/\*[\d\D]*?\*\/|\t+/", "/\s+/", "/\}\s+/"), array(null, ' ', "}\n"),
          str_replace("\r\n", "\n", trim($stylesheet))
        )
      )
    );
  }

  private function _fixCssPaths($content, $path)
  {
    if (preg_match_all("/url\(\s?[\'|\"]?(.+)[\'|\"]?\s?\)/ix", $content, $urlMatches))
    {
      $urlMatches = array_unique( $urlMatches[1] );
      $cssPathArray = explode('/', $path);
      
      // pop the css file name
      array_pop( $cssPathArray );
      $cssPathCount   = count( $cssPathArray );

      foreach( $urlMatches as $match )
      {
        $match = str_replace( array('"', "'"), '', $match );
        // replace path if it is relative
        if ( $match[0] !== '/' && strpos( $match, 'http:' ) === false )
        {
          $relativeCount = substr_count( $match, '../' );
          $cssPathSlice = $relativeCount === 0 ? $cssPathArray : array_slice($cssPathArray  , 0, $cssPathCount - $relativeCount);
          $newMatchPath = implode('/', $cssPathSlice) . '/' . str_replace('../', '', $match);
          $content = str_replace($match, $newMatchPath, $content);
        }
      }
    }
    
    return $content;
  }
}