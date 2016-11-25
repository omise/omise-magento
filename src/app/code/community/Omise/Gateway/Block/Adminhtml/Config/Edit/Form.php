<?php
class Omise_Gateway_Block_Adminhtml_Config_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare a form for setting Omise Keys
     * @return self
     */
    protected function _prepareForm()
    {
        // Instantiate a new form to display our brand for editing.
        $form = new Varien_Data_Form(array( 'id'      => 'edit_form',
                                            'action'  => $this->getUrl('adminhtml/omise/config/edit', array('_current' => true,
                                                                                                            'continue' => 0)),
                                            'method'  => 'post'));
        $form->setUseContainer(true);
        $this->setForm($form);

        /* Field set for Test Keys */
        $fieldset = $form->addFieldset('omise_key_test', array('legend' => $this->__('Test Keys')));
        $this->_addFieldsToFieldset($fieldset, array( 'public_key_test' => array(   'label'     => $this->__('Public Key for Test'),
                                                                                    'input'     => 'password',
                                                                                    'required'  => false),

                                                      'secret_key_test' => array(   'label'     => $this->__('Secret Key for Test'),
                                                                                    'input'     => 'password',
                                                                                    'required'  => false),

                                                      'test_mode'       => array(   'label'     => $this->__('Enable Test mode'),
                                                                                    'input'     => 'checkbox',
                                                                                    'required'  => false,
                                                                                    'onclick'   => 'this.value = this.checked ? 1 : 0;')));

        /* Field set for Live Keys */
        $fieldset = $form->addFieldset('omise_key_live', array('legend' => $this->__('Live Keys')));
        $this->_addFieldsToFieldset($fieldset, array( 'public_key'      => array(   'label'     => $this->__('Public Key'),
                                                                                    'input'     => 'password',
                                                                                    'required'  => true),

                                                      'secret_key'      => array(   'label'     => $this->__('Secret Key'),
                                                                                    'input'     => 'password',
                                                                                    'required'  => true)));

        return $this;
    }

    /**
     * This method makes life a little easier for us by pre-populating
     * fields with $_POST data where applicable and wrapping our post data
     * in 'brandData' so that we can easily separate all relevant information
     * in the controller. You could of course omit this method entirely
     * and call the $fieldset->addField() method directly.
     * @return self
     */
    protected function _addFieldsToFieldset(Varien_Data_Form_Element_Fieldset $fieldset, $fields)
    {
        foreach ($fields as $name => $_data) {
            // Wrap all fields with brandData group.
            $_data['name'] = "configData[$name]";

            // Generally, label and title are always the same.
            $_data['title'] = $_data['label'];

            // If no new value exists, use the existing data.
            if (!array_key_exists('value', $_data)) {
                $_data['value'] = $this->_getValue()->getData($name);
            }

            if ($name == "test_mode") {
                $_data['checked'] = $this->_getValue()->getData($name);
            }

            // Finally, call vanilla functionality to add field.
            $fieldset->addField($name, $_data['input'], $_data);
        }

        return $this;
    }

    /**
     * Retrieve the existing brand for pre-populating the form fields.
     * For a new brand entry, this will return an empty brand object.
     * @return string|int
    */
    protected function _getValue()
    {
        if (!$this->hasData('value')) {
            // This will have been set in the controller.
            $value = Mage::registry('current_value');

            // Just in case the controller does not register the value.
            if (!$value instanceof Omise_Gateway_Model_Config) {
                $value = Mage::getModel('omise_gateway/config');
            }

            $this->setData('value', $value);
        }

        return $this->getData('value');
    }
}