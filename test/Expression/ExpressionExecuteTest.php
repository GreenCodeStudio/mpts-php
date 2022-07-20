<?php

use MKrawczyk\Mpts\Environment;
use MKrawczyk\Mpts\Nodes\Expressions\TEBoolean;
use MKrawczyk\Mpts\Nodes\Expressions\TENumber;
use MKrawczyk\Mpts\Nodes\Expressions\TEString;
use MKrawczyk\Mpts\Nodes\Expressions\TEVariable;
use MKrawczyk\Mpts\Parser\ExpressionParser;
use MKrawczyk\Mpts\Parser\XMLParser;
use MKrawczyk\Mpts\Nodes\TDocumentFragment;
use MKrawczyk\Mpts\Nodes\TText;
use MKrawczyk\Mpts\Nodes\TElement;
use PHPUnit\Framework\TestCase;

class ExpressionExecuteTest extends TestCase
{
    public function testVariable()
    {
        $obj = ExpressionParser::Parse("var1");

        $env = new Environment();
        $env->variables['var1'] = new StdClass();
        $this->assertEquals($env->variables['var1'], $obj->execute($env));
    }

    public function testBoolTrue()
    {
        $obj = ExpressionParser::Parse("true");

        $env = new Environment();
        $this->assertEquals(true, $obj->execute($env));
    }

    public function testBoolFalse()
    {
        $obj = ExpressionParser::Parse("false");

        $env = new Environment();
        $this->assertEquals(false, $obj->execute($env));
    }

    public function testProperty()
    {
        $obj = ExpressionParser::Parse("var1.sub.sub2");

        $env = new Environment();
        $env->variables['var1'] = (object)['sub'=>['sub2'=>new StdClass()]];
        $this->assertEquals($env->variables['var1']->sub['sub2'], $obj->execute($env));
    }

    public function testNumber()
    {
        $obj = ExpressionParser::Parse("123");

        $env = new Environment();
        $this->assertEquals(123, $obj->execute($env));
    }

    public function testNumberDecimal()
    {
        $obj = ExpressionParser::Parse("1.23");

        $env = new Environment();
        $this->assertEquals(1.23, $obj->execute($env));
    }

    public function testNumberE()
    {
        $obj = ExpressionParser::Parse("1.23e2");

        $env = new Environment();
        $this->assertEquals(1.23, $obj->execute($env));
    }

    public function testString1()
    {
        $obj = ExpressionParser::Parse("'text'");

        $env = new Environment();
        $this->assertEquals('text', $obj->execute($env));
    }

    public function testString2()
    {
        $obj = ExpressionParser::Parse('"text"');

        $env = new Environment();
        $this->assertEquals('text', $obj->execute($env));
    }

    public function testEqual()
    {
        $obj = ExpressionParser::Parse('a==b');

        $env = new Environment();
        $env->variables['a'] = 1;
        $env->variables['b'] = 2;
        $this->assertEquals(false, $obj->execute($env));
        $env->variables['b'] = 1;
        $this->assertEquals(true, $obj->execute($env));
    }

    public function testEqualDouble()
    {
        $obj = ExpressionParser::Parse('(a==b)==(c==d)');

        $env = new Environment();
        $env->variables['a'] = 1;
        $env->variables['b'] = 2;
        $env->variables['c'] = 3;
        $env->variables['d'] = 4;
        $this->assertEquals(true, $obj->execute($env));
    }
}