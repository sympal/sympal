<?php

/**
 * Get a Sympal pager header <h3>
 *
 * @param sfDoctrinePager $pager
 * @return string $html
 */
function get_sympal_pager_header($pager)
{
  return '<div class="sympal_pager_header"><h3>'.
    __('Showing %first% to %last% of %count% total results.', array(
      '%first%' => $pager->getFirstIndice(),
      '%last%' => $pager->getLastIndice(),
      '%count%' => $pager->getNbResults()
    )).'</h3></div>';
}

/**
 * Get the navigation links for given sfDoctrinePager instance
 *
 * @param sfDoctrinePager $pager
 * @param string $uri  The uri to prefix to the links
 * @return string $html
 */
function get_sympal_pager_navigation($pager, $uri, $requestKey = 'page')
{
  sympal_use_stylesheet('/sfSympalPlugin/css/pager.css');

  $navigation = '<div class="sympal_pager_navigation">';
 
  if ($pager->haveToPaginate())
  {  
    $uri .= (preg_match('/\?/', $uri) ? '&' : '?').$requestKey.'=';
 
    // First and previous page
    if ($pager->getPage() != 1)
    {
      $navigation .= link_to(image_tag('/sf/sf_admin/images/first.png', 'align=absmiddle'), $uri.'1');
      $navigation .= link_to(image_tag('/sf/sf_admin/images/previous.png', 'align=absmiddle'), $uri.$pager->getPreviousPage()).' ';
    }
 
    // Pages one by one
    $links = array();
    foreach ($pager->getLinks() as $page)
    {
      $links[] = '<span>'.link_to_unless($page == $pager->getPage(), $page, $uri.$page).'</span>';
    }
    $navigation .= join('  ', $links);
 
    // Next and last page
    if ($pager->getPage() != $pager->getLastPage())
    {
      $navigation .= ' '.link_to(image_tag('/sf/sf_admin/images/next.png', 'align=absmiddle'), $uri.$pager->getNextPage());
      $navigation .= link_to(image_tag('/sf/sf_admin/images/last.png', 'align=absmiddle'), $uri.$pager->getLastPage());
    }
  }
  $navigation .= '</div>';

  return $navigation;
}