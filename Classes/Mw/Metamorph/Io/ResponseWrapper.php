<?php
namespace Mw\Metamorph\Io;


use TYPO3\Flow\Mvc\ResponseInterface;


class ResponseWrapper implements OutputInterface
{


    const MAXIMUM_LINE_LENGTH = 79;


    /**
     * @var \TYPO3\Flow\Mvc\ResponseInterface
     */
    private $response;



    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }



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
    public function outputFormatted($text = '', array $arguments = array(), $leftPadding = 0)
    {
        $lines = explode(PHP_EOL, $text);
        foreach ($lines as $line)
        {
            $formattedText = str_repeat(' ', $leftPadding) . wordwrap($line, self::MAXIMUM_LINE_LENGTH - $leftPadding, PHP_EOL . str_repeat(' ', $leftPadding), TRUE);
            $this->outputLine($formattedText, $arguments);
        }
    }



    /**
     * Outputs specified text to the console window and appends a line break
     *
     * @param string $text      Text to output
     * @param array  $arguments Optional arguments to use for sprintf
     * @return void
     * @see output()
     * @see outputLines()
     */
    public function outputLine($text = '', array $arguments = array())
    {
        $this->output($text . PHP_EOL, $arguments);
    }



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
    public function output($text, array $arguments = array())
    {
        if ($arguments !== array())
        {
            $text = vsprintf($text, $arguments);
        }
        $this->response->appendContent($text);
    }
} 