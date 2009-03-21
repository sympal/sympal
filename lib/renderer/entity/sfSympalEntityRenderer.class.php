<?php
class sfSympalEntityRenderer
{
  protected
    $_menuItem,
    $_entity,
    $_entities,
    $_pager,
    $_format = 'html';

  public function __construct(MenuItem $menuItem, $format = 'html')
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url', 'Partial'));

    $this->_menuItem = $menuItem;
    $this->_format = $format ? $format:'html';
  }

  public function setEntity(Entity $entity)
  {
    $this->_entity = $entity;
  }

  public function setEntities(Doctrine_Collection $entities)
  {
    $this->_entities = $entities;
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
    $title = $this->_menuItem->getBreadcrumbs($this->_entity)->getPathAsString();
    $title = $title ? $this->_menuItem->Site->title.' - '.$title:$this->_menuItem->Site->title;
    $response->setTitle($title);

    sfSympalTools::setCurrentMenuItem($this->_menuItem);

    if ($this->_entity)
    {
      sfSympalTools::setCurrentEntity($this->_entity);

      sfSympalTools::changeLayout($this->_entity->getLayout());
    } else {
      sfSympalTools::changeLayout($this->_menuItem->getLayout());
    }
  }

  public function render()
  {
    $output = '';

    if ($this->_entities)
    {
      $output .= $this->_getEntityList($this->_entities, $this->_format);
    } else {
      $output .= $this->_getEntityView($this->_entity, $this->_format);
    }

    return $output;
  }

  protected function _getEntityList(Doctrine_Collection $entities, $format = 'html')
  {
    switch ($format)
    {
      case 'html':
        return $this->_getEntityListHtml($entities);
      break;
      case 'rss':
        return 'RSS feed coming soon...';
      break;
      case 'xml':
      case 'json':
      case 'yml':
        return $entities->exportTo($format);
      default:
        $this->_throwInvalidFormatException($format);
    }
  }

  protected function _renderEntityTemplate($type, $data, $template)
  {
    $key = $data instanceof Doctrine_Collection ? 'entities':'entity';
    $options = array($key => $data, 'menuItem' => $this->_menuItem);

    if ($key == 'entity')
    {
      $typeVarName = strtolower($data['Type']['name'][0]).substr($data['Type']['name'], 1, strlen($data['Type']['name']));
      $options[$typeVarName] = $data->getRecord();
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
      return sfSympalTools::renderContent($body, $options);;
    } else {
      return get_sympal_breadcrumbs($this->_menuItem, ($key == 'entity' ? $data:null)).$this->_renderDoctrineData($data);
    }
  }

  protected function _renderDoctrineData($data)
  {
    $type = $data instanceof Doctrine_Collection ? 'Entities':'Entity';
    $func = '_renderDoctrine'.$type;
    return $this->$func($data);
  }

  protected function _getEntityListHtml(Doctrine_Collection $entities)
  {
    $template = $this->_menuItem->EntityType->getTemplate('List');

    return $this->_renderEntityTemplate('List', $entities, $template);
  }

  protected function _getEntityViewHtml(Entity $entity)
  {
    if ($entity->entity_template_id)
    {
      $template = $entity->getTemplate();
    } else {
      $template = $entity->getType()->getTemplate('View');
    }

    return $this->_renderEntityTemplate('View', $entity, $template);
  }

  protected function _getEntityView(Entity $entity, $format = 'html')
  {
    switch ($format)
    {
      case 'html':
        return $this->_getEntityViewHtml($entity);
      break;
      case 'xml':
      case 'json':
      case 'yml':
        return $entity->exportTo($format);
      default:
        $this->_throwInvalidFormatException($format);
    }
  }

  protected function _renderDoctrineEntity(Doctrine_Record $entity)
  {
    use_helper('Entity');

    $html  = '<h1>Entity Data</h1>';
    $html .= $this->_renderData($entity->toArray(), false);

    $html .= '<h1>'.get_class($entity->getRecord()).' Data</h1>';
    $html .= $this->_renderData($entity->getRecord()->toArray(), false);

    $html .= '<h1>Slots</h1>';
    $html .= '<table>';
    foreach ($entity->getSlots() as $key => $slot)
    {
      $html .= '<tr><th>'.$key.'</th><td>' . sympal_entity_slot($entity, $slot['name']) . '</td></tr>';
    }
    $html .= '</table>';

    return $html;
  }

  protected function _renderData(array $data, $deep = true)
  {
    $html  = '';
    $html .= '<table>';  
    foreach ($data as $key => $value)
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

  protected function _renderDoctrineEntities(Doctrine_Collection $entities)
  {
    $pager = pager_navigation($this->_pager, url_for($this->_menuItem->getItemRoute()));

    $output = '';

    if (count($entities))
    {
      $indice = $this->_pager->getFirstIndice();
      $output .= '<h2>Showing ' . $indice . ' to ' . ($indice + count($entities) - 1) . ' of ' . $this->_pager->getNbResults() . ' total results.</h2>';

      $output .= '<table>';

      if ($pager)
      {
        $output .= '<thead><tr><th colspan="3">' . $pager . '</th></thead>';
      }

      $output .= '<thead><tr><th>Title</th><th>Date Published</th><th>Created By</th></tr></thead>';
      foreach ($entities as $record)
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

    if (sfSympalTools::isEditMode())
    {
      $output .= link_to('Create New', '@sympal_entities_create_type?type='.$this->_menuItem->getEntityType()->getSlug());
    }

    return $output;
  }

  protected function _throwInvalidFormatException($format)
  {
    throw new sfException(sprintf('Invalid output format: %s', $format));
  }
}