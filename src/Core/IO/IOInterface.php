<?php

/*
 * This file is part of the Yosymfony\Spress.
 *
 * (c) YoSymfony <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yosymfony\Spress\Core\IO;

/**
 * IO interface.
 *
 * Based on https://github.com/composer/composer/blob/master/src/Composer/IO/IOInterface.php
 * from François Pluchino <francois.pluchino@opendisplay.com>
 *
 * @api
 *
 * @author Victor Puertas
 */
interface IOInterface
{
    /** @var int */
    const VERBOSITY_QUIET = 1;

    /** @var int */
    const VERBOSITY_NORMAL = 2;

    /** @var int */
    const VERBOSITY_VERBOSE = 4;

    /** @var int */
    const VERBOSITY_VERY_VERBOSE = 8;

    /** @var int */
    const VERBOSITY_DEBUG = 16;

    /**
     * Is this input means interactive?
     *
     * @return bool
     */
    public function isInteractive();

    /**
     * Is this output verbose?
     *
     * @return bool
     */
    public function isVerbose();

    /**
     * Is the output very verbose?
     *
     * @return bool
     */
    public function isVeryVerbose();

    /**
     * Is the output in debug verbosity?
     *
     * @return bool
     */
    public function isDebug();

    /**
     * Is this output decorated?
     *
     * @return bool
     */
    public function isDecorated();

    /**
     * Writes a message to the output.
     *
     * @param string|array $messages  The message as an array of lines or a single string
     * @param bool         $newline   Whether to add a newline or not
     * @param int          $verbosity Verbosity level from VERBOSITY_* constants
     */
    public function write($messages, $newline = true, $verbosity = self::VERBOSITY_NORMAL);

    /**
     * Overwrites a previous message to the output.
     *
     * @param string|array $messages  The message as an array of lines or a single string
     * @param bool         $newline   Whether to add a newline or not
     * @param int          $verbosity Verbosity level from the VERBOSITY_* constants
     */
    public function overwrite($messages, $newline = true, $verbosity = self::VERBOSITY_NORMAL);

    /**
     * Asks a question to the user.
     *
     * @param string|array $question The question to ask
     * @param string       $default  The default answer if none is given by the user
     *
     * @return string The user answer
     *
     * @throws RuntimeException If there is no data to read in the input stream
     */
    public function ask($question, $default = null);

    /**
     * Asks a confirmation to the user.
     *
     * The question will be asked until the user answers by nothing, yes, or no.
     *
     * @param string|array $question The question to ask
     * @param bool         $default  The default answer if the user enters nothing
     *
     * @return bool true if the user has confirmed, false otherwise
     */
    public function askConfirmation($question, $default = true);

    /**
     * Asks for a value and validates the response.
     *
     * The validator receives the data to validate. It must return the
     * validated data when the data is valid and throw an exception
     * otherwise.
     *
     * @param string|array $question  The question to ask
     * @param callable     $validator A PHP callback
     * @param bool|int     $attempts  Max number of times to ask before giving up (false by default, which means infinite)
     * @param string       $default   The default answer if none is given by the user
     *
     * @return string The user answer
     *
     * @throws \Exception When any of the validators return an error
     */
    public function askAndValidate($question, callable $validator, $attempts = false, $default = null);

    /**
     * Asks a question to the user and hide the answer.
     *
     * @param string $question The question to ask
     * @param bool   $fallback In case the response can not be hidden, whether to fallback on non-hidden question or not
     *
     * @return string The use answer
     */
    public function askAndHideAnswer($question, $fallback = true);

    /**
     * Asks for a value, hide and validates the response.
     *
     * The validator receives the data to validate. It must return the
     * validated data when the data is valid and throw an exception
     * otherwise.
     *
     * @param string|array $question  The question to ask
     * @param callable     $validator A PHP callback
     * @param bool|int     $attempts  Max number of times to ask before giving up (false by default, which means infinite)
     * @param bool         $fallback  In case the response can not be hidden, whether to fallback on non-hidden question or not
     *
     * @return string The user answer
     *
     * @throws \Exception When any of the validators return an error
     */
    public function askHiddenResponseAndValidate($question, callable $validator, $attempts = false, $fallback = true);

    /**
     * Asks the user to select a value.
     *
     * @param string          $question     The question to ask
     * @param array           $choices      List of choices to pick from
     * @param string|int|null $default      The default answer if the user enters nothing
     * @param bool|int        $attempts     Max number of times to ask before giving up (false by default, which means infinite)
     * @param string          $errorMessage Message which will be shown if invalid value from choice list would be picked
     * @param bool            $multiselect  Select more than one value separated by comma
     *
     * @return string The selected value or values (the key of the choices array)
     *
     * @throws Exception When any of the validators return an error
     */
    public function askChoice($question, array $choices, $default = null, $attempts = false, $errorMessage = 'Value "%s" is invalid', $multiselect = false);
}
