Silverstripe Simple Link Field
====================================

#### A Silverstripe Link Field:
![Screenshot](/snapshot.png?raw=true)

* **http, https, or mailto**: Depending on the option selected we'll validate it.

#### Installation
```bash
composer require hailwood/silverstripe-simple-linkfield
```

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
            TextLinkField::create('LinkWithoutEmail')->withoutEmail()
        ]);
        
        return $fields;
    }

} 
