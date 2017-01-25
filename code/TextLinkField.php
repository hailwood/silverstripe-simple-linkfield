<?php

class TextLinkField extends TextField
{

    public    $children;
    protected $protocolField;
    protected $linkField;
    protected $emailEnabled = true;

    protected $localProtocols = [];

    protected static $default_protocols = ['http' => 'http://', 'https' => 'https://', 'mailto' => 'Email'];

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
        $this->protocolField->setSource($this->getProtocolList());

        $this->children = FieldGroup::create($this->protocolField, $this->linkField);

        parent::__construct($name, $title, null, $form);
        $this->setValue($value);
    }

    /**
     * Set the list of protocols for this instance
     *
     * @param array $list
     * @return $this
     */
    public function setProtocolList(array $list = [])
    {
        $this->localProtocols = $list;
        $this->protocolField->setSource($this->getProtocolList());
        return $this;
    }

    /**
     * Get the list of protocols to be used by this instance
     *
     * @return array
     */
    public function getProtocolList()
    {
        if (!empty($this->localProtocols)) return $this->localProtocols;
        return self::config()->get('default_protocols');
    }

    /**
     * Quick function to disable the email option
     *
     * @return $this
     */
    public function withoutEmailOption()
    {
        $this->setProtocolList(array_diff($this->getProtocolList(), ['mailto' => true]));
        return $this;
    }

    /**
     * Quick function to enable the email option or change it's title
     *
     * @param string $title
     * @return $this
     */
    public function includeEmailOption($title = 'Email')
    {
        $this->setProtocolList(array_merge($this->getProtocolList(), ['mailto' => $title]));
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
     * Set the field value
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

    /**
     * Converts a full url to the protocol and link array
     *
     * @param $value
     * @return array
     */
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

    /**
     * Error message for an invalid email
     *
     * @return string
     */
    protected function emailErrorMessage()
    {
        return _t(
            'TextLinkField.VALIDATEEMAIL',
            'The value for {name} is not a valid email address',
            ['name' => $this->getName()]
        );
    }

    /**
     * Error message for an invalid url
     *
     * @return string
     */
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
            default:
                return true;
        }

        return true;
    }

}