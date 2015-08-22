<?php

/**
 * Bricks Framework & Bricks CMS
 * http://bricks-cms.org
 *
 * The MIT License (MIT)
 * Copyright (c) 2015 bricks-cms.org
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
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