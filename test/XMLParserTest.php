<?php

use MKrawczyk\Mpts\Nodes\Expressions\TEBoolean;
use MKrawczyk\Mpts\Nodes\Expressions\TENumber;
use MKrawczyk\Mpts\Nodes\Expressions\TEString;
use MKrawczyk\Mpts\Nodes\Expressions\TEVariable;
use MKrawczyk\Mpts\Nodes\TAttribute;
use MKrawczyk\Mpts\Nodes\TComment;
use MKrawczyk\Mpts\Nodes\TIf;
use MKrawczyk\Mpts\Parser\XMLParser;
use MKrawczyk\Mpts\Nodes\TDocumentFragment;
use MKrawczyk\Mpts\Nodes\TText;
use MKrawczyk\Mpts\Nodes\TElement;
use PHPUnit\Framework\TestCase;

class XMLParserTest extends TestCase
{
    public function testBasicText()
    {
        $obj = XMLParser::Parse("Hello, world!");
        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TText::class, $obj->children[0]);
        $this->assertEquals("Hello, world!", $obj->children[0]->text);
    }

    public function testBasicElement()
    {
        $obj = XMLParser::Parse("<br/>");
        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("br", $obj->children[0]->tagName);
    }

    public function testBasicElement2()
    {
        $obj = XMLParser::Parse("<div></div>");
        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("div", $obj->children[0]->tagName);
    }

    public function testNotClosedElement()
    {
        $this->expectExceptionMessageMatches("/Element <div> not closed/");
        $obj = XMLParser::Parse("<div>");
    }

    public function testNotOpenedElement()
    {
        $this->expectExceptionMessageMatches("/Last opened element is not <div>/");
        $obj = XMLParser::Parse("<div>");
    }

    public function testElementsInside()
    {
        $obj = XMLParser::Parse("<div><p><strong></strong><span></span></p></div>");
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("div", $obj->children[0]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[0]);
        $this->assertEquals("p", $obj->children[0]->children[0]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[0]->children[0]);
        $this->assertEquals("strong", $obj->children[0]->children[0]->children[0]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[0]->children[1]);
        $this->assertEquals("span", $obj->children[0]->children[0]->children[1]->tagName);
    }

    public function testBadOrderOfClose()
    {
        $this->expectExceptionMessageMatches("/Last opened element is not <span>/");
        $obj = XMLParser::Parse("<span><strong></span></strong>");
    }

    public function testElementWithAttribute()
    {

        $obj = XMLParser::Parse("<img src=\"a.png\" alt='a'/>");

        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("img", $obj->children[0]->tagName);
        $this->assertInstanceOf(TAttribute::class, $obj->children[0]->attributes[0]);
        $this->assertEquals("src", $obj->children[0]->attributes[0]->name);
        $this->assertInstanceOf(TEString::class, $obj->children[0]->attributes[0]->expression);
        $this->assertEquals("a.png", $obj->children[0]->attributes[0]->expression->value);
        $this->assertInstanceOf(TAttribute::class, $obj->children[0]->attributes[1]);
        $this->assertEquals("alt", $obj->children[0]->attributes[1]->name);
        $this->assertInstanceOf(TEString::class, $obj->children[0]->attributes[1]->expression);
        $this->assertEquals("a", $obj->children[0]->attributes[1]->expression->value);
    }


    public function testElementWithAttributeWithVariables()
    {
        $obj = XMLParser::Parse("<img src=(v1) alt=v2/>");

        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("img", $obj->children[0]->tagName);
        $this->assertInstanceOf(TAttribute::class, $obj->children[0]->attributes[0]);
        $this->assertEquals("src", $obj->children[0]->attributes[0]->name);
        $this->assertInstanceOf(TEVariable::class, $obj->children[0]->attributes[0]->expression);
        $this->assertEquals("v1", $obj->children[0]->attributes[0]->expression->name);
        $this->assertInstanceOf(TAttribute::class, $obj->children[0]->attributes[1]);
        $this->assertEquals("alt", $obj->children[0]->attributes[1]->name);
        $this->assertInstanceOf(TEVariable::class, $obj->children[0]->attributes[1]->expression);
        $this->assertEquals("v2", $obj->children[0]->attributes[1]->expression->name);
    }

    public function testComment()
    {
        $obj = XMLParser::Parse("<!--comment-->");

        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TComment::class, $obj->children[0]);
        $this->assertEquals("comment", $obj->children[0]->text);
    }

    public function testIf()
    {
        $obj = XMLParser::Parse("<:if condition=false>text</:if><:else>text</:else>");

        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TIf::class, $obj->children[0]);
        $this->assertInstanceOf(TEBoolean::class, $obj->children[0]->conditions[0]->expression);
        $this->assertInstanceOf(TText::class, $obj->children[0]->conditions[0]->children[0]);
        $this->assertInstanceOf(TText::class, $obj->children[0]->else->children[0]);
    }

    public function testLoop()
    {
        $obj = XMLParser::Parse("<:loop count=10>b</:loop>");

        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TLoop::class, $obj->children[0]);
        $this->assertInstanceOf(TENumber::class, $obj->children[0]->count);
        $this->assertEquals(10, $obj->children[0]->count->value);
        $this->assertInstanceOf(TText::class, $obj->children[0]->children[0]);
        $this->assertEquals("b", $obj->children[0]->children[0]->text);
    }


    public function testForeachBasic()
    {
        $obj = XMLParser::Parse("<:foreach collection=a>b</:foreach>");

        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TForeach::class, $obj->children[0]);
        $this->assertInstanceOf(TEVariable::class, $obj->children[0]->collection);
        $this->assertEquals("a", $obj->children[0]->collection->name);
        $this->assertInstanceOf(TText::class, $obj->children[0]->children[0]);
        $this->assertEquals("b", $obj->children[0]->children[0]->text);
    }

    public function testForeachAdvanced()
    {
        $obj = XMLParser::Parse("<:foreach collection=a item=b key=c><div>{{c}}:{{b}}</div></:foreach>");

        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TForeach::class, $obj->children[0]);
        $this->assertInstanceOf(TEVariable::class, $obj->children[0]->collection);
        $this->assertEquals("a", $obj->children[0]->collection->name);
        $this->assertEquals("b", $obj->children[0]->item);
        $this->assertEquals("c", $obj->children[0]->key);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[0]);
    }
}
