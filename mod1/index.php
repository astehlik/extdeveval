<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2003 Kasper Skaarhoj (kasper@typo3.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is 
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
* 
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/** 
 * Module 'ExtDevEval' for the 'extdeveval' extension.
 *
 * $Id$
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   75: class tx_extdeveval_module1 extends t3lib_SCbase 
 *   87:     function init()	
 *   96:     function menuConfig()	
 *  125:     function main()	
 *  215:     function printContent()	
 *  228:     function moduleContent()	
 *
 *              SECTION: Various helper functions
 *  385:     function getSelectForLocalExtensions()	
 *  407:     function getSelectForExtensionFiles()	
 *  434:     function getCurrentPHPfileName()	
 *  453:     function getCurrentExtDir()	
 *
 * TOTAL FUNCTIONS: 9
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */



	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);	
require ('conf.php');
require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');
require_once (PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]




/**
 * Script class for the Extension Development Evaluation module
 * 
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 * @package TYPO3
 * @subpackage tx_extdeveval
 */
class tx_extdeveval_module1 extends t3lib_SCbase {

		// Internal, fixed:
	var $localExtensionDir = 'typo3conf/ext/';			// Operate on local extensions (the ext. main dir relative to PATH_site). Can be set to the global and system ext. dirs as well (but should not be needed for the common man...)
#	var $localExtensionDir = 'typo3/ext/';
#	var $localExtensionDir = 'typo3/sysext/';

	/**
	 * Init function, calling the parent init function
	 * 
	 * @return	void		
	 */
	function init()	{
		parent::init();
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 * 
	 * @return	void		
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			'function' => Array (
				'1' => 'getLL() converter',
				'2' => 'PHP script documentation help',
				'4' => 'Create/Update Extensions PHP API data',
#				'5' => 'Create/Update Extensions TypoScript API data (still empty)',
				'6' => 'Display API from "ext_php_api.dat" file',
				'3' => 'temp_CACHED files confirmed removal',
				'10' => 'PHP source code tuning',
				'11' => 'Code highlighting',
				'13' => 'CSS analyzer',
				'12' => 'Table Icon Listing ',
			),
			'extSel' => '',
			'phpFile' => '',
			'tuneXHTML' => '',
			'tuneQuotes' => '',
			'tuneBeautify' => '',
		);
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * 
	 * @return	void		
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$HTTP_GET_VARS,$HTTP_POST_VARS,$CLIENT,$TYPO3_CONF_VARS;
		
			// Draw the header.
		$this->doc = t3lib_div::makeInstance('noDoc');
		$this->doc->backPath = $BACK_PATH;
		$this->doc->form='<form action="" method="post">';
		$this->doc->docType = 'xhtml_trans';

			// JavaScript
		$this->doc->JScode = $this->doc->wrapScriptTags('
				script_ended = 0;
				function jumpToUrl(URL)	{	//
					document.location = URL;
				}
		');
		
			// Styles:
		$this->doc->inDocStylesArray[]='
			TR.nonSelectedRows { background-color: #cccccc; }

			/* Styles for the API display: */

				DIV#c-APIdoc  A { text-decoration: none; }
				DIV#c-APIdoc  DIV#c-openInNewWindowLink A { text-decoration: underline; }
				DIV#c-APIdoc TABLE TR TD {padding: 1px 3px 1px 3px; }
				DIV#c-APIdoc TABLE TR {background-color: '.$this->doc->bgColor4.'; }
				DIV#c-APIdoc DIV#c-body DIV.c-class TABLE.c-details TR TD.c-Hcell {background-color: '.$this->doc->bgColor2.'; font-weight: bold; }
				DIV#c-APIdoc DIV#c-body DIV.c-function TABLE.c-details TR TD.c-Hcell, DIV#c-APIdoc DIV#c-body DIV.c-class TABLE.c-details TR TD.c-Hcell {background-color: '.$this->doc->bgColor5.'; font-weight: bold; }
				DIV#c-APIdoc DIV#c-openInNewWindowLink { margin: 10px 0px 20px 0px;}
				
				DIV#c-APIdoc DIV#c-index P.c-fileDescription { margin-left: 30px;  margin-bottom: 10px; font-style: italic; }
				DIV#c-APIdoc DIV#c-index P.c-indexTags { margin-left: 90px; }
				DIV#c-APIdoc DIV#c-index H4 { margin-left: 50px; }
				DIV#c-APIdoc DIV#c-index H4.c-function { margin-left: 70px; }
				DIV#c-APIdoc DIV#c-index H3 { margin-left: 30px; margin-top: 20px;}
				DIV#c-APIdoc DIV#c-index { margin-bottom: 30px; }
				
				DIV#c-APIdoc DIV#s-index {margin-top: 20px;}
				DIV#c-APIdoc DIV#s-index H3 {background-color: '.$this->doc->bgColor5.'; margin: 0px 0px 0px 30px;}

				DIV#c-APIdoc DIV#c-body DIV.c-class {margin-left: 25px;margin-top: 10px; }
				DIV#c-APIdoc DIV#c-body DIV.c-function TABLE.c-details TR TD.c-vType {font-weight: bold;}
				DIV#c-APIdoc DIV#c-body P.c-funcDescription {font-style: italic;}
				DIV#c-APIdoc DIV#c-body DIV.c-header {background-color: '.$this->doc->bgColor2.'; margin-top: 30px;}
				DIV#c-APIdoc DIV#c-body DIV.c-function { margin-top: 20px; margin-left: 70px; }
				DIV#c-APIdoc DIV#c-body TABLE.c-details {margin-top: 5px; width: 100%; }
				DIV#c-APIdoc DIV#c-body TABLE.c-details TR TD.c-Hcell {width: 25%;}
				DIV#c-APIdoc DIV#c-body TABLE.c-details TR TD.c-vDescr {width: 75%;}
				DIV#c-APIdoc DIV#c-index H3.section { margin-left: 80px;  width: 70%; background-color: '.$this->doc->bgColor4.';}
				
		';

		$this->content.=$this->doc->startPage('Extension Development Evaluator');
		$this->content.=$this->doc->header('Extension Development Evaluator');
		$this->content.=$this->doc->spacer(5);
		$this->content.=$this->doc->section('',$this->doc->funcMenu('',t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));

			// Shows extension and ext.file selector only for SOME of the tools:
		switch((string)$this->MOD_SETTINGS['function'])	{
			case 1:
			case 2:
			case 10:
			case 6:
				$this->content.=$this->doc->section('Select Local Extension:',$this->getSelectForLocalExtensions().'<br />'.$this->getSelectForExtensionFiles());
				$this->content.=$this->doc->divider(5);
			break;
			case 4:
				$this->content.=$this->doc->section('Select Local Extension:',$this->getSelectForLocalExtensions());
				$this->content.=$this->doc->divider(5);
			break;
		}

			// Render content:
		$this->moduleContent();

		
		// ShortCut
		if ($BE_USER->mayMakeShortcut())	{
			$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
		}

		$this->content.=$this->doc->spacer(10);
	}

	/**
	 * Prints out the module HTML
	 * 
	 * @return	void		
	 */
	function printContent()	{
		global $SOBE;

		$this->content.=$this->doc->middle();
		$this->content.=$this->doc->endPage();
		echo $this->content;
	}
	
	/**
	 * Generates the module content
	 * 
	 * @return	void		
	 */
	function moduleContent()	{
		switch((string)$this->MOD_SETTINGS['function'])	{
			case 1:
				$content = 'A tool which helps developers of extensions to (more) easily convert hardcoded labels to labels provided by the localization engine in TYPO3 (using the pi_getLL() functions)';
				$this->content.=$this->doc->section('getLL() converter',$content,0,1);
				$phpFile = $this->getCurrentPHPfileName();
				if (is_array($phpFile))	{
					require_once('./class.tx_extdeveval_submodgetll.php');
					
					$inst = t3lib_div::makeInstance('tx_extdeveval_submodgetll');
					$content = $inst->analyseFile($phpFile[0],$this->localExtensionDir);

					$this->content.=$this->doc->section('File: '.basename(current($phpFile)),$content,0,1);
				} else {
					$this->content.=$this->doc->section('NOTICE',$phpFile,0,1,2);
				}
			break;
			case 2:
				$content = 'A tool which helps to insert JavaDoc comments for PHP functions in a script.';
				$this->content.=$this->doc->section('PHP Script Documentation Help',$content,0,1);
				$phpFile = $this->getCurrentPHPfileName();
				if (is_array($phpFile))	{
					require_once('./class.tx_extdeveval_phpdoc.php');
					
					$inst = t3lib_div::makeInstance('tx_extdeveval_phpdoc');
					$content = $inst->analyseFile($phpFile[0],$this->localExtensionDir);

					$this->content.=$this->doc->section('File: '.basename(current($phpFile)),$content,0,1);
				} else {
					$this->content.=$this->doc->section('NOTICE',$phpFile,0,1,2);
				}
			break;
			case 4:
				$content = 'A tool which will read JavaDoc data out of PHP scripts in the extension and stores it in a "ext_php_api.dat" file for use on TYPO3.org';
				$this->content.=$this->doc->section('PHP API data creator/updator',$content,0,1);

				require_once('./class.tx_extdeveval_phpdoc.php');
				$inst = t3lib_div::makeInstance('tx_extdeveval_phpdoc');
				$path = $this->getCurrentExtDir();
				if ($path)	{
					$content = $inst->updateDat($path,t3lib_div::removePrefixPathFromList(t3lib_div::getAllFilesAndFoldersInPath(array(),$path,'php,inc'),$path),$this->localExtensionDir);
				}
				$this->content.=$this->doc->section('',$content,0,1);
			break;
			case 6:
				$content = 'Displays the content of an API xml file as a nice HTML page';
				$this->content.=$this->doc->section('Extension PHP API',$content,0,1);
				
					// Getting the path to the currently selected extension (blank if none):
				$path = $this->getCurrentExtDir();
				if ($path)	{
					if (@is_file($path.'ext_php_api.dat'))	{		// If there is an API file:
						require_once('./class.tx_extdeveval_apidisplay.php');
						$inst = t3lib_div::makeInstance('tx_extdeveval_apidisplay');
						$content = '<hr />'.$inst->main(t3lib_div::getUrl($path.'ext_php_api.dat'), $this->MOD_SETTINGS['phpFile']);
					} else {	// No API file:
						$content='<br /><br /><strong>Error:</strong> The file "ext_php_api.dat" (which contains API information) was NOT found for this extension. You can create such a file with the tool from the menu called "Create/Update Extensions PHP API data".';
					}

						// Add content:
					$this->content.=$this->doc->section('',$content,0,1);
				}
			break;
			case 3:
				$content = 'A tool which removes the temp_CACHED files from typo3conf/ AND checks if they truely were removed!<br />This is a rather seldom need but if you experience certain problems (with installation/de-installation of extensions) it might be useful to know if the "temp_CACHED_*" files can be removed by the extension management class. This is what this module tests.<hr />';
				$this->content.=$this->doc->section('Remove temp_CACHED files',$content,0,1);

				require_once('./class.tx_extdeveval_cachefiles.php');
				$inst = t3lib_div::makeInstance('tx_extdeveval_cachefiles');
				$content = $inst->cacheFiles();
				$this->content.=$this->doc->section('',$content,0,1);
			break;
			case 10:
				$content = 'A tool to tune your source code.<br />';

				$onCLick = "document.location='index.php?SET[tuneQuotes]=".($this->MOD_SETTINGS['tuneQuotes']?'0':'1')."';return false;";
				$content .= '<br /><input type="hidden" name="SET[tuneQuotes]" value="0" />
						<input type="checkbox" name="SET[tuneQuotes]" value="1"'.($this->MOD_SETTINGS['tuneQuotes']?' checked':'').' onclick="'.htmlspecialchars($onCLick).'" /> convert double quotes ( " ) to single quotes ( \' )';

#				$onCLick = "document.location='index.php?SET[tuneXHTML]=".($this->MOD_SETTINGS['tuneXHTML']?'0':'1')."';return false;";
#				$content .= '<br /><input type="hidden" name="SET[tuneXHTML]" value="0" />
#						<input type="checkbox" name="SET[tuneXHTML]" value="1"'.($this->MOD_SETTINGS['tuneXHTML']?' checked':'').' onclick="'.htmlspecialchars($onCLick).'" /> convert to XHTML (silently; use for HTML)';
$this->MOD_SETTINGS['tuneXHTML'] = false;
				$onCLick = "document.location='index.php?SET[tuneBeautify]=".($this->MOD_SETTINGS['tuneBeautify']?'0':'1')."';return false;";
				$content .= '<br /><input type="hidden" name="SET[tuneBeautify]" value="0" />
						<input type="checkbox" name="SET[tuneBeautify]" value="1"'.($this->MOD_SETTINGS['tuneBeautify']?' checked':'').' onclick="'.htmlspecialchars($onCLick).'" /> reformat/beautify PHP source code (not nice with arrays like TCA)';


				$this->content.=$this->doc->section('PHP source code tuning',$content,0,1);
				$phpFile = $this->getCurrentPHPfileName();
				if (is_array($phpFile))	{
					require_once('./class.tx_extdeveval_tunecode.php');
					$inst = t3lib_div::makeInstance('tx_extdeveval_tunecode');
					$content = $inst->tune($phpFile[0], $this->localExtensionDir, $this->MOD_SETTINGS);

					$this->content.=$this->doc->section('File: '.basename(current($phpFile)),$content,0,1);
				} else {
					$this->content.=$this->doc->section('NOTICE',$phpFile,0,1,2);
				}
			break;
			case 11:
				$content = 'Highlights PHP or TypoScript code for copy/paste into OpenOffice manuals.<br /><br />';
				$this->content.=$this->doc->section('Code highlighting',$content,0,1);

				require_once('./class.tx_extdeveval_highlight.php');
				$inst = t3lib_div::makeInstance('tx_extdeveval_highlight');
				$this->content.=$inst->main();
			break;
			case 12:
				$content = 'A tool which can list all possible icon combinations from a database table.<hr />';
				$this->content.=$this->doc->section('List icon combinations for a table',$content,0,1);

				require_once('./class.tx_extdeveval_iconlister.php');
				$inst = t3lib_div::makeInstance('tx_extdeveval_iconlister');
				$content = $inst->main();
				$this->content.=$this->doc->section('',$content,0,1);
			break;			
			case 13:
				$content = 'A tool which can analyse HTML source code for the CSS hierarchy inside. Useful to get exact CSS selectors for elements on an HTML page.<hr />';
				$this->content.=$this->doc->section('CSS Analyser',$content,0,1);

				require_once('./class.tx_extdeveval_cssanalyzer.php');
				$inst = t3lib_div::makeInstance('tx_extdeveval_cssanalyzer');
				$content = $inst->main();
				$this->content.=$this->doc->section('',$content,0,1);
			break;			
            default:
                $this->content = $this->extObjContent();
            break;
		} 
	}


	











	
	/*************************************
	 *
	 * Various helper functions
	 * 
	 *************************************/
	
	/**
	 * Generates a selector box with the extension keys locally available for this install.
	 * 
	 * @return	string		Selector box for selecting the local extension to work on (or error message)
	 */
	function getSelectForLocalExtensions()	{
		$path = PATH_site.$this->localExtensionDir;
		if (@is_dir($path))	{
			$dirs = t3lib_div::get_dirs($path);
			if (is_array($dirs))	{
				sort($dirs);
				$opt=array();
				$opt[]='<option value="">[ Select Local Extension ]</option>';
				foreach($dirs as $dirName)		{
					$selVal = strcmp($dirName,$this->MOD_SETTINGS['extSel']) ? '' : ' selected="selected"';
					$opt[]='<option value="'.htmlspecialchars($dirName).'"'.$selVal.'>'.htmlspecialchars($dirName).'</option>';
				}
				return '<select name="SET[extSel]" onchange="jumpToUrl(\'?SET[extSel]=\'+this.options[this.selectedIndex].value,this);">'.implode('',$opt).'</select>';
			} else return 'ERROR: Could not read directories from path: "'.$path.'"';
		} else return 'ERROR: No local extensions path: "'.$path.'"';
	}

	/**
	 * Generates a selector box with file names of the currently selected extension
	 * 
	 * @return	string		Selectorbox or error message.
	 */
	function getSelectForExtensionFiles()	{
		if ($this->MOD_SETTINGS['extSel'])	{
			$path = PATH_site.$this->localExtensionDir.ereg_replace('\/$','',$this->MOD_SETTINGS['extSel']).'/';
			if (@is_dir($path))	{
				$phpFiles = t3lib_div::removePrefixPathFromList(t3lib_div::getAllFilesAndFoldersInPath(array(),$path,'php,inc'),$path);
				if (is_array($phpFiles))	{
					sort($phpFiles);
					$opt=array();
					$allFilesToComment=array();
					$opt[]='<option value="">[ Select PHP File ]</option>';
					foreach($phpFiles as $phpName)		{
						$selVal = strcmp($phpName,$this->MOD_SETTINGS['phpFile']) ? '' : ' selected="selected"';
						$opt[]='<option value="'.htmlspecialchars($phpName).'"'.$selVal.'>'.htmlspecialchars($phpName).'</option>';
						$allFilesToComment[]=htmlspecialchars($phpName);
					}
					return '<select name="SET[phpFile]" onchange="jumpToUrl(\'?SET[phpFile]=\'+this.options[this.selectedIndex].value,this);">'.implode('',$opt).'</select>'.
							chr(10).chr(10).'<!--'.chr(10).implode(chr(10),$allFilesToComment).chr(10).'-->'.chr(10);
				} else return 'No PHP files found in path: "'.$path.'"';
			} else return 'ERROR: Local extension not found: "'.$this->MOD_SETTINGS['extSel'].'"';
		}
	}

	/**
	 * Returns the currently selected PHP file name according to the selectors with field names SET[extSel] and SET[phpFile]
	 * 
	 * @return	mixed		String: Error message. Array: The PHP-file as first value in key "0" (zero)
	 */
	function getCurrentPHPfileName()	{
		if ($this->MOD_SETTINGS['extSel'])	{
			$path = PATH_site.$this->localExtensionDir.ereg_replace('\/$','',$this->MOD_SETTINGS['extSel']).'/';
			if (@is_dir($path))	{
				if ($this->MOD_SETTINGS['phpFile'])	{
					$currentFile = $path.$this->MOD_SETTINGS['phpFile'];
					if (@is_file($currentFile))	{
						return array($currentFile);
					} else return 'Currently selected PHP file was not found: '.$this->MOD_SETTINGS['phpFile'];
				} else return 'You must select a PHP file from the selector box above.';
			} else return 'ERROR: Local extension not found: "'.$this->MOD_SETTINGS['extSel'].'"';
		} else return 'You must select an extension from the selector box above.';
	}

	/**
	 * Returns the absolute path to the currently selected extension directory.
	 * 
	 * @return	string		Returns the directory IF it is also found to be a true directory. Otherwise blank.
	 */
	function getCurrentExtDir()	{
		if ($this->MOD_SETTINGS['extSel'])	{
			$path = PATH_site.$this->localExtensionDir.ereg_replace('\/$','',$this->MOD_SETTINGS['extSel']).'/';
			if (@is_dir($path))	{
				return $path;
			}
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_extdeveval_module1');
$SOBE->init();

// Include files?
reset($SOBE->include_once);	
while(list(,$INC_FILE)=each($SOBE->include_once))	{	include_once($INC_FILE);	}
$SOBE->checkExtObj();

$SOBE->main();
$SOBE->printContent();

?>