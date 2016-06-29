<?php

namespace Filehosting;

class Translator
{
    private $locale;

    public function __construct($locale)
    {
        $this->locale = $locale;
    }

    public function translatePlural($text, $number)
    {
        $messageFormatter = new \MessageFormatter($this->locale, _($text));
        return $messageFormatter->format([$number]);
    }
}