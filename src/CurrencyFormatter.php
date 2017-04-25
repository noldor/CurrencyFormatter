<?php
declare(strict_types=1);

namespace Noldors\Formatters;

use Noldors\Formatters\Exceptions\InflectorNotSetException;
use Noldors\Inflect\Inflector;

/**
 * Class CurrencyFormatter
 * @package Noldors\Formatters
 */
class CurrencyFormatter
{
    /**
     * Rounding mode to round towards positive infinity.
     *
     * @see http://php.net/manual/en/intl.numberformatter-constants.php
     */
    public const ROUND_CEILING = 0;

    /**
     * Rounding mode to round towards negative infinity.
     *
     * @see http://php.net/manual/en/intl.numberformatter-constants.php
     */
    public const ROUND_FLOOR = 1;

    /**
     * Rounding mode to round towards zero.
     *
     * @see http://php.net/manual/en/intl.numberformatter-constants.php
     */
    public const ROUND_DOWN = 2;

    /**
     * Rounding mode to round away from zero.
     *
     * @see http://php.net/manual/en/intl.numberformatter-constants.php
     */
    public const ROUND_UP = 3;

    /**
     * Rounding mode to round towards the "nearest neighbor" unless both
     * neighbors are equidistant, in which case, round towards the even
     * neighbor.
     *
     * @see http://php.net/manual/en/intl.numberformatter-constants.php
     */
    public const ROUND_HALFEVEN = 4;

    /**
     * Rounding mode to round towards "nearest neighbor" unless both neighbors
     * are equidistant, in which case round down.
     *
     * @see http://php.net/manual/en/intl.numberformatter-constants.php
     */
    public const ROUND_HALFDOWN = 5;

    /**
     * Rounding mode to round towards "nearest neighbor" unless both neighbors
     * are equidistant, in which case round up.
     *
     * @see http://php.net/manual/en/intl.numberformatter-constants.php
     */
    public const ROUND_HALFUP = 6;

    /**
     * Spell only intenger part of float price.
     */
    public const SPELL_INT = 100;

    /**
     * Spell int part as string and fraction part as number.
     */
    public const SPELL_FRACTION_AS_NUMBER = 200;

    /**
     * Spell integer and fraction parts of float price.
     */
    public const SPELL_ALL = 300;

    /**
     * Locale used for currency manipulation.
     *
     * @var string
     */
    protected $locale;

    /**
     * Formatter, used for currency formatting.
     *
     * @var \NumberFormatter
     */
    protected $formatter;

    /**
     * Inflector for spell method.
     *
     * @var \Noldors\Inflect\Inflector
     */
    protected $inflector;

    /**
     * Name of integer part of float price.
     *
     * @var string
     */
    protected $intCurrencyName = '';

    /**
     * Name of fraction part of float price.
     *
     * @var string
     */
    protected $fractionCurrencyName = '';

    /**
     * Before using this class, make sure that you have installed correct locale like this
     * \Locale::setDefault('en-US'), or you can simply pass locale to constructor.
     *
     * @param string    $locale
     * @param Inflector $inflector
     * @param string    $intCurrencyName
     * @param string    $fractionCurrencyName
     */
    public function __construct(
        string $locale = null,
        Inflector $inflector = null,
        string $intCurrencyName = '',
        string $fractionCurrencyName = ''
    ) {
        if (is_null($locale)) {
            $this->locale = \Locale::getDefault();
        } else {
            $this->locale = $locale;
        }

        if ($intCurrencyName !== '') {
            $this->intCurrencyName = $intCurrencyName;
        }

        if ($fractionCurrencyName !== '') {
            $this->fractionCurrencyName = $fractionCurrencyName;
        }

        if (!is_null($inflector)) {
            $this->inflector = $inflector;
        }

        $this->formatter = new \NumberFormatter($this->locale, \NumberFormatter::CURRENCY);
    }

    /**
     * Static constructor.
     *
     * @param string    $locale
     * @param Inflector $inflector
     * @param string    $intCurrencyName
     * @param string    $fractionCurrencyName
     *
     * @return self
     */
    public static function make(
        string $locale = null,
        Inflector $inflector = null,
        string $intCurrencyName,
        string $fractionCurrencyName
    ): self {
        return new static($locale, $inflector, $intCurrencyName, $fractionCurrencyName);
    }

    /**
     * Set name of integer part of float price.
     *
     * @param string $name
     *
     * @return self
     */
    public function setIntCurrencyName(string $name): self
    {
        $this->intCurrencyName = $name;

        return $this;
    }

    /**
     * Set name of fraction part of float price.
     *
     * @param string $name
     *
     * @return self
     */
    public function setFractionCurrencyName(string $name): self
    {
        $this->fractionCurrencyName = $name;

        return $this;
    }

    /**
     * Set normal quantity of decimal digits.
     *
     * @param int $digits
     *
     * @return self
     */
    public function setFractionDigits(int $digits = 2): self
    {
        $this->formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $digits);

        return $this;
    }

    /**
     * Set maximum quantity of decimal digits.
     *
     * @param int $digits
     *
     * @return self
     */
    public function setMaxFractionDigits(int $digits): self
    {
        $this->formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $digits);

        return $this;
    }

    /**
     * Set minimum quantity of decimal digits. If decimal digits quantity less than this number,
     * missing digits will be filled with zeros.
     *
     * @param int $digits
     *
     * @return self
     */
    public function setMinFractionDigits(int $digits = 0): self
    {
        $this->formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $digits);

        return $this;
    }

    /**
     * Set group size of formatted price.
     *
     * @param int $size
     *
     * @return self
     */
    public function setGroupSize(int $size): self
    {
        $this->formatter->setAttribute(\NumberFormatter::GROUPING_SIZE, $size);

        return $this;
    }

    /**
     * Set round mode for formatter, it can be one of ROUND_CEILING (0), ROUND_FLOOR (1), ROUND_DOWN
     * (2), ROUND_UP (3), ROUND_HALFEVEN (4), ROUND_HALFDOWN (5), ROUND_HALFUP (6). It is better
     * to ise constants like this: CurrencyFormatter::ROUND_UP
     *
     * @param int $roundMode
     *
     * @return self
     */
    public function setRoundMode(int $roundMode): self
    {
        $this->formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, $roundMode);

        return $this;
    }

    /**
     * Set decimal separator for currency. For example: if separator = '/' currency would be like
     * this - $1234/57
     *
     * @param string $separator
     *
     * @return self
     */
    public function setDecimalSeparator(string $separator): self
    {
        $this->formatter->setSymbol(\NumberFormatter::MONETARY_SEPARATOR_SYMBOL, $separator);

        return $this;
    }

    /**
     * Set group separator. For example: if separator = '/' currency would be like this -
     * $123/456.78
     *
     * @param string $separator
     *
     * @return self
     */
    public function setGroupSeparator(string $separator): self
    {
        $this->formatter->setSymbol(\NumberFormatter::MONETARY_GROUPING_SEPARATOR_SYMBOL,
            $separator);

        return $this;
    }

    /**
     * Set currency symbol. You can set any symbol you want, maybe like this - '₽'.
     *
     * @param string $symbol
     *
     * @return self
     */
    public function setCurrencySymbol(string $symbol): self
    {
        $this->formatter->setSymbol(\NumberFormatter::CURRENCY_SYMBOL, $symbol);

        return $this;
    }

    /**
     * Format number with set of rules added in constructor or by methods.
     *
     * @param int|float $number
     *
     * @return string
     */
    public function format($number): string
    {
        return $this->formatter->format($number);
    }

    /**
     * @param     $number
     * @param int $type
     *
     * @return string
     * @throws \Noldors\Formatters\Exceptions\InflectorNotSetException
     */
    public function spell($number, $type = self::SPELL_ALL): string
    {
        if (is_null($this->inflector)) {
            throw new InflectorNotSetException('Pass \Noldors\Inflect\Inflector object as second parameter to constructor');
        }

        [$int, $fraction] = explode('.', $this->formatFloat($number));

        if ($type === self::SPELL_INT) {
            return $this->spellInteger((int)$int);
        } else {
            if ($type === self::SPELL_FRACTION_AS_NUMBER) {
                return $this->spellFractionAsNumber((int)$int, $fraction);
            } else {
                return $this->spellAll((int)$int, (int)$fraction);
            }
        }
    }

    /**
     * Spell only integer part of float.
     *
     * @param int $int
     *
     * @return string
     */
    protected function spellInteger(int $int): string
    {
        return (new \NumberFormatter($this->locale, \NumberFormatter::SPELLOUT))->format($int) . ' ' .
            $this->inflector->plural($this->intCurrencyName, $int);
    }

    /**
     * Spell int part as string and fraction part as number.
     *
     * @param int $int
     * @param int $fraction
     *
     * @return string
     */
    protected function spellFractionAsNumber(int $int, $fraction): string
    {
        return (new \NumberFormatter($this->locale, \NumberFormatter::SPELLOUT))->format($int) . ' ' .
            $this->inflector->plural($this->intCurrencyName, $int) . ' ' .
            $fraction . ' ' .
            $this->inflector->plural($this->fractionCurrencyName, (int)$fraction);
    }

    /**
     * Spell integer and fraction part of float.
     *
     * @param int $int
     * @param int $fraction
     *
     * @return string
     */
    protected function spellAll(int $int, int $fraction): string
    {
        return (new \NumberFormatter($this->locale, \NumberFormatter::SPELLOUT))->format($int) . ' ' .
            $this->inflector->plural($this->intCurrencyName, $int) . ' ' .
            (new \NumberFormatter($this->locale, \NumberFormatter::SPELLOUT))->format($fraction) . ' ' .
            $this->inflector->plural($this->fractionCurrencyName, $fraction);
    }

    /**
     * Format number as float so fraction digits is always shown.
     *
     * @param $number
     *
     * @return string
     */
    protected function formatFloat($number)
    {
        return number_format($number, $this->formatter->getAttribute(\NumberFormatter::FRACTION_DIGITS), '.', '');
    }
}