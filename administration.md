# Administration
## Adding new tools and maintaining existing ones
Part of the code in toolsprog.bra is dedicated registration of tools. The interface for registration of tools is in the upper part of [http://localhost/texton/admin/](http://localhost/texton/admin.html). (This is in a local setting).
You can 
1. register new tools
2. change the metadata of existing ones
3. generate a PHP wrapper for a tool that extracts all the HTTP parameters that the tool needs
4. export all metadata to a file
5. import all metadata from a file in another instance of Text Tonsorium, optionally without overwriting metadata that are specific to this particular instance.

There are many metadata related files, but the only ones that are affected by the web based GUI are /opt/texton/BASE/meta/tooladm and /opt/texton/BASE/meta/toolprop.
In the folder /opt/texton/BASE/meta/ and its subfolders are a number of files that contain the data with wich drop down lists are filled in the GUI. If you think a value is missing in a dropdown list, then the corresponding metadata file has to be edited directly.
