<?php
/**
 * Formatacao de datas no idioma corrente da instancia, em substituicao a
 * strftime(), deprecada no PHP 8.1.
 *
 * A saida reproduz a da glibc para os idiomas suportados pelo Titan (pt_BR,
 * en_US e es_ES), de modo que %x, %X e %c continuam identicos ao que o
 * strftime() produzia com setlocale(). Para um idioma fora da tabela,
 * IntlDateFormatter (extensao intl) e usado quando disponivel; na ausencia
 * da extensao, cai para os nomes em ingles via date().
 *
 * Este arquivo precisa rodar no PHP 7.4 e no 8.x: sem sintaxe exclusiva do 8.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @copyright 2005-2026 Titan Framework
 * @license http://www.titanframework.com/license/ BSD License (3 Clause)
 * @see Localization
 */
class DateFormatter
{
	/**
	 * Tabela por idioma, compativel com os arquivos de locale da glibc:
	 * d_fmt (%x), t_fmt (%X), d_t_fmt (%c), abday (%a), day (%A), abmon (%b), mon (%B).
	 */
	static private $locales = array (
		'pt_BR' => array (
			'x' => '%d/%m/%Y',
			'X' => '%H:%M:%S',
			'c' => '%a %d %b %Y %H:%M:%S',
			'am' => '', 'pm' => '',
			'abday' => array ('dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sáb'),
			'day' => array ('domingo', 'segunda', 'terça', 'quarta', 'quinta', 'sexta', 'sábado'),
			'abmon' => array ('jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'),
			'mon' => array ('janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro')
		),
		'en_US' => array (
			'x' => '%m/%d/%Y',
			'X' => '%I:%M:%S %p',
			'c' => '%a %d %b %Y %I:%M:%S %p %Z',
			'am' => 'AM', 'pm' => 'PM',
			'abday' => array ('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'),
			'day' => array ('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
			'abmon' => array ('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'),
			'mon' => array ('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')
		),
		'es_ES' => array (
			'x' => '%d/%m/%y',
			'X' => '%H:%M:%S',
			'c' => '%a %d %b %Y %H:%M:%S',
			'am' => "a.\xE2\x80\xAFm.", 'pm' => "p.\xE2\x80\xAFm.", // glibc >= 2.33 usa U+202F; 2.31 (Ubuntu 20.04) deixa vazio
			'abday' => array ('dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb'),
			'day' => array ('domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'),
			'abmon' => array ('ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'),
			'mon' => array ('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre')
		)
	);

	/**
	 * Equivalente a strftime ($format, $timestamp) no idioma corrente.
	 *
	 * @param string $format Formato no padrao do strftime (%x, %X, %c, %d, %m, %Y, %H...).
	 * @param int|string|null $timestamp Unix timestamp; NULL usa o instante atual.
	 * @param string|false $language Idioma (pt_BR, en_US, es_ES); FALSE usa o da Localization.
	 * @return string
	 */
	static public function strftime ($format, $timestamp = NULL, $language = FALSE)
	{
		if ($timestamp === NULL || $timestamp === '' || $timestamp === FALSE)
			$timestamp = time ();
		elseif (!is_int ($timestamp))
			$timestamp = is_numeric ($timestamp) ? (int) $timestamp : (int) strtotime ((string) $timestamp);

		if ($language === FALSE)
			$language = self::currentLanguage ();

		$table = array_key_exists ($language, self::$locales) ? self::$locales [$language] : NULL;

		$format = (string) $format;

		$out = '';

		$len = strlen ($format);

		for ($i = 0; $i < $len; $i++)
		{
			$char = $format [$i];

			if ($char != '%' || $i + 1 >= $len)
			{
				$out .= $char;

				continue;
			}

			$spec = $format [++$i];

			// Modificadores POSIX %E e %O sao ignorados (glibc faz o mesmo nas locales do Titan).
			if (($spec == 'E' || $spec == 'O') && $i + 1 < $len)
				$spec = $format [++$i];

			$out .= self::directive ($spec, $timestamp, $table, $language);
		}

		return $out;
	}

	static private function directive ($spec, $timestamp, $table, $language)
	{
		switch ($spec)
		{
			case '%': return '%';
			case 'n': return "\n";
			case 't': return "\t";

			// Compostos dependentes de idioma
			case 'x': return self::strftime ($table !== NULL ? $table ['x'] : self::intlPattern ($timestamp, $language, 'x', '%m/%d/%y'), $timestamp, $language);
			case 'X': return self::strftime ($table !== NULL ? $table ['X'] : self::intlPattern ($timestamp, $language, 'X', '%H:%M:%S'), $timestamp, $language);
			case 'c': return self::strftime ($table !== NULL ? $table ['c'] : self::intlPattern ($timestamp, $language, 'c', '%a %b %e %H:%M:%S %Y'), $timestamp, $language);

			// Compostos fixos
			case 'D': return date ('m/d/y', $timestamp);
			case 'F': return date ('Y-m-d', $timestamp);
			case 'T': return date ('H:i:s', $timestamp);
			case 'R': return date ('H:i', $timestamp);
			case 'r': return date ('h:i:s', $timestamp) .' '. self::ampm ($timestamp, $table, $language);

			// Nomes de dias e meses
			case 'a': return $table !== NULL ? $table ['abday'][(int) date ('w', $timestamp)] : self::intlName ($timestamp, $language, 'EEE', 'D');
			case 'A': return $table !== NULL ? $table ['day'][(int) date ('w', $timestamp)] : self::intlName ($timestamp, $language, 'EEEE', 'l');
			case 'b':
			case 'h': return $table !== NULL ? $table ['abmon'][(int) date ('n', $timestamp) - 1] : self::intlName ($timestamp, $language, 'MMM', 'M');
			case 'B': return $table !== NULL ? $table ['mon'][(int) date ('n', $timestamp) - 1] : self::intlName ($timestamp, $language, 'MMMM', 'F');
			case 'p': return self::ampm ($timestamp, $table, $language);
			case 'P': return strtolower (self::ampm ($timestamp, $table, $language));

			// Numericos
			case 'd': return date ('d', $timestamp);
			case 'e': return str_pad (date ('j', $timestamp), 2, ' ', STR_PAD_LEFT);
			case 'j': return str_pad ((string) ((int) date ('z', $timestamp) + 1), 3, '0', STR_PAD_LEFT);
			case 'm': return date ('m', $timestamp);
			case 'u': return date ('N', $timestamp);
			case 'w': return date ('w', $timestamp);
			case 'U': return str_pad ((string) floor (((int) date ('z', $timestamp) + 7 - (int) date ('w', $timestamp)) / 7), 2, '0', STR_PAD_LEFT);
			case 'W': return str_pad ((string) floor (((int) date ('z', $timestamp) + 7 - ((int) date ('N', $timestamp) - 1)) / 7), 2, '0', STR_PAD_LEFT);
			case 'V': return date ('W', $timestamp);
			case 'G': return date ('o', $timestamp);
			case 'g': return substr (date ('o', $timestamp), -2);
			case 'y': return date ('y', $timestamp);
			case 'Y': return date ('Y', $timestamp);
			case 'C': return str_pad ((string) floor ((int) date ('Y', $timestamp) / 100), 2, '0', STR_PAD_LEFT);
			case 'H': return date ('H', $timestamp);
			case 'k': return str_pad (date ('G', $timestamp), 2, ' ', STR_PAD_LEFT);
			case 'I': return date ('h', $timestamp);
			case 'l': return str_pad (date ('g', $timestamp), 2, ' ', STR_PAD_LEFT);
			case 'M': return date ('i', $timestamp);
			case 'S': return date ('s', $timestamp);
			case 's': return (string) $timestamp;
			case 'z': return date ('O', $timestamp);
			case 'Z': return date ('T', $timestamp);
		}

		// Diretiva desconhecida: devolve como veio (mesmo comportamento da glibc).
		return '%'. $spec;
	}

	static private function ampm ($timestamp, $table, $language)
	{
		$pm = (int) date ('G', $timestamp) >= 12;

		if ($table !== NULL)
			return $table [$pm ? 'pm' : 'am'];

		if (class_exists ('IntlDateFormatter', FALSE))
		{
			$name = self::intlName ($timestamp, $language, 'a', 'A');

			if ($name !== '')
				return $name;
		}

		return $pm ? 'PM' : 'AM';
	}

	/**
	 * Nome localizado (dia/mes) via IntlDateFormatter; sem intl, usa date().
	 */
	static private function intlName ($timestamp, $language, $icuPattern, $dateFallback)
	{
		if (class_exists ('IntlDateFormatter', FALSE))
		{
			$formatter = new IntlDateFormatter ($language, IntlDateFormatter::NONE, IntlDateFormatter::NONE, date_default_timezone_get (), IntlDateFormatter::GREGORIAN, $icuPattern);

			$name = $formatter->format ($timestamp);

			if ($name !== FALSE && $name !== NULL && $name !== '')
				return rtrim ($name, '.');
		}

		return date ($dateFallback, $timestamp);
	}

	/**
	 * Para idiomas fora da tabela: %x, %X e %c vem do ICU (formatos curtos).
	 * Devolve um formato strftime, para reaproveitar o laco principal.
	 */
	static private function intlPattern ($timestamp, $language, $spec, $fallback)
	{
		if (!class_exists ('IntlDateFormatter', FALSE))
			return $fallback;

		switch ($spec)
		{
			case 'x': $formatter = new IntlDateFormatter ($language, IntlDateFormatter::SHORT, IntlDateFormatter::NONE); break;
			case 'X': $formatter = new IntlDateFormatter ($language, IntlDateFormatter::NONE, IntlDateFormatter::MEDIUM); break;
			default:  $formatter = new IntlDateFormatter ($language, IntlDateFormatter::MEDIUM, IntlDateFormatter::MEDIUM); break;
		}

		$formatted = $formatter->format ($timestamp);

		// O texto ja formatado nao tem diretivas; escapa eventuais '%'.
		return $formatted === FALSE ? $fallback : str_replace ('%', '%%', $formatted);
	}

	static private function currentLanguage ()
	{
		if (class_exists ('Localization', FALSE) || class_exists ('Instance', FALSE))
		{
			try
			{
				$language = Localization::singleton ()->getLanguage ();

				if (is_string ($language) && $language != '')
					return $language;
			}
			catch (Exception $e) {}
		}

		return 'pt_BR';
	}
}
