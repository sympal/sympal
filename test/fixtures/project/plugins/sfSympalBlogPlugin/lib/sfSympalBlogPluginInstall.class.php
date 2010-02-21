<?php

class sfSympalBlogPluginInstall extends sfSympalPluginManagerInstall
{
  public function customInstall($installVars)
  {
    $installVars['content']['sfSympalBlogPost']['teaser'] = 'This is the teaser line for the sample blog post';
    $installVars['content']->save();
    $installVars['contentType']->setTemplate('default_view');
    $installVars['contentType']->save();

    $slot = $installVars['content']->getOrCreateSlot('body', 'Markdown');
    $slot->setValue(sfSympalLorem::getMarkdownLorem(1));
    $slot->save();

    $installVars['menuItem']['name'] = 'Blog';
    $installVars['menuItem']['label'] = 'Blog';

    $this->saveMenuItem($installVars['menuItem']);

    $installVars['contentList']->setTemplate('sympal_blog/list');
    $installVars['contentList']->sort_column = 'id';
    $installVars['contentList']->sort_order = 'DESC';
    $installVars['contentList']->save();
  }
}