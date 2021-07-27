<?php
# vi:tabstop=2:expandtab:sw=2
# Transliterate Unicode text into plain 7-bit ASCII.
#
# The transliteration uses a straightforward map, and doesn't have alternatives
# for the same character based on language, position, or anything else.
#
# Author: Benjamin Renard <brenard@easter-eggs.com>
#
# This is a PHP port of Unidecode Python module by Tomaž Šolc <tomaz.solc@tablix.org>
# and this Python module is a port of Text::Unidecode Perl module by Sean M. Burke
# <sburke@cpan.org>.

namespace Unidecode;
use Exception;


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
