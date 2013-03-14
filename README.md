[![Flattr this git repo](http://api.flattr.com/button/flattr-badge-large.png)](https://flattr.com/submit/auto?user_id=SchumacherFM&url=https://github.com/SchumacherFM/TYPO3Mind&title=TYPO3Mind&language=en_GB&tags=github&category=software)

TYPO3Mind
=========

This is the version for TYPO3 v6. Still under development.

TYPO3Mind is an extension for generating mind mapping files from your whole
TYPO3 installation. Mind maps helps you to understand how your TYPO3 project
has been setup and what the current running status is. Currently you can only
export .mm files which can be imported by FreeMind (strongly recommended),
Freeplane, XMind, Mindjet, MindManager, etc. TYPO3Mind uses the cool icon
from FreeMind. This extension hooks into the tree click menu and in the left
pane. The mind map includes many icons and pictures with URIs to your
webserver. You have a lot of configuration options.
Needs TYPO3 6.0 or later, SimpleXML, PHP5.3 or later.


How to install:

1. Create a SysFolder in root page tree called e.g. TYPO3Mind.

2. During editing this SysFolder add as "General Record Storage Page" the
	SysFolder itself

3. Go to your TypoScript root template and include the template for TYPO3Mind
	in the section "Include static (from extensions):"

4. Set as new constant value at least this:
	module.tx_typo3mind.persistence.storagePid = XXX
	where XXX is the pid of the SysFolder "TYPO3Mind".

5. Clear all caches

6. In left pane click on "TYPO3Mind Export" and the .mm file is generating.


Collaborating
=============

I follow the principles of (http://felixge.de/2013/03/11/the-pull-request-hack.html)
send me a Pull Request which proves your PHP coding abilities and I'll add you
as a collaborator to this git repository.

License
=======

The GNU General Public License can be found at (http://www.gnu.org/copyleft/gpl.html)

