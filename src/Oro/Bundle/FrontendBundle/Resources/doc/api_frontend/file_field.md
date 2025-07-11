# %file_target_entity%

## FIELDS

### %file_field%

{@inheritdoc}

It is an object with the following properties:

**mimeType** is a string that contains the media type of the file.

**url** is a string that contains URL of the file.

Example of data: **{"mimeType": "text/plain", "url": "/path/to/file.txt"}**

### %multi_file_field%

{@inheritdoc}

It is an array of objects. Each object has the following properties:

**mimeType** is a string that contains the media type of the file.

**url** is a string that contains URL of the file.

Example of data: **\[{"mimeType": "text/plain", "url": "/path/to/file.txt"}\]**
