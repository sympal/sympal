<?php

/**
 * Class responsible for rendering the results of a sfSympalServerCheck instance
 * to HTML.
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalServerCheckHtmlRenderer extends sfSympalServerCheckRenderer
{
  /**
   * Render the results to HTML
   *
   * @return string $html
   */
  public function render()
  {
    return
      '<div class="clearfix">'.
      sprintf('<div class="half">%s%s%s</div>',
      $this->_renderTable('server'),
      $this->_renderTable('symfony'),
      $this->_renderTable('php config')
      ).
      sprintf('<div class="half">%s</div>', $this->_renderTable('php_extensions')).
      '</div>';
  }

  /**
   * Render a HTML table for given check space name
   *
   * @param string $name
   * @return $html
   */
  protected function _renderTable($name)
  {
    return
      '<table>'.
      sprintf('<thead><tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th></tr></thead>', ucfirst($name), 'Sympal requirement', 'Server state', 'Diagnostic').
      sprintf('<tbody>%s</tbody>', $this->_renderRows($this->_check->getCheckspace($name))).
      '</table>';
  }

  /**
   * Render a given array of checks as HTML table rows
   *
   * @param array $checks
   * @return string $html
   */
  protected function _renderRows(array $checks)
  {
    $html = '';
    foreach($checks as $unit)
    {
      $html .= sprintf('<tr class="%s %s"><th>%s</th><td>%s</td><td>%s</td><td>%s</td></tr>',
      $unit->getDiagnostic(),
      $unit->isRequired() ? 'required' : '',
      $this->_renderName($unit),
      $this->_renderRequirement($unit),
      $this->_renderState($unit),
      $this->_renderDiagnostic($unit)
      );
    }
    return $html;
  }

  /**
   * Render the diagnostic value HTML for a single check
   *
   * @param sfSympalServerCheckUnit $unit 
   * @return string $html
   */
  protected function _renderDiagnostic(sfSympalServerCheckUnit $unit)
  {
    $icons = array(
      'valid' => 'valid',
      'error' => 'error',
      'warning' => 'warning'
    );
    return image_tag('/sfSympalPlugin/images/'.$icons[$unit->getDiagnostic()].'.png', 'title='.$unit->getDiagnostic());
  }
}