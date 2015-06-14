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
use PhpParser\Comment\Doc;

class FileRefComment extends NodeVisitorAbstract {

	/**
	 * @var string
	 */
	protected $referencedFile;
	
	/**
	 * @param string $referencedFile
	 */
	public function __construct($referencedFile){
		$this->setReferencedFile($referencedFile);
	}
	
	/**
	 * @param string $referencedFile
	 */
	public function setReferencedFile($referencedFile){
		$this->referencedFile = $referencedFile;
	}
	
	/**
	 * @return string
	 */
	public function getReferencedFile(){
		return $this->referencedFile;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \PhpParser\NodeVisitorAbstract::enterNode()
	 */
	public function enterNode(Node $node) {				
		if($node->getType()=='Stmt_Namespace'){	
			$comments = $node->getAttributes()['comments'];
			$string = '/*@referenced-file:('.$this->getReferencedFile().')*/';
			$found = false;
			foreach($comments AS $comment){
				if($comment->getText()==$string){
					$found = true;
				}
			}
			if(!$found){
				$doc = new Doc($string);
				array_unshift($comments,$doc);
				$node->setAttribute('comments',$comments);
			}						
		}	
	}
	
}