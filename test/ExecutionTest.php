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
        $env->variables['c']="C";
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
}