<?php

class sfSympalRenderingResponseFilterContent extends sfSympalListener
{
  public function getEventName()
  {
    return 'response.filter_content';
  }

  public function run(sfEvent $event, $content)
  {
    if ($code = sfSympalConfig::get('google_analytics_code'))
    {
      $js = <<<EOF
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
	document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
	var pageTracker = _gat._getTracker("$code");
	pageTracker._trackPageview();
} catch(err) {}</script>
EOF;
      return str_replace('</body>', $js.'</body>', $content);
    } else {
      return $content;
    }
  }
}