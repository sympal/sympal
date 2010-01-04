<?php

class sfSympalAssetVideoObject extends sfSympalAssetFileObject
{
  protected $_type = 'video';

  public function getEmbed($options = array())
  {
    $url = $this->getUrl();
    $width = isset($options['width']) ? $options['width'] : sfSympalConfig::get('default_video_width', null, 400);
    $height = isset($options['height']) ? $options['height'] : sfSympalConfig::get('default_video_height', null, 400);
    $extension = $this->getExtension();
    $id = $this->getDoctrineAsset()->getId();

    if ($extension == 'swf')
    {
      return sprintf('<object width="%s" height="%s">
<param name="movie" value="%s">
<embed src="%s" width="%s" height="%s">
</embed>
</object>',
        $width,
        $height,
        $url,
        $url,
        $width,
        $height
      );
    } else if ($extension == 'flv') {
      sympal_use_jquery();
      sympal_use_javascript('/sfSympalPlugin/js/flowplayer.min.js');
      return sprintf('<a href="%s" style="display:block;width:%spx;height:%spx;" id="asset_%s"></a>
<script language="JavaScript"> 
    flowplayer("asset_%s", "%s"); 
</script>',
        $url,
        $width,
        $height,
        $id,
        $id,
        _compute_public_path('/sfSympalPlugin/js/flowplayer.swf', 'swf', 'swf')
      );
    } else {
      return $this->getLink($options);
    }
  }
}