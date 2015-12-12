<?php

/**
 * Class WordsStorage
 */
class WordsStorage
{
	protected $_dir;
    protected $_fileDescriptor;
    protected $_packFormat = "S";

    protected $_debug = false;

    /**
     * @param string $dir location for temporary files
     * @throws Exception
     */
	public function __construct($dir)
	{
        if (!is_writable($dir)) {
            throw new Exception("'{$dir}' is readonly");
        }

		$this->_dir = $dir;
	}

    public function __destruct()
    {
        if (false === $this->_debug) {
            foreach (glob("{$this->_dir}/*.txt") as $filename) {
                unlink($filename);
            }
        }
    }

    /**
     * Increment word count and save
     * @param $word
     */
	public function increment($word)
	{
		$location = $this->getWordLocation($word);
		$mode = "r+";
		if (!file_exists($location)) {
			$mode = "w+";
		}
		$this->_fileDescriptor = fopen($location, $mode);

        $count = $this->_getWordCount($word);

        $this->_rewriteAndClose($word, $count);
	}

    public function saveResult($location)
    {
        $this->_fileDescriptor = fopen($location, "w");

        foreach (glob("{$this->_dir}/*.txt") as $filename) {
            $tmp = fopen($filename, "r");
            while ($line = fgets($tmp)) {
                if (empty($line)) {
                    continue;
                }
                list($fileWord, $count) = explode(" ", $line);
                $count = base64_decode($count);
                $count = array_pop((unpack($this->_packFormat, $count)));
                fwrite($this->_fileDescriptor, "$fileWord $count" . PHP_EOL);
            }
            fclose($tmp);
        }

        fclose($this->_fileDescriptor);
    }

    protected function _rewriteAndClose($word, $count)
    {
        $countLocal = pack($this->_packFormat, $count);
        $countLocal = base64_encode($countLocal);
        $string = "$word $countLocal" . PHP_EOL;

        if (1 < $count) {
            fseek($this->_fileDescriptor, -strlen($string), SEEK_CUR);
        }

        fwrite($this->_fileDescriptor, $string);
        fclose($this->_fileDescriptor);
    }

    protected function _getWordCount($word)
    {
        while ($line = fgets($this->_fileDescriptor)) {
            // try to find word
            if (empty($line)) {
                continue;
            }
            list($fileWord, $count) = explode(" ", $line);
            if ($word === $fileWord) {
                $count = base64_decode($count);
                $count = array_pop((unpack($this->_packFormat, $count)));
                ++$count;
                break;
            }
        }

        if (!$line) {
            $count = 1;
        }

        return $count;
    }

	public function getWordLocation($word)
	{
        $fileName = mb_strtolower($word);
		$fileName = str_pad($fileName, 2, "a");
		$fileName = substr($fileName, 0, 2);

		return $this->_dir . "/{$fileName}.txt";
	}

    /**
     * @return string
     */
    public function getPackFormat()
    {
        return $this->_packFormat;
    }

    /**
     * @param string $packFormat
     */
    public function setPackFormat($packFormat)
    {
        $this->_packFormat = $packFormat;
    }

    /**
     * @param boolean $debug
     */
    public function setDebug($debug)
    {
        $this->_debug = $debug;
    }
}