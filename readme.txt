Export/Import Tool (V3.22) for OpenCart 3.x
===========================================

The Import/Export Tool allows the admin user to do a bulk export
of categories, products, options, attributes, filters and customers to an Excel spreadsheet file.
The spreadsheet file can be edited offline and then be re-imported to the OpenCart database.

Features include:

    The Import can be incremental, that is, data is updated or added to the OpenCart server. 
    Or it can be non-incremental which means all old data is first deleted before the Import.

    The Export can be limited to certain data ranges only for products and categories.

    Multiple languages are supported, too.



Requirements and Limitations
============================

Memory requirements can be quite high.

Not every shared web hosting account supports a high process memory usage. 
Therefore, if you use a basic shared web hosting account, 
no more than a few thousand products can be exported or imported at a time. 
Use a more dedicated web hosting account if a higher number of products 
are to be processed in one go. Or export and import multiple times in smaller batches.


Installation
============

In the OpenCart admin backend, do the following steps:

Step 1)
	Go to Extensions > Extension Installer

Step 2)
	Upload the opencart-3-x-export-import-multilingual-3-x.ocmod.zip

Step 3)
	Go to Extensions > Modifications
	You should see an entry for this Export/Import tool

Step 4)
	Click on the Refresh button (top right of the page)

Step 5)
	Go to System > Users > User Group > Edit Administrator

Step 6)
	Set access and modify permissions for 'extension/export_import'

That's it! You should now see the Export/Import tool under the menu

	System > Maintenance > Export / Import



Further help and customized versions
====================================

This tool has been successfully tested for standard OpenCart 3.x versions.
Don't use other Opencart versions with this module.

If you need a customized version of the Export/Import Tool,
let us know and we can create one for a charge.

You can contact us at <http://www.mhccorp.com>

