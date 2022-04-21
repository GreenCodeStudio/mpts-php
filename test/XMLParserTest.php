<?php

use MKrawczyk\Mpts\Nodes\Expressions\TEString;
use MKrawczyk\Mpts\Nodes\TAttribute;
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

//
//public function testElementWithAttributeWithVariables(){
//    const obj = XMLParser.Parse("<img src=(v1) alt=v2/>");
//    expect(obj).to.be.instanceOf(TDocumentFragment);
//        expect(obj.children[0]).to.be.instanceOf(TElement);
//        expect(obj.children[0].tagName).to.be.equals("img");
//        expect(obj.children[0].attributes[0]).to.be.instanceOf(TAttribute);
//        expect(obj.children[0].attributes[0].name).to.be.equals("src");
//        expect(obj.children[0].attributes[0].expression).to.be.instanceOf(TEVariable);
//        expect(obj.children[0].attributes[0].expression.name).to.be.equal("v1");
//        expect(obj.children[0].attributes[1]).to.be.instanceOf(TAttribute);
//        expect(obj.children[0].attributes[1].name).to.be.equals("alt");
//        expect(obj.children[0].attributes[1].expression).to.be.instanceOf(TEVariable);
//        expect(obj.children[0].attributes[1].expression.name).to.be.equal("v2");
//    });
//}
//
//public function testComment()
//{
//    it('comment', async() => {
//    const obj = XMLParser . Parse("<!--comment-->");
//    expect(obj) . to . be .instanceof(TDocumentFragment);
//        expect(obj . children[0]) . to . be .instanceof(TComment);
//        expect(obj . children[0] . text) . to . be . equals("comment");
//    });
//}
//public function testIf()
//    const obj = XMLParser.Parse("<:if condition=false>text</:if><:else>text</:else>");
//    expect(obj).to.be.instanceOf(TDocumentFragment);
//        expect(obj.children[0]).to.be.instanceOf(TIf);
//        expect(obj.children[0].conditions[0].expression).to.be.instanceOf(TEBoolean);
//        expect(obj.children[0].conditions[0].children[0]).to.be.instanceOf(TText);
//        expect(obj.children[0].conditions[0].expression).to.be.instanceOf(TEBoolean);
//        expect(obj.children[0].else.children[0]).to.be.instanceOf(TText);
//   }
}
