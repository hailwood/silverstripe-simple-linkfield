Silverstripe Simple Link Field
====================================

#### A Silverstripe Link Field:
![Screenshot](/snapshot.png?raw=true)

* **user defined protocols**: Any are supported and depending on the option selected (http, https, or mailto) we'll validate it.

#### Installation
```bash
composer require hailwood/silverstripe-simple-linkfield
```

#### Options

```yml
TextLinkField:
  default_protocols:
    http: 'http://'
    https: 'https://'
    mailto: 'Email'
````

#### Usage
##### Dataobject/Page
```php
class DataObjectWithLink extends DataObject {

    protected static $db = [
        'Link' => 'Varchar(255)',
        'LinkWithoutEmail' => 'Varchar(255)',
    ];
    
    public function getCMSFields(){
        $fields = parent::getCMSFields();
        
        $fields->addFieldsToTab('Root.Main', [
            TextLinkField::create('Link', 'Link'),
            TextLinkField::create('LinkWithoutEmail')->withoutEmailOption()
        ]);
        
        return $fields;
    }

}
```
##### Methods
 * **withoutEmailOption()**: Quickly disable the default email option
 * **withEmailOption($title = 'Email')**: Quickly enable or set the title on the default email option
 * **setProtocolList([])**: Set the local instances protocol list
 * **getProtocolList()**: Get the list of protocols in use by this local instance
