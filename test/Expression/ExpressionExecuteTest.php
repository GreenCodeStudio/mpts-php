<?php

use MKrawczyk\Mpts\CodePosition;
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
    protected function parse(string $code)
    {
        return ExpressionParser::Parse($code, new CodePosition("file.mpts", 1, 0, 0, 0));
    }

    public function testVariable()
    {
        $obj = $this->parse("var1");

        $env = new Environment();
        $env->variables['var1'] = new StdClass();
        $this->assertEquals($env->variables['var1'], $obj->execute($env));
    }

    public function testBoolTrue()
    {
        $obj = $this->parse("true");

        $env = new Environment();
        $this->assertEquals(true, $obj->execute($env));
    }

    public function testBoolFalse()
    {
        $obj = $this->parse("false");

        $env = new Environment();
        $this->assertEquals(false, $obj->execute($env));
    }

    public function testProperty()
    {
        $obj = $this->parse("var1.sub.sub2");

        $env = new Environment();
        $env->variables['var1'] = (object)['sub' => ['sub2' => new StdClass()]];
        $this->assertEquals($env->variables['var1']->sub['sub2'], $obj->execute($env));
    }

    public function testNumber()
    {
        $obj = $this->parse("123");

        $env = new Environment();
        $this->assertEquals(123, $obj->execute($env));
    }

    public function testNumberDecimal()
    {
        $obj = $this->parse("1.23");

        $env = new Environment();
        $this->assertEquals(1.23, $obj->execute($env));
    }

    public function testNumberE()
    {
        $obj = $this->parse("1.23e2");

        $env = new Environment();
        $this->assertEquals(1.23e2, $obj->execute($env));
    }

    public function testString1()
    {
        $obj = $this->parse("'text'");

        $env = new Environment();
        $this->assertEquals('text', $obj->execute($env));
    }

    public function testString2()
    {
        $obj = $this->parse('"text"');

        $env = new Environment();
        $this->assertEquals('text', $obj->execute($env));
    }

    public function testStringConcat()
    {
        $obj = $this->parse('"te":\'xt\'');

        $env = new Environment();
        $this->assertEquals('text', $obj->execute($env));
    }

    public function testEqual()
    {
        $obj = $this->parse('a==b');

        $env = new Environment();
        $env->variables['a'] = 1;
        $env->variables['b'] = 2;
        $this->assertEquals(false, $obj->execute($env));
        $env->variables['b'] = 1;
        $this->assertEquals(true, $obj->execute($env));
    }

    public function testEqualDouble()
    {
        $obj = $this->parse('(a==b)==(c==d)');

        $env = new Environment();
        $env->variables['a'] = 1;
        $env->variables['b'] = 2;
        $env->variables['c'] = 3;
        $env->variables['d'] = 4;
        $this->assertEquals(true, $obj->execute($env));
    }

    public function testMethodCall()
    {
        $obj = $this->parse('fun(x)');

        $env = new Environment();
        $env->variables['x'] = 1;
        $env->variables['fun'] = fn($z) => $z * 10;
        $this->assertEquals(10, $obj->execute($env));
    }

    public function testMethodCallMultiple()
    {
        $obj = $this->parse('fun(first,second)');

        $env = new Environment();
        $env->variables['first'] = 3;
        $env->variables['second'] = 7;
        $env->variables['fun'] = fn($a, $b) => $a * $b;
        $this->assertEquals(21, $obj->execute($env));
    }

    public function testAdd()
    {
        $obj = $this->parse("2+5 + 3");

        $env = new Environment();
        $this->assertEquals(10, $obj->execute($env));
    }

    public function testSub()
    {
        $obj = $this->parse("2-5 - 3");

        $env = new Environment();
        $this->assertEquals(-6, $obj->execute($env));
    }


    public function testMultiply()
    {
        $obj = $this->parse("2*5 * 3");

        $env = new Environment();
        $this->assertEquals(30, $obj->execute($env));
    }

    public function testDivide()
    {
        $obj = $this->parse("20/5 / 2");
        $env = new Environment();
        $this->assertEquals(2, $obj->execute($env));
    }

    public function testPrecedence()
    {
        $obj = $this->parse("2+5 * 3");

        $env = new Environment();
        $this->assertEquals(17, $obj->execute($env));
    }

    public function testParenthesis()
    {
        $obj = $this->parse("(2+5) * 3");

        $env = new Environment();
        $this->assertEquals(21, $obj->execute($env));
    }

    public function testModulo()
    {
        $obj = $this->parse("20 % 6");

        $env = new Environment();
        $this->assertEquals(2, $obj->execute($env));
    }

    public function testOrNull()
    {
        $obj = $this->parse('var1??"empty"');

        $env = new Environment();
        $this->assertEquals("empty", $obj->execute($env));
        $env->variables['var1'] = null;
        $this->assertEquals("empty", $obj->execute($env));
        $env->variables['var1'] = "val";
        $this->assertEquals("val", $obj->execute($env));
    }

    public function testOrNullProperty()
    {
        $obj = $this->parse('var1.property??"empty"');

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
        $obj = $this->parse("a && b");
        $env = new Environment();
        $env->variables['a'] = true;
        $env->variables['b'] = false;
        $this->assertEquals(false, $obj->execute($env));
        $env->variables['b'] = true;
        $this->assertEquals(true, $obj->execute($env));
    }

    public function testOr()
    {
        $obj = $this->parse("a || b");
        $env = new Environment();
        $env->variables['a'] = false;
        $env->variables['b'] = false;
        $this->assertEquals(false, $obj->execute($env));
        $env->variables['b'] = true;
        $this->assertEquals(true, $obj->execute($env));
    }

    public function testNot()
    {
        $obj = $this->parse("!a");
        $env = new Environment();
        $env->variables['a'] = false;
        $this->assertEquals(true, $obj->execute($env));
        $env->variables['a'] = true;
        $this->assertEquals(false, $obj->execute($env));


    }

    public function testDoubleNot()
    {
        $obj = $this->parse("!!a");
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
        $obj = $this->parse("notExisting");
        $env = new Environment();
        $env->allowUndefined = false;

        $this->expectException(\MKrawczyk\Mpts\MptsExecutionError::class);
        $this->expectExceptionMessageMatches('/Undefined variable: notExisting/');
        $this->expectExceptionMessageMatches('/file.mpts:1:0/');
        $obj->execute($env);
    }

    public function testNotExistingVariableAllowUndefined()
    {
        $obj = $this->parse("notExisting");
        $env = new Environment();
        $env->allowUndefined = true;
        $this->assertNull($obj->execute($env));
    }

    public function testNotExistingProperty()
    {
        $obj = $this->parse("a.b.c");
        $env = new Environment();
        $env->allowUndefined = false;
        $env->variables['a'] = (object)[];
        $this->expectException(\MKrawczyk\Mpts\MptsExecutionError::class);
        $this->expectExceptionMessageMatches('/Undefined property: b/');
        $this->expectExceptionMessageMatches('/file.mpts:1:2/');
        $obj->execute($env);
    }

    public function testNotExistingPropertyAllowUndefined()
    {
        $obj = $this->parse("a.b.c");
        $env = new Environment();
        $env->allowUndefined = true;
        $env->variables['a'] = (object)[];
        $this->assertNull($obj->execute($env));
    }

    public function testNotExistingPropertyNullableOperator()
    {
        $obj = $this->parse("a?.b.c");
        $env = new Environment();
        $env->allowUndefined = false;
        $env->variables['a'] = (object)[];
        $this->assertNull($obj->execute($env));
    }

    public function testBadTypeMethodCall()
    {
        $obj = $this->parse("a.b()");
        $env = new Environment();
        // set variable to non-callable to trigger method-call-on-non-method
        $env->variables['a'] = (object)[];
        $this->expectException(\MKrawczyk\Mpts\MptsExecutionError::class);
        $this->expectExceptionMessageMatches('/method call on non method/');
        $this->expectExceptionMessageMatches('/file\\.mpts:1:3/');
        $obj->execute($env);
    }

    public function testNotExistingMethodCall()
    {
        $obj = $this->parse("a.c()");
        $env = new Environment();
        $env->variables['a'] = (object)['b' => (object)[]];
        $this->expectException(\MKrawczyk\Mpts\MptsExecutionError::class);
        $this->expectExceptionMessageMatches('/method don\'t exists/');
        $this->expectExceptionMessageMatches('/file\\.mpts:1:3/');
        $obj->execute($env);
    }

    public function testExceptionInsideMethodCall()
    {
        $obj = $this->parse("a()");
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
