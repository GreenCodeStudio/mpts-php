<?php

use MKrawczyk\Mpts\Environment;
use MKrawczyk\Mpts\Parser\XMLParser;
use MKrawczyk\Mpts\Nodes\TDocumentFragment;
use MKrawczyk\Mpts\Nodes\TText;
use MKrawczyk\Mpts\Nodes\TElement;
use PHPUnit\Framework\TestCase;

class ExecutionTest extends TestCase
{

    private function fragmentToHtml(DOMDocumentFragment $fragment)
    {
        return $fragment->ownerDocument->saveHtml($fragment);
    }

    public function testBasicText()
    {
        $obj = XMLParser::Parse("Hello, world!");
        $env = new Environment();
        $env->document = new DOMDocument();
        $result = $obj->execute($env);
        $this->assertEquals("Hello, world!", $this->fragmentToHtml($result));
    }

    public function testEncodedText()
    {
        $obj = XMLParser::Parse("&lt;&#65;&#x0042;&copy;&amp;&gt;");
        $env = new Environment();
        $env->document = new DOMDocument();
        $result = $obj->execute($env);
        $this->assertEquals("<AB©&>", $result->textContent);
    }

    public function testEncodedTextFromVariable()
    {
        $obj = XMLParser::Parse("{{element}}");
        $env = new Environment();
        $env->document = new DOMDocument();
        $env->variables['element'] = "<div></div>";
        $result = $obj->execute($env);
        $this->assertEquals("<div></div>", $result->textContent);
    }

    public function testEncodedHtmlFromVariable()
    {
        $obj = XMLParser::Parse("<<element>>");
        $env = new Environment();
        $env->document = new DOMDocument();
        $env->variables['element'] = "<div>text</div>";
        $result = $obj->execute($env);
        $this->assertEquals("text", $result->textContent);
        $this->assertEquals("div", $result->firstChild->tagName);
    }

    public function testBasicElement()
    {
        $obj = XMLParser::Parse("<br/>");
        $env = new Environment();
        $env->document = new DOMDocument();
        $result = $obj->execute($env);
        $this->assertEquals("<br>", $this->fragmentToHtml($result));
    }

    public function testElementsInside()
    {
        $obj = XMLParser::Parse("<div><p><strong></strong><span></span></p></div>");
        $env = new Environment();
        $env->document = new DOMDocument();
        $result = $obj->execute($env);
        $this->assertEquals("<div><p><strong></strong><span></span></p></div>", $this->fragmentToHtml($result));
    }

    public function testBasicHtml()
    {
        $obj = XMLParser::Parse("<div>&#65;&#x0042;{{c}}</div>");
        $env = new Environment();
        $env->document = new DOMDocument();
        $env->variables['c'] = "C";
        $result = $obj->execute($env);
        $this->assertEquals("<div>ABC</div>", $this->fragmentToHtml($result));
    }

    public function testElementWithAttributes()
    {
        $obj = XMLParser::Parse("<div a=\"1\" b='2' c=3 d=d e=(e)></div>");
        $env = new Environment();
        $env->document = new DOMDocument();
        $env->variables['d'] = 4;
        $env->variables['e'] = 5;
        $result = $obj->execute($env);
        $this->assertEquals('<div a="1" b="2" c="3" d="4" e="5"></div>', $this->fragmentToHtml($result));
    }

    public function testIfElse()
    {
        $obj = XMLParser::Parse('<:if condition=(v==1)>a</:if><:else-if condition=(v==2)>b</:else-if><:else>c</:else>');
        $env = new Environment();
        $env->document = new DOMDocument();

        $env->variables['v'] = 1;
        $result1 = $obj->execute($env);
        $this->assertEquals('a', $this->fragmentToHtml($result1));

        $env->variables['v'] = 2;
        $result1 = $obj->execute($env);
        $this->assertEquals('b', $this->fragmentToHtml($result1));

        $env->variables['v'] = 3;
        $result1 = $obj->execute($env);
        $this->assertEquals('c', $this->fragmentToHtml($result1));
    }

    public function testIRealExample()
    {
        $obj = XMLParser::Parse('<:if condition=canAdd><a class="button" href="/PalletMovement/add"><span class="icon-add"></span>Dodaj</a></:if>');
        $env = new Environment();
        $env->document = new DOMDocument();

        $env->variables['canAdd'] = true;
        $result1 = $obj->execute($env);
        $this->assertEquals('<a class="button" href="/PalletMovement/add"><span class="icon-add"></span>Dodaj</a>', $this->fragmentToHtml($result1));

    }

    public function testLoop()
    {
        $obj = XMLParser::Parse("<:loop count=10>b</:loop>");
        $env = new Environment();
        $env->document = new DOMDocument();
        $result = $obj->execute($env);
        $this->assertEquals('bbbbbbbbbb', $this->fragmentToHtml($result));
    }

    public function testLoopByVariable()
    {
        $obj = XMLParser::Parse("<:loop count=a>b</:loop>");
        $env = new Environment();
        $env->document = new DOMDocument();
        $env->variables['a'] = 3;
        $result = $obj->execute($env);
        $this->assertEquals('bbb', $this->fragmentToHtml($result));
    }

    public function testForeachBasic()
    {
        $obj = XMLParser::Parse("<:foreach collection=a>b</:foreach>");
        $env = new Environment();
        $env->document = new DOMDocument();
        $env->variables['a'] = [1, 2, 3, 4, 5];
        $result = $obj->execute($env);
        $this->assertEquals('bbbbb', $this->fragmentToHtml($result));
    }

    public function testForeachAdvanced()
    {
        $obj = XMLParser::Parse("<:foreach collection=a item=b key=c><div>{{c}}:{{b}}</div></:foreach>");
        $env = new Environment();
        $env->document = new DOMDocument();
        $env->variables['a'] = ['a', 'b', 'c', 'd', 'e'];
        $result = $obj->execute($env);
        $this->assertEquals('<div>0:a</div><div>1:b</div><div>2:c</div><div>3:d</div><div>4:e</div>', $this->fragmentToHtml($result));
    }

    public function testForeachIfFalse()
    {
        $obj = XMLParser::Parse("<:foreach collection=a><:if condition=false>A</:if></:foreach>");
        $env = new Environment();
        $env->document = new DOMDocument();
        $env->variables['a'] = [1, 2, 3, 4, 5];
        $result = $obj->execute($env);
        $this->assertEquals('', $this->fragmentToHtml($result));
    }

    public function testAttributeConcat()
    {
        $obj = XMLParser::Parse("<div ab=\"cd\":x:\"gh\"/>");
        $env = new Environment();
        $env->document = new DOMDocument();
        $env->variables['x'] = 'ef';
        $result = $obj->execute($env);
        $this->assertEquals("<div ab=\"cdefgh\"></div>", $this->fragmentToHtml($result));
    }

    public function testDisabled()
    {
        $obj = XMLParser::Parse("<input disabled=(a==1) /><input disabled=(a==2) />");
        $env = new Environment();
        $env->document = new DOMDocument();
        $env->variables['a'] = 1;
        $result = $obj->execute($env);
        $this->assertEquals("<input disabled><input>", $this->fragmentToHtml($result));

    }

    public function testEntities()
    {
        $obj = XMLParser::Parse("<div class=\"&quot;&amp;&lt;&gt;&apos;\">&#x61;&#98;</div>");
        $env = new Environment();
        $env->document = new DOMDocument();
        $result = $obj->execute($env);
        $this->assertEquals($result->firstChild->getAttribute('class'), "\"&<>'");
        $this->assertEquals($result->textContent, "ab");
    }

    public function testXmlDeclaration()
    {
        $obj = XMLParser::Parse("<?xml version=\"1.0\" encoding=\"UTF-8\"?><root/>");
        $env = new Environment();
        $env->document = new DOMDocument();
        $result = $obj->execute($env);
        $this->assertEquals("root",  $result->childNodes[0]->tagName);
        $this->assertEmpty($result->childNodes[0]->childNodes);
    }

    public function testNonExistingVariable()
    {
        $this->expectExceptionMessageMatches('/Undefined variable: abc/');
        $this->expectExceptionMessageMatches('/file\.mpts:1:2/');
        $obj = XMLParser::Parse("{{abc}}", "file.mpts");
        $env = new Environment();
        $env->document = new DOMDocument();
        $obj->execute($env);
    }

    public function testExceptionInsideExpression()
    {
        $this->expectExceptionMessageMatches('/inside method error/');
        $this->expectExceptionMessageMatches('/file\.mpts:1:5/');

        $obj = XMLParser::Parse("{{a.b()}}", "file.mpts");
        $env = new Environment();
        $env->document = new DOMDocument();
        $env->variables['a'] = new class {
            public function b()
            {
                throw new \Exception("inside method error");
            }
        };

        $obj->execute($env);
    }
}
