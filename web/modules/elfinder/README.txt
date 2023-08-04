elFinder file manager integration module for Drupal
===================================================

System Requirements:

 - Drupal 9.x
 - elFinder 2.1.57+

Optional:

 - Wysiwyg editor:

    - CKEditor
    - BUEdtor

Installation:

  1. Unpack archive contents into modules/elfinder
  2. Get latest elFinder at http://elfinder.org and it's contents to sites/all/libraries/elfinder
  3. REMOVE following files from library directory if it exists to avoid security hole:

     sites/all/libraries/elfinder/connectors/php/connector.php
     sites/all/libraries/elfinder/php/connector.php

  4. Enable elFinder module in Extend menu
  5. Add 'Insert image with elFinder' button to toolbar for the text format (CKEditor)

Usage:

 1. Administration backend

  a. Open /elfinder url (or ?q=elfinder if seo-capable urls disabled)
  b. Go to Administer page. Click 'Files' under Content section.

 2. Inside CKEditor
  2.1. Click 'Insert image with elFinder' button
  2.2. Double click on image (or click image and arrow toolbar icon) and it will be inserted into the editor

 3. Inside BUEditor
  3.1. TBD

Known Issues:

 - Not all editors supported



