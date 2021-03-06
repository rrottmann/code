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
namespace APF\modules\socialbookmark\biz;

/**
 * Represents a single bookmark service (e.g. google, technorati, ...).
 *
 * @author Christian W. Schafer
 * @version
 * Version 0.1, 02.06.2007<br />
 */
class SocialBookmarkItem {

   /**
    * Basis-URL des Bookmark-Services.
    *
    * @var string $serviceBaseUrl
    */
   private $serviceBaseUrl;

   /**
    * Name of the url parameter for the url to bookmark.
    *
    * @var string $urlParamName
    */
   private $urlParamName;

   /**
    * Name of the utl parameter for the title to bookmark.
    *
    * @var string $titleParamName
    */
   private $titleParamName;

   /**
    * Title of the bookmark entry (e.g. used as link title or alt text).
    *
    * @var string $title
    */
   private $title;

   /**
    * Name of the bookmark icon without it's extension.
    *
    * @var string $imageUrl
    */
   private $imageUrl;

   /**
    * File extension of the bookmark icon.
    *
    * @var string $imageExt
    */
   private $imageExt;

   /**
    * @param string $baseURL Base url of the bookmark service.
    * @param string $bookmarkURL Name of the url parameter for the url to bookmark.
    * @param string $bookmarkTitle Name of the url parameter of the title.
    * @param string $title Title of the bookmark entry (e.g. used as link title or alt text).
    * @param string $imageURL Name of the bookmark icon without it's extension.
    * @param string $imageExt File extension of the bookmark icon.
    *
    * @author Christian W. Schäfer
    * @version
    * Version 0.1, 02.06.2007<br />
    */
   public function __construct($baseURL, $bookmarkURL, $bookmarkTitle, $title, $imageURL, $imageExt) {
      $this->serviceBaseUrl = $baseURL;
      $this->urlParamName = $bookmarkURL;
      $this->titleParamName = $bookmarkTitle;
      $this->title = $title;
      $this->imageUrl = $imageURL;
      $this->imageExt = $imageExt;
   }

   public function getServiceBaseUrl() {
      return $this->serviceBaseUrl;
   }

   public function getUrlParamName() {
      return $this->urlParamName;
   }

   public function getTitleParamName() {
      return $this->titleParamName;
   }

   public function getTitle() {
      return $this->title;
   }

   public function getImageUrl() {
      return $this->imageUrl;
   }

   public function getImageExt() {
      return $this->imageExt;
   }

}
