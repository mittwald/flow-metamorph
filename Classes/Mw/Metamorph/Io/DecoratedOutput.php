<?php
namespace Mw\Metamorph\Io;


use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;


class DecoratedOutput implements DecoratedOutputInterface
{


    /** @var OutputInterface */
    private $output;


    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }


    public function writeFormatted($text, $leftPadding=0)
    {
        $lines = explode(PHP_EOL, $text);
        foreach ($lines as $line)
        {
            $formattedText = str_repeat(' ', $leftPadding) . wordwrap($line, 80 - $leftPadding, PHP_EOL . str_repeat(' ', $leftPadding), TRUE);
            $this->writeln($formattedText);
        }
    }


    /**
     * Writes a message to the output.
     *
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool $newline Whether to add a newline
     * @param int $type The type of output (one of the OUTPUT constants)
     *
     * @throws \InvalidArgumentException When unknown output type is given
     *
     * @api
     */
    public function write($messages, $newline = FALSE, $type = self::OUTPUT_NORMAL)
    {
        $this->output->write($messages, $newline, $type);
    }


    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string|array $messages The message as an array of lines of a single string
     * @param int $type The type of output (one of the OUTPUT constants)
     *
     * @throws \InvalidArgumentException When unknown output type is given
     *
     * @api
     */
    public function writeln($messages, $type = self::OUTPUT_NORMAL)
    {
        $this->output->writeln($messages, $type);
    }


    /**
     * Sets the verbosity of the output.
     *
     * @param int $level The level of verbosity (one of the VERBOSITY constants)
     *
     * @api
     */
    public function setVerbosity($level)
    {
        $this->output->setVerbosity($level);
    }


    /**
     * Gets the current verbosity of the output.
     *
     * @return int     The current level of verbosity (one of the VERBOSITY constants)
     *
     * @api
     */
    public function getVerbosity()
    {
        return $this->output->getVerbosity();
    }


    /**
     * Sets the decorated flag.
     *
     * @param bool $decorated Whether to decorate the messages
     *
     * @api
     */
    public function setDecorated($decorated)
    {
        $this->output->setDecorated($decorated);
    }


    /**
     * Gets the decorated flag.
     *
     * @return bool    true if the output will decorate messages, false otherwise
     *
     * @api
     */
    public function isDecorated()
    {
        return $this->output->isDecorated();
    }


    /**
     * Sets output formatter.
     *
     * @param OutputFormatterInterface $formatter
     *
     * @api
     */
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        $this->output->setFormatter($formatter);
    }


    /**
     * Returns current output formatter instance.
     *
     * @return  OutputFormatterInterface
     *
     * @api
     */
    public function getFormatter()
    {
        return $this->output->getFormatter();
    }


} 