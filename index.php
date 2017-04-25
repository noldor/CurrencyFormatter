<?php
require 'vendor/autoload.php';

/*$inflector = new \Noldors\Inflect\Inflector(new \Noldors\Inflect\Inflectors\RussianInflector());

$currency = new \Noldors\Formatters\CurrencyFormatter('ru_RU', $inflector, 'рубль', 'копейка');

var_dump($currency->spell(56741, \Noldors\Formatters\CurrencyFormatter::SPELL_INT));

var_dump($currency->spell(56741, \Noldors\Formatters\CurrencyFormatter::SPELL_FRACTION_AS_NUMBER));

var_dump($currency->spell(56741, \Noldors\Formatters\CurrencyFormatter::SPELL_ALL));*/

$formatter = new NumberFormatter('ru_RU', NumberFormatter::DECIMAL);

$formatter->setAttribute(\NumberFormatter::DECIMAL_ALWAYS_SHOWN, false);

$formatter->setAttribute(\NumberFormatter::GROUPING_SIZE, 2);


var_dump($formatter->format(543756765));