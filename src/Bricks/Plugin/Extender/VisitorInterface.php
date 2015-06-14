<?php
/**
 * Bricks Framework & Bricks CMS
 * http://bricks-cms.org
 *
 * @link https://github.com/bricks81/BricksPlugin
 * @license http://www.gnu.org/licenses/ (GPLv3)
 */
namespace Bricks\Plugin\Extender;

use Bricks\Plugin\Extender;

interface VisitorInterface {
	
	public function extend(Extender $extender);
	
}