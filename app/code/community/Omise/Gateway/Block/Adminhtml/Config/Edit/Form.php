<?php
class Omise_Gateway_Block_Adminhtml_Config_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
    // Instantiate a new form to display our brand for editing.
    $form = new Varien_Data_Form(array( 'id'      => 'edit_form',
                                        'action'  => $this->getUrl('#', array('_current' => true,
                                                                              'continue' => 0)),
                                        'method'  => 'post'));
    $form->setUseContainer(true);
    $this->setForm($form);

    $fieldset = $form->addFieldset('general', array('legend' => $this->__('Brand Details')));

    $this->_addFieldsToFieldset($fieldset, array( 'public_key'  => array( 'label'     => $this->__('Public Key'),
                                                                          'input'     => 'text',
                                                                          'required'  => true),

                                                  'secret_key'  => array( 'label'     => $this->__('Secret Key'),
                                                                          'input'     => 'text',
                                                                          'required'  => true)));

    return $this;
  }

  /**
   * This method makes life a little easier for us by pre-populating
   * fields with $_POST data where applicable and wrapping our post data
   * in 'brandData' so that we can easily separate all relevant information
   * in the controller. You could of course omit this method entirely
   * and call the $fieldset->addField() method directly.
   */
  protected function _addFieldsToFieldset(Varien_Data_Form_Element_Fieldset $fieldset, $fields)
  {
      // $requestData = new Varien_Object($this->getRequest()
      //   ->getPost('brandData'));

    foreach ($fields as $name => $_data) {
      // if ($requestValue = $requestData->getData($name)) {
      //   $_data['value'] = $requestValue;
      // }

      //     // Wrap all fields with brandData group.
      // $_data['name'] = "brandData[$name]";

      //     // Generally, label and title are always the same.
      // $_data['title'] = $_data['label'];

      //     // If no new value exists, use the existing brand data.
      // if (!array_key_exists('value', $_data)) {
      //   $_data['value'] = $this->_getBrand()->getData($name);
      // }

      // Finally, call vanilla functionality to add field.
      $fieldset->addField($name, $_data['input'], $_data);
    }

    return $this;
  }

  /**
   * Retrieve the existing brand for pre-populating the form fields.
   * For a new brand entry, this will return an empty brand object.
   */
  protected function _getBrand()
  {
    if (!$this->hasData('brand')) {
      // This will have been set in the controller.
      $brand = Mage::registry('current_brand');

      // Just in case the controller does not register the brand.
      if (!$brand instanceof SmashingMagazine_BrandDirectory_Model_Brand) {
        $brand = Mage::getModel('smashingmagazine_branddirectory/brand');
      }

      $this->setData('brand', $brand);
    }

    return $this->getData('brand');
  }
}