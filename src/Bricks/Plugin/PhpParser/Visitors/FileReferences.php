<?php
/**
 * Bricks Framework & Bricks CMS
 * http://bricks-cms.org
 *
 * @link https://github.com/bricks81/BricksPlugin
 * @license http://www.gnu.org/licenses/ (GPLv3)
 */
namespace Bricks\Plugin\PhpParser\Visitors;

use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;

class FileReferences extends NodeVisitorAbstract {

	/**
	 * @var string
	 */
	protected $classpath;
	
	/**
	 * @param string $classpath
	 */
	public function __construct($classpath){
		$this->setClasspath($classpath);
	}
	
	/**
	 * @return string
	 */
	public function getClasspath(){
		return $this->classpath;
	}
	
	/**
	 * @param string $classpath
	 */
	public function setClasspath($classpath){
		$this->classpath = $classpath;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \PhpParser\NodeVisitorAbstract::enterNode()
	 */
	public function enterNode(Node $node) { 
		if($node->getType()=='Scalar_MagicConst_Dir'){
			return new Node\Scalar\String_(dirname($this->getClasspath()));
		} elseif($node->getType()=='Scalar_MagicConst_File'){
			return new Node\Scalar\String_($this->getClasspath());
		}		
	}
	
}