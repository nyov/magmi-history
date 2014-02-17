This is [Magmi][sfproject], the Magento Mass Importer, by [dweeves]

----

**The official source is at [magmi-git].**  
_This was an SVN mirror, and now only contains old history (grafted and rewritten)._

----

**Multiple histories** -- all cleaned up now.  
All branches have been rewritten to contain the same top-level structure. Easy diffs now.  
All SVN branches have been grafted together,
new magmi-git history has some git replace glue to old branches.

To fetch these glue refs, the refspec has to be explicitly added to the .git/config:  
```bash
$ git config --add remote.origin.fetch '+refs/replace/*:refs/replace/*'
$ git pull
```

----

Software is provided under the MIT (X11) license.

[sfproject]: http://sourceforge.net/projects/magmi/
[dweeves]:   https://github.com/dweeves
[magmi-git]: https://github.com/dweeves/magmi-git
