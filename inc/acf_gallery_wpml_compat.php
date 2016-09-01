<?php

class acf_gallery_wpml_compat
{
  public function __construct($postID)
  {
    $this->postID = $postID;
    add_action('wpml_updated_translation_status', array($this, 'parse_post_fields'), 50);
  }

  /**
   * Parses all field groups attached to a post
   *
   * @method parse_post_fields
   *
   * @return void
   */
  public function parse_post_fields($data = array())
  {
    $groups = acf_get_field_groups();

    foreach ($groups as $group) {
      $this->iterate_fields(acf_get_fields($group));
    }
  }

  /**
   * Iterates over each field attached to a post
   *
   * @method iterate_fields
   *
   * @param  array         $fields attached fields
   *
   * @return void
   */
  protected function iterate_fields($fields)
  {
    foreach ($fields as $field) {
      if (!isset($field['type'])) {
        continue;
      }

      switch ($field['type']) {
        case 'gallery':
          $this->update_gallery_translation($field);
          break;

        case 'flexible_content':
          $this->update_gallery_subfield($field);
          break;

        case 'repeater':
          $this->update_gallery_subfield($field);
          break;
      }
    }
  }

  /**
   * Update gallery subfields that are part of a repeater of a flexible content
   *
   * @method update_gallery_subfield
   *
   * @param  array                  $field the field
   *
   * @return void
   */
  protected function update_gallery_subfield($field)
  {
    while (have_rows($field['name'], $this->postID)) {
      the_row();
      $row = get_row();

      if (isset($row['acf_fc_layout'])) {
        $layout = $row['acf_fc_layout'];
        unset($row['acf_fc_layout']);
      }
      foreach ($row as $fieldID => $values) {
        $fieldDetailed = get_field_object($fieldID);
        $subfieldSelector = array($field['name']);
        if ($fieldDetailed['type'] == 'gallery' && !empty($values)) {
          $subfieldSelector[] = get_row_index();
          $subfieldSelector[] = $fieldID;
          $updatedValues = $this->get_translated_ids($values);

          update_sub_field($subfieldSelector, $updatedValues, $this->postID);
        }
      }
    }
  }

  /**
   * Updates a basic gallery with corresponding translations
   *
   * @method update_gallery_translation
   *
   * @param  array                     $field the field
   *
   * @return void
   */
  protected function update_gallery_translation($field)
  {
    $values = get_field($field['name'], $this->postID, false);
    update_field($field['name'], $this->get_translated_ids($values), $this->postID);
  }

  /**
   * Parse each attachment ID and get its translation ID
   *
   * @method get_translated_ids
   *
   * @param  array             $values attachment IDs
   *
   * @return array             translated attachment IDs
   */
  protected function get_translated_ids($values)
  {
    if (empty($values) || !is_array($values)) {
      return;
    }

    $updatedIDs = array();
    foreach ($values as $value) {
      $updatedIDs[] = icl_object_id($value, 'attachment', true, ICL_LANGUAGE_CODE);
    }

    return $updatedIDs;
  }
}
