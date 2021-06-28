<?php
# vi:tabstop=2:expandtab:sw=2
# Transliterate Unicode text into plain 7-bit ASCII.
#
# Example usage:
# include "unidecode/unidecode.php";
# unidecode(mb_chr(0x5317).mb_chr(0x4EB0));
# Resultin string: "Bei Jing ";
#
# The transliteration uses a straightforward map, and doesn't have alternatives
# for the same character based on language, position, or anything else.
#
# Author: Benjamin Renard <brenard@easter-eggs.com>
#
# This is a PHP port of Unidecode Python module by Tomaž Šolc <tomaz.solc@tablix.org>
# and this Python module is a port of Text::Unidecode Perl module by Sean M. Burke
# <sburke@cpan.org>.

class UnidecodeError extends Exception {
  /*
   Raised for Unidecode-related errors.

   The index attribute contains the index of the character that caused
   the error.
   */
  public function __construct($message, $index=null) {
    $this -> index = $index;
    parent::__construct($message);
  }
}

function _get_table_path($table) {
  if (__FILE__ != "") {
	  $script = __FILE__;
  }
  else {
	  foreach(get_included_files() as $script)
		  if (basename($script) == 'unidecode.php')
			  break;
  }
  return realpath(dirname($script)."/tables/$table.php");
}

function _get_repl_str($char, $encoding=null) {
  $codepoint = mb_ord($char, $encoding);

  if ($codepoint < 0x80)
    # Already ASCII
    return strval($char);

  if ($codepoint > 0xeffff)
    # No data on characters in Private Use Area and above.
    return null;

  $section = $codepoint >> 8;   # Chop off the last two hex digits
  $position = $codepoint % 256; # Last two hex digits
  $table = sprintf('x%03x', $section);

  if (!isset($GLOBALS["UNIDECODE_TABLE_$table"])) {
    $table_file = _get_table_path($table);
    if (!is_file($table_file)) {
      return null;
    }

    include($table_file);
    if (!isset($GLOBALS["UNIDECODE_TABLE_$table"]))
      return null;
  }

  if (count($GLOBALS["UNIDECODE_TABLE_$table"]) > $position)
    return $GLOBALS["UNIDECODE_TABLE_$table"][$position];
  return null;
}

function unidecode($string, $errors=null, $replace_str=null, $encoding=null) {
  $retval = "";
  if (is_null($encoding))
    $encoding = mb_internal_encoding();

  for ($index=0; $index < mb_strlen($string, $encoding); $index++) {
    $char = mb_substr($string, $index, 1, $encoding);
    $repl = _get_repl_str($char, $encoding);

    if (!is_null($repl)) {
      $retval .= $repl;
      continue;
    }

    // Handle substitution error
    switch($errors) {
      case null:
      case 'ignore':
        $repl = '';
        break;

      case 'strict':
        ob_start();
        var_dump($char);
        $char = ob_get_contents();
        ob_end_clean();
        throw new UnidecodeError("no replacement found for character $char in position $index", $index);
        break;

      case 'replace':
        $repl = (is_string($replace_str)?$replace_str:'?');
        break;

      case 'preserve':
        $repl = $char;
        break;

      default:
        throw new UnidecodeError('invalid value for errors parameter '.$errors);
    }

    $retval .= $repl;
  }

  return $retval;
}
