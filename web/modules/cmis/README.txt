CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Credits
 * Maintainers


INTRODUCTION
------------

The CMIS API project aims to provide a generic API for integrating with CMIS
(http://en.wikipedia.org/wiki/Content_Management_Interoperability_Services)
compliant Enterprise CMS (ECM) systems.

When to use this module:

 * If your needs for managing structured and unstructured content go beyond
   Drupal's current file handling capabilities.
 * If you need advanced workflow or security for managed documents that need to
   be exposed to the web or an intranet.
 * If you already have a CMIS compliant document management system in place and
   need a way for web content managers to easily be able to add documents to
   sites Resources.

What this project does:

The CMIS API is composed of several modules and primarily provides an API for
connecting to CMIS compliant systems to bi-directionally synchronize content
between the CMIS ECM system and Drupal.

In addition to the API, this package provides a range of basic functionality for
creating, updating, browsing and searching content in the CMIS ECM system via
the Drupal interface.

The overall goal of the modules is to provide an easy-to-use, WCM front-end in
Drupal for ECM systems that are often unfamiliar to web content managers.

While this module does provide interfaces and functionality out of the box,
most developers doing advanced integrations will want to create custom modules
that invoke the included API for custom authentication, synchronization, content
type extension, etc.

What is included:
 * CMIS:
   - A connection config entity to be able to connect as many CMIS compliant
     system as you want.
   - A browser to interact with the remote documents: folder creation, deletion,
     document upload, document deletion.
   - A query page to directly make query against the remote system (cmis/query).
   - A new field type to get links to remote document.
 * CMIS Alfresco Auth User:
   - Allow users to use CMIS features by connecting to Alfresco using Drupal's
     login/password.
   - When the Alfresco's connection ticket will expire, the user will be logged
     out.


REQUIREMENTS
------------

The module requires the https://github.com/dkd/php-cmis-client library. And
therefore you need to use Composer to download the module.

Otherwise, this module has no requirement except Drupal core.


INSTALLATION
------------

 * Install and enable this module like any other drupal 8 module.


CONFIGURATION
-------------

 * Enable the CMIS module on your site
 * Go to the configuration page
   (/admin/config/cmis/connection/cmis_connection_entity)
 * Configure a connection
 * Save the connection
 * Use the browse link on the configuration page


CREDITS
-------

Contributors on previous versions / drupal.org projects related to CMIS:
 * Dries Buytaert (Dries) - https://www.drupal.org/user/1
 * Yong Qu
 * Matt Asay
 * Scott Davis (jhabiteici) - https://www.drupal.org/user/1599684
 * Jeff Potts (jpotts) - https://www.drupal.org/user/226200
 * Dave Gynn
 * Rich McKnight (richmck) - https://www.drupal.org/user/334839


MAINTAINERS
-----------

Current maintainers:
 * Florent Torregrosa (Grimreaper) - https://www.drupal.org/user/2388214
 * Ines WALLON (liber_t) - https://www.drupal.org/user/3377176

Previous maintainers:
 * Chris Fuller (cfuller12) - https://www.drupal.org/user/61928
 * Ian Norton (IanNorton) - https://www.drupal.org/user/1314428
 * Catalin Balan (cbalan) - https://www.drupal.org/user/435484
 * József Dudás (dj1999) - https://www.drupal.org/user/387119
 * Ruben Teijeiro (rteijeiro) - https://www.drupal.org/user/780508
 * tudor.sitaru - https://www.drupal.org/user/665088
 * Mark Payne (webcurl) - https://www.drupal.org/user/2483100

This project has been sponsored by:

 * Acquia - https://www.drupal.org/acquia
   Sponsored development, maintenance and support on previous versions /
   drupal.org projects related to CMIS.
 * Optaros - http://www.optaros.com
   Sponsored development, maintenance and support on previous versions /
   drupal.org projects related to CMIS.
 * Alfresco - https://www.alfresco.com/
   Sponsored development, maintenance and support on previous versions /
   drupal.org projects related to CMIS.
 * Tieto - https://www.drupal.org/tieto
   Sponsored Drupal 8 port.
 * Brainsum - https://www.drupal.org/brainsum
   Sponsored Drupal 8 port.
 * 040lab - https://www.drupal.org/040lab
   Sponsored Drupal 8 port.
 * Smile - https://www.drupal.org/smile
   Sponsored evolutions, maintenance and support.
