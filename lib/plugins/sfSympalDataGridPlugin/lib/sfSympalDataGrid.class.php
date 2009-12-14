<?php

sfApplicationConfiguration::getActive()->loadHelpers(array('Date', 'Tag'));

class sfSympalDataGrid
{
  protected
    $_id,
    $_class = 'sympal_data_grid',
    $_modelName,
    $_table,
    $_query,
    $_pager,
    $_sort,
    $_order = 'asc',
    $_columns = array(),
    $_parents = array(),
    $_renderingModule = 'sympal_data_grid',
    $_isSortable = true,
    $_initialized = false;

  public function __construct($modelName, $alias = null)
  {
    if (is_string($modelName))
    {
      $this->_modelName = $modelName;

      $query = Doctrine_Core::getTable($modelName)
        ->createQuery($alias);

      $this->_query = $query;
      $this->_pager = new sfDoctrinePager($this->_modelName);
      $this->_pager->setQuery($this->_query);
    } else if ($modelName instanceof Doctrine_Query) {
      $this->_query = $modelName;
      $this->_query->getSqlQuery(array(), false);
      $this->_modelName = $this->_query->getRoot()->getOption('name');
      $this->_pager = new sfDoctrinePager($this->_modelName);
      $this->_pager->setQuery($this->_query);
    } else if ($modelName instanceof sfDoctrinePager) {
      $this->_pager = $modelName;
      $this->_query = $this->_pager->getQuery();
      $this->_query->getSqlQuery(array(), false);
      $this->_modelName = $this->_query->getRoot()->getOption('name');
    } else {
      throw new Doctrine_Exception('First argument should be either the name of a model or an existing Doctrine_Query object');
    }
    $this->_table = Doctrine_Core::getTable($this->_modelName);
  }

  public static function create($modelName, $alias = null)
  {
    $dataGrid = new self($modelName, $alias);
    return $dataGrid;
  }

  public function isSortable($bool = null)
  {
    if ($bool !== null)
    {
      $this->_isSortable = $bool;
      return $this;
    }
    return $this->_isSortable;
  }

  public function setSort($sort, $order = null)
  {
    $this->_sort = $sort;
    if ($order)
    {
      $this->setOrder($order);
    }
    return $this;
  }

  public function getSort()
  {
    return $this->_sort;
  }

  public function setOrder($order)
  {
    $this->_order = strtolower($order);
    return $this;
  }

  public function getOrder()
  {
    return $this->_order;
  }

  public function setId($id)
  {
    $this->_id = $id;
    return $this;
  }

  public function getId()
  {
    if ( ! $this->_id)
    {
      $this->_id = sfInflector::tableize($this->_modelName.'DataGrid');
    }
    return $this->_id;
  }

  public function setClass($class)
  {
    $this->_class = $class;
  }

  public function getClass()
  {
    return $this->_class;
  }

  public function setQuery(Doctrine_Query $query)
  {
    $this->_query = $query;
    return $this;
  }

  public function getQuery()
  {
    return $this->_query;
  }

  public function setPager(sfDoctrinePager $pager)
  {
    $this->_pager = $pager;
    return $this;
  }

  public function getPager()
  {
    return $this->_pager;
  }

  public function setRenderingModule($renderingModule)
  {
    $this->_renderingModule = $renderingModule;
    return $this;
  }

  public function getRenderingModule()
  {
    return $this->_renderingModule;
  }

  public function getColumn($name)
  {
    $this->init();

    if (!isset($this->_columns[$name]))
    {
      throw new InvalidArgumentException(sprintf('Column named "%s" does not exist.', $name));
    }
    return $this->_columns[$name];
  }

  public function hasColumn($name)
  {
    return isset($this->_column[$name]);
  }

  public function addColumn($name, $options = array())
  {
    $this->_columns[$name] = $options;
    return $this;
  }

  public function configureColumn($name, $options)
  {
    if ( ! $this->_columns)
    {
      $this->_populateDefaultColumns();
    }

    $options = _parse_attributes($options);
    foreach ($this->_columns as $key => $column)
    {
      if ($column['name'] == $name)
      {
        foreach ($options as $k => $v)
        {
          $this->_columns[$key][$k] = $v;
        }
        return $this;
      }
    }
    return $this->addColumn($name, $options);
  }

  public function getColumns()
  {
    return $this->_columns;
  }

  public function getColumnSortUrl(array $column, $url = null)
  {
    $context = sfContext::getInstance();
    $routing = $context->getRouting();
    $request = $context->getRequest();
    if ($url === null)
    {
      $url = $routing->getCurrentInternalUri();

      if (strpos($url, '?') !== false)
      {
        $url = '@'.$routing->getCurrentRouteName().substr($url, strpos($url, '?'));
      } else {
        $url = '@'.$routing->getCurrentRouteName();
      }
    }
    $sep = strpos($url, '?') === false ? '?' : '&';
    return $url.$sep.'sort='.$column['name'].'&order='.(($this->_order == 'asc') ? 'desc' : 'asc');
  }

  public function getColumnSortLink(array $column, $url = null)
  {
    if ($this->_sort == $column['name'])
    {
      $image = image_tag('/sfSympalPlugin/images/'.($this->_order ? $this->_order : 'asc').'_sort_icon.png').' ';
    } else {
      $image = null;
    }
    if ($this->_isSortable && $column['is_sortable'])
    {
      return link_to($image.$column['label'], $this->getColumnSortUrl($column, $url));
    } else {
      return $image.$column['label'];
    }
  }

  public function getPagerHeader()
  {
    $this->init();

    $params = array(
      'dataGrid' => $this
    );

    return sfSympalToolkit::getSymfonyResource($this->_renderingModule.'/pager_header', $params);
  }

  public function getPagerNavigation($url)
  {
    $this->init();

    $params = array(
      'dataGrid' => $this,
      'url' => $url
    );

    return sfSympalToolkit::getSymfonyResource($this->_renderingModule.'/pager_navigation', $params);
  }

  public function getRows($hydrationMode = null)
  {
    $this->init();

    $rows = array();

    $results = $this->_pager->getResults($hydrationMode);
    foreach ($results as $result)
    {
      $rows[] = $this->getRow($result);
      if (is_object($result))
      {
        $result->free();
        unset($result);
      }
    }
    if (is_object($results))
    {
      $results->free();
    }
    return $rows;
  }

  public function getRow($record)
  {
    $row = array();
    foreach ($this->_columns as $column)
    {
      if (isset($column['method']) && $record instanceof Doctrine_Record)
      {
        $row[$column['name']] = $record->$column['method']();
      } else if (isset($column['renderer'])) {
        $row[$column['name']] = sfSympalToolkit::getSymfonyResource($column['renderer'], array(
          'dataGrid' => $this,
          'column' => $column,
          'record' => $record
        ));
      } else {
        $value = $this->getRecordColumnValue($record, $column);
        if (isset($column['type']) && $column['type'] == 'timestamp')
        {
          $value = format_date($value);
        }
        $row[$column['name']] = $value;
      }
    }
    return $row;
  }

  public function render($hydrationMode = null)
  {
    $this->init();

    $params = array();
    $params['dataGrid'] = $this;
    $params['pager'] = $this->_pager;
    $params['hydrationMode'] = $hydrationMode;

    return sfSympalToolkit::getSymfonyResource($this->_renderingModule.'/list', $params);
  }

  public function init()
  {
    if ($this->_initialized)
    {
      return $this;
    }

    $this->_query->getSqlQuery(array(), false);
    $this->_pager->init();
    if (!$this->_columns)
    {
      $this->_populateDefaultColumns();
    } else {
      $this->_initializeColumns();
    }

    $request = sfContext::getInstance()->getRequest();

    if ($request['sort'])
    {
      $this->_sort = $request['sort'];
    }
    if ($request['order'])
    {
      $this->_order = $request['order'];
    }

    if ($this->_sort && $this->_isSortable)
    {
      $this->_query->addOrderBy($this->_sort.(isset($this->_order) ? ' '.$this->_order : null));
    }

    $this->_initialized = true;

    return $this;
  }

  private function _initializeColumns()
  {
    foreach ($this->_columns as $name => $options)
    {
      $options = _parse_attributes($options);
      $e = explode('.', $name);
      $alias = isset($e[1]) ? $e[0] : $this->_query->getRootAlias();
      if ($this->_query->hasAliasDeclaration($alias))
      {
        $component = $this->_query->getQueryComponent($alias);
      } else {
        $component = array('table' => $this->_table);
      }
      $fieldName = isset($e[1]) ? $e[1] : $e[0];

      if ($component['table']->hasField($fieldName))
      {
        $column = array_merge(array(
          'dqlAlias' => $alias,
          'name' => $name,
          'fieldName' => $fieldName,
          'is_sortable' => true
        ), $options, $component['table']->getDefinitionOf($fieldName), $component);
      } else {
        $column = array(
          'name' => $fieldName,
          'fieldName' => $fieldName,
          'is_sortable' => false
        );
      }

      if ( ! isset($column['label']))
      {
        $column['label'] = sfInflector::humanize($column['fieldName']);
      }

      if (isset($column['parent']) && isset($column['relation']))
      {
        $current = $column;
        while (isset($current['parent']))
        {
          $this->_parents[$column['dqlAlias']][] = $current['relation']['alias'];
          $current = $this->_query->getQueryComponent($current['parent']);
          $this->_parents[$column['dqlAlias']] = array_unique($this->_parents[$column['dqlAlias']]);
          $this->_parents[$column['dqlAlias']] = array_reverse($this->_parents[$column['dqlAlias']]);
        }
      }

      $this->_columns[$name] = $column;
    }
  }

  private function _populateDefaultColumns()
  {
    $rootAlias = $this->_query->getRootAlias();
    $this->_columns = array();
    foreach ($this->_table->getColumns() as $name => $column)
    {
      $column['fieldName'] = $this->_table->getFieldName($name);
      $column['name'] = $rootAlias.'.'.$column['fieldName'];
      $column['label'] = sfInflector::humanize($column['fieldName']);
      $column['is_sortable'] = true;
      $this->_columns[$column['name']] = $column;
    }
  }

  public function getRecordColumnValue($record, $column)
  {
    $current = $record;
    if (isset($column['dqlAlias']) && isset($this->_parents[$column['dqlAlias']]))
    {
      
      foreach ($this->_parents[$column['dqlAlias']] as $parent)
      {
        if (isset($current[$parent]))
        {
          $current = $current[$parent];
        }
      }
    }
    return $current[$column['fieldName']];
  }

  public function __call($method, $arguments)
  {
    if (method_exists($this->_query, $method))
    {
      $return = call_user_func_array(array($this->_query, $method), $arguments);
      if ($return === $this->_query)
      {
        return $this;
      } else {
        return $return;
      }
    } else if (method_exists($this->_pager, $method)) {
      call_user_func_array(array($this->_pager, $method), $arguments);
      return $this;
    } else {
      throw new Doctrine_Exception(sprintf('Uknown method "%s" called on "%s"', $method, get_class($this)));
    }
  }
}