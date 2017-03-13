# Spike on LandingPage FieldType repository-forms integration

###[JIRA task](https://jira.ez.no/browse/EZP-27023)

## 1. Creating a new object

`/api/ezp/v2/content/objects`

__Accept:__ _application/vnd.ez.api.Content+json_

__Content-Type:__ _application/vnd.ez.api.SimpleContentCreate+json_



### 1.1 complex request

```json
{
    "SimpleContentCreate": {
        "ContentType": "article",
        "ContentLocation": "/places-tastes/tastes/",
        "ContentSection": 1,
        "User": "jessica",
        "alwaysAvailable": "false",
        "modificationDate": "2017-03-02T12:00:00",
        "remoteId": "remote123456",
        "fields": {
            "title": "This is a title",
            "intro": {
                "xhtml5edit": "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<section xmlns=\"http://ez.no/namespaces/ezpublish5/xhtml5/edit\"><p>Article intro.</p></section>\n",
                "xml": "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<section xmlns=\"http://docbook.org/ns/docbook\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:ezxhtml=\"http://ez.no/xmlns/ezpublish/docbook/xhtml\" xmlns:ezcustom=\"http://ez.no/xmlns/ezpublish/docbook/custom\" version=\"5.0-variant ezpublish-1.0\"><para>Article intro.</para></section>\n"
            },
            "short_title": "This is a short_title"
        }
    }
}
```
    
#### 1.1 simple request
```json
{
    "SimpleContentCreate": {
        "ContentType": "article",
        "ContentLocation": "/places-tastes/tastes/",
        "fields": {
            "title": "This is a title",
            "intro": {
                "xhtml5edit": "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<section xmlns=\"http://ez.no/namespaces/ezpublish5/xhtml5/edit\"><p>Article intro.</p></section>\n",
                "xml": "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<section xmlns=\"http://docbook.org/ns/docbook\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:ezxhtml=\"http://ez.no/xmlns/ezpublish/docbook/xhtml\" xmlns:ezcustom=\"http://ez.no/xmlns/ezpublish/docbook/custom\" version=\"5.0-variant ezpublish-1.0\"><para>Article intro.</para></section>\n"
            },
            "short_title": "This is a short_title"
        }
    }
}

```

_For both output is a standard Content rest object_


## 2. Patching an object
 
### 2.1 request
`/api/ezp/v2/content/object/72`

__Accept:__ _application/vnd.ez.api.Content+json_

__Content-Type:__ _application/vnd.ez.api.SimpleContentUpdate+json_

```json
{
    "SimpleContentUpdate": {
        "fields": {
            "name": "This is a new title"
        }
    }
}
```
