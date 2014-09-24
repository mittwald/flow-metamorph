<?php
namespace Mw\Metamorph\Io;



use Symfony\Component\Console\Output\OutputInterface as SymfonyOutputInterface;

class SymfonyConsoleOutput implements OutputInterface
{


    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;



    public function __construct(SymfonyOutputInterface $output)
    {
        $this->output = $output;
    }



    /**
     * Outputs specified text to the console window
     * You can specify arguments that will be passed to the text via sprintf
     *
     * @see http://www.php.net/sprintf
     *
     * @param string $text Text to output
     * @param array $arguments Optional arguments to use for sprintf
     * @return void
     */
    public function output($text, array $arguments = array())
    {
        $this->output->write(sprintf($text, $arguments));
    }



    /**
     * Outputs specified text to the console window and appends a line break
     *
     * @param string $text Text to output
     * @param array $arguments Optional arguments to use for sprintf
     * @return void
     * @see output()
     * @see outputLines()
     */
    public function outputLine($text = '', array $arguments = array())
    {
        $this->output->writeln(sprintf($text, $arguments));
    }



    /**
     * Formats the given text to fit into MAXIMUM_LINE_LENGTH and outputs it to the
     * console window
     *
     * @param string $text Text to output
     * @param array $arguments Optional arguments to use for sprintf
     * @param integer $leftPadding The number of spaces to use for indentation
     * @return void
     * @see outputLine()
     */
    public function outputFormatted($text = '', array $arguments = array(), $leftPadding = 0)
    {
        $lines = explode(PHP_EOL, $text);
        foreach ($lines as $line)
        {
            $formattedText = str_repeat(' ', $leftPadding) . wordwrap($line, 80 - $leftPadding, PHP_EOL . str_repeat(' ', $leftPadding), TRUE);
            $this->outputLine($formattedText, $arguments);
        }
    }
}