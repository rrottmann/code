<?php
/**
 * <!--
 * This file is part of the adventure php framework (APF) published under
 * https://adventure-php-framework.org.
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
namespace APF\extensions\apfelsms\biz\pages\decorators;

use APF\core\pagecontroller\APFObject;
use APF\extensions\apfelsms\biz\pages\SMSPage;
use APF\extensions\apfelsms\biz\SMSException;
use APF\tools\link\Url;

/**
 * @author  : Jan Wiese <jan.wiese@adventure-php-framework.org>
 * @version : v0.1 (06.06.12)
 *            v0.2 (23.09.12) Changed method signature of getDecoratorTypes and getAllDecorators to optional give-through arrays
 */
abstract class SMSAbstractPageDec extends APFObject implements SMSPageDec {


   /**
    * Page or PageDec object the decorator is wrapped around
    *
    * @var SMSPage $SMSPage
    */
   protected $SMSPage;


   /**
    * Decorator type name
    *
    * @var string $type
    */
   protected $type;


   /**
    * @var array $mapVars
    */
   public static $mapVars = [];


   /**
    * @param SMSPage $SMSPage
    */
   public function setPage(SMSPage $SMSPage) {


      $this->SMSPage = $SMSPage;
   }


   /**
    * @return SMSPage
    */
   public function getPage() {


      return $this->SMSPage;
   }


   /**
    * @param string $type
    */
   public function setDecType($type) {


      $this->type = $type;
   }


   /**
    * @return string
    */
   public function getDecType() {


      return $this->type;
   }


   /**
    * @param array $giveThrough
    *
    * @return array
    */
   public function getDecoratorTypes(array $giveThrough = []) {

      $giveThrough[] = $this->getDecType();

      $page = $this->SMSPage;

      if ($page instanceof SMSPageDec) {

         return $page->getDecoratorTypes($giveThrough);
      }

      return $giveThrough;
   }


   /**
    * @param array $giveThrough
    *
    * @return array
    */
   public function getAllDecorators(array $giveThrough = []) {


      $giveThrough[] = $this;

      $page = $this->SMSPage;

      if ($page instanceof SMSPageDec) {
         /**
          *
          * @var SMSPageDec $page
          */
         return $page->getAllDecorators($giveThrough);
      }

      return $giveThrough;
   }


   /**
    * @param $name
    * @param $arguments
    *
    * @return mixed
    */
   public function __call($name, $arguments) {


      return call_user_func_array(
            [$this->SMSPage, $name],
            $arguments
      );
   }


   /**
    * @param $name
    *
    * @return bool
    */
   public function providesDecMethod($name) {


      if (method_exists($this, $name)) {
         return true;
      }

      $page = $this->SMSPage;

      if ($page instanceof SMSPageDec) {

         /**
          * @var SMSPageDec $page
          */
         return $page->providesDecMethod($name);
      }

      return false;

   }


   /**
    * @return SMSPage
    */
   public function getPageWithoutDecorators() {


      /**
       * @var SMSPageDec $site
       */
      $site = $this->SMSPage;

      if (!($site instanceof SMSPageDec)) {
         /**
          * @var SMSPage $site
          */
         return $site;
      }

      return $site->getPageWithoutDecorators();
   }


   /**
    * @param array $data
    *
    * @throws SMSException
    */
   public function mapData(array $data) {


      foreach ($data AS $prop => $val) {

         if (property_exists($this, $prop)) { // check if property is applicable
            $this->$prop = $val;
         } elseif (is_array($val) && property_exists($this, $prop . 's')) { // try plural form, e.g. an XMl element name may be "requestParam" and belong to property "requestParams"
            $pluralProp = $prop . 's';
            $this->$pluralProp = $val;
         } else {
            throw new SMSException('[' . get_class($this) . '::mapData()] Mapper delivers data that is not applicable to ' . get_class($this) . ' object');
         }

      }
   }


   /* Give through methods */

   /**
    * @param $id
    *
    * @return mixed
    */
   public function setId($id) {


      return $this->SMSPage->setId($id);
   }


   /**
    * @return string Id of underlying SMSPage
    */
   public function getId() {


      return $this->SMSPage->getId();
   }


   /**
    * @param int $lvl
    */
   public function setLevel($lvl) {


      return $this->SMSPage->setLevel($lvl);
   }


   /**
    * @return int
    */
   public function getLevel() {


      return $this->SMSPage->getLevel();
   }


   /**
    * @return string Title of underlying SMSPage
    */
   public function getTitle() {


      return $this->SMSPage->getTitle();
   }


   /**
    * @param string $title
    */
   public function setTitle($title) {


      return $this->SMSPage->setTitle($title);
   }


   /**
    * @return string
    */
   public function getNavTitle() {


      return $this->SMSPage->getNavTitle();
   }


   /**
    * @param string $navTitle
    */
   public function setNavTitle($navTitle) {


      return $this->SMSPage->setNavTitle($navTitle);
   }


   /**
    * @return string[] CSS includes of underlying SMSPage
    */
   public function getCSS() {


      return $this->SMSPage->getCSS();
   }


   /**
    * @param string $css
    * @param string|null $media
    */
   public function addCSS($css, $media = null) {


      return $this->SMSPage->addCSS($css, $media);
   }


   /**
    * @return string[] JS includes of underlying SMSPage
    */
   public function getJS() {


      return $this->SMSPage->getJS();
   }


   /**
    * @param string $js
    */
   public function addJS($js) {


      return $this->SMSPage->addJS($js);
   }


   /**
    * @param Url $url
    *
    * @return string
    */
   public function getLink(Url $url) {


      return $this->SMSPage->getLink($url);
   }


   /**
    * @param Url $url
    *
    * @return Url
    */
   final public function setPageRequestParamInURL(Url $url) {


      return $this->SMSPage->setPageRequestParamInURL($url);
   }


   /**
    * @return string
    */
   public function getTemplateName() {


      return $this->SMSPage->getTemplateName();
   }


   /**
    * @return boolean Id of underlying SMSPage
    */
   public function isHidden() {


      return $this->SMSPage->isHidden();
   }


   /**
    * @return boolean
    */
   public function isAccessProtected() {


      return $this->SMSPage->isAccessProtected();
   }


   /**
    * @return boolean
    */
   public function isReference() {


      return $this->SMSPage->isReference();
   }


   /**
    * @return boolean
    */
   public function isCurrentPage() {


      return $this->SMSPage->isCurrentPage();
   }


   /**
    * @return boolean
    */
   public function isActive() {


      return $this->SMSPage->isActive();
   }


   /**
    * @return SMSPage
    */
   public function getParent() {


      return $this->SMSPage->getParent();
   }


   /**
    * @param $includeMe
    *
    * @return SMSPage[]
    */
   public function getSiblings($includeMe = false) {


      return $this->SMSPage->getSiblings($includeMe);
   }


   /**
    * @return SMSPage[]
    */
   public function getChildren() {


      return $this->SMSPage->getChildren();
   }


   /**
    * @return SMSPage
    */
   final public function getOuterPage() {


      return $this->SMSPage->getOuterPage();
   }

}
