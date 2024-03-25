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
use MKrawczyk\Mpts\Parser\XMLParser;
use MKrawczyk\Mpts\Nodes\TDocumentFragment;
use MKrawczyk\Mpts\Nodes\TText;
use MKrawczyk\Mpts\Nodes\TElement;
use PHPUnit\Framework\TestCase;

include_once 'UniParserTest.php';

class XMLParserTest extends UniParserTest
{
    protected function parse(string $input): TDocumentFragment
    {
        return XMLParser::Parse($input);
    }

    public function testNotClosedElement()
    {
        $this->expectExceptionMessageMatches("/Element <div> not closed/");
        $obj = $this->parse("<div>");
    }

    public function testBadOrderOfClose()
    {
        $this->expectExceptionMessageMatches("/Last opened element is not <span>/");
        $obj = $this->parse("<span><strong></span></strong>");
    }

    public function testRealLife1(){
        $obj= $this->parse("<input name=\"realizationTime\" type=\"number\" step=\"0.01\" value=(data.realizationTime??t('roomsList.sumPrice.realizationTime.value')) />");
        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertInstanceOf(TAttribute::class, $obj->children[0]->attributes[0]);
        $this->assertEquals("name", $obj->children[0]->attributes[0]->name);
        $this->assertInstanceOf(TEString::class, $obj->children[0]->attributes[0]->expression);
        $this->assertEquals("realizationTime", $obj->children[0]->attributes[0]->expression->value);
        $this->assertInstanceOf(TAttribute::class, $obj->children[0]->attributes[1]);
        $this->assertEquals("type", $obj->children[0]->attributes[1]->name);
        $this->assertInstanceOf(TEString::class, $obj->children[0]->attributes[1]->expression);
        $this->assertEquals("number", $obj->children[0]->attributes[1]->expression->value);
        $this->assertInstanceOf(TAttribute::class, $obj->children[0]->attributes[2]);
        $this->assertEquals("step", $obj->children[0]->attributes[2]->name);
        $this->assertInstanceOf(TEString::class, $obj->children[0]->attributes[2]->expression);
        $this->assertEquals("0.01", $obj->children[0]->attributes[2]->expression->value);
        $this->assertInstanceOf(TAttribute::class, $obj->children[0]->attributes[3]);
        $this->assertEquals("value", $obj->children[0]->attributes[3]->name);
        $this->assertInstanceOf(TEBoolean::class, $obj->children[0]->attributes[3]->expression);
        $this->assertInstanceOf(TEVariable::class, $obj->children[0]->attributes[3]->expression->left);
        $this->assertInstanceOf(TEMethodCall::class, $obj->children[0]->attributes[3]->expression->right);
        $this->assertInstanceOf(TEVariable::class, $obj->children[0]->attributes[3]->expression->right->source);
    }
}
