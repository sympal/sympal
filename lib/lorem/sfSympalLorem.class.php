<?php

/**
 * Class responsible for generating paragraphs of lorem ipsum text used for
 * dummy content in a CMS
 *
 * Original code borrowed from www.diem-project.org
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalLorem
{
  protected static
    $_loremText,
    $_markdownLoremText;

  /**
   * Get specified number of lorem markdown paragraphs
   *
   * @param integer $nbParagraphs 
   * @return string $markdown
   */
  public static function getMarkdownLorem($nbParagraphs = 1)
  {
    return str_repeat(self::_getMarkdownLoremText(), $nbParagraphs);
  }

  /**
   * Get big body of lorem ipsum text
   *
   * @param integer $nbParagraphs 
   * @return string $text
   */
  public static function getBigLorem($nbParagraphs = null)
  {
    $lorem = self::_getLoremText();

    if (null === $nbParagraphs)
    {
      $nbParagraphs = 1;
    }

    $paragraphs = array();
    for($it = 0; $it < $nbParagraphs; $it++)
    {
      $paragraphs[] = $lorem[array_rand($lorem)];
    }

    return implode("\n", $paragraphs);
  }

  /**
   * Get little body of lorem ipsum text
   *
   * @param integer $nbCarac Number of characters
   * @param integer $maxNbCarac Max number of characters
   * @return string $text
   */
  public static function getLittleLorem($nbCarac = null, $maxNbCarac = 255)
  {
    if (!$nbCarac)
    {
      $nbCarac = 5 + rand(0, 60);
    }

    $nbCarac = min($nbCarac, $maxNbCarac);

    $paragraph = self::getBigLorem(1);

    return substr($paragraph, rand(0, strlen($paragraph)-$nbCarac), $nbCarac);
  }

  /**
   * Get all the lorem text
   *
   * @return string $text
   */
  protected static function _getLoremText()
  {
    if (null === self::$_loremText)
    {
      self::$_loremText = file(dirname(__FILE__).'/../../data/lorem/big');
    }

    return self::$_loremText;
  }

  /**
   * Get all the markdown lorem text
   *
   * @return string $markdown
   */
  protected static function _getMarkdownLoremText()
  {
    if (null === self::$_markdownLoremText)
    {
      self::$_markdownLoremText = implode('', file(dirname(__FILE__).'/../../data/lorem/markdown'));
    }

    return self::$_markdownLoremText;
  }
}