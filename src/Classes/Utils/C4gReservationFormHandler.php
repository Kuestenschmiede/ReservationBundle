<?php

namespace con4gis\ReservationBundle\Classes\Utils;

use con4gis\ProjectsBundle\Classes\Conditions\C4GBrickCondition;
use con4gis\ProjectsBundle\Classes\Conditions\C4GBrickConditionType;
use con4gis\ProjectsBundle\Classes\Dialogs\C4GBrickDialogParams;
use con4gis\ProjectsBundle\Classes\Dialogs\C4GDialogChangeHandler;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GInfoTextField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GKeyField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GSelectField;
use con4gis\ReservationBundle\Controller\C4gReservationController;
use Contao\ModuleModel;

class C4gReservationFormHandler
{
    protected $module = null;
    protected $fieldList = [];
    protected $typeObject = [];
    protected $dialogParams = null;
    protected $condition = null;
    protected $initialValues = null;

    /**
     * @param ModuleModel $module
     */
    public function __construct(
        C4gReservationController &$module,
        Array &$fieldList,
        Array &$typeObject,
        C4GBrickDialogParams &$dialogParams,
        C4gReservationInitialValues &$initialValues)
    {
        $this->module = $module;
        $this->fieldList = $fieldList;
        $this->typeObject = $typeObject;
        $this->dialogParams = $dialogParams;
        $this->initialValues = $initialValues;

        $this->condition = new C4GBrickCondition(
            C4GBrickConditionType::VALUESWITCH,
            'reservation_type',
            $typeObject['id']
        );
    }

    /**
     * @return void
     */
    public function addFields() {

    }

    /**
     * @return ModuleModel|null
     */
    public function getModule(): ?ModuleModel
    {
        return $this->module;
    }

    /**
     * @param ModuleModel|null $module
     */
    public function setModule(?ModuleModel $module): void
    {
        $this->module = $module;
    }

    /**
     * @return array
     */
    public function getFieldList(): array
    {
        return $this->fieldList;
    }

    /**
     * @param array $fieldList
     */
    public function setFieldList(array $fieldList): void
    {
        $this->fieldList = $fieldList;
    }

    /**
     * @return array
     */
    public function getTypeObject(): array
    {
        return $this->typeObject;
    }

    /**
     * @param array $typeObject
     */
    public function setTypeObject(array $typeObject): void
    {
        $this->typeObject = $typeObject;
    }

    /**
     * @return C4GDialogChangeHandler|null
     */
    public function getDialogParams(): ?C4GDialogChangeHandler
    {
        return $this->dialogParams;
    }

    /**
     * @param C4GDialogChangeHandler|null $dialogParams
     */
    public function setDialogParams(?C4GDialogChangeHandler $dialogParams): void
    {
        $this->dialogParams = $dialogParams;
    }

    /**
     * @return C4GBrickCondition|null
     */
    public function getCondition(): ?C4GBrickCondition
    {
        return $this->condition;
    }

    /**
     * @param C4GBrickCondition|null $condition
     */
    public function setCondition(?C4GBrickCondition $condition): void
    {
        $this->condition = $condition;
    }

    /**
     * @return C4gReservationInitialValues|null
     */
    public function getInitialValues(): ?C4gReservationInitialValues
    {
        return $this->initialValues;
    }

    /**
     * @param C4gReservationInitialValues|null $initialValues
     */
    public function setInitialValues(?C4gReservationInitialValues $initialValues): void
    {
        $this->initialValues = $initialValues;
    }
}