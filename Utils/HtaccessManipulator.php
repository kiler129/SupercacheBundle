<?php

namespace noFlash\SupercacheBundle\Utils;


use noFlash\SupercacheBundle\Exceptions\FilesystemException;
use noFlash\SupercacheBundle\Exceptions\InvalidPattern;
use noFlash\SupercacheBundle\Exceptions\PatternNotFoundException;

/**
 * Manipulates .htaccess file content by adding and removing lines.
 */
class HtaccessManipulator
{
    /**
     * @var array .htaccess file content. Each array element contains single line of file.
     */
    private $file = array();

    /**
     * @param string $file File contents
     *
     * @throws \InvalidArgumentException Given argument is not string
     */
    public function __construct($file)
    {
        if (!is_string($file)) {
            throw new \InvalidArgumentException("Expected string, got " . gettype($file));
        }

        foreach (explode("\n", $file) as $line) {
            $this->file[] = rtrim($line, "\r\n");
        }
    }

    /**
     * Finds line number matching given pattern.
     * Note: Be aware this method will return line number, and line numbers (unlike arrays) are counted from 1.
     *
     * @param string $regex preg_match() complain pattern
     *
     * @return int|null Returns line number or null if none found
     * @throws InvalidPattern
     */
    private function locateLineByPattern($regex)
    {
        $matches = @preg_grep($regex, $this->file);
        if ($matches === false) {
            throw new InvalidPattern("Given regular expression - $regex - is not valid, consult manual.");
        }
        reset($matches);
        $match = key($matches);
        if ($match !== null) {
            $match++; //Array are counted from 0 while lines from 0
        }

        return $match;
    }

    /**
     * Adds line before line matching given pattern.
     *
     * @param string $regex preg_match() complain pattern
     * @param string $content Line content to insert
     *
     * @return bool
     * @throws PatternNotFoundException Given pattern cannot be located inside file
     * @throws InvalidPattern
     */
    public function addBefore($regex, $content)
    {
        $locatedLineNumber = $this->locateLineByPattern($regex);
        if ($locatedLineNumber === null) {
            throw new PatternNotFoundException('No line was found for pattern ' . $regex);
        }

        $content = array($content);

        return !(array_splice($this->file, $locatedLineNumber - 1, 0, $content) === false);
    }

    /**
     * Adds line after line matching given pattern.
     *
     * @param string $regex preg_match() complain pattern
     * @param string $content Line content to insert
     *
     * @return bool
     * @throws PatternNotFoundException Given pattern cannot be located inside file
     * @throws InvalidPattern
     */
    public function addAfter($regex, $content)
    {
        $locatedLineNumber = $this->locateLineByPattern($regex);
        if ($locatedLineNumber === null) {
            throw new PatternNotFoundException('No line was found for pattern ' . $regex);
        }

        $content = array($content);

        return !(array_splice($this->file, $locatedLineNumber, 0, $content) === false);
    }

    /**
     * Adds line at the end of file
     *
     * @param string $content Line content to insert
     *
     * @return bool
     */
    public function append($content)
    {
        $this->file[] = $content;

        return true;
    }

    /**
     * Adds line at the beelining of file
     *
     * @param string $content Line content to insert
     *
     * @return bool
     */
    public function prepend($content)
    {
        array_unshift($this->file, $content);

        return true;
    }

    /**
     * Removes first line matching pattern
     * Note: This method will not throw PatternNotFoundException like other methods do - instead false will be returned.
     *
     * @param string $regex preg_match() complain pattern
     *
     * @return bool False if line cannot be found, true otherwise
     * @throws InvalidPattern
     */
    public function removeLine($regex)
    {
        $lineNumber = $this->locateLineByPattern($regex);
        if ($lineNumber === null) {
            return false;
        }

        unset($this->file[$lineNumber - 1]);
        $this->file = array_values($this->file);

        return true;
    }


    /**
     * Provides object instance from physical file.
     *
     * @param string $filePath Path to file
     *
     * @return static Returns HtaccessManipulator object (or it's child)
     * @throws FilesystemException Unable to read requested file
     */
    public static function fromFile($filePath)
    {
        $file = @file_get_contents($filePath);

        if ($file === false) {
            throw new FilesystemException("Failed to read $filePath");
        }

        return new static($file);
    }

    /**
     * Saves object to file
     *
     * @param string $filePath Path to file
     *
     * @return bool
     * @throws FilesystemException Unable to write to file requested
     */
    public function toFile($filePath)
    {
        $status = @file_put_contents($filePath, (string)$this . "\n");

        if ($status === false) {
            throw new FilesystemException("Failed to write $filePath");
        }

        return true;
    }

    /**
     * @return string .htaccess file contents
     */
    public function __toString()
    {
        return implode("\n", $this->file);
    }
}
