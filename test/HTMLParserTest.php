<?php


use MKrawczyk\Mpts\Nodes\TDocumentFragment;
use MKrawczyk\Mpts\Nodes\TElement;
use MKrawczyk\Mpts\Parser\HTMLParser;

include_once 'UniParserTest.php';

class HTMLParserTest extends UniParserTest
{
    protected function parse(string $input): TDocumentFragment
    {
        return HTMLParser::Parse($input);
    }
    public function testNotClosedElement(){
        $obj = $this->parse("<div>");
        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("div", $obj->children[0]->tagName);
    }
    public function testBadOrderOfClose(){
        $this->expectExceptionMessageMatches("/Last opened element is not <strong>/");
        $obj = $this->parse("<span><strong></span></strong>");
    }
    public function testOmmitTagTypeDeep()
    {
        $obj = $this->parse("<div><div><div>");
        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("div", $obj->children[0]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[0]);
        $this->assertEquals("div", $obj->children[0]->children[0]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[0]->children[0]);
        $this->assertEquals("div", $obj->children[0]->children[0]->children[0]->tagName);
    }
    public function testOmmitTagTypeNotDeep()
    {
        $obj = $this->parse("<p><p><p>");
        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("p", $obj->children[0]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[1]);
        $this->assertEquals("p", $obj->children[1]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[2]);
        $this->assertEquals("p", $obj->children[2]->tagName);
    }
    public function testOmmitTagTypeMix()
    {
        $obj = $this->parse("<div><br><section>");
        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("div", $obj->children[0]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[0]);
        $this->assertEquals("br", $obj->children[0]->children[0]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[1]);
        $this->assertEquals("section", $obj->children[0]->children[1]->tagName);
    }
    public function testOmmitTagTypeList()
    {
        $obj = $this->parse("<ul><li>one<li>two<li>three</ul>");
        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("ul", $obj->children[0]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[0]);
        $this->assertEquals("li", $obj->children[0]->children[0]->tagName);
        $this->assertEquals("one", $obj->children[0]->children[0]->children[0]->text);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[1]);
        $this->assertEquals("li", $obj->children[0]->children[1]->tagName);
        $this->assertEquals("two", $obj->children[0]->children[1]->children[0]->text);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[2]);
        $this->assertEquals("li", $obj->children[0]->children[2]->tagName);
        $this->assertEquals("three", $obj->children[0]->children[2]->children[0]->text);
    }

    public function testLiEndTagOmission()
    {
        // Test that li end tags can be omitted when followed by another li
        $obj = $this->parse("<ul><li>one<li>two<li>three</ul>");
        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("ul", $obj->children[0]->tagName);
        $this->assertCount(3, $obj->children[0]->children);
        
        // Check each li element
        $this->assertEquals("li", $obj->children[0]->children[0]->tagName);
        $this->assertEquals("one", $obj->children[0]->children[0]->children[0]->text);
        $this->assertEquals("li", $obj->children[0]->children[1]->tagName);
        $this->assertEquals("two", $obj->children[0]->children[1]->children[0]->text);
        $this->assertEquals("li", $obj->children[0]->children[2]->tagName);
        $this->assertEquals("three", $obj->children[0]->children[2]->children[0]->text);
        
        // Test that li end tags can be omitted when there's no more content in the parent
        $obj = $this->parse("<ul><li>single item</ul>");
        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("ul", $obj->children[0]->tagName);
        $this->assertCount(1, $obj->children[0]->children);
        $this->assertEquals("li", $obj->children[0]->children[0]->tagName);
        $this->assertEquals("single item", $obj->children[0]->children[0]->children[0]->text);
    }
}
