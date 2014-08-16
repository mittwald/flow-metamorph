<?php
namespace Mw\Metamorph\Io;


interface OutputInterface
{


    /**
     * Outputs specified text to the console window
     * You can specify arguments that will be passed to the text via sprintf
     *
     * @see http://www.php.net/sprintf
     *
     * @param string $text      Text to output
     * @param array  $arguments Optional arguments to use for sprintf
     * @return void
     */
    public function output($text, array $arguments = array());



    /**
     * Outputs specified text to the console window and appends a line break
     *
     * @param string $text      Text to output
     * @param array  $arguments Optional arguments to use for sprintf
     * @return void
     * @see output()
     * @see outputLines()
     */
    public function outputLine($text = '', array $arguments = array());



    /**
     * Formats the given text to fit into MAXIMUM_LINE_LENGTH and outputs it to the
     * console window
     *
     * @param string  $text        Text to output
     * @param array   $arguments   Optional arguments to use for sprintf
     * @param integer $leftPadding The number of spaces to use for indentation
     * @return void
     * @see outputLine()
     */
    public function outputFormatted($text = '', array $arguments = array(), $leftPadding = 0);

}