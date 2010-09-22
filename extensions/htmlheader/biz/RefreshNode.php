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

   import('extensions::htmlheader::biz', 'HtmlNode');
   import('extensions::htmlheader::biz', 'MetaNode');
   import('tools::link', 'LinkHandler');

   /**
    * @namespace extensions::htmlheader::biz
    * @class RefreshNode
    *
    * Implements a meta node that introduces the browser to redirect
    * to another page after a given time.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 20.08.2010<br />
    */
   class RefreshNode extends HtmlNode implements MetaNode {

      /**
       * Receives information and configures node.
       * @param string $namespace Namespace of stylesheet
       * @param string $filename Name of stylesheet (without .css)
       * @param array $additionalParameters Optional. Array of url parameters.
       */
      public function __construct($target, $time, $additionalParameters = array()) {
         $this->setAttribute('http-equiv', 'refresh');
         $link = LinkHandler::generateLink($target, $additionalParameters);
         $this->setAttribute('content', $time . ';URL=' . $link);
      }

      public function getChecksum() {
         return md5($this->getAttribute('content'));
      }

      protected function getTagName() {
         return 'meta';
      }

   }
?>