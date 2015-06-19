<?php
/**
 * Bricks Framework & Bricks CMS
 * http://bricks-cms.org
 *
 * @link https://github.com/bricks81/BricksPlugin
 * @license http://www.gnu.org/licenses/ (GPLv3)
 */
namespace Bricks\Plugin;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\NodeTraverser;
use Bricks\Plugin\PhpParser\Visitors\FileReferences;
use Bricks\Plugin\PhpParser\Visitors\FileRefComment;

class Extender {
	
	/**
	 * @var array
	 */
	protected $nodes = array();
	
	/**
	 * @var array
	 */
	protected $eventized = array();
	
	/**
	 * @param $nodes
	 * @param string $classpath
	 */
	public function __construct($nodes,$classpath){		
		$this->nodes = $nodes;
		$traverser = new NodeTraverser(false);
		$visitor = new FileReferences($classpath);
		$traverser->addVisitor($visitor);
		$visitor = new FileRefComment($classpath);
		$traverser->addVisitor($visitor);
		$traverser->traverse($this->nodes);
	}
	
	/**
	 * @param string $namespace
	 * @param string $class
	 * @param string $method
	 */
	public function eventize($namespace,$class,$method){		
		if(!isset($eventized[$method])){
			
			// fetch namespace
			foreach($this->nodes AS $node){
				$node = $this->fetchNamespace($node,$namespace);
				if(false!==$node){
					break;
				}
			}			
			if(false==$node){
				return;
			}
			
			$node = $this->fetchClass($node,$class);			
			if(false==$node){
				return;
			}			
			
			$node = $this->fetchMethod($node,$method);			
			if(false==$node){
				return;
			}
			
			$lexer = new Lexer();
			$parser = new Parser($lexer);
			$printer = new Standard();
			
			$code = '<?php 
				$return = null;
				$params = get_defined_vars();
				$response = isset($GLOBALS["BricksPlugin/EventManager"])
					? $GLOBALS["BricksPlugin/EventManager"]->trigger(__METHOD__.".pre",$this,$params)
					: false;
				if($response && $response->stopped()){
					return $response->last();
				}
			?>';
			$first = $parser->parse($code);			
			
			$compact = '';
			foreach($node->params AS $paramNode){
				$compact .= '$'.$paramNode->name.',';
			}
			$compact = rtrim($compact,',');
			$code = '<?php
				$call = function('.(!empty($compact)?$compact:'').') {'.preg_replace('#^<\?php#ui','',$printer->prettyPrintFile($node->stmts)).'}; 
			?>';			
			$ret = $parser->parse($code);			
			$body = $ret;
			
			$code = '<?php 
				$return = $call('.(!empty($compact)?$compact:'').'); 
			?>';
			$call = $parser->parse($code);
			
			$code = '<?php
				$params = get_defined_vars();
				$response = isset($GLOBALS["BricksPlugin/EventManager"])
					? $GLOBALS["BricksPlugin/EventManager"]->trigger(__METHOD__.".post",$this,$params)
					: false;
				if($response && $response->last() !== null){
					return $response->last();
				}
				return $return;
			?>';
			$last = $parser->parse($code);
			
			$node->stmts = array();
			$node->stmts = array_merge($node->stmts,$first);
			$node->stmts = array_merge($node->stmts,$body);
			$node->stmts = array_merge($node->stmts,$call);
			$node->stmts = array_merge($node->stmts,$last);	
		}
		$eventized[$method] = true;
	}
	
	/**
	 * @param Node $rootNode
	 * @param string $namespace
	 * @return Node|boolean
	 */
	protected function fetchNamespace(Node $rootNode,$namespace){
		if('Stmt_Namespace'==$rootNode->getType() && implode('\\',$rootNode->name->parts) == $namespace){
			return $rootNode;
		}
		return false;
	}
	
	/**
	 * @param Node $namespaceNode
	 * @param string $class
	 * @return Node|boolean
	 */
	public function fetchClass(Node $namespaceNode,$class){
		foreach($namespaceNode->stmts AS $node){
			if('Stmt_Class'==$node->getType() && $node->name == $class){
				return $node;
			}
		}
		return false;
	}
	
	/**
	 * @param Node $classNode
	 * @param string $method
	 * @return Node|boolean
	 */
	public function fetchMethod(Node $classNode,$method){
		foreach($classNode->getMethods() AS $node){
			if($node->name == $method){
				return $node;
			}
		}
		return false;
	}	
	
}