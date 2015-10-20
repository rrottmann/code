<?php
/**
 * <!--
 * This file is part of the adventure php framework (APF) published under
 * http://adventure-php-framework.org.
 *
 * The APF is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The APF is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with the APF. If not, see http://www.gnu.org/licenses/lgpl-3.0.txt.
 * -->
 */
namespace APF\tests\suites\core\pagecontroller;

use APF\core\expression\taglib\ExpressionEvaluationTag;
use APF\core\loader\RootClassLoader;
use APF\core\loader\StandardClassLoader;
use APF\core\pagecontroller\Document;
use APF\core\pagecontroller\DomNode;
use APF\core\pagecontroller\ParserException;
use APF\core\pagecontroller\PlaceHolderTag;
use APF\core\pagecontroller\TemplateTag;
use APF\modules\usermanagement\pres\documentcontroller\registration\RegistrationController;
use Exception;
use InvalidArgumentException;
use ReflectionMethod;

/**
 * Tests the <em>Document::getTemplateFilePath()</em> regarding class loader usage.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 27.02.2014<br />
 */
class DocumentTest extends \PHPUnit_Framework_TestCase {

   const VENDOR = 'VENDOR';
   const SOURCE_PATH = '/var/www/html/src';

   public function testWithNormalNamespace() {

      $method = $this->getFilePathMethod();
      $document = new Document();

      $filePath = $method->invokeArgs($document, [self::VENDOR . '\foo', 'bar']);
      $this->assertEquals(self::SOURCE_PATH . '/foo/bar.html', $filePath);

      $filePath = $method->invokeArgs($document, [self::VENDOR . '\foo\bar', 'baz']);
      $this->assertEquals(self::SOURCE_PATH . '/foo/bar/baz.html', $filePath);

   }

   /**
    * @return ReflectionMethod The <em>APF\core\pagecontroller\Document::getTemplateFilePath()</em> method.
    */
   private function getFilePathMethod() {
      $method = new ReflectionMethod(Document::class, 'getTemplateFilePath');
      $method->setAccessible(true);

      return $method;
   }

   public function testWithVendorOnly() {
      $filePath = $this->getFilePathMethod()->invokeArgs(new Document(), [self::VENDOR, 'foo']);
      $this->assertEquals(self::SOURCE_PATH . '/foo.html', $filePath);
   }

   public function testGetChildNode() {
      $doc = new TemplateTag();
      $doc->setContent('<html:template name="foo">bar</html:template>');
      $doc->onParseTime();
      $template = $doc->getChildNode('name', 'foo', TemplateTag::class);
      $this->assertNotNull($template);
      $this->assertEquals('bar', $template->getContent());

      // ensure that a reference is returned instead of a clone or copy
      $children = $doc->getChildren();
      $this->assertEquals(
            spl_object_hash($template),
            spl_object_hash($children[array_keys($children)[0]])
      );
   }

   public function testGetChildNodeWithException() {
      $this->setExpectedException(InvalidArgumentException::class);
      $doc = new Document();
      $doc->onParseTime();
      $doc->getChildNode('foo', 'bar', Document::class);
   }

   public function testGetChildNodes() {
      $doc = new TemplateTag();
      $doc->setContent('<html:placeholder name="foo" /><html:placeholder name="foo" /><html:placeholder name="foo" />');
      $doc->onParseTime();
      /* @var $placeHolders DomNode[] */
      $placeHolders = $doc->getChildNodes('name', 'foo', PlaceHolderTag::class);
      $this->assertEquals(3, count($placeHolders));
      $this->assertEquals('foo', $placeHolders[0]->getAttribute('name'));

      // ensure that a reference is returned instead of a clone or copy
      $children = $doc->getChildren();
      $keys = array_keys($children);

      for ($i = 0; $i < 3; $i++) {
         $this->assertEquals(
               spl_object_hash($placeHolders[$i]),
               spl_object_hash($children[$keys[$i]])
         );
      }
   }

   public function testGetChildNodesWithException() {
      $this->setExpectedException('\InvalidArgumentException');
      $doc = new Document();
      $doc->getChildNodes('foo', 'bar', Document::class);
   }

   public function testDocumentControllerParsingTest() {

      $controllerClass = RegistrationController::class;

      // rear cases
      $this->executeControllerTest('<@controller class="' . $controllerClass . '"@>', $controllerClass);
      $this->executeControllerTest('<@controller class="' . $controllerClass . '" @>', $controllerClass);
      $this->executeControllerTest('<@controller class="' . $controllerClass . '"' . "\n" . '@>', $controllerClass);
      $this->executeControllerTest('<@controller class="' . $controllerClass . '"' . "\n" . ' @>', $controllerClass);
      $this->executeControllerTest('<@controller class="' . $controllerClass . '"  ' . "\n" . '  @>', $controllerClass);
      $this->executeControllerTest('<@controller class="' . $controllerClass . '"  ' . "\n\r" . '  @>', $controllerClass);

      // front cases
      $this->executeControllerTest('<@controller' . "\n" . 'class="' . $controllerClass . '" @>', $controllerClass);
      $this->executeControllerTest('<@controller' . " \n" . 'class="' . $controllerClass . '" @>', $controllerClass);
      $this->executeControllerTest('<@controller' . " \n" . '   class="' . $controllerClass . '" @>', $controllerClass);

      // mixed
      $this->executeControllerTest('<@controller' . "\n" . 'class="' . $controllerClass . '"@>', $controllerClass);
      $this->executeControllerTest('<@controller' . "\n" . 'class="' . $controllerClass . '"' . "\n" . '@>', $controllerClass);
      $this->executeControllerTest('<@controller' . " \n" . 'class="' . $controllerClass . '"' . "\n" . '@>', $controllerClass);
      $this->executeControllerTest('<@controller' . " \n" . '   class="' . $controllerClass . '" ' . "\n" . '@>', $controllerClass);

      $this->executeControllerTest('   <@controller' . " \n" . '   class="' . $controllerClass . '" ' . "\n" . '   @>', $controllerClass);
      $this->executeControllerTest('   <@controller' . " \n\r" . '   class="' . $controllerClass . '" ' . "\n\r" . '   @>', $controllerClass);

   }

   protected function executeControllerTest($content, $controllerClass) {

      $method = new ReflectionMethod(Document::class, 'extractDocumentController');
      $method->setAccessible(true);

      $doc = new Document();
      $doc->setContent($content);
      $method->invoke($doc);
      $this->assertEquals($controllerClass, get_class($doc->getDocumentController()));

   }

   public function testTagClosingSignInAttribute() {

      $doc = new TemplateTag();
      $doc->setData('model', [new TestDataModel(), new TestDataModel()]);

      $expressionOne = 'model[0]->getFoo()';
      $expressionTwo = 'model[1]->getBar()';
      $expressionThree = 'model[0]->getBaz()->getBar()';
      $expressionFour = 'model[1]->getBaz()->getBaz()->getFoo()';

      $doc->setContent('<core:addtaglib
   class="APF\core\expression\taglib\ExpressionEvaluationTag"
   prefix="dyn"
   name="expr"
/>
<dyn:expr
   name="one"
   expression="' . $expressionOne . '"
/>

<dyn:expr name="two" expression="' . $expressionTwo . '">
   Bar Baz
</dyn:expr>

<dyn:expr
   name="three"
   expression="' . $expressionThree . '"/>

<dyn:expr name="four" expression="' . $expressionFour . '"/>');

      $doc->onParseTime();

      $expressionNodeOne = $doc->getChildNode('name', 'one', ExpressionEvaluationTag::class);
      $expressionNodeTwo = $doc->getChildNode('name', 'two', ExpressionEvaluationTag::class);
      $expressionNodeThree = $doc->getChildNode('name', 'three', ExpressionEvaluationTag::class);
      $expressionNodeFour = $doc->getChildNode('name', 'four', ExpressionEvaluationTag::class);

      $this->assertEquals($expressionOne, $expressionNodeOne->getAttribute('expression'));
      $this->assertEquals($expressionTwo, $expressionNodeTwo->getAttribute('expression'));
      $this->assertEquals($expressionThree, $expressionNodeThree->getAttribute('expression'));
      $this->assertEquals($expressionFour, $expressionNodeFour->getAttribute('expression'));

      $this->assertEquals('foo', $expressionNodeOne->transform());
      $this->assertEquals('bar', $expressionNodeTwo->transform());
      $this->assertEquals('bar', $expressionNodeThree->transform());
      $this->assertEquals('foo', $expressionNodeFour->transform());

   }

   public function testInvalidTemplateSyntaxWithTagClosingSignInAttribute1() {

      $this->setExpectedException(ParserException::class);

      $doc = new TemplateTag();
      $doc->setContent('<html:placeholder name="tes>t"/');
      $doc->onParseTime();

   }

   public function testInvalidTemplateSyntaxWithTagClosingSignInAttribute2() {

      $this->setExpectedException(ParserException::class);

      $doc = new TemplateTag();
      $doc->setContent('<html:placeholder name="test" /');
      $doc->onParseTime();

   }

   public function testControllerAccessFromDocument() {

      $doc = new Document();
      $doc->setContent('<@controller class="APF\tests\suites\core\pagecontroller\TestDocumentController"@>
<core:addtaglib
   prefix="read-from"
   name="controller-tag"
   class="APF\tests\suites\core\pagecontroller\ReadValueFromControllerTag"
/>
<read-from:controller-tag />');

      $extractDocConMethod = new ReflectionMethod(Document::class, 'extractDocumentController');
      $extractDocConMethod->setAccessible(true);
      $extractDocConMethod->invoke($doc);

      $extractTagLibsMethod = new ReflectionMethod(Document::class, 'extractTagLibTags');
      $extractTagLibsMethod->setAccessible(true);
      $extractTagLibsMethod->invoke($doc);

      $result = $doc->transform();

      $this->assertEquals(TestDocumentController::VALUE, trim($result));

   }

   /**
    * Tests whether the parser ignores an HTML comment such as <em>&lt;!-- foo:bar --&gt;</em> going
    * through the document.
    * <p/>
    * See http://tracker.adventure-php-framework.org/view.php?id=238 for details.
    */
   public function testHtmlCommentWithTagNotation() {

      $doc = new Document();
      $doc->setContent('This is the content of a document with tags and comments:

<!-- app:footer -->

This is text after a comment...

<html:placeholder name="foo" />

This is text after a place holder...
');

      try {
         $this->getParserMethod()->invoke($doc);

         $placeHolder = $doc->getChildNode('name', 'foo', PlaceHolderTag::class);
         $this->assertTrue($placeHolder instanceof PlaceHolderTag);
      } catch (Exception $e) {
         $this->fail('Parsing comments failed. Message: ' . $e->getMessage());
      }

   }

   /**
    * @return ReflectionMethod
    */
   protected function getParserMethod() {
      $method = new ReflectionMethod(Document::class, 'extractTagLibTags');
      $method->setAccessible(true);

      return $method;
   }

   /**
    * Tests parser capabilities with <em>&lt;li&gt;FOO:</em> statements in e.g. HTML lists.
    */
   public function testClosingBracket() {

      $doc = new Document();
      $doc->setContent('<p>
   This is the content of a document with tags and lists:
</p>
<ul>
   <li>Foo: Foo is the first part of the &quot;foo bar&quot; phrase.</li>
   <li>Bar: Bar is the second part of the &quot;foo bar&quot; phrase.</li>
</ul>
<p>
 This is text after a list...
</p>
<html:placeholder name="foo" />
<p>
   This is text after a place holder...
</p>
');

      try {
         $this->getParserMethod()->invoke($doc);

         $placeHolder = $doc->getChildNode('name', 'foo', PlaceHolderTag::class);
         $this->assertTrue($placeHolder instanceof PlaceHolderTag);
      } catch (Exception $e) {
         $this->fail('Parsing lists failed. Message: ' . $e->getMessage());
      }

   }

   /**
    * Tests whether the parser ignores "normal" HTML code with colons (":") in tag attributes.
    * <p/>
    * See http://tracker.adventure-php-framework.org/view.php?id=266 for details.
    */
   public function testColonsInTagAttributes() {

      $doc = new Document();
      $doc->setContent(
            '<p>
   This is static content...
</p>
<p>
   To quit your session, please <a href="/?:action=logout">Logout</a>
</p>
<p>
   This is static content...
</p>'
      );

      try {
         $this->getParserMethod()->invoke($doc);
         $this->assertEmpty($doc->getChildren());
      } catch (Exception $e) {
         $this->fail('Parsing HTML failed. Message: ' . $e->getMessage());
      }
   }

   public function testTransformation() {

      $doc = new Document();
      $doc->setContent('<html:placeholder name="test" />');

      $method = new ReflectionMethod('APF\core\pagecontroller\Document', 'extractTagLibTags');
      $method->setAccessible(true);
      $method->invoke($doc);

      $expected = 'foo';

      /* @var $placeHolder PlaceHolderTag */
      $placeHolder = $doc->getChildNode('name', 'test', 'APF\core\pagecontroller\PlaceHolderTag');
      $placeHolder->setContent($expected);

      $this->assertEquals($expected, $doc->transform());

   }

   /**
    * Test exception raised with not-existing place holder specified.
    */
   public function testSetPlaceHolder1() {
      $this->setExpectedException(InvalidArgumentException::class);
      $this->getTemplateWithPlaceHolder()->setPlaceHolder('foo', 'bar');
   }

   protected function getTemplateWithPlaceHolder($content = '<html:placeholder name="test"/>') {
      $doc = new TemplateTag();
      $doc->setContent($content);
      $doc->onParseTime();

      return $doc;
   }

   /**
    * Test simple existing place holder setting.
    */
   public function testSetPlaceHolder2() {
      $template = $this->getTemplateWithPlaceHolder();
      $expected = 'foo';
      $template->setPlaceHolder('test', $expected);
      $template->transformOnPlace();
      $this->assertEquals($expected, $template->transform());
   }

   /**
    * Test multiple place holders within one document.
    */
   public function testSetPlaceHolder3() {
      $template = $this->getTemplateWithPlaceHolder('<html:placeholder name="test"/><html:placeholder name="test"/>');
      $expected = 'foo';
      $template->setPlaceHolder('test', $expected);
      $template->transformOnPlace();
      $this->assertEquals($expected . $expected, $template->transform());
   }

   /**
    * Test place holder appending.
    */
   public function testSetPlaceHolder4() {
      $template = $this->getTemplateWithPlaceHolder();
      $expected = 'foo';
      $template->setPlaceHolder('test', $expected, true);
      $template->setPlaceHolder('test', $expected, true);
      $template->transformOnPlace();
      $this->assertEquals($expected . $expected, $template->transform());
   }

   protected function setUp() {
      RootClassLoader::addLoader(new StandardClassLoader(self::VENDOR, self::SOURCE_PATH));
   }

}
