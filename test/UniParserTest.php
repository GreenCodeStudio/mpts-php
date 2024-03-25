<?php

use MKrawczyk\Mpts\Nodes\Expressions\TEBoolean;
use MKrawczyk\Mpts\Nodes\Expressions\TEMethodCall;
use MKrawczyk\Mpts\Nodes\Expressions\TENumber;
use MKrawczyk\Mpts\Nodes\Expressions\TEString;
use MKrawczyk\Mpts\Nodes\Expressions\TEVariable;
use MKrawczyk\Mpts\Nodes\TAttribute;
use MKrawczyk\Mpts\Nodes\TComment;
use MKrawczyk\Mpts\Nodes\TForeach;
use MKrawczyk\Mpts\Nodes\TIf;
use MKrawczyk\Mpts\Nodes\TLoop;
use MKrawczyk\Mpts\Parser\AbstractParser;
use MKrawczyk\Mpts\Parser\XMLParser;
use MKrawczyk\Mpts\Nodes\TDocumentFragment;
use MKrawczyk\Mpts\Nodes\TText;
use MKrawczyk\Mpts\Nodes\TElement;
use PHPUnit\Framework\TestCase;


abstract class  UniParserTest extends TestCase
{
    abstract protected function parse(string $input): TDocumentFragment;

    public function testBasicText()
    {
        $obj = $this->parse("Hello, world!");
        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TText::class, $obj->children[0]);
        $this->assertEquals("Hello, world!", $obj->children[0]->text);
    }

    public function testBasicElement()
    {
        $obj = $this->parse("<br/>");
        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("br", $obj->children[0]->tagName);
    }

    public function testBasicElement2()
    {
        $obj = $this->parse("<div></div>");
        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("div", $obj->children[0]->tagName);
    }

    public function testNotOpenedElement()
    {
        $this->expectExceptionMessageMatches("/Last opened element is not <div>/");
        $obj = $this->parse("</div>");
    }

    public function testElementsInside()
    {
        $obj = $this->parse("<div><p><strong></strong><span></span></p></div>");
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("div", $obj->children[0]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[0]);
        $this->assertEquals("p", $obj->children[0]->children[0]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[0]->children[0]);
        $this->assertEquals("strong", $obj->children[0]->children[0]->children[0]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[0]->children[1]);
        $this->assertEquals("span", $obj->children[0]->children[0]->children[1]->tagName);
    }


    public function testElementWithAttribute()
    {

        $obj = $this->parse("<img src=\"a.png\" alt='a'/>");

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
        $obj = $this->parse("<img src=(v1) alt=v2 class=(getClass())/>");

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
        $this->assertInstanceOf(TAttribute::class, $obj->children[0]->attributes[2]);
        $this->assertEquals("class", $obj->children[0]->attributes[2]->name);
        $this->assertInstanceOf(TEMethodCall::class, $obj->children[0]->attributes[2]->expression);
        $this->assertInstanceOf(TEVariable::class, $obj->children[0]->attributes[2]->expression->source);
        $this->assertEquals("getClass", $obj->children[0]->attributes[2]->expression->source->name);
    }

    public function testElementWithBooleanAtribute()
    {
        $obj = $this->parse("<textarea required/>");

        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("textarea", $obj->children[0]->tagName);
        $this->assertInstanceOf(TAttribute::class, $obj->children[0]->attributes[0]);
        $this->assertEquals("required", $obj->children[0]->attributes[0]->name);
        $this->assertEquals(null, $obj->children[0]->attributes[0]->expression);
    }

    public function testComment()
    {
        $obj = $this->parse("<!--comment-->");

        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TComment::class, $obj->children[0]);
        $this->assertEquals("comment", $obj->children[0]->text);
    }

    public function test2Comments()
    {
        $obj = $this->parse("<!--comment1--><!--comment2-->");

        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TComment::class, $obj->children[0]);
        $this->assertEquals("comment1", $obj->children[0]->text);
        $this->assertInstanceOf(TComment::class, $obj->children[1]);
        $this->assertEquals("comment2", $obj->children[1]->text);
    }

    public function testIf()
    {
        $obj = $this->parse("<:if condition=false>text</:if><:else>text</:else>");

        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TIf::class, $obj->children[0]);
        $this->assertInstanceOf(TEBoolean::class, $obj->children[0]->conditions[0]->expression);
        $this->assertInstanceOf(TText::class, $obj->children[0]->conditions[0]->children[0]);
        $this->assertInstanceOf(TText::class, $obj->children[0]->else->children[0]);
    }

    public function testLoop()
    {
        $obj = $this->parse("<:loop count=10>b</:loop>");

        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TLoop::class, $obj->children[0]);
        $this->assertInstanceOf(TENumber::class, $obj->children[0]->count);
        $this->assertEquals(10, $obj->children[0]->count->value);
        $this->assertInstanceOf(TText::class, $obj->children[0]->children[0]);
        $this->assertEquals("b", $obj->children[0]->children[0]->text);
    }


    public function testForeachBasic()
    {
        $obj = $this->parse("<:foreach collection=a>b</:foreach>");

        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TForeach::class, $obj->children[0]);
        $this->assertInstanceOf(TEVariable::class, $obj->children[0]->collection);
        $this->assertEquals("a", $obj->children[0]->collection->name);
        $this->assertInstanceOf(TText::class, $obj->children[0]->children[0]);
        $this->assertEquals("b", $obj->children[0]->children[0]->text);
    }

    public function testForeachAdvanced()
    {
        $obj = $this->parse("<:foreach collection=a item=b key=c><div>{{c}}:{{b}}</div></:foreach>");

        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TForeach::class, $obj->children[0]);
        $this->assertInstanceOf(TEVariable::class, $obj->children[0]->collection);
        $this->assertEquals("a", $obj->children[0]->collection->name);
        $this->assertEquals("b", $obj->children[0]->item);
        $this->assertEquals("c", $obj->children[0]->key);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[0]);
    }

    public function testForeachInsideElement()
    {
        $obj = $this->parse("<select><:foreach collection=a>b</:foreach></select>");

        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertInstanceOf(TForeach::class, $obj->children[0]->children[0]);
    }

    public function testCommentAfterElement()
    {
        $obj = $this->parse("<tr data-amount=article.amount><!--comment--></tr>");

        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertInstanceOf(TComment::class, $obj->children[0]->children[0]);
    }
}
