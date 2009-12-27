<?php

class sfWidgetFormSympalRichText extends sfWidgetFormSympalMultiLineText
{
  public function getJavaScripts()
  {
    return array(
      '/sfSympalPlugin/wymeditor/jquery.wymeditor.pack.js'
    );
  }
}