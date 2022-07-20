<?php

use MKrawczyk\Mpts\Environment;
use MKrawczyk\Mpts\Nodes\Expressions\TEBoolean;
use MKrawczyk\Mpts\Nodes\Expressions\TEEqual;
use MKrawczyk\Mpts\Nodes\Expressions\TEMethodCall;
use MKrawczyk\Mpts\Nodes\Expressions\TENumber;
use MKrawczyk\Mpts\Nodes\Expressions\TEString;
use MKrawczyk\Mpts\Nodes\Expressions\TEVariable;
use MKrawczyk\Mpts\Parser\ExpressionParser;
use MKrawczyk\Mpts\Parser\XMLParser;
use MKrawczyk\Mpts\Nodes\TDocumentFragment;
use MKrawczyk\Mpts\Nodes\TText;
use MKrawczyk\Mpts\Nodes\TElement;
use PHPUnit\Framework\TestCase;

class ExpressionParseTest extends TestCase
{
    public function testVariable()
    {
        $obj = ExpressionParser::Parse("var1");

        $this->assertInstanceOf(TEVariable::class, $obj);
        $this->assertEquals("var1", $obj->name);
    }

    public function testBoolTrue()
    {
        $obj = ExpressionParser::Parse("true");

        $this->assertInstanceOf(TEBoolean::class, $obj);
        $this->assertEquals(true, $obj->value);
    }

    public function testBoolFalse()
    {
        $obj = ExpressionParser::Parse("false");

        $this->assertInstanceOf(TEBoolean::class, $obj);
        $this->assertEquals(false, $obj->value);
    }

    public function testProperty()
    {
        $obj = ExpressionParser::Parse("var1.sub.sub2");

        $this->assertInstanceOf(TEProperty::class, $obj);
        $this->assertEquals("sub2", $obj->name);
        $this->assertInstanceOf(TEProperty::class, $obj->source);
        $this->assertEquals("sub", $obj->source->name);
        $this->assertInstanceOf(TEVariable::class, $obj->source->source);
        $this->assertEquals("var1", $obj->source->source->name);
    }

    public function testNumber()
    {
        $obj = ExpressionParser::Parse("123");

        $this->assertInstanceOf(TENumber::class, $obj);
        $this->assertEquals(123, $obj->value);
    }

    public function testNumberDecimal()
    {
        $obj = ExpressionParser::Parse("1.23");

        $this->assertInstanceOf(TENumber::class, $obj);
        $this->assertEquals(1.23, $obj->value);
    }

    public function testNumberE()
    {
        $obj = ExpressionParser::Parse("1.23e2");

        $this->assertInstanceOf(TENumber::class, $obj);
        $this->assertEquals(123, $obj->value);
    }

    public function testString1()
    {
        $obj = ExpressionParser::Parse("'text'");

        $this->assertInstanceOf(TEString::class, $obj);
        $this->assertEquals("text", $obj->value);
    }

    public function testString2()
    {
        $obj = ExpressionParser::Parse('"text"');

        $this->assertInstanceOf(TEString::class, $obj);
        $this->assertEquals("text", $obj->value);
    }

    public function testEqual()
    {
        $obj = ExpressionParser::Parse('a==b');

        $this->assertInstanceOf(TEEqual::class, $obj);
        $this->assertInstanceOf(TEVariable::class, $obj->left);
        $this->assertEquals("a", $obj->left->name);
        $this->assertInstanceOf(TEVariable::class, $obj->right);
        $this->assertEquals("b", $obj->right->name);
    }

    public function testEqualDouble()
    {
        $obj = ExpressionParser::Parse('(a==b)==(c==d)');

        $this->assertInstanceOf(TEEqual::class, $obj);
        $this->assertInstanceOf(TEEqual::class, $obj->left);
        $this->assertInstanceOf(TEVariable::class, $obj->left->left);
        $this->assertEquals("a", $obj->left->left->name);
        $this->assertInstanceOf(TEVariable::class, $obj->left->right);
        $this->assertEquals("b", $obj->left->right->name);

        $this->assertInstanceOf(TEEqual::class, $obj->right);
        $this->assertInstanceOf(TEVariable::class, $obj->right->left);
        $this->assertEquals("c", $obj->right->left->name);
        $this->assertInstanceOf(TEVariable::class, $obj->right->right);
        $this->assertEquals("d", $obj->right->right->name);
    }

    public function testMethodCall()
    {
        $obj = ExpressionParser::Parse('fun(x)');

        $this->assertInstanceOf(TEMethodCall::class, $obj);
        $this->assertInstanceOf(TEVariable::class, $obj->source);
        $this->assertEquals("fun", $obj->source->name);
        $this->assertInstanceOf(TEVariable::class, $obj->args[0]);
        $this->assertEquals("x", $obj->args[0]->name);
    }
}