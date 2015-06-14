<?php
/**
 * Bricks Framework & Bricks CMS
 * http://bricks-cms.org
 *
 * @link https://github.com/bricks81/BricksPlugin
 * @license http://www.gnu.org/licenses/ (GPLv3)
 */
namespace Bricks\Plugin\StorageAdapter;

use Bricks\File\Directory;
use Zend\Config\Config;
use Zend\Config\Writer\WriterInterface;

class FilesystemAdapter implements AdapterInterface {
	
	/**
	 * (non-PHPdoc)
	 * @see \Bricks\Plugin\StorageAdapter\AdapterInterface::filePutContents()
	 */
	public function filePutContents($filename,$content){
		if(!is_dir(dirname($file))){
			Directory::mkdir(dirname($filename));
		}		
		return file_put_contents($file,$content);		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Bricks\Plugin\StorageAdapter\AdapterInterface::fileGetContents()
	 */
	public function fileGetContents($filename){
		return file_get_contents($filename);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Bricks\Plugin\StorageAdapter\AdapterInterface::writeConfig()
	 */
	public function writeConfig($filename,Config $config,WriterInterface $writer){
		$writer->toFile($filename,$config);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Bricks\Plugin\StorageAdapter\AdapterInterface::loadConfig()
	 */
	public function loadConfig($filename){
		return new ZendConfig(require($filename),true);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Bricks\Plugin\StorageAdapter\AdapterInterface::fileMTime()
	 */
	public function fileMTime($filename){
		return filemtime($filename);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Bricks\Plugin\StorageAdapter\AdapterInterface::realpath()
	 */
	public function realpath($filename){
		return realpath($filename);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Bricks\Plugin\StorageAdapter\AdapterInterface::fileExists()
	 */
	public function fileExists($filename){
		return file_exists($filename);
	}

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