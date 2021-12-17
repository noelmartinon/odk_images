# ODK Images

Images server for ODK Central and ODK Aggregate

* [Installation](#installation)
* [Usage](#usage)
* [License](#license)
* [Credits](#credits)

## Features

* Showing the images stored in ODK databases (Central and Aggregate)
* Automatic image rotation based on EXIF information
* Setting the output quality of images

## Installation

* Copy the files from the 'odk_images' folder to a web server
* Set the variables in 'config.php'

## Usage

Get images like this:

* Aggregate: https://www.domain.tld/odk_images/?blobKey=FORMID&uuid=328df3a4-169f-4ea2-95cb-1c5d53231ef2
* Central: https://www.domain.tld/odk_images/?blobId=12&photo=1634855487924.jpg

## Informations and prerequisites

For your QGIS project, see required fields in database and create a function in postgresql.

### ODK Aggregate

The fields used are blobKey and uuid in 'FORMID_BLB','FORMID_BN','FORMID_REF' tables.

### ODK Central

Due to the fact that ODK Central stores datas in xml format, it is necessary to go through a view and trigger functions for each form. Here is an example, the code must be adapted according to your own ODK Central form fields:

* SQL View code:

```
SELECT row_number() OVER () AS id,
    st_setsrid(st_makepoint(tbl2.x, tbl2.y), 4326) AS geometry,
    tbl2.id_form,
    tbl2."instanceID",
    tbl2.date,
    tbl2.nom_arbre AS arbre,
    tbl2.nom_autre_arbre AS autre,
    tbl2.nom_phenologie AS phenologie,
    NULLIF(tbl2.commentaire, ''::text) AS commentaire,
    tbl2.nom_observateur AS observateur,
    tbl2.statut,
        CASE
            WHEN tbl2.photo_arbre = ''::text THEN NULL::text
            ELSE concat('https://www.domain.tld/odk_images/?blobId=', tbl3."blobId", '&photo=', tbl2.photo_arbre)
        END AS url,
    NULLIF(tbl2.photo_arbre, ''::text) AS photo,
    tbl3."blobId"
   FROM ( SELECT tbl1.id_defs,
            "xmltable"."instanceID",
            "xmltable".id_form,
            "xmltable".version,
            "xmltable".formulaire,
            "xmltable".nom_observateur,
            "xmltable".date,
            "xmltable".nom_arbre,
            "xmltable".nom_autre_arbre,
            "xmltable".nom_phenologie,
            "xmltable".geolocalisation,
            "xmltable".commentaire,
            "xmltable".photo_arbre,
            "xmltable".statut,
            split_part("xmltable".geolocalisation, ' '::text, 2)::double precision AS x,
            split_part("xmltable".geolocalisation, ' '::text, 1)::double precision AS y
           FROM ( SELECT submission_defs.id AS id_defs,
                    submission_defs.xml::xml AS colonne_xml
                   FROM submission_defs
                  WHERE split_part(submission_defs."instanceName", '-'::text, 1) = 'obsar'::text) tbl1,
            LATERAL XMLTABLE(('//data/obs_detail'::text) PASSING (tbl1.colonne_xml) COLUMNS "instanceID" text PATH ('//meta/instanceID'::text), id_form text PATH ('/data/@id'::text), version text PATH ('/data/@version'::text), formulaire text PATH ('formulaire'::text), nom_observateur text PATH ('nom_observateur'::text), date text PATH ('date'::text), nom_arbre text PATH ('nom_arbre'::text), nom_autre_arbre text PATH ('nom_autre_arbre'::text), nom_phenologie text PATH ('nom_phenologie'::text), geolocalisation text PATH ('geolocalisation'::text), commentaire text PATH ('commentaire'::text), photo_arbre text PATH ('photo_arbre'::text), statut text PATH ('statut'::text))) tbl2
     LEFT JOIN submission_attachments tbl3 ON tbl2.photo_arbre = tbl3.name AND tbl2.id_defs = tbl3."submissionDefId";
```

* Trigger 1 *insert_to_MYODKFORM_details*:

```
BEGIN
    IF split_part(NEW."instanceName", '-',1)::text = 'myodkform' THEN
    INSERT INTO central_noxml.myodkform_details
    (
    "geometry" ,
    "id_form" ,
    "instanceID" ,
    "date",
    "arbre" ,
    "autre" ,
    "phenologie" ,
    "commentaire" ,
    "observateur" ,
    "statut",
    "photo"
    )
    (
        SELECT
        "myodkform_details_view"."geometry",
        "myodkform_details_view"."id_form",
        "myodkform_details_view"."instanceID",
        "myodkform_details_view"."date",
        "myodkform_details_view"."arbre",
        "myodkform_details_view"."autre",
        "myodkform_details_view"."phenologie",
        "myodkform_details_view"."commentaire",
        "myodkform_details_view"."observateur",
        "myodkform_details_view"."statut",
        "myodkform_details_view"."photo"
        FROM central_noxml.myodkform_details_view
        WHERE "myodkform_details_view"."instanceID" = NEW."instanceId");
        END IF ;
    RETURN NULL;
END;
```

* Trigger 2 *photo_to_MYODKFORM_details*:

```
DECLARE
    form TEXT := NULL;
    instance_id TEXT := NULL;
BEGIN
    select split_part(tbl1."instanceName", '-',1)::text INTO "form" from public."submission_defs" as tbl1 where tbl1."id" = NEW."submissionDefId";
    select tbl1."instanceId"::text INTO "instance_id" from public."submission_defs" as tbl1 where tbl1."id" = NEW."submissionDefId";
    IF "form" = 'myodkform' THEN
        UPDATE central_noxml."myodkform_details"
            SET    "blobId" =    NEW."blobId",
                "url" = CONCAT ('https://www.domain.tld/odk_images/?blobId=', NEW."blobId", '&photo=', NEW."name")
            WHERE "myodkform_details"."instanceID" = "instance_id" AND "myodkform_details"."photo" = NEW."name" ;
        END IF ;
    RETURN NULL;
END;
```

## License

GPLv3\
Author: Noël MARTINON

## Credits

Special thanks to Rudy MUSQUET for the time spent on ODK Central and PostgreSQL in order to be able to easily use the images from the forms and the complete availability of his SQL code which results from it.
