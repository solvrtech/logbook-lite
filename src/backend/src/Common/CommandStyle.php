<?php

namespace App\Common;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\OutputWrapper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\TrimmedBufferOutput;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Terminal;

class CommandStyle extends OutputStyle
{
    public const MAX_LINE_LENGTH = 120;

    private InputInterface $input;
    private OutputInterface $output;
    private SymfonyQuestionHelper $questionHelper;
    private ProgressBar $progressBar;
    private int $lineLength;
    private TrimmedBufferOutput $bufferedOutput;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->bufferedOutput = new TrimmedBufferOutput(
            \DIRECTORY_SEPARATOR === '\\' ? 4 : 2,
            $output->getVerbosity(),
            false,
            clone $output->getFormatter()
        );
        // Windows cmd wraps lines as soon as the terminal width is reached, whether there are following chars or not.
        $width = (new Terminal())->getWidth() ?: self::MAX_LINE_LENGTH;
        $this->lineLength = min($width - (int)(\DIRECTORY_SEPARATOR === '\\'), self::MAX_LINE_LENGTH);

        parent::__construct($this->output = $output);
    }

    /**
     * @param string $message
     */
    public function title(string $message): void
    {
        $this->autoPrependBlock();
        $this->writeln([
            sprintf('<comment>%s</>', OutputFormatter::escapeTrailingBackslash($message)),
            sprintf(
                '<comment>%s</>',
                str_repeat('=', Helper::width(Helper::removeDecoration($this->getFormatter(), $message)))
            ),
        ]);
        $this->newLine();
    }

    private function autoPrependBlock(): void
    {
        $chars = substr(str_replace(\PHP_EOL, "\n", $this->bufferedOutput->fetch()), -2);

        if (!isset($chars[0])) {
            $this->newLine(); // empty history, so we should start with a new line.

            return;
        }
        // Prepend new line for each non LF chars (This means no blank line was output before)
        $this->newLine(2 - substr_count($chars, "\n"));
    }

    /**
     * @param int $count
     */
    public function newLine(int $count = 1): void
    {
        parent::newLine($count);
        $this->bufferedOutput->write(str_repeat("\n", $count));
    }

    /**
     * @param string|iterable $messages
     * @param bool $newline
     * @param int $type
     */
    public function write(string|iterable $messages, bool $newline = false, int $type = self::OUTPUT_NORMAL): void
    {
        if (!is_iterable($messages)) {
            $messages = [$messages];
        }

        foreach ($messages as $message) {
            parent::write($message, $newline, $type);
            $this->writeBuffer($message, $newline, $type);
        }
    }

    /**
     * @param string $message
     * @param bool $newLine
     * @param int $type
     */
    private function writeBuffer(string $message, bool $newLine, int $type): void
    {
        // We need to know if the last chars are PHP_EOL
        $this->bufferedOutput->write($message, $newLine, $type);
    }

    /**
     * @param string|iterable $messages
     * @param int $type
     */
    public function writeln(string|iterable $messages, int $type = self::OUTPUT_NORMAL): void
    {
        if (!is_iterable($messages)) {
            $messages = [$messages];
        }

        foreach ($messages as $message) {
            parent::writeln($message, $type);
            $this->writeBuffer($message, true, $type);
        }
    }

    /**
     * @param array $elements
     */
    public function listing(array $elements): void
    {
        $this->autoPrependText();
        $elements = array_map(fn($element) => sprintf(' * %s', $element), $elements);

        $this->writeln($elements);
        $this->newLine();
    }

    private function autoPrependText(): void
    {
        $fetched = $this->bufferedOutput->fetch();
        // Prepend new line if last char isn't EOL:
        if ($fetched && !str_ends_with($fetched, "\n")) {
            $this->newLine();
        }
    }

    /**
     * @param string|array $message
     */
    public function text(string|array $message): void
    {
        $this->autoPrependText();

        $messages = \is_array($message) ? array_values($message) : [$message];
        foreach ($messages as $message) {
            $this->writeln(sprintf(' %s', $message));
        }
    }

    /**
     * Formats a command comment.
     *
     * @param string|array $message
     */
    public function comment(string|array $message): void
    {
        $this->block($message, null, null, '<fg=default;bg=default> // </>', false, false);
    }

    /**
     * Formats a message as a block of text.
     *
     * @param string|array $messages
     * @param string|null $type
     * @param string|null $style
     * @param string $prefix
     * @param bool $padding
     * @param bool $escape
     */
    public function block(
        string|array $messages,
        string $type = null,
        string $style = null,
        string $prefix = ' ',
        bool $padding = false,
        bool $escape = true
    ): void {
        $messages = \is_array($messages) ? array_values($messages) : [$messages];

        $this->autoPrependBlock();
        $this->writeln($this->createBlock($messages, $type, $style, $prefix, $padding, $escape));
        $this->newLine();
    }

    private function createBlock(
        iterable $messages,
        string $type = null,
        string $style = null,
        string $prefix = ' ',
        bool $padding = false,
        bool $escape = false
    ): array {
        $indentLength = 0;
        $prefixLength = Helper::width(Helper::removeDecoration($this->getFormatter(), $prefix));
        $lines = [];

        if (null !== $type) {
            $type = sprintf('[%s] ', $type);
            $indentLength = Helper::width($type);
            $lineIndentation = str_repeat(' ', $indentLength);
        }

        // wrap and add newlines for each element
        $outputWrapper = new OutputWrapper();
        foreach ($messages as $key => $message) {
            if ($escape) {
                $message = OutputFormatter::escape($message);
            }

            $lines = array_merge(
                $lines,
                explode(
                    \PHP_EOL,
                    $outputWrapper->wrap(
                        $message,
                        $this->lineLength - $prefixLength - $indentLength,
                        \PHP_EOL
                    )
                )
            );

            if (\count($messages) > 1 && $key < \count($messages) - 1) {
                $lines[] = '';
            }
        }

        $firstLineIndex = 0;
        if ($padding && $this->isDecorated()) {
            $firstLineIndex = 1;
            array_unshift($lines, '');
            $lines[] = '';
        }

        foreach ($lines as $i => &$line) {
            if (null !== $type) {
                $line = $firstLineIndex === $i ? $type.$line : $lineIndentation.$line;
            }

            $line = $prefix.$line;
            $line .= str_repeat(
                ' ',
                max($this->lineLength - Helper::width(Helper::removeDecoration($this->getFormatter(), $line)), 0)
            );

            if ($style) {
                $line = sprintf('<%s>%s</>', $style, $line);
            }
        }

        return $lines;
    }

    /**
     * @param string|array $message
     */
    public function success(string|array $message): void
    {
        $this->block($message, 'OK', 'fg=green', ' ', true);
    }

    /**
     * @param string|array $message
     */
    public function error(string|array $message): void
    {
        $this->block($message, 'ERROR', 'fg=red', ' ', true);
    }

    /**
     * @param string|array $message
     */
    public function warning(string|array $message): void
    {
        $this->block($message, 'WARNING', 'fg=yellow', ' ', true);
    }

    /**
     * @param string|array $message
     */
    public function note(string|array $message): void
    {
        $this->block($message, 'NOTE', 'fg=yellow', ' ! ');
    }

    /**
     * Formats an info message.
     *
     * @param string|array $message
     */
    public function info(string|array $message): void
    {
        $this->block($message, 'INFO', 'fg=green', ' ', true);
    }

    /**
     * @param string|array $message
     */
    public function caution(string|array $message): void
    {
        $this->block($message, 'CAUTION', 'fg=red', ' ! ', true);
    }

    /**
     * @param array $headers
     * @param array $rows
     */
    public function table(array $headers, array $rows): void
    {
        $this->createTable()
            ->setHeaders($headers)
            ->setRows($rows)
            ->render();

        $this->newLine();
    }

    public function createTable(): Table
    {
        $output = $this->output instanceof ConsoleOutputInterface ? $this->output->section() : $this->output;
        $style = clone Table::getStyleDefinition('symfony-style-guide');
        $style->setCellHeaderFormat('<info>%s</info>');

        return (new Table($output))->setStyle($style);
    }

    /**
     * @param string $message
     */
    public function section(string $message): void
    {
        $this->autoPrependBlock();
        $this->writeln([
            sprintf('<comment>%s</>', OutputFormatter::escapeTrailingBackslash($message)),
            sprintf(
                '<comment>%s</>',
                str_repeat('-', Helper::width(Helper::removeDecoration($this->getFormatter(), $message)))
            ),
        ]);
        $this->newLine();
    }

    /**
     * Formats a list of key/value horizontally.
     *
     * Each row can be one of:
     * * 'A title'
     * * ['key' => 'value']
     * * new TableSeparator()
     *
     * @param string|array|TableSeparator ...$list
     */
    public function definitionList(string|array|TableSeparator ...$list): void
    {
        $headers = [];
        $row = [];
        foreach ($list as $value) {
            if ($value instanceof TableSeparator) {
                $headers[] = $value;
                $row[] = $value;
                continue;
            }
            if (\is_string($value)) {
                $headers[] = new TableCell($value, ['colspan' => 2]);
                $row[] = null;
                continue;
            }
            if (!\is_array($value)) {
                throw new InvalidArgumentException(
                    'Value should be an array, string, or an instance of TableSeparator.'
                );
            }
            $headers[] = key($value);
            $row[] = current($value);
        }

        $this->horizontalTable($headers, [$row]);
    }

    /**
     * Formats a horizontal table.
     *
     * @param array $headers
     * @param array $rows
     */
    public function horizontalTable(array $headers, array $rows): void
    {
        $this->createTable()
            ->setHorizontal(true)
            ->setHeaders($headers)
            ->setRows($rows)
            ->render();

        $this->newLine();
    }

    /**
     * @param string $question
     * @param string|null $default
     * @param callable|null $validator
     *
     * @return mixed
     */
    public function ask(string $question, string $default = null, callable $validator = null): mixed
    {
        $question = new Question($question, $default);
        $question->setValidator($validator);

        return $this->askQuestion($question);
    }

    /**
     * @param Question $question
     *
     * @return mixed
     */
    public function askQuestion(Question $question): mixed
    {
        if ($this->input->isInteractive()) {
            $this->autoPrependBlock();
        }

        $this->questionHelper ??= new SymfonyQuestionHelper();

        $answer = $this->questionHelper->ask($this->input, $this, $question);

        if ($this->input->isInteractive()) {
            if ($this->output instanceof ConsoleSectionOutput) {
                // add the new line of the `return` to submit the input to ConsoleSectionOutput, because ConsoleSectionOutput is holding all it's lines.
                // this is relevant when a `ConsoleSectionOutput::clear` is called.
                $this->output->addNewLineOfInputSubmit();
            }
            $this->newLine();
            $this->bufferedOutput->write("\n");
        }

        return $answer;
    }

    /**
     * @param string $question
     * @param callable|null $validator
     *
     * @return mixed
     */
    public function askHidden(string $question, callable $validator = null): mixed
    {
        $question = new Question($question);

        $question->setHidden(true);
        $question->setValidator($validator);

        return $this->askQuestion($question);
    }

    /**
     * @param string $question
     * @param bool $default
     *
     * @return bool
     */
    public function confirm(string $question, bool $default = true): bool
    {
        return $this->askQuestion(new ConfirmationQuestion($question, $default));
    }

    /**
     * @param string $question
     * @param array $choices
     * @param mixed|null $default
     * @param bool $multiSelect
     *
     * @return mixed
     */
    public function choice(string $question, array $choices, mixed $default = null, bool $multiSelect = false): mixed
    {
        if (null !== $default) {
            $values = array_flip($choices);
            $default = $values[$default] ?? $default;
        }

        $questionChoice = new ChoiceQuestion($question, $choices, $default);
        $questionChoice->setMultiselect($multiSelect);

        return $this->askQuestion($questionChoice);
    }

    /**
     * @param int $max
     */
    public function progressStart(int $max = 0): void
    {
        $this->progressBar = $this->createProgressBar($max);
        $this->progressBar->start();
    }

    /**
     * @param int $max
     *
     * @return ProgressBar
     */
    public function createProgressBar(int $max = 0): ProgressBar
    {
        $progressBar = parent::createProgressBar($max);

        if ('\\' !== \DIRECTORY_SEPARATOR || 'Hyper' === getenv('TERM_PROGRAM')) {
            $progressBar->setEmptyBarCharacter('░'); // light shade character \u2591
            $progressBar->setProgressCharacter('');
            $progressBar->setBarCharacter('▓'); // dark shade character \u2593
        }

        return $progressBar;
    }

    /**
     * @param int $step
     */
    public function progressAdvance(int $step = 1): void
    {
        $this->getProgressBar()->advance($step);
    }

    /**
     * @return ProgressBar
     */
    private function getProgressBar(): ProgressBar
    {
        return $this->progressBar
            ?? throw new RuntimeException('The ProgressBar is not started.');
    }

    public function progressFinish(): void
    {
        $this->getProgressBar()->finish();
        $this->newLine(2);
        unset($this->progressBar);
    }

    /**
     * @param iterable $iterable
     * @param int|null $max
     *
     * @return iterable
     *
     * @see ProgressBar::iterate()
     */
    public function progressIterate(iterable $iterable, int $max = null): iterable
    {
        yield from $this->createProgressBar()->iterate($iterable, $max);

        $this->newLine(2);
    }

    /**
     * Returns a new instance which makes use of stderr if available.
     */
    public function getErrorStyle(): self
    {
        return new self($this->input, $this->getErrorOutput());
    }
}