<?php

/**
 * Class Reader
 */
class Reader
{
	protected $_file;
	protected $_fileDescriptor;

    protected $_progress;

	public function __construct($file)
	{
		$this->setFile($file);
	}

	public function setFile($file)
	{
		$this->_file = realpath($file);

		if ($this->_fileDescriptor) {
			fclose($this->_fileDescriptor);
		}
		$this->_fileDescriptor = fopen($this->_file, "r");
	}

	public function getWordsIterator()
	{
        $fileSize = filesize($this->_file);
        $counter = 0;

		while ($chunk = fread($this->_fileDescriptor, 4096)) {
			$words = preg_split("#[^\p{L}]#u", $chunk);
            $words = array_filter($words);

            if (1 < count($words)) {
                $lastWord = array_pop($words);
                fseek($this->_fileDescriptor, -(strlen($lastWord)+1), SEEK_CUR );
            }

			foreach ($words as $word) {
				$word = preg_replace("#[^\p{L}]#u", "", $word);
				if (!$word) {
					continue;
				}
                if (0 === ++$counter % 100) {
                    $this->_progress = ftell($this->_fileDescriptor) / $fileSize;
                }
				yield $word;
			}
		}

        fclose($this->_fileDescriptor);
	}

    /**
     * @return mixed
     */
    public function getProgress()
    {
        return $this->_progress;
    }
}