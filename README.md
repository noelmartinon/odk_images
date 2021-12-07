# ODK Images

Images server for ODK Central and ODK Aggregate

* [Installation](#installation)
* [License](#license)

## Installation
- Copy the files from the 'odk_images' folder to a web server
- Set the variables in 'config.php'

## Usage
For your QGIS project, see required fields in database and create a function in postgresql:
- Aggregate: blobKey, uuid in 'FORMID_BLB','FORMID_BN','FORMID_REF' tables
- Central: blobID, name in 'submission_attachments' table

Then get images like this:
- Aggregate: https://www.domain.tld/odk_images/?blobKey=FORMID&uuid=328df3a4-169f-4ea2-95cb-1c5d53231ef2
- Central: https://www.domain.tld/odk_images/?blobId=12&photo=1634855487924.jpg

## License
GPLv3

Author: Noël MARTINON
