<?php
class sfSympalContentRenderer
{
  protected
    $_menuItem,
    $_content,
    $_pager,
    $_format = 'html';

  public function __construct(MenuItem $menuItem, $format = 'html')
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url', 'Partial'));

    $this->_menuItem = $menuItem;
    $this->_format = $format ? $format:'html';
  }

  public function setContent($content)
  {
    $this->_content = $content;
  }

  public function setPager(sfDoctrinePager $pager)
  {
    $this->_pager = $pager;
  }

  public function initialize()
  {
    $context = sfContext::getInstance();
    $context->getConfiguration()->loadHelpers(array('Tag', 'Url'));

    $response = $context->getResponse();

    sfSympalToolkit::setCurrentMenuItem($this->_menuItem);

    if ($this->_content instanceof Doctrine_Record)
    {
      sfSympalToolkit::setCurrentContent($this->_content);

      sfSympalToolkit::changeLayout($this->_content->getLayout());

      $title = $this->_menuItem->getBreadcrumbs($this->_content)->getPathAsString();
    } else {
      sfSympalToolkit::changeLayout($this->_menuItem->getLayout());

      $title = $this->_menuItem->getBreadcrumbs()->getPathAsString();
    }

    $title = $title ? $this->_menuItem->Site->title.' - '.$title:$this->_menuItem->Site->title;
    $response->setTitle($title);
  }

  public function render()
  {
    $output = '';

    if ($this->_content instanceof Doctrine_Collection)
    {
      $output .= $this->_getContentList($this->_content, $this->_format);
    } else {
      $output .= $this->_getContentView($this->_content, $this->_format);
    }

    return $output;
  }

  protected function _getContentList(Doctrine_Collection $content, $format = 'html')
  {
    switch ($format)
    {
      case 'html':
        return $this->_getContentListHtml($content);
      break;
      case 'rss':
        return 'RSS feed coming soon...';
      break;
      case 'xml':
      case 'json':
      case 'yml':
        return $content->exportTo($format);
      default:
        $this->_throwInvalidFormatException($format);
    }
  }

  protected function _renderContentTemplate($type, $content, $template)
  {
    $options = array('content' => $content, 'menuItem' => $this->_menuItem, 'pager' => $this->_pager);

    if ($type == 'object')
    {
      $typeVarName = strtolower($content['Type']['name'][0]).substr($content['Type']['name'], 1, strlen($content['Type']['name']));
      $options[$typeVarName] = $content->getRecord();
    }

    if ($template && $partialPath = $template->getPartialPath())
    {
      return get_partial($partialPath, $options);
    }
    else if ($template && $componentPath = $template->getComponentPath())
    {
      list($module, $action) = explode('/', $componentPath);
      return get_component($module, $action, $options);
    }
    else if ($template && $body = $template->getBody())
    {
      return sfSympalToolkit::processPhpCode($body, $options);;
    } else {
      return get_sympal_breadcrumbs($this->_menuItem, ($type == 'list' ? $content:null)).$this->_renderDoctrineData($content, $type);
    }
  }

  protected function _renderDoctrineData($content, $type)
  {
    $func = '_renderDoctrine'.$type;
    return $this->$func($content);
  }

  protected function _getContentListHtml(Doctrine_Collection $content)
  {
    $template = $this->_menuItem->ContentType->getTemplate('List');

    return $this->_renderContentTemplate('list', $content, $template);
  }

  protected function _getContentViewHtml(Content $content)
  {
    if ($content->content_template_id)
    {
      $template = $content->getTemplate();
    } else {
      $template = $content->getType()->getTemplate('View');
    }

    return $this->_renderContentTemplate('object', $content, $template);
  }

  protected function _getContentView(Content $content, $format = 'html')
  {
    switch ($format)
    {
      case 'html':
        return $this->_getContentViewHtml($content);
      break;
      case 'xml':
      case 'json':
      case 'yml':
        return $content->exportTo($format);
      default:
        $this->_throwInvalidFormatException($format);
    }
  }

  protected function _renderDoctrineObject(Doctrine_Record $content)
  {
    $html  = '<h1>Content Data</h1>';
    $html .= $this->_renderData($content->toArray(), false);

    $html .= '<h1>'.get_class($content->getRecord()).' Data</h1>';
    $html .= $this->_renderData($content->getRecord()->toArray(), false);

    $html .= '<h1>Slots</h1>';
    $html .= '<table>';
    foreach ($content->getSlots() as $key => $slot)
    {
      $html .= '<tr><th>'.$key.'</th><td>'.get_sympal_content_slot($content, $slot['name']).'</td></tr>';
    }
    $html .= '</table>';

    return $html;
  }

  protected function _renderData(array $content, $deep = true)
  {
    $html  = '';
    $html .= '<table>';  
    foreach ($content as $key => $value)
    {
      if (strstr($key, '_id'))
      {
        continue;
      }
      $val = null;
      if (is_array($value) && $deep)
      {
        $val = '<td>' . $this->_renderData($value) . '</td>';
      } else if (!is_array($value)) {
        $val = '<td>' . $value . '</td>';
      }
      if (isset($val) && $val)
      {
        $html .= '<tr>';
        $html .= '<th>' . Doctrine_Inflector::classify(str_replace('_id', '', $key)) . '</th>';
        $html .= $val;
        $html .= '</tr>';
      }
    }
    $html .= '</table>';
    return $html;
  }

  protected function _renderDoctrineList(Doctrine_Collection $content)
  {
    $pager = get_sympal_pager_navigation($this->_pager, url_for($this->_menuItem->getItemRoute()));

    $output = '';

    if (count($content))
    {
      $output .= get_sympal_pager_header($this->_pager, $content);
      $output .= '<table>';

      if ($pager)
      {
        $output .= '<thead><tr><th colspan="3">' . $pager . '</th></thead>';
      }

      $output .= '<thead><tr><th>Title</th><th>Date Published</th><th>Created By</th></tr></thead>';
      foreach ($content as $record)
      {
        $route = $record->getRoute();
        $output .= '<tr>';
        $output .= '<td><strong>' . link_to($record->getHeaderTitle(), $route, 'absolute=true') . '</strong></td>';
        $output .= '<td>' . date('m/d/Y h:i', strtotime($record['date_published'])) . '</td>';
        $output .= '<td>' . $record['CreatedBy']['username'] . '</td>';
        $output .= '</tr>';
      }

      if ($pager)
      {
        $output .= '<tfoot><tr><th colspan="3">' . $pager . '</th></tfoot>';
      }

      $output .= '</table>';
    } else {
      $output .= '<p><strong>No results found.</strong></p>';
    }

    if (sfSympalToolkit::isEditMode())
    {
      $output .= link_to('Create New', '@sympal_content_create_type?type='.$this->_menuItem->getContentType()->getSlug());
    }

    return $output;
  }

  protected function _throwInvalidFormatException($format)
  {
    throw new sfException(sprintf('Invalid output format: %s', $format));
  }
}