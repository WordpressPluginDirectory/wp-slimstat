<?php

declare(strict_types=1);

namespace SlimStat\Dependencies\BrowscapPHP\Command;

use SlimStat\Dependencies\BrowscapPHP\Browscap;
use SlimStat\Dependencies\BrowscapPHP\Exception;
use SlimStat\Dependencies\BrowscapPHP\Helper\LoggerHelper;
use SlimStat_JsonException;
use SlimStat\Dependencies\League\Flysystem\Filesystem;
use SlimStat\Dependencies\League\Flysystem\Local\LocalFilesystemAdapter;
use SlimStat\Dependencies\MatthiasMullie\Scrapbook\Adapters\Flysystem;
use SlimStat\Dependencies\MatthiasMullie\Scrapbook\Psr16\SimpleCache;
use SlimStat\Dependencies\Symfony\Component\Console\Command\Command;
use SlimStat\Dependencies\Symfony\Component\Console\Exception\InvalidArgumentException;
use SlimStat\Dependencies\Symfony\Component\Console\Exception\LogicException;
use SlimStat\Dependencies\Symfony\Component\Console\Input\InputArgument;
use SlimStat\Dependencies\Symfony\Component\Console\Input\InputInterface;
use SlimStat\Dependencies\Symfony\Component\Console\Input\InputOption;
use SlimStat\Dependencies\Symfony\Component\Console\Output\OutputInterface;

use function assert;
use function is_string;
use function json_encode;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

/**
 * commands to parse a given useragent
 *
 * @internal This extends Symfony API, and we do not want to expose upstream BC breaks, so we DO NOT promise BC on this
 */
class ParserCommand extends Command
{
    public const PARSER_ERROR = 11;

    private ?string $defaultCacheFolder = null;

    /**
     * @throws LogicException
     */
    public function __construct(string $defaultCacheFolder)
    {
        $this->defaultCacheFolder = $defaultCacheFolder;

        parent::__construct();
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function configure(): void
    {
        $this
            ->setName('browscap:parse')
            ->setDescription('Parses a user agent string and dumps the results.')
            ->addArgument(
                'user-agent',
                InputArgument::REQUIRED,
                'User agent string to analyze',
                null
            )
            ->addOption(
                'cache',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Where the cache files are located',
                $this->defaultCacheFolder
            );
    }

    /**
     * @throws InvalidArgumentException
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = LoggerHelper::createDefaultLogger($output);

        $cacheOption = $input->getOption('cache');
        assert(is_string($cacheOption));

        $adapter    = new LocalFilesystemAdapter($cacheOption);
        $filesystem = new Filesystem($adapter);
        $cache      = new SimpleCache(
            new Flysystem($filesystem)
        );

        $browscap = new Browscap($cache, $logger);

        $uaArgument = $input->getArgument('user-agent');
        assert(is_string($uaArgument));

        try {
            $result = $browscap->getBrowser($uaArgument);
        } catch (Exception $e) {
            $logger->debug($e);

            return self::PARSER_ERROR;
        }

        try {
            $output->writeln(json_encode($result, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        } catch (SlimStat_JsonException $e) {
            $logger->error($e);

            return self::PARSER_ERROR;
        }

        return self::SUCCESS;
    }
}
