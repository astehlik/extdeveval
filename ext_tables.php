<?php
# TYPO3 CVS ID: $Id$

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::addModule('tools','txextdevevalM1','',t3lib_extMgm::extPath($_EXTKEY).'mod1/');
}
?>