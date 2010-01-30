<?php

class sfSympalRenderingPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    $this->dispatcher->connect('response.filter_content', array($this, 'listenToResponseFilterContent'));
    
    $this->dispatcher->connect('sympal.asset_replacer.filter_map', array($this, 'listenToAssetReplacerFilterMap'));
  }

  public function listenToResponseFilterContent(sfEvent $event, $content)
  {
    if ($code = sfSympalConfig::get('google_analytics_code'))
    {
      $js = '<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "'.$code.'";
urchinTracker();
</script>';
      return str_replace('</body>', $js.'</body>', $content);
    } else {
      return $content;
    }
  }
  
  /**
   * Listens to sympal.asset_replacer.filter_map and adds in all of the
   * content slot objects that should be filtered
   */
  public function listenToAssetReplacerFilterMap(sfEvent $event, $map)
  {
    $slotObjects = sfSympalConfig::get('content_slot_objects', null, array());
    foreach($slotObjects as $slotKey => $slotConfig)
    {
      $callback = isset($slotConfig['replacement_callback']) ? $slotConfig['replacement_callback'] : array('sfSympalAssetReplacer', '_replaceObjects');
      $map[$slotKey] = $callback;
    }
    
    return $map;
  }
}