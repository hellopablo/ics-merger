<?php

namespace IcsMerger\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Merge extends Command
{
    /**
     * Holds the file pointer for the output/destination file
     * @var Resource
     */
    private $fileHandle;

    // --------------------------------------------------------------------------

    /**
     * Configures the command
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('merge')
            ->setDescription('Looks for .ics files and merges them into a single file.')
            ->addOption(
                'src',
                null,
                InputOption::VALUE_OPTIONAL,
                'Which directory to search for .ics files.',
                getcwd()
            )
            ->addOption(
                'dest',
                null,
                InputOption::VALUE_OPTIONAL,
                'The destination of the merged .ics file.',
                getcwd()
            )
            ->addOption(
                'file',
                null,
                InputOption::VALUE_OPTIONAL,
                'The filename of the merged .ics file.',
                'merged.ics'
            );
    }

    // --------------------------------------------------------------------------

    /**
     * Executes the command
     * @param  InputInterface  $input  The Input Interface
     * @param  OutputInterface $output The Output Interface
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('<info>---------------</info>');
        $output->writeln('ICS File Merger');
        $output->writeln('<info>---------------</info>');
        $output->writeln('');

        // --------------------------------------------------------------------------

        //  Get variables
        $srcDir = $input->getOption('src');
        $destDir = $input->getOption('dest');
        $fileName = $input->getOption('file');

        // --------------------------------------------------------------------------

        //  Test source directory
        if (!is_dir($destDir)) {

            $output->writeln('<error>Source directory does not exist</error>');
            $output->writeln($srcDir);
            $output->writeln('');
            return;
        }

        //  Test output destination
        if (!is_writable($destDir)) {

            $output->writeln('<error>Destination is not writeable</error>');
            $output->writeln($destDir);
            $output->writeln('');
            return;
        }

        // --------------------------------------------------------------------------

        //  Look for .ics files
        $output->writeln('<info>Searching for .ics in:</info> ' . $srcDir);
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($srcDir));

        $numFiles = 0;
        $it->rewind();
        while ($it->valid()) {

            if (!$it->isDot() && strtolower(substr($it->key(), -4)) === '.ics') {
                $numFiles++;
            }

            $it->next();
        }

        if ($numFiles === 0) {

            $output->writeln('<info>Did not find any .ics files</info>');
            $output->writeln('');
            return;
        }

        $output->writeln('<info>Found ' . $numFiles . ' .ics files</info>');

        // --------------------------------------------------------------------------

        //  Begin merging files
        $output->writeln('');
        $output->writeln('<info>Merging .ics files</info>');
        $output->writeln('');

        //  Create the merge file and write the initial lines to it
        if (!$this->makeFile($destDir, $fileName)) {

            $output->writeln('<error>Failed to create merge file</error>');
            $output->writeln('');
            return;
        }

        //  write file header
        $this->writeFileLine('BEGIN:VCALENDAR');
        $this->writeFileLine('VERSION:2.0');
        $this->writeFileLine('PRODID:XXX');
        $this->writeFileLine('CALSCALE:GREGORIAN');

        //  Set up the progress helper
        $progress = $this->getHelper('progress');
        $progress->start($output, $numFiles);

        $it->rewind();
        while ($it->valid()) {

            if (!$it->isDot() && strtolower(substr($it->key(), -4)) === '.ics') {

                //  Open the file and extract the event data
                $fileContents = file_get_contents($it->key());

                if (!empty($fileContents)) {

                    preg_match_all('/BEGIN\:VEVENT(.*?)END\:VEVENT/s', $fileContents, $matches);
                    if (!empty($matches[1])) {

                        foreach ($matches[1] as $event) {

                            $this->writeFileLine('BEGIN:VEVENT');

                            $lines = explode("\r\n", $event);
                            $lines = array_map('trim', $lines);
                            $lines = array_filter($lines);

                            foreach ($lines as $line) {

                                $this->writeFileLine($line);
                            }

                            $this->writeFileLine('END:VEVENT');
                        }
                    }
                }
                $progress->advance();
            }

            $it->next();
        }

        $progress->finish();

        //  Write file footer
        $this->writeFileLine('END:VCALENDAR');

        $output->writeln('');
        $output->writeln('<info>Completed merging .ics files</info>');
        $output->writeln('<info>Output available at:</info> ' . $destDir);
        $output->writeln('');
    }

    // --------------------------------------------------------------------------

    /**
     * Opens the destination file for writing
     * @param  string $destDir  The destination file's directory
     * @param  string $fileName The destination file's name
     * @return file pointer
     */
    private function makeFile($destDir, $fileName)
    {
        $this->fileHandle = fopen($destDir . '/' . $fileName, 'w+');

        if ($this->fileHandle === false) {

            return false;

        } else {

            return true;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Writes a single line to the file handle created by makeFile
     * @param  string $line The line to write
     * @return integer
     */
    private function writeFileLine($line = '')
    {
        return fwrite($this->fileHandle, $line . "\r\n");
    }
}