<?php
/**
 * VDateValidator validator for date formated fields/inputs.
 * Validating by formatting a DateTime object attribute
 *
 */
class VDateValidator extends CValidator {

    /**
     * @var string The default date format
     */
    public $format='MM/dd/yyyy';

    /**
     * @var bool Strict compliance date (rule format 99/99/9999)
     */
    public $strict = true;

    /**
     * @var bool Attribute value can be null or empty. The default setting is Means
     * that if the attribute is empty, it is considered valid.
     */
    public $allowEmpty = false;

    /**
     * Validation of attributes of the object.
     * If an error occurs, an error message is added to the object.
     *
     * @param CModel $object The object that is validated
     * @param string $attribute An attribute that is checked
     */
    protected function validateAttribute($object,$attribute)
    {
        $value=$object->$attribute;
        if($this->allowEmpty && $this->isEmpty($value)) {
            return;
        }

        $dateObject = DateTime::createFromFormat($this->format, $value);

        if($this->strict) {
            $errors = DateTime::getLastErrors();
            $valid = is_object($dateObject) && empty($errors['warning_count']);
        } else {
            $valid = is_object($dateObject);
        }

        if(!$valid) {
            $message=$this->message!==null?$this->message : Yii::t('yii','The format of {attribute} is invalid.');
            $this->addError($object,$attribute,$message);
        }
    }
}