TypeFriendly 0.1.4
==================

TypeFriendly is a documentation generator written in PHP5 and developed to be easy in use.
You may use it to create a HTML documentations for your projects using simple text files
and the Markdown syntax.

Run TF from the command line to self-generate a complete user manual:

    ./typefriendly.php build "./docs/" -o xhtml 

The output files will be in `./docs/output/xhtml`

Polish-language users can use:

    ./typefriendly.php build "./docs/" -o xhtml -l pl 

Authors and licensing
---------------------

The project is distributed under the terms of GNU General Public License 3.

Authors and contributors:

- The TypeFriendly code and engine: Copyright (c) 2008-2010 Invenzzia Group <http://www.invenzzia.org/>
  GNU General Public License 3
- The PHP-Markdown Extra parser: Copyright (c) 2004-2009 Michel Fortin <http://www.michelf.com/>
  New BSD license
- The original Markdown: Copyright (c) 2003-2006 John Gruber <http://daringfireball.net/>
  New BSD license
- Tango Icon Library: Copyright (c) Tango Desktop Project <http://tango.freedesktop.org/>
  Creative Commons Attribution-ShareAlike 2.5 License Agreement
 
You should obtain the copies of the licenses in the `/info` directory. They are also available
on-line:

- <http://www.invenzzia.org/license/gpl>, <http://www.gnu.org/licenses/>
- <http://www.invenzzia.org/license/markdown> 
- <http://www.invenzzia.org/license/cc-share-alike-2.5> 
