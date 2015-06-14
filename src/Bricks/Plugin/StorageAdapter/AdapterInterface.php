<?php
/**
 * Bricks Framework & Bricks CMS
 * http://bricks-cms.org
 *
 * @link https://github.com/bricks81/BricksPlugin
 * @license http://www.gnu.org/licenses/ (GPLv3)
 */
namespace Bricks\Plugin\StorageAdapter;

use Zend\Config\Config;
use Zend\Config\Writer\WriterInterface;
interface AdapterInterface {
	
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
	
	/**
	 * @param string $cachedir
	 */
	public function setCacheDir($cachedir);
	
	/**
	 * @return string
	 */
	public function getCacheDir();
	
	/**
	 * @param string $filename
	 */
	public function setClassModFileName($filename);
	
	/**
	 * @return string
	 */
	public function getClassModFileName();	
	
	/**
	 * @param string $filename
	 */
	public function setAutoloadMapFileName($filename);
	
	/**
	 * @return string
	 */
	public function getAutoloadMapFileName();
	
}