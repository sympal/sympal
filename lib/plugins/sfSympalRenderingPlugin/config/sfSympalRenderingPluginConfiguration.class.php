<?php

class sfSympalRenderingPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    $this->dispatcher->connect('response.filter_content', array($this, 'listenToResponseFilterContent'));
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
}