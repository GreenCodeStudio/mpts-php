<?php


use MKrawczyk\Mpts\Nodes\TDocumentFragment;
use MKrawczyk\Mpts\Parser\HTMLParser;

include_once 'UniParserTest.php';

class HTMLParserTest extends UniParserTest
{
    protected function parse(string $input): TDocumentFragment
    {
        return HTMLParser::Parse($input);
    }
}
