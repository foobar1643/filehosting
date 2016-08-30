<?php

namespace Filehosting;

/**
 * Translates plural expressions, used mostly in templates.
 *
 * @author foobar1643 <foobar76239@gmail.com>
 */
class Translator
{
    /** @var string $locale Current application locale. */
    private $locale;

    /**
     * Constructor.
     *
     * @param string $locale A locale string.
     */
    public function __construct($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Translates plural expression using gettext and MessageFormatter class.
     *
     * @param string $text Text pattern for plural expression.
     * @param int $number Number that will be used in plural expression.
     *
     * @return string
     */
    public function translatePlural($text, $number)
    {
        $messageFormatter = new \MessageFormatter($this->locale, _($text));
        return $messageFormatter->format([$number]);
    }

    /**
     * Translates a string using gettext.
     *
     * @param string $string A string to translate.
     *
     * @return string
     */
    public function translate($string)
    {
        return _($string);
    }
}