<?php

/**
 * Doctrine record filter which allows us to access properties of the related content
 * type record from the content record itself.
 *
 * Example: Imagine you have a sfSympalBlogPost content type and it has a property
 * named teaser. You could access that property from the sfSympalContent instance.
 *
 *     [php]
 *     echo $content->getTeaser();
 *
 * The above is the same as doing:
 *
 *     [php]
 *     echo $content->getRecord()->getTeaser();
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalContentFilter extends Doctrine_Record_Filter
{
  /**
   * Filter Doctrine_Record::set() calls and see if we can call the property on
   * the content type record
   *
   * @param Doctrine_Record $content The sfSympalContent instance
   * @param string $name The name of the property
   * @param string $value The value of the property
   * @return sfSympalContent $content
   * @throws Doctrine_Record_UnknownPropertyException If property could not be found
   */
  public function filterSet(Doctrine_Record $content, $name, $value)
  {
    try {
      if ($content->getRecord())
      {
        $content->getRecord()->set($name, $value);
        return $content;
      }
    } catch (Exception $e) {}

    throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($content)));
  }

  /**
   * Filter Doctrine_Record::get() calls and see if we can call the property on
   * the content type record
   *
   * @param Doctrine_Record $content The sfSympalContent instance
   * @param string $name The name of the property
   * @return mixed $value The value of the property
   * @throws Doctrine_Record_UnknownPropertyException If property could not be found
   */
  public function filterGet(Doctrine_Record $content, $name)
  {
    try {
      if ($content->getRecord())
      {
        return $content->getRecord()->get($name);
      }
    } catch (Exception $e) {}

    throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($content)));
  }
}