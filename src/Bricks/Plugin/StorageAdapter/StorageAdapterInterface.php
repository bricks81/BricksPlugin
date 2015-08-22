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

namespace Bricks\Plugin\StorageAdapter;

use Zend\Config\Config;
use Zend\Config\Writer\WriterInterface;
interface StorageAdapterInterface {
	
	/**
	 * @param string $filename
	 * @param string $content
	 */
	public function filePutContents($filename,$content);
	
	/**
	 * @param string $filename
	 * @return string
	 */
	public function fileGetContents($filename);
	
	/**
	 * @param string $filename
	 * @param Config $config
	 * @param WriterInterface $writer
	 */
	public function writeConfig($filename,Config $config,WriterInterface $writer);

	/**
	 * @param string $filename
	 * @return \Zend\Config\Config
	 */
	public function loadConfig($filename);
	
	/**
	 * @param string $filename
	 * @return int
	 */
	public function fileMTime($filename);
	
	/**
	 * @param string $filename
	 * @return string
	 */
	public function realpath($filename);
	
	/**
	 * @param string $filename
	 * @return boolean
	 */
	public function fileExists($filename);
	
}