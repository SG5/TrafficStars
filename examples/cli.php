<?php
include "./../Reader.php";
include "./../WordsStorage.php";

$reader = new Reader("./../dict.txt");
$storage = new WordsStorage(sys_get_temp_dir() . "/words");

//$storage->setDebug(true);

$counter = 0;
foreach ($reader->getWordsIterator() as $word) {
	if (0 === ++$counter % 1000) {
        echo $reader->getProgress(), PHP_EOL;
    }
    $storage->increment($word);
}

$storage->saveResult("./result.txt");