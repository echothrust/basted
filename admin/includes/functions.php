<?

// Copyright (c) 2004 Steven Hazel
// Copyright (c) 2003 ByteTaxi, Inc.
//
// Permission to use, copy, modify, distribute, and sell this software
// and its documentation for any purpose is hereby granted without
// fee, provided that the above copyright notice appear in all copies
// and that both that copyright notice and this permission notice
// appear in supporting documentation.  No representations are made
// about the suitability of this software for any purpose.  It is
// provided "as is" without express or implied warranty.
//
// Based on Crypt::RandPasswd by JDPORTER@cpan.org (John Porter)
//
// See Also:
// FIPS 181 - (APG), Automated Password Generator:
// http://www.itl.nist.gov/fipspubs/fip181.htm

// Deviations From Standard:
//
// This implementation deviates in one critical way from the standard
// upon which it is based: the random number generator in this
// implementation does not use DES.  Instead, it uses PHP's built-in
// mt_rand() function.

// PLEASE READ THE FOLLOWING...
// This copyright notice is left here in order to respect the original 
// authors wishes. The following functions does not fall under this copyright
// statement, this means that you can do what ever you like with them we simply
// dont care.
// Function List that doesnt fall under this Copyright Notice.
// function getvalid($link) 
// function insert($ip,$port,$agent,$referer,$email) 
// function dblink($user="",$pass="",$host="",$database="")
// function mquery($QUERY) 
// function adv_decode($badvar="") 
// function tohtml($badvar="") 
// function tosql($badvar="") 
// function getmsg($mid) 
 
define('MIN_LENGTH_PASSWORD', 6);
define('MAX_LENGTH_PASSWORD', 14);


//
// Global Variables:
//

global $saved_pair;

global $grams;
$grams = array( 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l',
                'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y',
                'z', 'ch', 'gh', 'ph', 'rh', 'sh', 'th', 'wh', 'qu', 'ck' );

global $vowel_grams;
$vowel_grams = array( 'a', 'e', 'i', 'o', 'u', 'y' );

global $occurrence_frequencies;
$occurrence_frequencies = array(
    'a'  => 10,      'b'  =>  8,      'c'  => 12,      'd'  => 12,
    'e'  => 12,      'f'  =>  8,      'g'  =>  8,      'h'  =>  6,
    'i'  => 10,      'j'  =>  8,      'k'  =>  8,      'l'  =>  6,
    'm'  =>  6,      'n'  => 10,      'o'  => 10,      'p'  =>  6,
    'r'  => 10,      's'  =>  8,      't'  => 10,      'u'  =>  6,
    'v'  =>  8,      'w'  =>  8,      'x'  =>  1,      'y'  =>  8,
    'z'  =>  1,      'ch' =>  1,      'gh' =>  1,      'ph' =>  1,
    'rh' =>  1,      'sh' =>  2,      'th' =>  1,      'wh' =>  1,
    'qu' =>  1,      'ck' =>  1,
);

global $numbers;
$numbers = array();
foreach( $grams as $gram ) {
    for( $i = 0; $i < $occurrence_frequencies[$gram]; $i++ ) {
        $numbers[] = $gram;
    }
}

global $vowel_numbers;
$vowel_numbers = array();
foreach( $vowel_grams as $gram ) {
    for( $i = 0; $i < $occurrence_frequencies[$gram]; $i++ ) {
        $vowel_numbers[] = $gram;
    }
}

//
// Bit flags
//

define( "MAX_UNACCEPTABLE",  20 );

# gram rules:
define( "RULE_NOT_BEGIN_SYLLABLE", 0x08 );
define( "RULE_NO_FINAL_SPLIT", 0x04 );
define( "RULE_VOWEL", 0x02 );
define( "RULE_ALTERNATE_VOWEL", 0x01 );
define( "NO_SPECIAL_RULE", 0x00 );

# digram rules:
define( "RULE_BEGIN", 0x80 );
define( "RULE_NOT_BEGIN", 0x40 );
define( "RULE_BREAK", 0x20 );
define( "RULE_PREFIX", 0x10 );
define( "RULE_ILLEGAL_PAIR", 0x08 );
define( "RULE_SUFFIX", 0x04 );
define( "RULE_END", 0x02 );
define( "RULE_NOT_END", 0x01 );
define( "RULE_ANY_COMBINATION", 0x00 );

global $gram_rules;

$gram_rules = array();
foreach( $grams as $gram ) {
    $gram_rules[ $gram ] = NO_SPECIAL_RULE;
}
foreach( $vowel_grams as $gram ) {
    $gram_rules[ $gram ] = RULE_VOWEL;
}

$gram_rules['e'] |= RULE_NO_FINAL_SPLIT;
$gram_rules['y'] |= RULE_ALTERNATE_VOWEL;

$gram_rules['x']  = RULE_NOT_BEGIN_SYLLABLE;
$gram_rules['ck'] = RULE_NOT_BEGIN_SYLLABLE;



global $digram_rules;
$digram_rules = array();

###############################################################################
# BEGIN DIGRAM RULES
###############################################################################

    $digram_rules['a']['a'] = RULE_ILLEGAL_PAIR;
    $digram_rules['a']['b'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['c'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['d'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['e'] = RULE_ILLEGAL_PAIR;
    $digram_rules['a']['f'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['g'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['a']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['j'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['k'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['l'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['m'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['n'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['o'] = RULE_ILLEGAL_PAIR;
    $digram_rules['a']['p'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['r'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['s'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['t'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['v'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['w'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['x'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['y'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['z'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['ch'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['gh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['a']['ph'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['a']['sh'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['th'] = RULE_ANY_COMBINATION;
    $digram_rules['a']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['a']['qu'] = RULE_BREAK | RULE_NOT_END;
    $digram_rules['a']['ck'] = RULE_ANY_COMBINATION;

    $digram_rules['b']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['b']['b'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['b']['c'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['b']['d'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['b']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['b']['f'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['b']['g'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['b']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['b']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['b']['j'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['b']['k'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['b']['l'] = RULE_BEGIN | RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['b']['m'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['b']['n'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['b']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['b']['p'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['b']['r'] = RULE_BEGIN | RULE_END;
    $digram_rules['b']['s'] = RULE_NOT_BEGIN;
    $digram_rules['b']['t'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['b']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['b']['v'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['b']['w'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['b']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['b']['y'] = RULE_ANY_COMBINATION;
    $digram_rules['b']['z'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['b']['ch'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['b']['gh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['b']['ph'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['b']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['b']['sh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['b']['th'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['b']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['b']['qu'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['b']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['c']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['c']['b'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['c']['c'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['c']['d'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['c']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['c']['f'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['c']['g'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['c']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['c']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['c']['j'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['c']['k'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['c']['l'] = RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['c']['m'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['c']['n'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['c']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['c']['p'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['c']['r'] = RULE_NOT_END;
    $digram_rules['c']['s'] = RULE_NOT_BEGIN | RULE_END;
    $digram_rules['c']['t'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['c']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['c']['v'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['c']['w'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['c']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['c']['y'] = RULE_ANY_COMBINATION;
    $digram_rules['c']['z'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['c']['ch'] = RULE_ILLEGAL_PAIR;
    $digram_rules['c']['gh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['c']['ph'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['c']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['c']['sh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['c']['th'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['c']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['c']['qu'] = RULE_NOT_BEGIN | RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['c']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['d']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['d']['b'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['d']['c'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['d']['d'] = RULE_NOT_BEGIN;
    $digram_rules['d']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['d']['f'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['d']['g'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['d']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['d']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['d']['j'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['d']['k'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['d']['l'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['d']['m'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['d']['n'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['d']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['d']['p'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['d']['r'] = RULE_BEGIN | RULE_NOT_END;
    $digram_rules['d']['s'] = RULE_NOT_BEGIN | RULE_END;
    $digram_rules['d']['t'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['d']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['d']['v'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['d']['w'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['d']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['d']['y'] = RULE_ANY_COMBINATION;
    $digram_rules['d']['z'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['d']['ch'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['d']['gh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['d']['ph'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['d']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['d']['sh'] = RULE_NOT_BEGIN | RULE_NOT_END;
    $digram_rules['d']['th'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['d']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['d']['qu'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['d']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['e']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['b'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['c'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['d'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['f'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['g'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['e']['i'] = RULE_NOT_END;
    $digram_rules['e']['j'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['k'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['l'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['m'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['n'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['o'] = RULE_BREAK;
    $digram_rules['e']['p'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['r'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['s'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['t'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['v'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['w'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['x'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['y'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['z'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['ch'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['gh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['e']['ph'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['e']['sh'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['th'] = RULE_ANY_COMBINATION;
    $digram_rules['e']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['e']['qu'] = RULE_BREAK | RULE_NOT_END;
    $digram_rules['e']['ck'] = RULE_ANY_COMBINATION;

    $digram_rules['f']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['f']['b'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['f']['c'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['f']['d'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['f']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['f']['f'] = RULE_NOT_BEGIN;
    $digram_rules['f']['g'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['f']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['f']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['f']['j'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['f']['k'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['f']['l'] = RULE_BEGIN | RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['f']['m'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['f']['n'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['f']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['f']['p'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['f']['r'] = RULE_BEGIN | RULE_NOT_END;
    $digram_rules['f']['s'] = RULE_NOT_BEGIN;
    $digram_rules['f']['t'] = RULE_NOT_BEGIN;
    $digram_rules['f']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['f']['v'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['f']['w'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['f']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['f']['y'] = RULE_NOT_BEGIN;
    $digram_rules['f']['z'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['f']['ch'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['f']['gh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['f']['ph'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['f']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['f']['sh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['f']['th'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['f']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['f']['qu'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['f']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['g']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['g']['b'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['g']['c'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['g']['d'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['g']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['g']['f'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['g']['g'] = RULE_NOT_BEGIN;
    $digram_rules['g']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['g']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['g']['j'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['g']['k'] = RULE_ILLEGAL_PAIR;
    $digram_rules['g']['l'] = RULE_BEGIN | RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['g']['m'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['g']['n'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['g']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['g']['p'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['g']['r'] = RULE_BEGIN | RULE_NOT_END;
    $digram_rules['g']['s'] = RULE_NOT_BEGIN | RULE_END;
    $digram_rules['g']['t'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['g']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['g']['v'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['g']['w'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['g']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['g']['y'] = RULE_NOT_BEGIN;
    $digram_rules['g']['z'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['g']['ch'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['g']['gh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['g']['ph'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['g']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['g']['sh'] = RULE_NOT_BEGIN;
    $digram_rules['g']['th'] = RULE_NOT_BEGIN;
    $digram_rules['g']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['g']['qu'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['g']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['h']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['h']['b'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['h']['c'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['h']['d'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['h']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['h']['f'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['h']['g'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['h']['h'] = RULE_ILLEGAL_PAIR;
    $digram_rules['h']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['h']['j'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['h']['k'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['h']['l'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['h']['m'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['h']['n'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['h']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['h']['p'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['h']['r'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['h']['s'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['h']['t'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['h']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['h']['v'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['h']['w'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['h']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['h']['y'] = RULE_ANY_COMBINATION;
    $digram_rules['h']['z'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['h']['ch'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['h']['gh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['h']['ph'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['h']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['h']['sh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['h']['th'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['h']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['h']['qu'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['h']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['i']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['i']['b'] = RULE_ANY_COMBINATION;
    $digram_rules['i']['c'] = RULE_ANY_COMBINATION;
    $digram_rules['i']['d'] = RULE_ANY_COMBINATION;
    $digram_rules['i']['e'] = RULE_NOT_BEGIN;
    $digram_rules['i']['f'] = RULE_ANY_COMBINATION;
    $digram_rules['i']['g'] = RULE_ANY_COMBINATION;
    $digram_rules['i']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['i']['i'] = RULE_ILLEGAL_PAIR;
    $digram_rules['i']['j'] = RULE_ANY_COMBINATION;
    $digram_rules['i']['k'] = RULE_ANY_COMBINATION;
    $digram_rules['i']['l'] = RULE_ANY_COMBINATION;
    $digram_rules['i']['m'] = RULE_ANY_COMBINATION;
    $digram_rules['i']['n'] = RULE_ANY_COMBINATION;
    $digram_rules['i']['o'] = RULE_BREAK;
    $digram_rules['i']['p'] = RULE_ANY_COMBINATION;
    $digram_rules['i']['r'] = RULE_ANY_COMBINATION;
    $digram_rules['i']['s'] = RULE_ANY_COMBINATION;
    $digram_rules['i']['t'] = RULE_ANY_COMBINATION;
    $digram_rules['i']['u'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['i']['v'] = RULE_ANY_COMBINATION;
    $digram_rules['i']['w'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['i']['x'] = RULE_ANY_COMBINATION;
    $digram_rules['i']['y'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['i']['z'] = RULE_ANY_COMBINATION;
    $digram_rules['i']['ch'] = RULE_ANY_COMBINATION;
    $digram_rules['i']['gh'] = RULE_NOT_BEGIN;
    $digram_rules['i']['ph'] = RULE_ANY_COMBINATION;
    $digram_rules['i']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['i']['sh'] = RULE_ANY_COMBINATION;
    $digram_rules['i']['th'] = RULE_ANY_COMBINATION;
    $digram_rules['i']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['i']['qu'] = RULE_BREAK | RULE_NOT_END;
    $digram_rules['i']['ck'] = RULE_ANY_COMBINATION;

    $digram_rules['j']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['j']['b'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['j']['c'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['j']['d'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['j']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['j']['f'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['j']['g'] = RULE_ILLEGAL_PAIR;
    $digram_rules['j']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['j']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['j']['j'] = RULE_ILLEGAL_PAIR;
    $digram_rules['j']['k'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['j']['l'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['j']['m'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['j']['n'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['j']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['j']['p'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['j']['r'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['j']['s'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['j']['t'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['j']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['j']['v'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['j']['w'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['j']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['j']['y'] = RULE_NOT_BEGIN;
    $digram_rules['j']['z'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['j']['ch'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['j']['gh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['j']['ph'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['j']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['j']['sh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['j']['th'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['j']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['j']['qu'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['j']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['k']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['k']['b'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['k']['c'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['k']['d'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['k']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['k']['f'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['k']['g'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['k']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['k']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['k']['j'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['k']['k'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['k']['l'] = RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['k']['m'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['k']['n'] = RULE_BEGIN | RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['k']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['k']['p'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['k']['r'] = RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['k']['s'] = RULE_NOT_BEGIN | RULE_END;
    $digram_rules['k']['t'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['k']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['k']['v'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['k']['w'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['k']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['k']['y'] = RULE_NOT_BEGIN;
    $digram_rules['k']['z'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['k']['ch'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['k']['gh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['k']['ph'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['k']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['k']['sh'] = RULE_NOT_BEGIN;
    $digram_rules['k']['th'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['k']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['k']['qu'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['k']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['l']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['l']['b'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['l']['c'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['l']['d'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['l']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['l']['f'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['l']['g'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['l']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['l']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['l']['j'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['l']['k'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['l']['l'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['l']['m'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['l']['n'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['l']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['l']['p'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['l']['r'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['l']['s'] = RULE_NOT_BEGIN;
    $digram_rules['l']['t'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['l']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['l']['v'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['l']['w'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['l']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['l']['y'] = RULE_ANY_COMBINATION;
    $digram_rules['l']['z'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['l']['ch'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['l']['gh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['l']['ph'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['l']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['l']['sh'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['l']['th'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['l']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['l']['qu'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['l']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['m']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['m']['b'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['m']['c'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['m']['d'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['m']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['m']['f'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['m']['g'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['m']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['m']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['m']['j'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['m']['k'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['m']['l'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['m']['m'] = RULE_NOT_BEGIN;
    $digram_rules['m']['n'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['m']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['m']['p'] = RULE_NOT_BEGIN;
    $digram_rules['m']['r'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['m']['s'] = RULE_NOT_BEGIN;
    $digram_rules['m']['t'] = RULE_NOT_BEGIN;
    $digram_rules['m']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['m']['v'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['m']['w'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['m']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['m']['y'] = RULE_ANY_COMBINATION;
    $digram_rules['m']['z'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['m']['ch'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['m']['gh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['m']['ph'] = RULE_NOT_BEGIN;
    $digram_rules['m']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['m']['sh'] = RULE_NOT_BEGIN;
    $digram_rules['m']['th'] = RULE_NOT_BEGIN;
    $digram_rules['m']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['m']['qu'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['m']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['n']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['n']['b'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['n']['c'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['n']['d'] = RULE_NOT_BEGIN;
    $digram_rules['n']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['n']['f'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['n']['g'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['n']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['n']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['n']['j'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['n']['k'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['n']['l'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['n']['m'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['n']['n'] = RULE_NOT_BEGIN;
    $digram_rules['n']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['n']['p'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['n']['r'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['n']['s'] = RULE_NOT_BEGIN;
    $digram_rules['n']['t'] = RULE_NOT_BEGIN;
    $digram_rules['n']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['n']['v'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['n']['w'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['n']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['n']['y'] = RULE_NOT_BEGIN;
    $digram_rules['n']['z'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['n']['ch'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['n']['gh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['n']['ph'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['n']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['n']['sh'] = RULE_NOT_BEGIN;
    $digram_rules['n']['th'] = RULE_NOT_BEGIN;
    $digram_rules['n']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['n']['qu'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['n']['ck'] = RULE_NOT_BEGIN | RULE_PREFIX;

    $digram_rules['o']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['b'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['c'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['d'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['e'] = RULE_ILLEGAL_PAIR;
    $digram_rules['o']['f'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['g'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['o']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['j'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['k'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['l'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['m'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['n'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['p'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['r'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['s'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['t'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['v'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['w'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['x'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['y'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['z'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['ch'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['gh'] = RULE_NOT_BEGIN;
    $digram_rules['o']['ph'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['o']['sh'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['th'] = RULE_ANY_COMBINATION;
    $digram_rules['o']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['o']['qu'] = RULE_BREAK | RULE_NOT_END;
    $digram_rules['o']['ck'] = RULE_ANY_COMBINATION;

    $digram_rules['p']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['p']['b'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['p']['c'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['p']['d'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['p']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['p']['f'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['p']['g'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['p']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['p']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['p']['j'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['p']['k'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['p']['l'] = RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['p']['m'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['p']['n'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['p']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['p']['p'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['p']['r'] = RULE_NOT_END;
    $digram_rules['p']['s'] = RULE_NOT_BEGIN | RULE_END;
    $digram_rules['p']['t'] = RULE_NOT_BEGIN | RULE_END;
    $digram_rules['p']['u'] = RULE_NOT_BEGIN | RULE_END;
    $digram_rules['p']['v'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['p']['w'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['p']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['p']['y'] = RULE_ANY_COMBINATION;
    $digram_rules['p']['z'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['p']['ch'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['p']['gh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['p']['ph'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['p']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['p']['sh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['p']['th'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['p']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['p']['qu'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['p']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['r']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['r']['b'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['r']['c'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['r']['d'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['r']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['r']['f'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['r']['g'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['r']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['r']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['r']['j'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['r']['k'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['r']['l'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['r']['m'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['r']['n'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['r']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['r']['p'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['r']['r'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['r']['s'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['r']['t'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['r']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['r']['v'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['r']['w'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['r']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['r']['y'] = RULE_ANY_COMBINATION;
    $digram_rules['r']['z'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['r']['ch'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['r']['gh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['r']['ph'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['r']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['r']['sh'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['r']['th'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['r']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['r']['qu'] = RULE_NOT_BEGIN | RULE_PREFIX | RULE_NOT_END;
    $digram_rules['r']['ck'] = RULE_NOT_BEGIN | RULE_PREFIX;

    $digram_rules['s']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['s']['b'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['s']['c'] = RULE_NOT_END;
    $digram_rules['s']['d'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['s']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['s']['f'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['s']['g'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['s']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['s']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['s']['j'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['s']['k'] = RULE_ANY_COMBINATION;
    $digram_rules['s']['l'] = RULE_BEGIN | RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['s']['m'] = RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['s']['n'] = RULE_PREFIX | RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['s']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['s']['p'] = RULE_ANY_COMBINATION;
    $digram_rules['s']['r'] = RULE_NOT_BEGIN | RULE_NOT_END;
    $digram_rules['s']['s'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['s']['t'] = RULE_ANY_COMBINATION;
    $digram_rules['s']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['s']['v'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['s']['w'] = RULE_BEGIN | RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['s']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['s']['y'] = RULE_ANY_COMBINATION;
    $digram_rules['s']['z'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['s']['ch'] = RULE_BEGIN | RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['s']['gh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['s']['ph'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['s']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['s']['sh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['s']['th'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['s']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['s']['qu'] = RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['s']['ck'] = RULE_NOT_BEGIN;

    $digram_rules['t']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['t']['b'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['t']['c'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['t']['d'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['t']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['t']['f'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['t']['g'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['t']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['t']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['t']['j'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['t']['k'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['t']['l'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['t']['m'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['t']['n'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['t']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['t']['p'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['t']['r'] = RULE_NOT_END;
    $digram_rules['t']['s'] = RULE_NOT_BEGIN | RULE_END;
    $digram_rules['t']['t'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['t']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['t']['v'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['t']['w'] = RULE_BEGIN | RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['t']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['t']['y'] = RULE_ANY_COMBINATION;
    $digram_rules['t']['z'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['t']['ch'] = RULE_NOT_BEGIN;
    $digram_rules['t']['gh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['t']['ph'] = RULE_NOT_BEGIN | RULE_END;
    $digram_rules['t']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['t']['sh'] = RULE_NOT_BEGIN | RULE_END;
    $digram_rules['t']['th'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['t']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['t']['qu'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['t']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['u']['a'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['u']['b'] = RULE_ANY_COMBINATION;
    $digram_rules['u']['c'] = RULE_ANY_COMBINATION;
    $digram_rules['u']['d'] = RULE_ANY_COMBINATION;
    $digram_rules['u']['e'] = RULE_NOT_BEGIN;
    $digram_rules['u']['f'] = RULE_ANY_COMBINATION;
    $digram_rules['u']['g'] = RULE_ANY_COMBINATION;
    $digram_rules['u']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['u']['i'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['u']['j'] = RULE_ANY_COMBINATION;
    $digram_rules['u']['k'] = RULE_ANY_COMBINATION;
    $digram_rules['u']['l'] = RULE_ANY_COMBINATION;
    $digram_rules['u']['m'] = RULE_ANY_COMBINATION;
    $digram_rules['u']['n'] = RULE_ANY_COMBINATION;
    $digram_rules['u']['o'] = RULE_NOT_BEGIN | RULE_BREAK;
    $digram_rules['u']['p'] = RULE_ANY_COMBINATION;
    $digram_rules['u']['r'] = RULE_ANY_COMBINATION;
    $digram_rules['u']['s'] = RULE_ANY_COMBINATION;
    $digram_rules['u']['t'] = RULE_ANY_COMBINATION;
    $digram_rules['u']['u'] = RULE_ILLEGAL_PAIR;
    $digram_rules['u']['v'] = RULE_ANY_COMBINATION;
    $digram_rules['u']['w'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['u']['x'] = RULE_ANY_COMBINATION;
    $digram_rules['u']['y'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['u']['z'] = RULE_ANY_COMBINATION;
    $digram_rules['u']['ch'] = RULE_ANY_COMBINATION;
    $digram_rules['u']['gh'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['u']['ph'] = RULE_ANY_COMBINATION;
    $digram_rules['u']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['u']['sh'] = RULE_ANY_COMBINATION;
    $digram_rules['u']['th'] = RULE_ANY_COMBINATION;
    $digram_rules['u']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['u']['qu'] = RULE_BREAK | RULE_NOT_END;
    $digram_rules['u']['ck'] = RULE_ANY_COMBINATION;

    $digram_rules['v']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['v']['b'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['c'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['d'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['v']['f'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['g'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['v']['j'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['k'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['l'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['m'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['n'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['v']['p'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['r'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['s'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['t'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['v']['v'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['w'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['v']['y'] = RULE_NOT_BEGIN;
    $digram_rules['v']['z'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['ch'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['gh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['ph'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['v']['sh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['th'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['v']['qu'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['v']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['w']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['w']['b'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['w']['c'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['w']['d'] = RULE_NOT_BEGIN | RULE_PREFIX | RULE_END;
    $digram_rules['w']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['w']['f'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['w']['g'] = RULE_NOT_BEGIN | RULE_PREFIX | RULE_END;
    $digram_rules['w']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['w']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['w']['j'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['w']['k'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['w']['l'] = RULE_NOT_BEGIN | RULE_PREFIX | RULE_SUFFIX;
    $digram_rules['w']['m'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['w']['n'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['w']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['w']['p'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['w']['r'] = RULE_BEGIN | RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['w']['s'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['w']['t'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['w']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['w']['v'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['w']['w'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['w']['x'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['w']['y'] = RULE_ANY_COMBINATION;
    $digram_rules['w']['z'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['w']['ch'] = RULE_NOT_BEGIN;
    $digram_rules['w']['gh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['w']['ph'] = RULE_NOT_BEGIN;
    $digram_rules['w']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['w']['sh'] = RULE_NOT_BEGIN;
    $digram_rules['w']['th'] = RULE_NOT_BEGIN;
    $digram_rules['w']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['w']['qu'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['w']['ck'] = RULE_NOT_BEGIN;

    $digram_rules['x']['a'] = RULE_NOT_BEGIN;
    $digram_rules['x']['b'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['c'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['d'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['e'] = RULE_NOT_BEGIN;
    $digram_rules['x']['f'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['g'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['i'] = RULE_NOT_BEGIN;
    $digram_rules['x']['j'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['k'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['l'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['m'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['n'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['o'] = RULE_NOT_BEGIN;
    $digram_rules['x']['p'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['r'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['s'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['t'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['u'] = RULE_NOT_BEGIN;
    $digram_rules['x']['v'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['w'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['x']['y'] = RULE_NOT_BEGIN;
    $digram_rules['x']['z'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['ch'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['gh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['ph'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['x']['sh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['th'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['x']['qu'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['x']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['y']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['y']['b'] = RULE_NOT_BEGIN;
    $digram_rules['y']['c'] = RULE_NOT_BEGIN | RULE_NOT_END;
    $digram_rules['y']['d'] = RULE_NOT_BEGIN;
    $digram_rules['y']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['y']['f'] = RULE_NOT_BEGIN | RULE_NOT_END;
    $digram_rules['y']['g'] = RULE_NOT_BEGIN;
    $digram_rules['y']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['y']['i'] = RULE_BEGIN | RULE_NOT_END;
    $digram_rules['y']['j'] = RULE_NOT_BEGIN | RULE_NOT_END;
    $digram_rules['y']['k'] = RULE_NOT_BEGIN;
    $digram_rules['y']['l'] = RULE_NOT_BEGIN | RULE_NOT_END;
    $digram_rules['y']['m'] = RULE_NOT_BEGIN;
    $digram_rules['y']['n'] = RULE_NOT_BEGIN;
    $digram_rules['y']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['y']['p'] = RULE_NOT_BEGIN;
    $digram_rules['y']['r'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['y']['s'] = RULE_NOT_BEGIN;
    $digram_rules['y']['t'] = RULE_NOT_BEGIN;
    $digram_rules['y']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['y']['v'] = RULE_NOT_BEGIN | RULE_NOT_END;
    $digram_rules['y']['w'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['y']['x'] = RULE_NOT_BEGIN;
    $digram_rules['y']['y'] = RULE_ILLEGAL_PAIR;
    $digram_rules['y']['z'] = RULE_NOT_BEGIN;
    $digram_rules['y']['ch'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['y']['gh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['y']['ph'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['y']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['y']['sh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['y']['th'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['y']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['y']['qu'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['y']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['z']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['z']['b'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['z']['c'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['z']['d'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['z']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['z']['f'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['z']['g'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['z']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['z']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['z']['j'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['z']['k'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['z']['l'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['z']['m'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['z']['n'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['z']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['z']['p'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['z']['r'] = RULE_NOT_BEGIN | RULE_NOT_END;
    $digram_rules['z']['s'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['z']['t'] = RULE_NOT_BEGIN;
    $digram_rules['z']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['z']['v'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['z']['w'] = RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['z']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['z']['y'] = RULE_ANY_COMBINATION;
    $digram_rules['z']['z'] = RULE_NOT_BEGIN;
    $digram_rules['z']['ch'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['z']['gh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['z']['ph'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['z']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['z']['sh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['z']['th'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['z']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['z']['qu'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['z']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['ch']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['ch']['b'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ch']['c'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ch']['d'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ch']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['ch']['f'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ch']['g'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ch']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ch']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['ch']['j'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ch']['k'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ch']['l'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ch']['m'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ch']['n'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ch']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['ch']['p'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ch']['r'] = RULE_NOT_END;
    $digram_rules['ch']['s'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ch']['t'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ch']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['ch']['v'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ch']['w'] = RULE_NOT_BEGIN | RULE_NOT_END;
    $digram_rules['ch']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['ch']['y'] = RULE_ANY_COMBINATION;
    $digram_rules['ch']['z'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ch']['ch'] = RULE_ILLEGAL_PAIR;
    $digram_rules['ch']['gh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ch']['ph'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ch']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['ch']['sh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ch']['th'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ch']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['ch']['qu'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ch']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['gh']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['gh']['b'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_PREFIX | RULE_NOT_END;
    $digram_rules['gh']['c'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_PREFIX | RULE_NOT_END;
    $digram_rules['gh']['d'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_PREFIX | RULE_NOT_END;
    $digram_rules['gh']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['gh']['f'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_PREFIX | RULE_NOT_END;
    $digram_rules['gh']['g'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_PREFIX | RULE_NOT_END;
    $digram_rules['gh']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_PREFIX | RULE_NOT_END;
    $digram_rules['gh']['i'] = RULE_BEGIN | RULE_NOT_END;
    $digram_rules['gh']['j'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_PREFIX | RULE_NOT_END;
    $digram_rules['gh']['k'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_PREFIX | RULE_NOT_END;
    $digram_rules['gh']['l'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_PREFIX | RULE_NOT_END;
    $digram_rules['gh']['m'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_PREFIX | RULE_NOT_END;
    $digram_rules['gh']['n'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_PREFIX | RULE_NOT_END;
    $digram_rules['gh']['o'] = RULE_BEGIN | RULE_NOT_END;
    $digram_rules['gh']['p'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['gh']['r'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_PREFIX | RULE_NOT_END;
    $digram_rules['gh']['s'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['gh']['t'] = RULE_NOT_BEGIN | RULE_PREFIX;
    $digram_rules['gh']['u'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_PREFIX | RULE_NOT_END;
    $digram_rules['gh']['v'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_PREFIX | RULE_NOT_END;
    $digram_rules['gh']['w'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_PREFIX | RULE_NOT_END;
    $digram_rules['gh']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['gh']['y'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_PREFIX | RULE_NOT_END;
    $digram_rules['gh']['z'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_PREFIX | RULE_NOT_END;
    $digram_rules['gh']['ch'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_PREFIX | RULE_NOT_END;
    $digram_rules['gh']['gh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['gh']['ph'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_PREFIX | RULE_NOT_END;
    $digram_rules['gh']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['gh']['sh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_PREFIX | RULE_NOT_END;
    $digram_rules['gh']['th'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_PREFIX | RULE_NOT_END;
    $digram_rules['gh']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['gh']['qu'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_PREFIX | RULE_NOT_END;
    $digram_rules['gh']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['ph']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['ph']['b'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ph']['c'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ph']['d'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ph']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['ph']['f'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ph']['g'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ph']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ph']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['ph']['j'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ph']['k'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ph']['l'] = RULE_BEGIN | RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['ph']['m'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ph']['n'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ph']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['ph']['p'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ph']['r'] = RULE_NOT_END;
    $digram_rules['ph']['s'] = RULE_NOT_BEGIN;
    $digram_rules['ph']['t'] = RULE_NOT_BEGIN;
    $digram_rules['ph']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['ph']['v'] = RULE_NOT_BEGIN | RULE_NOT_END;
    $digram_rules['ph']['w'] = RULE_NOT_BEGIN | RULE_NOT_END;
    $digram_rules['ph']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['ph']['y'] = RULE_NOT_BEGIN;
    $digram_rules['ph']['z'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ph']['ch'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ph']['gh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ph']['ph'] = RULE_ILLEGAL_PAIR;
    $digram_rules['ph']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['ph']['sh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ph']['th'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ph']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['ph']['qu'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ph']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['rh']['a'] = RULE_BEGIN | RULE_NOT_END;
    $digram_rules['rh']['b'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['c'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['d'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['e'] = RULE_BEGIN | RULE_NOT_END;
    $digram_rules['rh']['f'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['g'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['h'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['i'] = RULE_BEGIN | RULE_NOT_END;
    $digram_rules['rh']['j'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['k'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['l'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['m'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['n'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['o'] = RULE_BEGIN | RULE_NOT_END;
    $digram_rules['rh']['p'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['r'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['s'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['t'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['u'] = RULE_BEGIN | RULE_NOT_END;
    $digram_rules['rh']['v'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['w'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['y'] = RULE_BEGIN | RULE_NOT_END;
    $digram_rules['rh']['z'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['ch'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['gh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['ph'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['sh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['th'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['qu'] = RULE_ILLEGAL_PAIR;
    $digram_rules['rh']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['sh']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['sh']['b'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['sh']['c'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['sh']['d'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['sh']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['sh']['f'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['sh']['g'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['sh']['h'] = RULE_ILLEGAL_PAIR;
    $digram_rules['sh']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['sh']['j'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['sh']['k'] = RULE_NOT_BEGIN;
    $digram_rules['sh']['l'] = RULE_BEGIN | RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['sh']['m'] = RULE_BEGIN | RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['sh']['n'] = RULE_BEGIN | RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['sh']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['sh']['p'] = RULE_NOT_BEGIN;
    $digram_rules['sh']['r'] = RULE_BEGIN | RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['sh']['s'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['sh']['t'] = RULE_SUFFIX;
    $digram_rules['sh']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['sh']['v'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['sh']['w'] = RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['sh']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['sh']['y'] = RULE_ANY_COMBINATION;
    $digram_rules['sh']['z'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['sh']['ch'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['sh']['gh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['sh']['ph'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['sh']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['sh']['sh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['sh']['th'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['sh']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['sh']['qu'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['sh']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['th']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['th']['b'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['th']['c'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['th']['d'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['th']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['th']['f'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['th']['g'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['th']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['th']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['th']['j'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['th']['k'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['th']['l'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['th']['m'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['th']['n'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['th']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['th']['p'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['th']['r'] = RULE_NOT_END;
    $digram_rules['th']['s'] = RULE_NOT_BEGIN | RULE_END;
    $digram_rules['th']['t'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['th']['u'] = RULE_ANY_COMBINATION;
    $digram_rules['th']['v'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['th']['w'] = RULE_SUFFIX | RULE_NOT_END;
    $digram_rules['th']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['th']['y'] = RULE_ANY_COMBINATION;
    $digram_rules['th']['z'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['th']['ch'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['th']['gh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['th']['ph'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['th']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['th']['sh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['th']['th'] = RULE_ILLEGAL_PAIR;
    $digram_rules['th']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['th']['qu'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['th']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['wh']['a'] = RULE_BEGIN | RULE_NOT_END;
    $digram_rules['wh']['b'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['c'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['d'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['e'] = RULE_BEGIN | RULE_NOT_END;
    $digram_rules['wh']['f'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['g'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['h'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['i'] = RULE_BEGIN | RULE_NOT_END;
    $digram_rules['wh']['j'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['k'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['l'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['m'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['n'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['o'] = RULE_BEGIN | RULE_NOT_END;
    $digram_rules['wh']['p'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['r'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['s'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['t'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['u'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['v'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['w'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['y'] = RULE_BEGIN | RULE_NOT_END;
    $digram_rules['wh']['z'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['ch'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['gh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['ph'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['sh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['th'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['qu'] = RULE_ILLEGAL_PAIR;
    $digram_rules['wh']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['qu']['a'] = RULE_ANY_COMBINATION;
    $digram_rules['qu']['b'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['c'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['d'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['e'] = RULE_ANY_COMBINATION;
    $digram_rules['qu']['f'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['g'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['h'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['i'] = RULE_ANY_COMBINATION;
    $digram_rules['qu']['j'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['k'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['l'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['m'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['n'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['o'] = RULE_ANY_COMBINATION;
    $digram_rules['qu']['p'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['r'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['s'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['t'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['u'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['v'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['w'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['y'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['z'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['ch'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['gh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['ph'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['sh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['th'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['qu'] = RULE_ILLEGAL_PAIR;
    $digram_rules['qu']['ck'] = RULE_ILLEGAL_PAIR;

    $digram_rules['ck']['a'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['b'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['c'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['d'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['e'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['f'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['g'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['h'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['i'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['j'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['k'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['l'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['m'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['n'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['o'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['p'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['r'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['s'] = RULE_NOT_BEGIN;
    $digram_rules['ck']['t'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['u'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['v'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['w'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['x'] = RULE_ILLEGAL_PAIR;
    $digram_rules['ck']['y'] = RULE_NOT_BEGIN;
    $digram_rules['ck']['z'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['ch'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['gh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['ph'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['rh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['ck']['sh'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['th'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['wh'] = RULE_ILLEGAL_PAIR;
    $digram_rules['ck']['qu'] = RULE_NOT_BEGIN | RULE_BREAK | RULE_NOT_END;
    $digram_rules['ck']['ck'] = RULE_ILLEGAL_PAIR;

###############################################################################
# END DIGRAM RULES
###############################################################################


// Generates a random word, as well as its hyphenated form.  The
// length of the returned word will be between minlen and maxlen.

function generate_password($minlen = MIN_LENGTH_PASSWORD,
                           $maxlen = MAX_LENGTH_PASSWORD)
{

    if( $minlen > $maxlen ) {
        echo "minlen $minlen is greater than maxlen $maxlen\n";
        return array('','');
    }

    //
    // Check for zero length words.  This is technically not an error,
    // so we take the short cut and return empty words.
    //
    if( $maxlen == 0 ) {
        return array('','');
    }

    $word = '';
    for( $try = 0; $try < MAX_UNACCEPTABLE and $word == ''; $try++ ) {
        $results = _random_word( mt_rand( $minlen, $maxlen ) );
        $word = $results[0];
        $hyphenated_word = $results[1];
    }

    if( $word == "" and ( $minlen > 0 ) ) {
        echo "failed to generate an acceptable random password.\n";
        return array('','');
    }
    return array($word, $hyphenated_word);
}
function getvalid($link) {
	$generated = generate_password( 6, 8 );
	$result=mquery("SELECT * FROM generated WHERE  genmail ='{$generated[1]}'");
	while(mysql_num_rows($result)>0) {
       	 $generated = generate_password( 6, 8 );
       	 $result=mquery("SELECT * FROM generated WHERE genmail='{$generated[1]}'");
	}
	return array($generated[0],$generated[1]);
}
function insert($ip,$port,$agent,$referer,$email) {
	mquery("INSERT INTO generated (`ip`,`port`,`agent`,`referer`,`genmail`,`gentime`) VALUES ('$ip','$port','$agent','$referer','$email',NOW())");
}

// Selects a random element from an array.

function random_element($ar) {
    $keys = array_keys($ar);
    return $ar[ $keys[mt_rand( 0, sizeof($keys) - 1 )] ];
}



// This is the routine that returns a random word.  It collects random
// syllables until a predetermined word length is found.  If a retry
// threshold is reached, another word is tried.

function _random_word($pwlen) {
    global $grams, $saved_pair;

    $word = '';
    $word_syllables = array();

    $max_retries = ( 4 * $pwlen ) + sizeof( $grams );

    $tries = 0; // count of retries.


    // $word_units used to be an array of indices into the 'rules' C-array.
    // now it's an array of actual units (grams).
    $word_units = array();


    $saved_pair = array();
    //
    // Find syllables until the entire word is constructed.
    //
    while( strlen($word) < $pwlen ) {
        //
        // Get the syllable and find its length.
        //

        $results = get_syllable( $pwlen - strlen($word) );
        $new_syllable = $results[0];
        $syllable_units = $results[1];

        //
        // Append the syllable units to the word units.
        //
        $word_units = $word_units + $syllable_units;

        //
        // If the word has been improperly formed, throw out
        // the syllable.  The checks performed here are those
        // that must be formed on a word basis.  The other
        // tests are performed entirely within the syllable.
        // Otherwise, append the syllable to the word.
        //
        if( !(
           _improper_word( $word_units )
             ||
             (
                 $word == ''
                 and
                 _have_initial_y( $syllable_units )
             )
             ||
             (
                 strlen( $word . $new_syllable ) == $pwlen
                 and
                 _have_final_split( $syllable_units )
             )
           ) ) {
            $word .= $new_syllable;
            array_push($word_syllables, $new_syllable);
        }

        //
        // Keep track of the times we have tried to get syllables.
        // If we have exceeded the threshold, start from scratch.
        //
        $tries++;
        if( $tries > $max_retries ) {
            $tries = 0;
            $word = '';
            $word_syllables = array();
            $word_units = array();
        }
    }

    return array( $word, join('-',$word_syllables) );
}



// Selects a gram (aka "unit").  This is the standard random unit
// generating routine for get_syllable().
//
// This routine attempts to return grams (units) with a distribution
// approaching that of the distribution of the units in English.
//
// The distribution of the units may be altered in this procedure
// without affecting the digram table or any other programs using the
// random_word function, as long as the set of grams (units) is kept
// consistent throughout this library.

function _random_unit($type) {
    global $vowel_numbers, $numbers;

    if( $type & RULE_VOWEL ) {
        // Sometimes, we are asked to explicitly get a vowel (i.e., if
        // a digram pair expects one following it).  This is a
        // shortcut to do that and avoid looping with rejected
        // consonants.
        return random_element( $vowel_numbers );
    } else {
        // Get any letter according to the English distribution.
        return random_element( $numbers );
    }
}




// Check that the word does not contain illegal combinations
// that may span syllables.  Specifically, these are:
//
//  1. An illegal pair of units between syllables.
//  2. Three consecutive vowel units.
//  3. Three consecutive consonant units.
//
// The checks are made against units (1 or 2 letters), not against
// the individual letters, so three consecutive units can have
// the length of 6 at most.

function _improper_word($units) {
    global $digram_rules, $gram_rules;
    $failure = 0;

    $units = array_values($units);

    for( $unit_count = 0; $unit_count < sizeof( $units ); $unit_count++ ) {
        //
        // Check for RULE_ILLEGAL_PAIR.
        // This should have been caught for units within a syllable,
        // but in some cases it would have gone unnoticed for units between syllables
        // (e.g., when saved units in get_syllable() were not used).
        //
        if( $unit_count > 0
            and ( $digram_rules[$units[$unit_count-1]][$units[$unit_count]]
                  & RULE_ILLEGAL_PAIR ) ) {
            echo "1\n";
            return 1; // Failure!
        }

        if( $unit_count >= 2 ) {
          //
          // Check for consecutive vowels or consonants.  Because the
          // initial y of a syllable is treated as a consonant rather
          // than as a vowel, we exclude y from the first vowel in the
          // vowel test.  The only problem comes when y ends a syllable
          // and two other vowels start the next, like fly-oint.  Since
          // such words are still pronounceable, we accept this.
          //

          //
          // Vowel check.
          //
          if( (
               ($gram_rules[$units[$unit_count - 2]] & RULE_VOWEL)
               &&
               !($gram_rules[$units[$unit_count - 2]] & RULE_ALTERNATE_VOWEL)
               &&
               ($gram_rules[$units[$unit_count - 1]] & RULE_VOWEL)
               &&
               ($gram_rules[$units[$unit_count    ]] & RULE_VOWEL)
               )
              ||
              //
              // Consonant check.
              //
              (
               !($gram_rules[$units[$unit_count - 2]] & RULE_VOWEL)
               &&
               !($gram_rules[$units[$unit_count - 1]] & RULE_VOWEL)
               &&
               !($gram_rules[$units[$unit_count    ]] & RULE_VOWEL)
               ) ) {
            return 1; // Failure!
          }
        }
    }

    return 0; // success
}


// Treating y as a vowel is sometimes a problem.  Some words get
// formed that look irregular.  One special group is when y starts a
// word and is the only vowel in the first syllable.  The word ycl is
// one example.  We discard words like these.

function _have_initial_y($units) {
    global $gram_rules;

    $vowel_count = 0;
    $normal_vowel_count = 0;

    for( $unit_count = 0; $unit_count < sizeof( $units ); $unit_count++ ) {
        //
        // Count vowels.
        //
        if( $gram_rules[$units[$unit_count]] & RULE_VOWEL ) {
            $vowel_count++;

            //
            // Count the vowels that are not:
            //  1. 'y'
            //  2. at the start of the word.
            //
            if( !($gram_rules[$units[$unit_count]] & RULE_ALTERNATE_VOWEL) || ($unit_count > 0) ) {
                $normal_vowel_count++;
           }
        }
    }

    return ($vowel_count <= 1) && ($normal_vowel_count == 0);
}


// Besides the problem with the letter y, there is one with a silent e
// at the end of words, like face or nice.  We allow this silent e,
// but we do not allow it as the only vowel at the end of the word or
// syllables like ble will be generated.

function _have_final_split($units) {
    global $gram_rules;

    $vowel_count = 0;

    //
    // Count all the vowels in the word.
    //
    for( $unit_count = 0; $unit_count < sizeof( $units ); $unit_count++ ) {
        if( $gram_rules[$units[$unit_count]] & RULE_VOWEL ) {
            $vowel_count++;
        }
    }

    //
    // Return TRUE iff the only vowel was e, found at the end if the word.
    //
    return ( ($vowel_count == 1)
             && ( $gram_rules[$units[sizeof( $units ) - 1]] & RULE_NO_FINAL_SPLIT ) );
}



// Generate next unit to password, making sure that it follows these rules:
//
// 1. Each syllable must contain exactly 1 or 2 consecutive vowels,
// where y is considered a vowel.
//
// 2. Syllable end is determined as follows:
//
//    a. Vowel is generated and previous unit is a consonant and
//       syllable already has a vowel.  In this case, new syllable is
//       started and already contains a vowel.
//    b. A pair determined to be a "break" pair is encountered.
//       In this case new syllable is started with second unit of this pair.
//    c. End of password is encountered.
//    d. "begin" pair is encountered legally.  New syllable is started
//    with this pair.
//    e. "end" pair is legally encountered.  New syllable has nothing yet.
//
// 3. Try generating another unit if:
//
//    a. third consecutive vowel and not y.
//    b. "break" pair generated but no vowel yet in current or
//       previous 2 units are "not_end".
//    c. "begin" pair generated but no vowel in syllable preceding begin pair,
//       or both previous 2 pairs are designated "not_end".
//    d. "end" pair generated but no vowel in current syllable or in
//       "end" pair.
//    e. "not_begin" pair generated but new syllable must begin
//       (because previous syllable ended as defined in 2 above).
//    f. vowel is generated and 2a is satisfied, but no syllable break
//       is possible in previous 3 pairs.
//    g. Second and third units of syllable must begin, and first unit
//       is "alternate_vowel".

function marked($flag, $first_unit, $second_unit) {
    global $digram_rules;

    return $digram_rules[$first_unit][$second_unit] & $flag;
};


function digram_is_invalid($first_unit, $second_unit, $current_unit_num,
                           $length_left, $units_in_syllable, $vowel_count) {
    global $digram_rules, $gram_rules;

    //
    // Reject RULE_ILLEGAL_PAIRS of units.
    //
    if( marked( RULE_ILLEGAL_PAIR,
                $first_unit,
                $second_unit ) ) {
        return 1;
    }

    //
    // Reject units that will be split between
    // syllables when the syllable has no vowels
    // in it.
    //
    if( marked( RULE_BREAK,
                $first_unit,
                $second_unit ) &&
        ( $vowel_count == 0 ) ) {
        return 1;
    }

    //
    // Reject a unit that will end a syllable when
    // no previous unit was a vowel and neither is
    // this one.
    //
    if( marked( RULE_END,
                $first_unit,
                $second_unit ) &&
        ( $vowel_count == 0 ) &&
        !( $gram_rules[$second_unit] & RULE_VOWEL ) ) {
        return 1;
    }

    if($current_unit_num == 1) {
        //
        // Reject the unit if we are at the starting
        // digram of a syllable and it does not fit.
        //
        if( marked( RULE_NOT_BEGIN,
                    $first_unit,
                    $second_unit ) ) {
            return 1;
        }
    } else {
        // We are not at the start of a syllable.

        //
        // Do not allow syllables where the first letter is y
        // and the next pair can begin a syllable.  This may
        // lead to splits where y is left alone in a syllable.
        // Also, the combination does not sound to good even
        // if not split.
        //
        if( ( $current_unit_num == 2 ) &&
            marked( RULE_BEGIN,
                    $first_unit,
                    $second_unit ) &&
            ( $gram_rules[$units_in_syllable[0]] &
              RULE_ALTERNATE_VOWEL ) ) {
            return 1;
        }

        //
        // If this is the last unit of a word, we
        // should reject any digram that cannot end a
        // syllable.
        //
        if( marked( RULE_NOT_END,
                    $first_unit,
                    $second_unit ) &&
            ($length_left == 0) ) {
            return 1;
        }

        //
        // Reject the unit if the digram it forms wants
        // to break the syllable, but the resulting
        // digram that would end the syllable is not
        // allowed to end a syllable.
        //
        if( marked( RULE_BREAK,
                    $first_unit,
                    $second_unit ) &&
            ( $digram_rules[$units_in_syllable[$current_unit_num-2]]
              [$first_unit] & RULE_NOT_END ) ) {
            return 1;
        }


        //
        // Reject the unit if the digram it forms
        // expects a vowel preceding it and there
        // is none.
        //
        if( marked( RULE_PREFIX,
                    $first_unit,
                    $second_unit ) &&
            !( $gram_rules[ $units_in_syllable[$current_unit_num-2] ] &
               RULE_VOWEL ) ) {
            return 1;
        }
    }

    return 0;
}


function get_syllable($pwlen) {
    global $digram_rules, $gram_rules, $grams, $saved_pair;

    //
    // This is needed if the saved_pair is tried and the syllable then
    // discarded because of the retry limit. Since the saved_pair is OK and
    // fits in nicely with the preceding syllable, we will always use it.
    //
    $hold_saved_pair = $saved_pair;

    $max_retries = ( 4 * $pwlen ) + sizeof( $grams );

    $max_loops = 100;
    $num_loops = 0;

    //
    // Loop until valid syllable is found.
    //
    do {
        //
        // Try for a new syllable.  Initialize all pertinent
        // syllable variables.
        //

        $syllable = "";               // string, returned
        $units_in_syllable = array(); // array of units, returned

        // grams:
        $unit = '';
        $current_unit = 0;
        $last_unit = '';

        // numbers:
        $vowel_count = 0;
        $tries = 0;
        $length_left = $pwlen;

        // flags:
        $rule_broken = 0;
        $want_vowel = 0;
        $want_another_unit = 1;

        $saved_pair = $hold_saved_pair;

        //
        // This loop finds all the units for the syllable.
        //
        do {
            $want_vowel = 0;

            //
            // This loop continues until a valid unit is found for the
            // current position within the syllable.
            //
            do {
                $rule_broken = 0;
                //
                // If there are saved units from the previous
                // syllable, use them up first.
                //

                //
                // If there were two saved units, the first is
                // guaranteed (by checks performed in the previous
                // syllable) to be valid.  We ignore the checks and
                // place it in this syllable manually.
                //
                if( sizeof( $saved_pair ) == 2 ) {
                    $syllable = array_pop( $saved_pair );
                    $units_in_syllable[0] = $syllable;
                    if( $gram_rules[$syllable] & RULE_VOWEL ) {
                        $vowel_count++;
                    }
                    $current_unit++;
                    $length_left -= strlen( $syllable );
                }

                if( sizeof( $saved_pair ) > 0 ) {
                    //
                    // The unit becomes the last unit checked in the
                    // previous syllable.
                    //
                    $unit = array_pop( $saved_pair );

                    //
                    // The saved units have been used.  Do not try to
                    // reuse them in this syllable (unless this
                    // particular syllable is rejected at which point
                    // we start to rebuild it with these same saved
                    // units).
                    //
                } else {
                    //
                    // If we don't have to consider the saved units,
                    // we generate a random one.
                    //
                    if( $want_vowel ) {
                        $unit = _random_unit( RULE_VOWEL );
                    } else {
                        $unit = _random_unit( NO_SPECIAL_RULE );
                    }
                }

                $length_left -= strlen( $unit );

                $rule_broken = 0;
                //
                // Prevent having a word longer than expected.
                //
                if( $length_left < 0 ) {
                    $rule_broken = 1;
                }

                //
                // First unit of syllable.  This is special because
                // the digram tests require 2 units and we don't have
                // that yet.  Nevertheless, we can perform some
                // checks.
                //
                if( $current_unit == 0 ) {
                    //
                    // If this shouldn't begin a syllable, don't use it.
                    //
                    if( $gram_rules[$unit] & RULE_NOT_BEGIN_SYLLABLE ) {
                        $rule_broken = 1;
                    } else if( $length_left == 0 ) {
                        //
                        // If this is the last unit of a word, we have
                        // a one unit syllable.  Since each syllable
                        // must have a vowel, we make sure the unit is
                        // a vowel.  Otherwise, we discard it.
                        //
                        if( $gram_rules[$unit] & RULE_VOWEL ) {
                            $want_another_unit = 0;
                        } else {
                            $rule_broken = 1;
                        }
                    }
                } else {

                    //
                    // We are not at the start of a syllable.
                    // Save the previous unit for later tests.
                    //
                    $last_unit = $units_in_syllable[$current_unit-1];

                    //
                    // There are some digram tests that are
                    // universally true.  We test them out.
                    //

                    if( digram_is_invalid(
                            $last_unit, $unit,
                            $current_unit, $length_left,
                            $units_in_syllable, $vowel_count ) ) {
                        $rule_broken = 1;
                    }

                    //
                    // The following checks occur when the current
                    // unit is a vowel and we are not looking at a
                    // word ending with an e.
                    //
                    if( !$rule_broken &&
                        ($gram_rules[$unit] & RULE_VOWEL) &&
                        ( ($length_left > 0)
                          || !($gram_rules[$last_unit] & RULE_NO_FINAL_SPLIT ) ) ) {
                        //
                        // Don't allow 3 consecutive vowels in a
                        // syllable.  Although some words formed
                        // like this are OK, like "beau", most are
                        // not.
                        //
                        if( ($vowel_count > 1) &&
                            ($gram_rules[$last_unit] & RULE_VOWEL) ) {
                            $rule_broken = 1;
                        }
                        //
                        // Check for the case of
                        // vowels-consonants-vowel, which is only
                        // legal if the last vowel is an e and we
                        // are the end of the word (which is not
                        // happening here due to a previous
                        // check).
                        //
                        else if( ($vowel_count != 0) && !($gram_rules[$last_unit] & RULE_VOWEL) ) {
                            //
                            // Try to save the vowel for the next
                            // syllable, but if the syllable left here
                            // is not proper (i.e., the resulting last
                            // digram cannot legally end it), just
                            // discard it and try for another.
                            //
                            if( $digram_rules[ $units_in_syllable[ $current_unit - 2] ][$last_unit] & RULE_NOT_END ) {
                                $rule_broken = 1;
                            } else {
                                $saved_pair = array( $unit );
                                $want_another_unit = 0;
                            }
                        }
                    }


                    //
                    // The unit picked and the digram formed are legal.
                    // We now determine if we can end the syllable.  It may,
                    // in some cases, mean the last unit(s) may be deferred to
                    // the next syllable.  We also check here to see if the
                    // digram formed expects a vowel to follow.
                    //
                    if( !$rule_broken and $want_another_unit ) {
                        if( ($vowel_count != 0) &&
                            ($gram_rules[$unit] & RULE_NO_FINAL_SPLIT ) &&
                            ($length_left == 0) &&
                            !($gram_rules[$last_unit] & RULE_VOWEL) ) {

                            //
                            // This word ends in a silent e.
                            //

                            $want_another_unit = 0;
                        } else if( marked( RULE_END,
                                           $last_unit,
                                           $unit )
                            || ($length_left == 0) ) {

                            //
                            // This syllable ends either because the
                            // digram is a RULE_END pair or we would
                            // otherwise exceed the length of the
                            // word.
                            //

                            $want_another_unit = 0;
                        } else if( $vowel_count != 0 and $length_left > 0 ) {
                            //
                            // Since we have a vowel in the syllable
                            // already, if the digram calls for the end of the
                            // syllable, we can legally split it off. We also
                            // make sure that we are not at the end of the
                            // dangerous because that syllable may not have
                            // vowels, or it may not be a legal syllable end,
                            // and the retrying mechanism will loop infinitely
                            // with the same digram.
                            //

                            //
                            // If we must begin a syllable, we do so if
                            // the only vowel in THIS syllable is not part
                            // of the digram we are pushing to the next
                            // syllable.
                            //
                            if( marked( RULE_BEGIN,
                                        $last_unit,
                                        $unit ) &&
                                ($current_unit > 1) &&
                                !( ($vowel_count == 1) &&
                                   ($gram_rules[$last_unit] & RULE_VOWEL) ) ) {
                                $saved_pair = array( $unit, $last_unit );
                                $want_another_unit = 0;
                            } else if(
                                marked( RULE_BREAK,
                                        $last_unit,
                                        $unit )) {
                                $saved_pair = array( $unit );
                                $want_another_unit = 0;
                            }
                        } else if(
                            marked( RULE_SUFFIX,
                                    $last_unit,
                                    $unit ) ) {
                            $want_vowel = 1;
                        }
                    }
                }

                $tries++;

                //
                // If this unit was illegal, redetermine the amount of
                // letters left to go in the word.
                //
                if( $rule_broken ) {
                    $length_left += strlen( $unit );
                }
            } while( $rule_broken and $tries <= $max_retries );


            //
            // The unit fit OK.
            //
            if( $tries <= $max_retries ) {
                //
                // If the unit were a vowel, count it in.  However, if
                // the unit were a y and appear at the start of the
                // syllable, treat it like a constant (so that words
                // like "year" can appear and not conflict with the 3
                // consecutive vowel rule).
                //
                if(
                    ($gram_rules[$unit] & RULE_VOWEL)
                &&
                    ( ($current_unit > 0) || !($gram_rules[$unit] & RULE_ALTERNATE_VOWEL) )
                ) {
                    $vowel_count++;
                }

                //
                // If a unit or units were to be saved, we must adjust
                // the syllable formed.  Otherwise, we append the
                // current unit to the syllable.
                //
                if( sizeof( $saved_pair ) == 2 ) {
                    $syllable = substr( $syllable, 0,
                                        strlen( $syllable ) -
                                        strlen( $last_unit ) );
                    $length_left += strlen( $last_unit );
                    $current_unit -= 2;
                }
                else if( sizeof( $saved_pair ) == 1 ) {
                    $current_unit--;
                }
                else {
                    $units_in_syllable[ $current_unit ] = $unit;
                    $syllable .= $unit;
                }
            } else {
                //
                // Whoops!  Too many tries.  We set rule_broken so we
                // can loop in the outer loop and try another
                // syllable.
                //

                $rule_broken = 1;
            }

            $current_unit++;
        } while( $tries <= $max_retries and $want_another_unit );

        $num_loops++;

    } while( ( $rule_broken or _illegal_placement( $units_in_syllable ) ) );

    return array( $syllable, $units_in_syllable );
}



// goes through an individual syllable and checks for illegal
// combinations of letters that go beyond looking at digrams.
//
// We look at things like 3 consecutive vowels or consonants, or
// syllables with consonants between vowels (unless one of them is the
// final silent e).

function _illegal_placement($units) {
    global $gram_rules;

    $vowel_count = 0;
    $failure = 0;

    for( $unit_count = 0; $unit_count < sizeof( $units ); $unit_count++ ) {
        if( $failure ) {
            break;
        }

        if( $unit_count >= 1 ) {
            //
            // Don't allow vowels to be split with consonants in a
            // single syllable.  If we find such a combination (except
            // for the silent e) we have to discard the syllable.
            //
            if(
                (
                    !( $gram_rules[$units[$unit_count-1]] & RULE_VOWEL)
                 &&
                     ( $gram_rules[$units[$unit_count  ]] & RULE_VOWEL)
                 &&
                    !(($gram_rules[$units[$unit_count  ]] & RULE_NO_FINAL_SPLIT ) && ($unit_count == sizeof( $units )))
                 &&
                     $vowel_count
                 )
             ||

                 //
                 // Perform these checks when we have at least 3 units.
                 //
                 (
                     ($unit_count >= 2)
                 &&
                     (
                         //
                         // Disallow 3 consecutive consonants.
                         //
                         (
                             !($gram_rules[$units[$unit_count-2]] & RULE_VOWEL)
                         &&
                             !($gram_rules[$units[$unit_count-1]] & RULE_VOWEL)
                         &&
                             !($gram_rules[$units[$unit_count  ]] & RULE_VOWEL)
                         )
                     ||

                         //
                         // Disallow 3 consecutive vowels, where the
                         // first is not a y.
                         //
                         (
                             ( $gram_rules[$units[$unit_count-2]] & RULE_VOWEL)
                         &&
                            !(($gram_rules[$units[0            ]] & RULE_ALTERNATE_VOWEL) && ($unit_count == 2))
                         &&
                             ( $gram_rules[$units[$unit_count-1]] & RULE_VOWEL)
                         &&
                             ( $gram_rules[$units[$unit_count  ]] & RULE_VOWEL)
                         )
                     )
                 )
             ) {
                    $failure = 1;
             }
        }

        //
        // Count the vowels in the syllable.  As mentioned somewhere
        // above, exclude the initial y of a syllable.  Instead, treat
        // it as a consonant.
        //
        if(
            ($gram_rules[$units[$unit_count]] & RULE_VOWEL)
        &&
            !(
                ($gram_rules[$units[0]] & RULE_ALTERNATE_VOWEL)
            &&
                ($unit_count == 0)
            &&
                (sizeof( $units ) > 1)
            )
        ) {
            $vowel_count++;
        }
    }

    return $failure;
}
function dblink($user="",$pass="",$host="",$database="")
{        
    $link = mysql_connect($host,$user,$pass) or die('Not connected : '.mysql_errno()." : " . mysql_error());
         
    $db_selected = mysql_select_db($database, $link) or die ('Can\'t use $database : ' . mysql_error
());
	return $link;
}        
function mquery($QUERY) {
    if(trim($QUERY)=="") return -2;      
    $result=@mysql_query($QUERY) ;
    $ER=mysql_error();            
        if(mysql_errno()!=0) {
            echo tohtml($QUERY).": $ER";
            return -2;
        }
    return $result;
}
function adv_decode($badvar="") {
    while(strlen(html_entity_decode(trim($badvar)))!=strlen($badvar))
        $badvar=trim(html_entity_decode($badvar));                   
return $badvar;
}
function tohtml($badvar="") {
return htmlentities(adv_decode($badvar));
}

function tosql($badvar="") {
return mysql_escape_string(adv_decode($badvar));
}

function getmsg($mid) {
    
$result=mquery("SELECT mailbox.id as mid, mailbox.delivered,mailbox.sender, mailbox.receipient, mailbox.subject, mailbox.headers, mailbox.content, mailbox.date, generated.ip, generated.port, generated.agent, generated.referer, generated.gentime FROM mailbox LEFT JOIN generated ON generated.genmail = mailbox.receipient WHERE mailbox.id='$mid'");

$result2=mquery("SELECT * FROM attachments WHERE mboxid='$mid'");
$rs=mysql_fetch_object($result);
$attachments=array();
$at_count=0;
while($rs2=mysql_fetch_object($result2)) {
	$attachments=array($rs2,$attachments);
    $at_count++;
}
	//$attachments=array($rs2->id,$rs2->filename,$attachments);
return array($rs, $attachments, $at_count);

}
function delete_mid($mid) {
        $result=mquery("DELETE FROM mailbox WHERE id='$mid'");
        $result=mquery("DELETE FROM attachments WHERE mboxid='$mid'");
}
function delete_gid($gid) {
        mquery("DELETE FROM generated WHERE id='$gid'");
}
function makenice($ip_addr,$id)
{
       //if(preg_match("/$num\.$num\.$num\.$num/",$ip_addr,$matches))
//if(preg_match_all("/([1-9]*)\.([0-9]*)\.([0-9]*)\.([0-9]*)/",$ip_addr,$matches))
       $matches=preg_replace("/([1-9]*)\.([0-9]*)\.([0-9]*)\.([0-9]*)/","<a href='fillabuse.php?ip=\\0&amp;id=$id'>Report abuse on \\0</a>",$ip_addr);
       //#$matches=preg_replace("/(\w+)@(\w+)\.(\w+)/","<b>\\0</b>",$matches);
       //$matches=preg_replace("/[\w-]+(?:\.[\w-]+)*@(?:[\w-]+\.)+[a-zA-Z]{2,7}/","<b>\\0</b>",$matches);
	return $matches;
}
function geninsert($link, $REMOTE_ADDR,$REMOTE_PORT,$USER_AGENT, 
			$REFERER,$domain) {

	$generated = getvalid($link);
	$word = $generated[0];
	$hyphenated_word = $generated[1];
	$ip = ip2long(@$REMOTE_ADDR);
	$port = tosql(@$REMOTE_PORT);
	$agent = tosql(@$USER_AGENT);
	$referer = tosql(@$REFERER);
	insert($ip,$port,$agent,$referer,"$hyphenated_word@$domain");

	return $generated;
}
?>
