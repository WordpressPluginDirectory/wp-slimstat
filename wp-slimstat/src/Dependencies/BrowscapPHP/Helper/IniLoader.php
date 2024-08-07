<?php

declare(strict_types=1);

namespace SlimStat\Dependencies\BrowscapPHP\Helper;

use function str_replace;

/**
 * class to load the browscap.ini
 */
final class IniLoader implements IniLoaderInterface
{
    /**
     * The location from which download the ini file. The placeholder for the file should be represented by a %s.
     */
    private const REMOTE_INI_URI = 'http://browscap.org/stream?q=%q';

    /**
     * The location to use to check out if a new version of the browscap.ini file is available.
     */
    private const REMOTE_TIME_URI = 'http://browscap.org/version';

    private const REMOTE_VERSION_URI = 'http://browscap.org/version-number';

    private string $remoteFilename = IniLoaderInterface::PHP_INI;

    /**
     * sets the name of the local ini file
     *
     * @param string $name the file name
     *
     * @throws Exception
     */
    public function setRemoteFilename(string $name): void
    {
        if (empty($name)) {
            throw new Exception(
                'the filename can not be empty',
                Exception::INI_FILE_MISSING
            );
        }

        $this->remoteFilename = $name;
    }

    /**
     * returns the of the remote location for updating the ini file
     *
     * @throws void
     */
    public function getRemoteIniUrl(): string
    {
        return str_replace('%q', $this->remoteFilename, self::REMOTE_INI_URI);
    }

    /**
     * returns the of the remote location for checking the version of the ini file
     *
     * @throws void
     */
    public function getRemoteTimeUrl(): string
    {
        return self::REMOTE_TIME_URI;
    }

    /**
     * returns the of the remote location for checking the version of the ini file
     *
     * @throws void
     */
    public function getRemoteVersionUrl(): string
    {
        return self::REMOTE_VERSION_URI;
    }
}
