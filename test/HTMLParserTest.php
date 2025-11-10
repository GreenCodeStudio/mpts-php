<?php


use MKrawczyk\Mpts\Nodes\TDocumentFragment;
use MKrawczyk\Mpts\Nodes\TElement;
use MKrawczyk\Mpts\Nodes\TText;
use MKrawczyk\Mpts\Parser\HTMLParser;

include_once 'UniParserTest.php';

class HTMLParserTest extends UniParserTest
{
    protected function parse(string $input, ?string $fileName = null): TDocumentFragment
    {
        return HTMLParser::Parse($input, $fileName);
    }

    public function testNotClosedElement()
    {
        $obj = $this->parse("<div>");
        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("div", $obj->children[0]->tagName);
    }

    public function testBadOrderOfClose()
    {
        $this->expectExceptionMessageMatches("/There is no opened elements, <strong> closed/");
        $this->expectExceptionMessageMatches("/file.mpts:0:11//");
        $obj = $this->parse("<span><strong></span></strong>", "file.mpts");
    }

    /*
     *     describe('auto closing tags', async () => {
        const tags = [
            'area',
            'base',
            'br',
            'col',
            'embed',
            'hr',
            'img',
            'input',
            'link',
            'meta',
            'param',
            'source',
            'track',
            'wbr'
        ];
        for (const tag of tags) {
            it('auto closing <' + tag + '>', async () => {
                const tagCase = tag.split().map(c => Math.random() > 0.5 ? c.toUpperCase() : c.toLowerCase()).join('');

                const obj = HTMLParser.Parse("<" + tagCase + ">after");
                expect(obj).to.be.instanceOf(TDocumentFragment);
                expect(obj.children[0]).to.be.instanceOf(TElement);
                expect(obj.children[0].tagName).to.be.equal(tagCase);
                expect(obj.children[1]).to.be.instanceOf(TText);
                expect(obj.children[1].text).to.be.equal(after);
            });
        }
    });
     */

    public function testAutoClosingTags()
    {
        $tags = [
            'area',
            'base',
            'br',
            'col',
            'embed',
            'hr',
            'img',
            'input',
            'link',
            'meta',
            'param',
            'source',
            'track',
            'wbr'
        ];
        foreach ($tags as $tag) {
            $tagCase = '';
            for ($i = 0; $i < strlen($tag); $i++) {
                $tagCase .= (rand(0, 1) ? strtoupper($tag[$i]) : strtolower($tag[$i]));
            }
            $obj = $this->parse("<".$tagCase.">after");
            $this->assertInstanceOf(TDocumentFragment::class, $obj);
            $this->assertInstanceOf(TElement::class, $obj->children[0]);
            $this->assertEquals($tagCase, $obj->children[0]->tagName);
            $this->assertInstanceOf(TText::class, $obj->children[1]);
            $this->assertEquals("after", $obj->children[1]->text);
        }
    }

    public function testOmmitTagTypeDeep()
    {
        $obj = $this->parse("<div><div><div>");
        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("div", $obj->children[0]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[0]);
        $this->assertEquals("div", $obj->children[0]->children[0]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[0]->children[0]);
        $this->assertEquals("div", $obj->children[0]->children[0]->children[0]->tagName);
    }

    public function testOmmitTagTypeNotDeep()
    {
        $obj = $this->parse("<p><p><p>");
        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("p", $obj->children[0]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[1]);
        $this->assertEquals("p", $obj->children[1]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[2]);
        $this->assertEquals("p", $obj->children[2]->tagName);
    }

    public function testOmmitTagTypeMix()
    {
        $obj = $this->parse("<div><br><section>");
        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("div", $obj->children[0]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[0]);
        $this->assertEquals("br", $obj->children[0]->children[0]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[1]);
        $this->assertEquals("section", $obj->children[0]->children[1]->tagName);
    }

    /*
     *         it('p closed by parent', async () => {
            const obj = HTMLParser.Parse("<div><p>content</div>");
            expect(obj).to.be.instanceOf(TDocumentFragment);
            expect(obj.children[0]).to.be.instanceOf(TElement);
            expect(obj.children[0].tagName).to.be.equals("div");
            expect(obj.children[0].children[0]).to.be.instanceOf(TElement);
            expect(obj.children[0].children[0].tagName).to.be.equals("p");
            expect(obj.children[0].children[0].children[0]).to.be.instanceOf(TText);
            expect(obj.children[0].children[0].children[0].text).to.be.equal("content");
        });
     */
    public function testPClosedByParent()
    {
        $obj = $this->parse("<div><p>content</div>");
        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("div", $obj->children[0]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[0]);
        $this->assertEquals("p", $obj->children[0]->children[0]->tagName);
        $this->assertInstanceOf(TText::class, $obj->children[0]->children[0]->children[0]);
        $this->assertEquals("content", $obj->children[0]->children[0]->children[0]->text);
    }

    public function testPClosedByNextElement()
    {
        $options = ['address', 'article', 'aside', 'blockquote', 'dir', 'div', 'dl', 'fieldset', 'footer', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'hgroup', 'hr', 'menu', 'nav', 'ol', 'p', 'pre', 'section', 'table', 'ul'];
        foreach ($options as $option) {
            $obj = $this->parse("<p>content<".$option." />after");
            $this->assertInstanceOf(TDocumentFragment::class, $obj);
            $this->assertInstanceOf(TElement::class, $obj->children[0]);
            $this->assertEquals("p", $obj->children[0]->tagName);
            $this->assertInstanceOf(TElement::class, $obj->children[1]);
            $this->assertEquals($option, $obj->children[1]->tagName);
            $this->assertInstanceOf(TText::class, $obj->children[2]);
            $this->assertEquals('after', $obj->children[2]->text);
        }
    }

    public function testAutocloseByNextOccurence()
    {
        $options = ['p', 'optgroup', 'option'];
        foreach ($options as $option) {
            $obj = $this->parse("<".$option."><".$option.">");
            $this->assertInstanceOf(TDocumentFragment::class, $obj);
            $this->assertInstanceOf(TElement::class, $obj->children[0]);
            $this->assertEquals($option, $obj->children[0]->tagName);
            $this->assertInstanceOf(TElement::class, $obj->children[1]);
            $this->assertEquals($option, $obj->children[1]->tagName);
        }
    }

    public function tetOptgroup()
    {
        $obj = $this->parse("<select><option>1<optgroup><option>2<option>3</select>");
        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("select", $obj->children[0]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[0]);
        $this->assertEquals("option", $obj->children[0]->children[0]->tagName);
        $this->assertInstanceOf(TText::class, $obj->children[0]->children[0]->children[0]);
        $this->assertEquals("1", $obj->children[0]->children[0]->children[0]->text);
        $this->assertEquals("optgroup", $obj->children[0]->children[1]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[1]->children[0]);
        $this->assertEquals("option", $obj->children[0]->children[1]->children[0]->tagName);
        $this->assertInstanceOf(TText::class, $obj->children[0]->children[1]->children[0]->children[0]);
        $this->assertEquals("2", $obj->children[0]->children[1]->children[0]->children[0]->text);
        $this->assertEquals("option", $obj->children[0]->children[1]->children[1]->tagName);
        $this->assertInstanceOf(TText::class, $obj->children[0]->children[1]->children[1]->children[0]);
        $this->assertEquals("3", $obj->children[0]->children[1]->children[1]->children[0]->text);
    }

    public function testPNotClosedByNextElement()
    {
        $obj = $this->parse("<p>content<nonexisitng>subcontent</nonexisitng>");
        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("p", $obj->children[0]->tagName);
        $this->assertInstanceOf(TText::class, $obj->children[0]->children[0]);
        $this->assertEquals('content', $obj->children[0]->children[0]->text);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[1]);
        $this->assertEquals("nonexisitng", $obj->children[0]->children[1]->tagName);
        $this->assertInstanceOf(TText::class, $obj->children[0]->children[1]->children[0]);
        $this->assertEquals("subcontent", $obj->children[0]->children[1]->children[0]->text);
    }

    public function testTypeMix()
    {
        $obj = $this->parse("<div><br><section>");
        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("div", $obj->children[0]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[0]);
        $this->assertEquals("br", $obj->children[0]->children[0]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[1]);
        $this->assertEquals("section", $obj->children[0]->children[1]->tagName);
    }

    public function testOmmitTagTypeList()
    {
        $obj = $this->parse("<ul><li>one<li>two<li>three</ul>");
        $this->assertInstanceOf(TDocumentFragment::class, $obj);
        $this->assertInstanceOf(TElement::class, $obj->children[0]);
        $this->assertEquals("ul", $obj->children[0]->tagName);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[0]);
        $this->assertEquals("li", $obj->children[0]->children[0]->tagName);
        $this->assertEquals("one", $obj->children[0]->children[0]->children[0]->text);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[1]);
        $this->assertEquals("li", $obj->children[0]->children[1]->tagName);
        $this->assertEquals("two", $obj->children[0]->children[1]->children[0]->text);
        $this->assertInstanceOf(TElement::class, $obj->children[0]->children[2]);
        $this->assertEquals("li", $obj->children[0]->children[2]->tagName);
        $this->assertEquals("three", $obj->children[0]->children[2]->children[0]->text);
    }

    /*
     *     it('dt element ommiting end tag', async () => {
                // Test dt element followed by another dt element
                const obj1 = HTMLParser.Parse("<dl><dt>term1<dt>term2<dd>def</dd></dl>");
                expect(obj1).to.be.instanceOf(TDocumentFragment);
                expect(obj1.children[0]).to.be.instanceOf(TElement);
                expect(obj1.children[0].tagName).to.be.equals("dl");
                expect(obj1.children[0].children[0]).to.be.instanceOf(TElement);
                expect(obj1.children[0].children[0].tagName).to.be.equals("dt");
                expect(obj1.children[0].children[0].children[0]).to.be.instanceOf(TText);
                expect(obj1.children[0].children[0].children[0].text).to.be.equals("term1");
                expect(obj1.children[0].children[1]).to.be.instanceOf(TElement);
                expect(obj1.children[0].children[1].tagName).to.be.equals("dt");
                expect(obj1.children[0].children[1].children[0]).to.be.instanceOf(TText);
                expect(obj1.children[0].children[1].children[0].text).to.be.equals("term2");
                expect(obj1.children[0].children[2]).to.be.instanceOf(TElement);
                expect(obj1.children[0].children[2].tagName).to.be.equals("dd");
                expect(obj1.children[0].children[2].children[0]).to.be.instanceOf(TText);
                expect(obj1.children[0].children[2].children[0].text).to.be.equals("def");

                // Test dt element followed by dd element
                const obj2 = HTMLParser.Parse("<dl><dt>term<dd>def1<dd>def2</dd></dl>");
                expect(obj2).to.be.instanceOf(TDocumentFragment);
                expect(obj2.children[0]).to.be.instanceOf(TElement);
                expect(obj2.children[0].tagName).to.be.equals("dl");
                expect(obj2.children[0].children[0]).to.be.instanceOf(TElement);
                expect(obj2.children[0].children[0].tagName).to.be.equals("dt");
                expect(obj2.children[0].children[0].children[0]).to.be.instanceOf(TText);
                expect(obj2.children[0].children[0].children[0].text).to.be.equals("term");
                expect(obj2.children[0].children[1]).to.be.instanceOf(TElement);
                expect(obj2.children[0].children[1].tagName).to.be.equals("dd");
                expect(obj2.children[0].children[1].children[0]).to.be.instanceOf(TText);
                expect(obj2.children[0].children[1].children[0].text).to.be.equals("def1");
                expect(obj2.children[0].children[2]).to.be.instanceOf(TElement);
                expect(obj2.children[0].children[2].tagName).to.be.equals("dd");
                expect(obj2.children[0].children[2].children[0]).to.be.instanceOf(TText);
                expect(obj2.children[0].children[2].children[0].text).to.be.equals("def2");
            });
     */
    public function testDtElementOmmitingEndTag()
    {
        $obj1 = $this->parse("<dl><dt>term1<dt>term2<dd>def</dd></dl>");
        $this->assertInstanceOf(TDocumentFragment::class, $obj1);
        $this->assertInstanceOf(TElement::class, $obj1->children[0]);
        $this->assertEquals("dl", $obj1->children[0]->tagName);
        $this->assertInstanceOf(TElement::class, $obj1->children[0]->children[0]);
        $this->assertEquals("dt", $obj1->children[0]->children[0]->tagName);
        $this->assertInstanceOf(TText::class, $obj1->children[0]->children[0]->children[0]);
        $this->assertEquals("term1", $obj1->children[0]->children[0]->children[0]->text);
        $this->assertInstanceOf(TElement::class, $obj1->children[0]->children[1]);
        $this->assertEquals("dt", $obj1->children[0]->children[1]->tagName);
        $this->assertInstanceOf(TText::class, $obj1->children[0]->children[1]->children[0]);
        $this->assertEquals("term2", $obj1->children[0]->children[1]->children[0]->text);
        $this->assertInstanceOf(TElement::class, $obj1->children[0]->children[2]);
        $this->assertEquals("dd", $obj1->children[0]->children[2]->tagName);
        $this->assertInstanceOf(TText::class, $obj1->children[0]->children[2]->children[0]);
        $this->assertEquals("def", $obj1->children[0]->children[2]->children[0]->text);

        $obj2 = $this->parse("<dl><dt>term<dd>def1<dd>def2</dd></dl>");
        $this->assertInstanceOf(TDocumentFragment::class, $obj2);
        $this->assertEquals("dl", $obj2->children[0]->tagName);
        $this->assertEquals("dt", $obj2->children[0]->children[0]->tagName);
        $this->assertEquals("term", $obj2->children[0]->children[0]->children[0]->text);
        $this->assertEquals("dd", $obj2->children[0]->children[1]->tagName);
        $this->assertEquals("def1", $obj2->children[0]->children[1]->children[0]->text);
        $this->assertEquals("dd", $obj2->children[0]->children[2]->tagName);
        $this->assertEquals("def2", $obj2->children[0]->children[2]->children[0]->text);
    }

}
