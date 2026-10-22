<?php

/**
 * Funcoes de compatibilidade entre versoes do PHP (7.4 ate 8.4).
 *
 * Este arquivo e carregado por function/general.php e nao deve usar sintaxe
 * exclusiva do PHP 8 (match, argumentos nomeados, nullsafe, union types etc.).
 */

/**
 * Substituto de each(), removida no PHP 8.0.
 *
 * Mantem exatamente a semantica da funcao original: devolve o par
 * chave/valor do elemento apontado pelo ponteiro interno do array e avanca
 * o ponteiro; devolve FALSE quando o ponteiro passou do ultimo elemento.
 * Assim como each(), nao reposiciona o ponteiro no inicio: quem itera deve
 * chamar reset() ao terminar (padrao ja adotado nas classes do nucleo).
 *
 * @param array $array Array iterado (por referencia, para mover o ponteiro).
 * @return array|false array (1 => valor, 'value' => valor, 0 => chave, 'key' => chave) ou FALSE.
 */
function arrayEach (&$array)
{
	if (!is_array ($array))
		return FALSE;

	$key = key ($array);

	if ($key === NULL)
		return FALSE;

	$value = current ($array);

	next ($array);

	return array (1 => $value, 'value' => $value, 0 => $key, 'key' => $key);
}

if (!function_exists ('str_contains'))
{
	function str_contains ($haystack, $needle)
	{
		return $needle === '' || strpos ((string) $haystack, (string) $needle) !== FALSE;
	}
}

if (!function_exists ('str_starts_with'))
{
	function str_starts_with ($haystack, $needle)
	{
		return $needle === '' || strncmp ((string) $haystack, (string) $needle, strlen ($needle)) === 0;
	}
}

if (!function_exists ('str_ends_with'))
{
	function str_ends_with ($haystack, $needle)
	{
		if ($needle === '')
			return TRUE;

		$haystack = (string) $haystack;

		return strlen ($haystack) >= strlen ($needle) && substr_compare ($haystack, (string) $needle, -strlen ($needle)) === 0;
	}
}

/**
 * Substitutos de utf8_encode()/utf8_decode(), deprecadas no PHP 8.2.
 * Convertem apenas entre ISO-8859-1 e UTF-8, exatamente como as originais.
 */
function latin1ToUtf8 ($str)
{
	$str = (string) $str;

	if (function_exists ('mb_convert_encoding'))
		return mb_convert_encoding ($str, 'UTF-8', 'ISO-8859-1');

	if (function_exists ('iconv'))
		return iconv ('ISO-8859-1', 'UTF-8', $str);

	return utf8_encode ($str);
}

function utf8ToLatin1 ($str)
{
	$str = (string) $str;

	if (function_exists ('mb_convert_encoding'))
		return mb_convert_encoding ($str, 'ISO-8859-1', 'UTF-8');

	if (function_exists ('iconv'))
		return iconv ('UTF-8', 'ISO-8859-1//IGNORE', $str);

	return utf8_decode ($str);
}
