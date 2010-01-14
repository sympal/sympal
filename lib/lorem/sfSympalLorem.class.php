<?php

class sfSympalLorem
{
  protected static
    $_loremText,
    $_markdownLoremText;

  public static function getMarkdownLorem($nbParagraphs = 1)
  {
    return str_repeat(self::getMarkdownLoremText(), $nbParagraphs);
  }

  public static function getBigLorem($nbParagraphs = null)
  {
    $lorem = self::getLoremText();

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

  protected static function getLoremText()
  {
    if (null === self::$_loremText)
    {
      self::$_loremText = file(dirname(__FILE__).'/../../data/lorem/big');
    }

    return self::$_loremText;
  }

  protected static function getMarkdownLoremText()
  {
    if (null === self::$_markdownLoremText)
    {
      self::$_markdownLoremText = implode('', file(dirname(__FILE__).'/../../data/lorem/markdown'));
    }

    return self::$_markdownLoremText;
  }

}