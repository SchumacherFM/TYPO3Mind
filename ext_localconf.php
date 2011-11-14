<?php

if (!defined ('TYPO3_MODE')){ 	die ('Access denied.');	}

$TYPO3_CONF_VARS['FE']['eID_include']['freemind2'] = t3lib_extMgm::extPath('freemind2').'Classes/Utility/eIDDispatcher.php';