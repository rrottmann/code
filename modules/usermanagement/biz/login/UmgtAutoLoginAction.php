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
namespace APF\modules\usermanagement\biz\login;

use APF\core\frontcontroller\AbstractFrontControllerAction;
use APF\core\service\APFService;
use APF\modules\usermanagement\biz\UmgtManager;
use APF\modules\usermanagement\biz\UmgtUserSessionStore;

/**
 * Automatically logs in a user using an authentication token stored in a secure long-living
 * cookie.
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 03.06.2011<br />
 */
class UmgtAutoLoginAction extends AbstractFrontControllerAction {

   /**
    * Defines the cookie name for the user's permanent auth token.
    *
    * @var string AUTO_LOGIN_COOKIE_NAME
    */
   const AUTO_LOGIN_COOKIE_NAME = 'umgt-auth-token';

   /**
    * Checks, whether the current request is for resource serving purposes or is a "true"
    * user request. If no, the action is considered out of order to not stress database and
    * resource delivery to much!
    * <p/>
    * Check is done by the ACCEPT HTTP header as this is a true sign whether the browser is
    * requesting an HTML page or is loading resources such as CSS or JS.
    *
    * @return bool True in case the action is to be considered active, false otherwise.
    */
   public function isActive() {
      return $this->getRequest()->isHtml();
   }

   public function run() {

      /* @var $sessionStore UmgtUserSessionStore */
      $sessionStore = $this->getServiceObject(UmgtUserSessionStore::class, [],
            APFService::SERVICE_TYPE_SESSION_SINGLETON);

      $appIdent = $this->getContext();
      $user = $sessionStore->getUser($appIdent);

      // try to log-in user from cookie
      if ($user === null) {

         /* @var $umgt UmgtManager */
         $umgt = $this->getDIServiceObject('APF\modules\usermanagement\biz', 'UmgtManager');

         $request = $this->getRequest();

         if (!$request->hasCookie(self::AUTO_LOGIN_COOKIE_NAME)) {
            return;
         }

         $cookie = $request->getCookie(self::AUTO_LOGIN_COOKIE_NAME);
         $authToken = $cookie->getValue();

         if ($authToken !== null) {
            $savedToken = $umgt->loadAuthTokenByTokenString($authToken);

            if ($savedToken !== null) {

               $user = $umgt->loadUserByAuthToken($savedToken);

               if ($user !== null) {
                  $sessionStore->setUser($appIdent, $user);
                  $cookie->setExpireTime(time() + $umgt->getAutoLoginCookieLifeTime());
                  $this->getResponse()->setCookie($cookie->setValue($savedToken->getToken()));
               }
            }
         }
      }
   }

}
