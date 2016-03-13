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
namespace APF\core\benchmark;

use InvalidArgumentException;

/**
 * This class implements the benchmark timer used for measurement of the core components
 * and your software. Must be used as a singleton to guarantee, that all benchmark tags
 * are included within the report. Usage (for each time!):
 * <pre>
 * $t = Singleton::getInstance(BenchmarkTimer::class);
 * $t->start('my_tag');
 * ...
 * $t->stop('my_tag');
 * </pre>
 * In order to create a benchmark report (typically at the end of your bootstrap file,
 * please note the following:
 * <pre>
 * $t = Singleton::getInstance(BenchmarkTimer::class);
 * echo $t->createReport();
 * </pre>
 *
 * @author Christian Achatz
 * @version
 * Version 0.1, 31.12.2006<br />
 * Version 0.2, 01.01.2007<br />
 * Version 0.3, 29.12.2009 (Refactoring due to new HTML markup for the process report.)<br />
 */
final class BenchmarkTimer {

   /**
    * @var StopWatch|OldStopWatch The stop watch instance.
    */
   private $stopWatch;

   /**
    * Constructor of the BenchmarkTimer. Initializes the root process.
    *
    * @author Christian Schäfer
    * @version
    * Version 0.1, 31.12.2006<br />
    */
   public function __construct() {
      $this->stopWatch = new StopWatch();
   }

   /**
    * Enables the benchmarker for measurement of the predefined points.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 10.01.2010<br />
    */
   public function enable() {
      $this->stopWatch->enable();
   }

   /**
    * Disables the benchmarker for measurement of the predefined points. This is often
    * important for performance reasons, because release 1.11 introduced onParseTime()
    * measurement, that could probably decrease the APF's performance!
    * <p />
    * Experiential tests proofed, that disabling the benchmarker can increase performance
    * from ~0.185s to ~0.138s, what is ~25%!
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 10.01.2010<br />
    */
   public function disable() {
      $this->stopWatch->disable();
   }

   /**
    * Sets the critical time. If the critical time is reached, the time is printed in red digits.
    *
    * @param float $time the critical time in seconds.
    *
    * @author Christian Schäfer
    * @version
    * Version 0.1, 31.12.2006<br />
    */
   public function setCriticalTime($time) {
      $this->stopWatch->setCriticalTime($time);
   }

   /**
    * Returns the critical time.
    *
    * @return float The critical time.
    *
    * @author Christian Schäfer
    * @version
    * Version 0.1, 31.12.2006<br />
    */
   public function getCriticalTime() {
      return $this->stopWatch->getCriticalTime();
   }

   /**
    * This method is used to starts a new benchmark timer.
    *
    * @param string $name The (unique!) name of the benchmark tag.
    *
    * @throws InvalidArgumentException In case the given name is null.
    *
    * @author Christian Schäfer
    * @version
    * Version 0.1, 31.12.2006<br />
    */
   public function start($name = null) {
      $this->stopWatch->start($name);
   }

   /**
    * Stops the benchmark timer, started with start().
    *
    * @param string $name The (unique!) name of the benchmark tag.
    *
    * @throws InvalidArgumentException In case the named process is not running.
    *
    * @author Christian Schäfer
    * @version
    * Version 0.1, 31.12.2006<br />
    */
   public function stop($name) {
      $this->stopWatch->stop($name);
   }

   /**
    * Generates the report of the recorded benchmark tags.
    *
    * @return string The HTML source code of the benchmark.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 31.12.2006<br />
    */
   public function createReport() {
      return $this->stopWatch->createReport();
   }

   /**
    * Returns the total process time recorded until the call to this method.
    * <p/>
    * You may use this method to add the total rendering time of an APF-based
    * application to your source code or any proprietary HTTP header.
    *
    * @return string The total processing time.
    *
    * @author Christian Achatz
    * @version
    * Version 0.1, 23.04.2012<br />
    */
   public function getTotalTime() {
      return $this->stopWatch->getTotalTime();
   }

}
