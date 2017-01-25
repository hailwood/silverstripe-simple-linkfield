<?php

class TextLinkField extends TextField
{

    public    $children;
    protected $protocolField;
    protected $linkField;
    protected $emailEnabled = true;

    /**
     * @param string $name
     * @param string $title
     * @param mixed  $value
     * @param Form   $form
     */
    public function __construct($name, $title = null, $value = "", $form = null)
    {

        Requirements::css(SIMPLELINKFIELD_DIR . "/css/text-link-field.css");

        // naming with underscores to prevent values from actually being saved somewhere
        $this->protocolField = DropdownField::create("{$name}[_Protocol]", false);
        $this->linkField     = TextField::create("{$name}[_Link]", false);
        $this->protocolField->setSource(['http' => 'http://', 'https' => 'https://', 'mailto' => 'Email']);

        $this->children = FieldGroup::create($this->protocolField, $this->linkField);

        parent::__construct($name, $title, null, $form);
        $this->setValue($value);
    }

    public function withoutEmailOption()
    {
        $this->protocolField->setSource(['http' => 'http://', 'https' => 'https://']);
        return $this;
    }

    public function includeEmailOption()
    {
        $this->protocolField->setSource(['http' => 'http://', 'https' => 'https://', 'mailto' => 'Email']);
        return $this;
    }

    /**
     * @param array $properties
     *
     * @return string
     */
    public function Field($properties = array())
    {
        $content = '';

        foreach ($this->children as $field) {
            $field->setDisabled($this->isDisabled());
            $field->setReadonly($this->isReadonly());

            if (count($this->attributes)) {
                foreach ($this->attributes as $name => $value) {
                    $field->setAttribute($name, $value);
                }
            }

            $content = (string)$field->FieldHolder();
        }

        return $content;
    }

    /**
     * Value is sometimes an array, and sometimes a single value, so we need
     * to handle both cases.
     *
     * @param mixed $value
     *
     * @return TextLinkField
     */
    public function setValue($value, $data = null)
    {
        // wrap it in an array if needed
        $value = $this->valueToArray($value);

        $this->protocolField->setValue($value['_Protocol']);
        $this->linkField->setValue($value['_Link']);
        $this->value = $value['_Link'] ? implode('://', $value) : '';

        return $this;
    }

    protected function valueToArray($value)
    {
        // wrap it in an array if needed
        if (!is_array($value)) {
            $value = explode('://', $value);
            $value = (count($value) === 2 ? $value : [null, null]);
            $value = array_combine(['_Protocol', '_Link'], $value);
        }

        return $value;
    }

    protected function emailErrorMessage()
    {
        return _t(
            'TextLinkField.VALIDATEEMAIL',
            'The value for {name} is not a valid email address',
            ['name' => $this->getName()]
        );
    }

    protected function urlErrorMessage()
    {
        return _t(
            'TextLinkField.VALIDATEURL',
            'The value for {name} is not a valid URL',
            ['name' => $this->getName()]
        );
    }

    /**
     * @param Validator $validator
     *
     * @return boolean
     */
    public function validate($validator)
    {

        $value = $this->valueToArray($this->value);
        if (!$value['_Link']) return true;

        switch ($value['_Protocol']) {
            case 'http':
            case 'https';
                if (!filter_var($this->value, FILTER_VALIDATE_URL)) {
                    $validator->validationError($this->name, $this->urlErrorMessage(), "validation");
                    return false;
                }
                break;
            case 'mailto';
                if (!filter_var($value['_Link'], FILTER_VALIDATE_EMAIL)) {
                    $validator->validationError($this->name, $this->emailErrorMessage(), "validation");
                    return false;
                }
        }

        return true;
    }

}