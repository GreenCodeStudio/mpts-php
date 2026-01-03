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
        $env->variables['var1'] = (object)['sub' => ['sub2' => new StdClass()]];
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
        $this->assertEquals(1.23e2, $obj->execute($env));
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

    public function testStringConcat()
    {
        $obj = ExpressionParser::Parse('"te":\'xt\'');

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

    public function testMethodCall()
    {
        $obj = ExpressionParser::Parse('fun(x)');

        $env = new Environment();
        $env->variables['x'] = 1;
        $env->variables['fun'] = fn($z) => $z * 10;
        $this->assertEquals(10, $obj->execute($env));
    }

    public function testMethodCallMultiple()
    {
        $obj = ExpressionParser::Parse('fun(first,second)');

        $env = new Environment();
        $env->variables['first'] = 3;
        $env->variables['second'] = 7;
        $env->variables['fun'] = fn($a, $b) => $a * $b;
        $this->assertEquals(21, $obj->execute($env));
    }

    public function testAdd()
    {
        $obj = ExpressionParser::Parse("2+5 + 3");

        $env = new Environment();
        $this->assertEquals(10, $obj->execute($env));
    }

    public function testSub()
    {
        $obj = ExpressionParser::Parse("2-5 - 3");

        $env = new Environment();
        $this->assertEquals(-6, $obj->execute($env));
    }


    public function testMultiply()
    {
        $obj = ExpressionParser::Parse("2*5 * 3");

        $env = new Environment();
        $this->assertEquals(30, $obj->execute($env));
    }

    public function testDivide()
    {
        $obj = ExpressionParser::Parse("20/5 / 2");
        $env = new Environment();
        $this->assertEquals(2, $obj->execute($env));
    }

    public function testPrecedence()
    {
        $obj = ExpressionParser::Parse("2+5 * 3");

        $env = new Environment();
        $this->assertEquals(17, $obj->execute($env));
    }

    public function testParenthesis()
    {
        $obj = ExpressionParser::Parse("(2+5) * 3");

        $env = new Environment();
        $this->assertEquals(21, $obj->execute($env));
    }

    public function testModulo()
    {
        $obj = ExpressionParser::Parse("20 % 6");

        $env = new Environment();
        $this->assertEquals(2, $obj->execute($env));
    }

    public function testOrNull()
    {
        $obj = ExpressionParser::Parse('var1??"empty"');

        $env = new Environment();
        $this->assertEquals("empty", $obj->execute($env));
        $env->variables['var1'] = null;
        $this->assertEquals("empty", $obj->execute($env));
        $env->variables['var1'] = "val";
        $this->assertEquals("val", $obj->execute($env));
    }

    public function testOrNullProperty()
    {
        $obj = ExpressionParser::Parse('var1.property??"empty"');

        $env = new Environment();
        $this->assertEquals("empty", $obj->execute($env));
        $env->variables['var1'] = null;
        $this->assertEquals("empty", $obj->execute($env));
        $env->variables['var1'] = [];
        $this->assertEquals("empty", $obj->execute($env));
        $env->variables['var1'] = ['property' => null];
        $this->assertEquals("empty", $obj->execute($env));
        $env->variables['var1'] = ['property' => 'val'];
        $this->assertEquals("val", $obj->execute($env));


        $env->variables['var1'] = (object)[];
        $this->assertEquals("empty", $obj->execute($env));
        $env->variables['var1'] = (object)['property' => null];
        $this->assertEquals("empty", $obj->execute($env));
        $env->variables['var1'] = (object)['property' => 'val'];
        $this->assertEquals("val", $obj->execute($env));
    }

    public function testAnd()
    {
        $obj = ExpressionParser::Parse("a && b");
        $env = new Environment();
        $env->variables['a'] = true;
        $env->variables['b'] = false;
        $this->assertEquals(false, $obj->execute($env));
        $env->variables['b'] = true;
        $this->assertEquals(true, $obj->execute($env));
    }

    public function testOr()
    {
        $obj = ExpressionParser::Parse("a || b");
        $env = new Environment();
        $env->variables['a'] = false;
        $env->variables['b'] = false;
        $this->assertEquals(false, $obj->execute($env));
        $env->variables['b'] = true;
        $this->assertEquals(true, $obj->execute($env));
    }

    public function testNot()
    {
        $obj = ExpressionParser::Parse("!a");
        $env = new Environment();
        $env->variables['a'] = false;
        $this->assertEquals(true, $obj->execute($env));
        $env->variables['a'] = true;
        $this->assertEquals(false, $obj->execute($env));


    }

    public function testDoubleNot()
    {
        $obj = ExpressionParser::Parse("!!a");
        $env = new Environment();
        $env->variables['a'] = false;
        $this->assertEquals(false, $obj->execute($env));
        $env->variables['a'] = true;
        $this->assertEquals(true, $obj->execute($env));
        $env->variables['a'] = 'text';
        $this->assertEquals(true, $obj->execute($env));
        $env->variables['a'] = '';
        $this->assertEquals(false, $obj->execute($env));
//        $env->variables['a'] = 'false';
//        $this->assertEquals(false, $obj->execute($env));//todo rethink
//        $env->variables['a'] = 'faLsE';
//        $this->assertEquals(false, $obj->execute($env));//todo rethink
        $env->variables['a'] = 0;
        $this->assertEquals(false, $obj->execute($env));
        $env->variables['a'] = 1;
        $this->assertEquals(true, $obj->execute($env));


    }

    public function testNotExistingVariable()
    {
        $obj = ExpressionParser::Parse("notExisting");
        $env = new Environment();
        $env->allowUndefined = false;

        $this->expectException(\MKrawczyk\Mpts\MptsExecutionError::class);
        $this->expectExceptionMessageMatches('/variable `notExisting` don\'t exists/');
        $this->expectExceptionMessageMatches('/file.mpts:1:2/');
        $obj->execute($env);
    }

    public function testNotExistingVariableAllowUndefined()
    {
        $obj = ExpressionParser::Parse("notExisting");
        $env = new Environment();
        $env->allowUndefined = true;
        $this->assertNull($obj->execute($env));
    }

    public function testNotExistingProperty()
    {
        $obj = ExpressionParser::Parse("a.b.c");
        $env = new Environment();
        $env->allowUndefined = false;
        $env->variables['a'] = (object)[];
        $this->expectException(\MKrawczyk\Mpts\MptsExecutionError::class);
        $this->expectExceptionMessageMatches('/property `b` don\'t exists/');
        $this->expectExceptionMessageMatches('/file.mpts:1:2/');
        $obj->execute($env);
    }

    public function testNotExistingPropertyAllowUndefined()
    {
        $obj = ExpressionParser::Parse("a.b.c");
        $env = new Environment();
        $env->allowUndefined = true;
        $env->variables['a'] = (object)[];
        $this->assertNull($obj->execute($env));
    }

    public function testBadTypeMethodCall()
    {
        $obj = ExpressionParser::Parse("a.b()");
        $env = new Environment();
        // set variable to non-callable to trigger method-call-on-non-method
        $env->variables['a'] = (object)[];
        $this->expectException(\MKrawczyk\Mpts\MptsExecutionError::class);
        $this->expectExceptionMessageMatches('/method call|callable|Cannot/m');
        // ensure code position is included in message (at least file.mpts token)
        $this->expectExceptionMessageMatches('/file\.mpts|<unknown>:/');
        // parse with a file position so position info is available
        $obj = ExpressionParser::Parse("a.b()");
        $obj->execute($env);
    }

    public function testNotExistingMethodCall()
    {
        $obj = ExpressionParser::Parse("a.c()");
        $env = new Environment();
        $env->variables['a'] = (object)['b' => (object)[]];
        $this->expectException(\MKrawczyk\Mpts\MptsExecutionError::class);
        $this->expectExceptionMessageMatches('/method call|callable|Cannot/m');
        $this->expectExceptionMessageMatches('/file\.mpts|<unknown>:/');
        $obj = ExpressionParser::Parse("a.c()");
        $obj->execute($env);
    }

    public function testExceptionInsideMethodCall()
    {
        $obj = ExpressionParser::Parse("a()");
        $env = new Environment();
        $env->variables['a'] = function () {
            throw new \Exception("inside method error");
        };
        $this->expectException(\MKrawczyk\Mpts\MptsExecutionError::class);
        $this->expectExceptionMessageMatches('/inside method error/');
        $this->expectExceptionMessageMatches('/file\.mpts:1:1/');
        $obj->execute($env);
    }

}
